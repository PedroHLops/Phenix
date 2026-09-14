<?php

declare(strict_types=1);

namespace Phenix\Analyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\Greater;
use PhpParser\Node\Expr\BinaryOp\GreaterOrEqual;
use PhpParser\Node\Expr\BinaryOp\Minus;
use PhpParser\Node\Expr\BinaryOp\Plus;
use PhpParser\Node\Expr\BinaryOp\Smaller;
use PhpParser\Node\Expr\BinaryOp\SmallerOrEqual;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Scalar\String_;

final class ExpressionAnalyzer
{
    public function analyze(Expr $expression): AnalyzedNode
    {
        $children = [];
        $value = null;

        if ($expression instanceof BinaryOp) {
            $children[] = $this->analyze($expression->left);
            $children[] = $this->analyze($expression->right);
        }

        if ($expression instanceof FuncCall) {
            if ($expression->name instanceof Name) {
                $value = $expression->name->toString();
            }

            foreach ($expression->args as $argument) {
                $children[] = $this->analyze($argument->value);
            }
        }

        if ($expression instanceof Variable) {
            $value = $expression->name;
        }

        if ($expression instanceof String_) {
            $value = $expression->value;
        }

        if ($expression instanceof Int_) {
            $value = (string) $expression->value;
        }


        return new AnalyzedNode(
            type: $this->resolveType($expression),
            children: $children,
            kind: NodeKind::EXPRESSION,
            value: $value,
        );
    }

    public function resolveType(Expr $expression): string
    {
        return match (true) {
            $expression instanceof BinaryOp\Identical => 'Identical',
            BinaryOp\Concat::class === $expression::class => 'Concat',
            Variable::class === $expression::class => 'Variable',
            FuncCall::class === $expression::class => 'FuncCall',
            String_::class === $expression::class => 'String_',
            Int_::class === $expression::class => 'Int_',
            Plus::class === $expression::class => 'Plus',
            Minus::class === $expression::class => 'Minus',
            Equal::class ===  $expression::class => 'Equal',
            Greater::class ===  $expression::class => 'Greater',
            Smaller::class ===  $expression::class => 'Smaller',
            GreaterOrEqual::class ===  $expression::class => 'GreaterOrEqual',
            SmallerOrEqual::class ===  $expression::class => 'SmallerOrEqual',
            default => $expression::class
        };
    }
}