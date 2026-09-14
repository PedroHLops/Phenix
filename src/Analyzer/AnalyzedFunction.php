<?php

declare(strict_types=1);

namespace Phenix\Analyzer;

final readonly class AnalyzedFunction
{
    public function __construct(
        public string $name,
        public array $parameters,
        public ?string $returnType,
        public Type $type,
        public array $bodyNodes,
    ) {
    }
}