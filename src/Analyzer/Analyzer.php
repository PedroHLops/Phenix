<?php

declare(strict_types=1);

namespace Phenix\Analyzer;

use PhpParser\Node;
use PhpParser\Node\Identifier;
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
                parameters: $this->analyzeParameters($node),
                returnType: $this->analyzeReturnType($node),
                type: $this->attributeResolver->resolve($node),
                bodyNodes: $this->bodyAnalyzer->analyze($node)
            );
        }

        return $functions;
    }

    /**
     * @return AnalyzedParameter[]
     */
    private function analyzeParameters(Function_ $function): array
    {
        $parameters = [];

        foreach ($function->params as $parameter) {
            $type = null;

            if ($parameter->type instanceof Identifier) {
                $type = $parameter->type->toString();
            }

            $parameters[] = new AnalyzedParameter(
                name: (string) $parameter->var->name,
                type: $type,
            );
        }

        return $parameters;
    }

    private function analyzeReturnType(Function_ $function): ?string
    {
        if ($function->returnType instanceof Identifier) {
            return $function->returnType->toString();
        }

        return null;
    }
}