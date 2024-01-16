<?php
declare(strict_types=1);

namespace Apex\Docs;

use Apex\Docs\Lib\{DocBlock, MethodParameter, Method};

/**
 * Method generator
 */
class MethodGenerator
{

    /**
     * Generate method
     */
    public function generate(\ReflectionMethod $method, string $dest_dir, string $theme = 'html', bool $include_file_ext = false, array $see_also = []): Method
    {

        // GEt html
        $ext = $theme == 'markdown' ? '.md' : '.html';
        $html = file_get_contents(__DIR__ . '/../themes/' . $theme . '/method' . $ext);
        if (!preg_match("/<parameters>(.*?)<\/parameters>/si", $html, $param_match)) {
            throw new \Exception("The /themes/$theme/method.html file does not contain a set of <parameters> tags.");
        }

        // Get doc block
        $doc = new DocBlock($method);
        $obj = $method->getDeclaringClass();

        // Start usage line
        $return_type = $this->getTypeName($method->getReturnType()) . ' ';
        $usage = $return_type;
        $usage .= $obj->getShortName() . '::' . $method->getName() . '(';

        // GO through parameters
        $sig_params = [];
        $param_html = '';
        foreach ($method->getParameters() as $param) {

            // Parse paramter
            $res = $this->parseParameter($param, $doc);
            $sig_params[] = $res->signature;

            // Add to param html
            $param_replace = [
                '~name~' => $res->name,
                '~type~' => $res->type,
                '~required~' => $res->is_required === true ? 'Yes' : 'No',
                '~description~' => $res->description
            ];

            // Change required for Syrus theme
            if ($theme == 'syrus') {
                $param_replace['~required~'] = $res->is_required === true ? '1' : '0';
            }
            $param_html .= rtrim(strtr($param_match[1], $param_replace));
        }
        $usage .= implode(', ', $sig_params) . ')';

        // Replace param html as necessary
        if ($param_html == '') {
            $html = preg_replace("/<if_has_params>(.*?)<\/if_has_params>/si", '', $html);
        } else {
            $html = str_replace("\n" . $param_match[0], rtrim($param_html), $html);
            $html = preg_replace("/<(\/?)if_has_params>\n/", "", $html);
        }

        // Process has return
        $return = $doc->return == '' ? $return_type : $doc->return;
        if ($return == '' || $return == 'video') {
            $html = preg_replace("/<if_has_return>(.*?)<\/if_has_return>\n/si", "", $html);
        } else {
            $html = preg_replace("/<(\/?)if_has_return>\n/", "", $html);
        }

        // Replace base merge variables
        $replace = [
            '~title~' => $doc->title == '' ? '' : ' - ' . $doc->title,
            '~class_name`' => $obj->name,
            '~class_short_name~' => $obj->getShortName(),
            '~method_name~' => $method->getName(),
            '~usage~' => $usage,
            '~description~' => $doc->description,
            '~return~' => $return,
            '~see_also~' => $this->createSeeAlso($method, $obj, $theme, $include_file_ext, $see_also)
        ];
        $html = strtr($html, $replace);

        // Save html
        list($visibility, $Is_static) = $this->getVisibility($method);
        $filename = $dest_dir . '/' . strtolower($method->getName()) . $ext;
        file_put_contents($filename, $html);

        // Create method instance
        $res = new Method(
            name: $method->getName(),
            title: $doc->title,
            description: $doc->description,
            visibility: $this->getVisibility($method),
            is_static: $method->isStatic(),
            signature: implode(", ", $sig_params),
            return_type: $return_type
        );

        // Return
        return $res;
    }

    /**
     * Get type name
     */
    private function getTypeName(?\ReflectionType $type):string
    {

        // Check for null
        if ($type === null) { 
            return '';
        }

        // Get name
        if (method_exists($type, 'getTypes')) {

            $names = [];
            foreach ($type->getTypes() as $t) {
                $names[] = $this->getTypeName($t);
            }
            return implode(' | ', $names);

        } else {
            $name = $type->getName();
        }
        if ($type->isBuiltin() === false) {
            $parts = explode("\\", $name);
            $name = array_pop($parts);
        }

        // Check for null
        if ($type->allowsNull() === true) {
            $name = '?' . $name;
        }

        // Return
        return $name;
    }

    /**
     * Get param signature
     */
    private function parseParameter(\ReflectionParameter $param, DocBlock $doc): MethodParameter
    {

        // Get type
        if ($param->isVariadic() === true) { 
            $type = '...';
        } else { 
            $type = $this->getTypeName($param->getType());
        }

        // Start sig
        $sig = $type . ' $' . $param->getName();

        // Add default value
        $def = '';
        if ($param->isDefaultValueAvailable() === true) {
            $def = $param->getDefaultValue();
            if (is_bool($def) && $def === true) {
                $def = 'true';
            } elseif (is_bool($def) && $def === false) {
                $def = 'false';
            } elseif ($def === null) {
                $def = 'null';
            } elseif ($param->isDefaultValueConstant()) {
                $def = '';
            } else {
                $def = is_array($def) ? '[]' : "'" . $def . "'";
            }
            $sig .= " = " . $def;
        }

        // Check is optional
        if ($param->isOptional() === true) {
            $sig = '[ ' . $sig . ' ]';
        }

        // Instantiate method param
        $name = $param->getName();
        $res = new MethodParameter(
            name: $name,
            type: $type,
            default_value: $def,
            is_required: $param->isOptional() === true ? false : true,
            allow_nulls: $param->allowsNull(),
            description: isset($doc->params[$name]) ? $doc->params[$name]['description'] : '',
            signature: $sig
        );

        // Return
        return $res;
    }

    /**
     * Generate see also
     */
    private function createSeeAlso(\ReflectionMethod $method, \ReflectionClass $obj, string $theme, bool $include_file_ext = false, array $see_also = []): string
    {

        // Init
        $ext = $theme == 'markdown' ? '.md' : '.html';

        // GO through methods
        $also_names = [];
        foreach ($obj->getMethods() as $also) {

            if ($also->getName() == $method->getName() || $also->getName() == '__construct') {
                continue;
            } elseif (count($see_also) > 0 && !in_array($also->getName(), $see_also)) {
                continue;
            }
            $also_names[] = $also->getName();
        }

        // Create see also html
        $also_html = '';
        sort($also_names);
        foreach ($also_names as $name) {

            // Get filename
            $filename = strtolower($name);
            if ($include_file_ext === true) {
                $filename .= $ext;
            }

            // Add html
            if ($theme == 'markdown') {
                $also_html .= "* [" . $name . "()](" . $filename . ")\n";
            } elseif ($theme == 'syrus') {
                $also_html .= "    <s:also>[" . $name . "()](" . $filename . ")</s:also>\n";
                //$also_html .= "    <s:also>$name</s:also>\n";
            } else {
                $also_html .= "    <li><a href=\"" . $filename . "\">" . $name . "()</a></li>\n";
            }
        }

        // Return
        return rtrim($also_html);
    }

    /**
     * Get visibility
     */
    private function getVisibility(\ReflectionMethod $method): string
    {

        // Get visilibty
        $visibility = 'public';
        if ($method->isPrivate() === true) {
            $visibility = 'private';
        } elseif ($method->isProtected() === true) {
            $visiblity = 'protected';
        }

        // Return
        return $visibility;
    }

}

