<?php
declare(strict_types=1);

namespace Apex\Docs;

/**
 * Index generatr
 */
class IndexGenerator
{

    /**
     * Generate index for directory of documentation
     */
    public function generate(array $class_names, string $dest_dir, string $base_namespace, string $base_uri = '/docs/', string $theme = 'html'): void
    {

        // Get html
        $ext = $theme == 'markdown' ? '.md' : '.html';
        $html = file_get_contents(__DIR__ . '/../themes/' . $theme . '/index' . $ext);
        $base_uri = rtrim($base_uri, '/');

        // Sort class names
        sort($class_names);
        $results = [];
        $base_namespace = trim($base_namespace, "\\");

        // Filter class names
        foreach ($class_names as $name) {
            $name = trim(str_replace($base_namespace, "", $name), "\\");
            $parts = explode("\\", $name);
            array_pop($parts);
            $parent = implode("\\", $parts);
            $results[$parent][] = $name;
        }

        // Generate html
        $contents = '';
        foreach ($results as $parent => $classes) {

            // Get indent
            $level = substr_count(trim($parent, "\\"), "\\");
            $indent = str_repeat(' ', ($level * 4));

            // Single class
            if (count($classes) == 1) {
                $path = $base_uri . '/' . strtolower(str_replace("\\", "/", $classes[0])) . "/";
                if ($theme == 'html') {
                    $contents .= $indent . "<li><a href=\"$path\">$classes[0]</a></li>\n";
                } else {
                    $contents .= $indent . "* [" . $classes[0] . "](" . $classes[0] . ")\n";
                }
                continue;
            }

            // Process parent namespace as necessary
            if ($parent != '') {

                // Generate index for parent
                $parent_uri = $base_uri . '/' . strtolower(str_replace("\\", "/", $parent) . '/');
                $parent_dest_dir = $dest_dir . '/' . strtolower(str_replace("\\", "/", $parent));
                $this->generate($classes, $parent_dest_dir, $parent, $parent_uri, $theme);

                // Add unordered list, if html
                if ($theme == 'html') {
                    $contents .= $indent . "<li><a href=\"" . $parent_uri . "index.html\">$parent\\</a></li>\n";
                    $contents .= $indent . "<ul>\n";
                } else {
                    $contents .= $indent . "* [$parent\\\\]($parent_uri)\n";
                }
            }

            // Add multiple classes
            foreach ($classes as $class) {
                $path = $base_uri . '/' . strtolower(str_replace("\\", "/", $class)) . '/';
                if ($theme == 'html') {
                    $path .= 'index.html';
                }
                $class = trim(str_replace($parent, "", $class), "\\");

                if ($theme == 'html') {
                    $contents .= "    " . $indent . "<li><a href=\"$path\">$class</a></li>\n";
                } else {
                    $contents .= $indent . "* [" . $class . "](" . $path . ")\n";
                }
            }

            // Close list, if needed
            if ($theme == 'html') {
                $contents .= $indent . "</ul>\n";
            }
        }

        // Save HTML
        $html = str_replace("~base_namespace~", $base_namespace, $html);
        $html = str_replace("~contents~", $contents, $html);
        file_put_contents("$dest_dir/index" . $ext, $html);
    }

}


