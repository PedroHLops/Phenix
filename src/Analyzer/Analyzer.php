<?php

declare(strict_types=1);

namespace Phenix\Analyzer;

use PhpParser\Node;
use PhpParser\Node\Stmt\Function_;

final class Analyzer
{
    public function __construct(
        private AttributeResolver $attributeResolver,
        private BodyAnalyzer $bodyAnalyzer
    ) {
    }

    /**
     * @param Node[] $ast
     * @return AnalyzedFunction[]
     */
    public function analyze(array $ast): array
    {
        $functions = [];

        foreach ($ast as $node) {
            if (!$node instanceof Function_) {
                continue;
            }

            $functions[] = new AnalyzedFunction(
                name: $node->name->toString(),
                parameters: count($node->params),
                type: $this->attributeResolver->resolve($node),
                bodyNodes: $this->bodyAnalyzer->analyze($node)
            );
        }

        return $functions;
    }
}