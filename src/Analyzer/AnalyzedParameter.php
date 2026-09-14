<?php

declare(strict_types=1);

namespace Phenix\Analyzer;

final readonly class AnalyzedParameter
{
    public function __construct(
        public string $name,
        public ?string $type = null,
    ) {
    }
}