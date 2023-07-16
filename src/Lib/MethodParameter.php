<?php
declare(strict_types=1);

namespace Apex\Docs\Lib;

/**
 * Method parameter
 */
class MethodParameter
{

    /**
     * Constructor
     */
    public function __construct(
        public readonly string $name,
        public readonly string $type,
        public readonly string $default_value,
        public readonly bool $allow_nulls, 
        public readonly bool $is_required,
        public readonly string $description,
        public readonly string $signature
    ) {

    }

}


