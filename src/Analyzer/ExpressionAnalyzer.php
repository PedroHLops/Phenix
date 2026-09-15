<?php

declare(strict_types=1);

namespace Phenix\Analyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\BinaryOp\Div;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\Greater;
use PhpParser\Node\Expr\BinaryOp\GreaterOrEqual;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\Minus;
use PhpParser\Node\Expr\BinaryOp\Mul;
use PhpParser\Node\Expr\BinaryOp\Plus;
use PhpParser\Node\Expr\BinaryOp\Smaller;
use PhpParser\Node\Expr\BinaryOp\SmallerOrEqual;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Cast\Bool_;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PostDec;
use PhpParser\Node\Expr\PostInc;
use PhpParser\Node\Expr\PreDec;
use PhpParser\Node\Expr\PreInc;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\Continue_;


final class ExpressionAnalyzer
{
    public function analyze(Expr $expression): AnalyzedNode
    {
        $children = [];
        $value = null;


        if ($expression instanceof BooleanNot) {
            $children[] = $this->analyze($expression->expr);
        } elseif ($expression instanceof BinaryOp) {
            $children[] = $this->analyze($expression->left);
            $children[] = $this->analyze($expression->right);
        }

        if ($expression instanceof ArrayDimFetch) {
            $children[] = $this->analyze($expression->var);

            if ($expression->dim instanceof Expr) {
                $children[] = $this->analyze($expression->dim);
            }
        }

        if ($expression instanceof PropertyFetch) {
            $children[] = $this->analyze($expression->var);

            $children[] = new AnalyzedNode(
                type: 'Property',
                kind: NodeKind::EXPRESSION,
                value: $expression->name->toString(),
            );
        }

        if ($expression instanceof MethodCall) {
            $children[] = $this->analyze($expression->var);

            $children[] = new AnalyzedNode(
                type: 'Method',
                kind: NodeKind::EXPRESSION,
                value: $expression->name->toString(),
            );

            foreach ($expression->args as $argument) {
                $children[] = $this->analyze($argument->value);
            }
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

        if ($expression instanceof ConstFetch) {
            $value = (string) $expression->name;
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
            Identical::class === $expression::class => 'Identical',
            Concat::class === $expression::class => 'Concat',
            Variable::class === $expression::class => 'Variable',
            FuncCall::class === $expression::class => 'FuncCall',
            String_::class === $expression::class => 'String_',
            Int_::class === $expression::class => 'Int_',
            Plus::class === $expression::class => 'Plus',
            Minus::class === $expression::class => 'Minus',
            Div::class === $expression::class => 'Div',
            Mul::class === $expression::class => 'Mul',
            Equal::class === $expression::class => 'Equal',
            Greater::class === $expression::class => 'Greater',
            Smaller::class === $expression::class => 'Smaller',
            GreaterOrEqual::class === $expression::class => 'GreaterOrEqual',
            SmallerOrEqual::class === $expression::class => 'SmallerOrEqual',
            Bool_::class === $expression::class => 'Bool_',
            BooleanAnd::class === $expression::class => 'BooleanAnd',
            BooleanNot::class === $expression::class => 'BooleanNot',
            BooleanOr::class === $expression::class => 'BooleanOr',
            $expression instanceof ConstFetch && in_array(strtolower($expression->name->toString()), ['true', 'false'], true) => 'Boolean_',
            ArrayDimFetch::class === $expression::class => 'ArrayDimFetch',
            PropertyFetch::class === $expression::class => 'PropertyFetch',
            MethodCall::class === $expression::class => 'MethodCall',
            PostInc::class === $expression::class => 'PostInc',
            PostDec::class === $expression::class => 'PostDec',
            PreInc::class === $expression::class => 'PreInc',
            PreDec::class === $expression::class => 'PreDec',
            Assign::class === $expression::class => 'Assign',
            default => $expression::class
        };
    }
}