<?php
declare(strict_types=1);

namespace Apex\Docs\Lib;

/**
 * Dock block
 */
class DocBlock
{

    // Properties
    public readonly string $body;
    public readonly string $title;
    public readonly string $description;
    public readonly array $params;
    public readonly string $return;
    private ?array $lines = [];

    /**
     * Constructor
     */
    public function __construct(\ReflectionClass | \ReflectionMethod | \ReflectionFUnction $obj)
    {

        // Get lines
        if (!$this->lines = $this->getLines($obj)) {
            return;
        }

        // Set title and description
        $this->setDescription();

        // Parse annotations
        $this->parseAnnotations();
    }

    /**
     * Get lines
     */
    private function getLines(\ReflectionClass | \ReflectionMethod | \ReflectionFunction $obj): ?array
    {

        // Check for doc comment
        if (!$contents = $obj->getDocComment()) {
            $this->blankProperties();
            return null;
        }

        // Parse lines
        $lines = array_map(
            fn ($line) => trim(preg_replace("/^(\/\*\*|\*|\*\/)/", "", trim($line))), 
            explode("\n", $contents)
        );

        // Get rid of first and last lines
        array_shift($lines);
        array_pop($lines);

        // Return
        return $lines;
    }

    /**
     * Set title and description
     */
    private function setDescription(): void
    {

        // Get plain text
        $first = $this->getPlainText();
        $second = $this->getPlainText();

        // Set title and description
        if ($second != '') {
            $this->title = $first;
            $this->description = $second;
        } else {
            $this->title = '';
            $this->description = $first;
        }

    }

    /**
     * Parse annotations
     */
    private function parseAnnotations(): void
    {

        // Initialize
        $type = '';
        $data_type = '';
        $name = '';
        $description = '';
        $return = '';
        $params = [];;

        // Go through lines
        foreach ($this->lines as $line) {

            // Finish line, if needed
            if (($line == '' || str_starts_with($line, '@')) && $type != '') {

                if ($type == 'return') {
                    $return = $description;
                } elseif ($type == 'param') {
                    $params[$name] = [
                        'type' => $type,
                        'data_type' => $data_type,
                        'description' => $description
                    ];
                }

                // Blank variables
                $type = '';
                $data_type = '';
                $name = '';
                $description = '';
            }

            // Start new line
            if (preg_match("/^\@(\w+?)\W(.*?)$/", $line, $match)) {

                if ($match[1] != 'param' && $match[1] != 'return') {
                    continue;
                }
                $type = $match[1];
                $description = preg_replace("/^\@(\w+?)\W/", "", $line);

                // Parse param, if needed
                if ($type == 'param') {
                    $vars = preg_split("/\W+/", $description, 3);
                    $data_type = $vars[0];
                    $name = str_replace("\$", '', $vars[1]);
                    $description = $vars[2];
                }
            } else if ($type == 'param' || $type == 'return') {
                $description .= ' ' . trim($line);
            }
        }

        // Check for last annotation
        if ($type == 'return') {
            $return = $description;
        } elseif ($type == 'param') {
            $params[$name] = [
                'type' => $type,
                'data_type' => $data_type,
                'description' => $description
            ];
        }

        // Set params
        $this->params = $params;
        $this->return = $return;
    }
    /**
     * BLank properties
     */
    private function blankProperties(): void
    {
        $this->body = '';
        $this->title = '';
        $this->description = '';
        $this->params = [];
        $this->return = '';
    }

    /**
     * Strip top blank lines
     */
    private function getPlainText(): string
    {

        // Check for null lines
        if ($this->lines === null) {
            return '';
        }

        while (count($this->lines) > 0 && $this->lines[0] == '') {
            array_shift($this->lines);
        }

        // Get text
        $text = '';
        while (count($this->lines) > 0 && $this->lines[0] != '' && !str_starts_with($this->lines[0], '@')) {
            $text .= $this->lines[0] . ' ';
            array_shift($this->lines);
        }

        // Return
        return trim($text);
    }



}


