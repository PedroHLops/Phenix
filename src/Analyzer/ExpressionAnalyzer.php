<?php

declare(strict_types=1);

namespace Phenix\Analyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp;

final class ExpressionAnalyzer
{
    public function analyze(Expr $expression): AnalyzedNode
    {
        $children = [];

        if ($expression instanceof BinaryOp) {
            $children[] = $this->analyze($expression->left);
            $children[] = $this->analyze($expression->right);
        }

        return new AnalyzedNode(
            type: $expression::class,
            children: $children,
        );
    }
}