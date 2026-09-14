<?php

declare(strict_types=1);

namespace Phenix\Analyzer;

final readonly class AnalyzedNode
{
    /**
     * @param AnalyzedNode[] $children
     */
    public function __construct(
        public string $type,
        public array $children = [],
    ) {
    }
}