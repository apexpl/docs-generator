<?php
declare(strict_types=1);

namespace Apex\Docs\Lib;

/**
 * Method
 */
class Method
{

    /**
     * Constructor
     */
    public function __construct(
        public readonly string $name,
        public readonly string $title,
        public readonly string $description,
        public readonly string $visibility,
        public readonly bool $is_static,
        public readonly string $signature,
        public readonly string $return_type
    ) {

    }

    /**
     * toArray
     */
    public function toArray(): array
    {

        // Set vars
        $vars = [];
        foreach ($this as $key => $value) {
            $vars['~' . $key . '~'] = $value;
        }

        // Set additional vars
        $vars['~is_static_string~'] = $this->is_static === true ? ' static' : '';
        $vars['~is_static~'] = $this->is_static === true ? '1' : '0';
        $vars['~name_lower~'] = strtolower($this->name);
        $vars['~signature~'] = str_replace("\$", "&#36;", $this->signature);
        // Return
        return $vars;
    }

}


