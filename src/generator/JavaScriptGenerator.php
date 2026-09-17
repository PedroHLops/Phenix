<?php

declare(strict_types=1);

namespace Phenix\Generator;

use Phenix\Analyzer\AnalyzedFunction;
use Phenix\Analyzer\AnalyzedNode;

final class JavaScriptGenerator
{
    public function generate(AnalyzedFunction $function): string
    {
        $parameters = array_map(
            fn($parameter) => $parameter->name,
            $function->parameters
        );

        $output = 'function ';
        $output .= $function->name;
        $output .= '(' . implode(', ', $parameters) . ')';
        $output .= " {\n";

        foreach ($function->bodyNodes as $node) {
            $output .= $this->generateNode($node, 1);
        }

        $output .= "}\n";

        return $output;
    }

    private function generateNode(AnalyzedNode $node, int $indent): string
    {
        $spaces = str_repeat('    ', $indent);

        if ($node->type === 'Return_') {
            $expression = $node->children[0];

            return $spaces . 'return ' . $this->generateExpression($expression) . ";\n";
        }

        return '';
    }

    private function generateExpression(AnalyzedNode $node): string
    {
        return match ($node->type) {
            'Variable' => $node->value ?? '',
            'Int_' => $node->value ?? '0',
            'String_' => '"' . addslashes($node->value ?? '') . '"',
            default => throw new \LogicException("Expressão não suportada pelo gerador: {$node->type}"),
        };
    }
}