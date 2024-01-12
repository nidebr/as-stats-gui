<?php

declare(strict_types=1);

namespace App\Util\Annotation;

#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class Menu
{
    public function __construct(
        private readonly string $domain,
    ) {
    }

    public function getDomain(): string
    {
        return $this->domain;
    }
}
