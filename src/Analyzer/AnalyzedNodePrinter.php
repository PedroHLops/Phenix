<?php

declare(strict_types=1);

namespace Phenix\Analyzer;

final class AnalyzedNodePrinter
{
    /**
     * @param AnalyzedNode[] $nodes
     */
    public function print(array $nodes): string
    {
        $lines = [];

        foreach ($nodes as $index => $node) {
            $this->appendNode(
                $lines,
                $node,
                '',
                $index === array_key_last($nodes)
            );
        }

        return implode(PHP_EOL, $lines);
    }

    /**
     * @param array<int, string> $lines
     */
    private function appendNode(
        array &$lines,
        AnalyzedNode $node,
        string $prefix,
        bool $isLast,
    ): void {
        $branch = $isLast ? '└── ' : '├── ';

        $value = $node->value !== null
            ? ' [' . $node->value . ']'
            : '';

        $lines[] = $prefix
            . $branch
            . $node->type
            . ' <' . $node->kind->value . '>'
            . $value;

        $childPrefix = $prefix . ($isLast ? '    ' : '│   ');

        foreach ($node->children as $index => $child) {
            $this->appendNode(
                $lines,
                $child,
                $childPrefix,
                $index === array_key_last($node->children)
            );
        }
    }
}