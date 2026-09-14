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
        public NodeKind $kind,
        public array $children = [],
        public ?string $value = null,
    ) {
    }
}