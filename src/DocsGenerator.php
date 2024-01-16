<?php
declare(strict_types=1);

namespace Apex\Docs;

use Apex\Docs\Lib\{Io, DocBlock};

/**
 Generate developer documentation for all PHP classes and methods.
 */
class DocsGenerator
{

    /**
     * Generate documentation for a single class and all its methods.
     *
     * @param string $class_name Fully qualified class name to generate docs for.
     * @param string $dest_dir Destination directory to save documenation files.
     * @param string $base_uri The base URI corresponding to the destination directory, used to correctly link to other pages.
     * @param string $theme Theme to use, sub-directory of /themes/ directory.  Supported values are -- html, markdown, syrus.
    */
    public function generateClass(string $class_name, string $dest_dir, string $base_uri = '/docs/', string $theme = 'html', bool $include_file_ext = false, array $see_also = []): void
    {

        // Ensure class exists
        if ((!class_exists($class_name)) && (!interface_exists($class_name))) {
            throw new \Exception("No class exists at, $class_name");
        }
        $base_uri = rtrim($base_uri, '/');

        // Get html
        $ext = $theme == 'markdown' ? '.md' : '.html';
        $html = file_get_contents(__DIR__ . '/../themes/' . $theme . '/class' . $ext);

        // Create destination directory
        $io = new Io();
        $dest_dir = rtrim($dest_dir, '/');
        $io->createBlankDir($dest_dir);

        // Load class, get class doc block
        $obj = new \ReflectionClass($class_name);
        $doc = new DocBlock($obj);
        $method_generator = new MethodGenerator();

        // Go through methods
        $toc = [];
        foreach ($obj->getMethods() as $method) {
            $res = $method_generator->generate($method, $dest_dir, $theme, $include_file_ext, $see_also);
            $toc[$res->name] = $res;
        }

        // Sort method names
        $method_names = array_keys($toc);
        sort($method_names);

        // Get method html
        if (!preg_match("/<methods>(.*?)<\/methods>/si", $html, $method_match)) {
            throw new \Exception("No 'methods' HTML tag exists within the class theme template.");
        }

        // Compile list of methods
        $methods_html = '';
        foreach ($method_names as $name) {
            $methods_html .= trim(strtr($method_match[1], $toc[$name]->toArray($include_file_ext, $ext)));
            if ($include_file_ext === true) { 
                $methods_html .= $ext;
        }
            $methods_html .= "\n";

        }
        $html = str_replace($method_match[0], rtrim($methods_html), $html);

        // Set basic replace vars
        $replace = [
            '~class_name~' => $obj->name,
            '~title~' => $doc->title,
            '~description~' => $doc->description,
            //'~base_uri~' => $base_uri
            '~base_uri~' => ''
        ];
        $html = strtr($html, $replace);

        // Save html file
        $filename = "$dest_dir/index" . $ext;
        file_put_contents($filename, $html);
    }

    /**
     * Generate documentation for all PHP classes within a directory.
     *
     * @param string $source_dir Directory to generate documentation for.
     * @param string $dest_dir * Destination directory to save documentation files.
     * @param string $base_namespace The namespace that corresponds to the source directory.
     * @param string $base_uri The base URI corresponding to the destination directory, used to correctly link to other pages.
     * @param string $theme Theme to use, sub-directory of /themes/ directory.  Supported values are -- html, markdown, syrus.
     */
    public function generateDirectory(string $source_dir, string $dest_dir, string $base_namespace, string $base_uri = '/docs/', string $theme = 'html', bool $include_file_ext = false): void
    {

        // Ensure source dir exists
        if (!is_dir($source_dir)) {
            throw new \Exception("Source dir does not exist at, $source_dir");
        }
        $source_dir = rtrim($source_dir);
        $base_uri = rtrim($base_uri, '/');

        // Create destination directory
        $io = new Io();
        $dest_dir = rtrim($dest_dir, '/');
        $io->createBlankDir($dest_dir);

        // Go through all files
        $classes = [];
        $files = $io->parseDir($source_dir);
        foreach ($files as $file) {

            // Check for .php
            if (!str_ends_with($file, '.php')) {
                continue;
            }

            // Extract class name
            if (!$class_name = $this->extractClassName("$source_dir/$file")) {
                continue;
            }
            $classes[] = $class_name;

            // Generate class
            $class_dir = $dest_dir . '/' . strtolower(preg_replace("/\.php$/", "", ltrim($file, '/')));
            $class_uri = $base_uri . '/' . strtolower(preg_replace("/\.php$/", "", ltrim($file, '/')));
            $this->generateClass($class_name, $class_dir, $class_uri, $theme, $include_file_ext);
        }

        // Generate index
        $index_gen = new IndexGenerator();
        $index_gen->generate($classes, $dest_dir, $base_namespace, $base_uri, $theme, $include_file_ext);
    }

    /**
     * Extract class name
     */
    private function extractClassName(string $filename): ?string
    {

        // Get contents
        $contents = file_get_contents($filename);

        // Check for namespace
        if (!preg_match("/\nnamespace (.*?);/", $contents, $match)) {
            return null;
        }
        $class_name = $match[1];

        // Get class name
        if (!preg_match("/\nclass (.+?)\W/", $contents, $match)) {
            return null;
        }
        $class_name .= "\\" . $match[1];

        // Check class exists
        if ((!class_exists($class_name)) && (!interface_exists($class_name))) {
            return null;
        }

        // Return
        return $class_name;
    }

}


