<?php

declare(strict_types=1);

namespace Phenix\Analyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;

final class BodyAnalyzer
{
    public function __construct(
        private ExpressionAnalyzer $expressionAnalyzer,
    ) {
    }

    /**
     * @return array<int, string>
     */
    public function analyze(Function_ $function): array
    {
        return $this->analyzeStatements($function->stmts ?? []);
    }

    /**
     * @param array<int, \PhpParser\Node\Stmt> $statements
     * @return array<int, string>
     */
    private function analyzeStatements(array $statements): array
    {
        $nodes = [];

        foreach ($statements as $statement) {
            if ($statement instanceof Return_) {
                if ($statement->expr instanceof Expr) {
                    foreach (
                        $this->expressionAnalyzer->analyze($statement->expr)
                        as $expressionNode
                    ) {
                        $nodes[] = $expressionNode;
                    }
                }

                continue;
            }

            if ($statement instanceof If_) {
                $nodes[] = $statement::class;

                foreach (
                    $this->expressionAnalyzer->analyze($statement->cond)
                    as $expressionNode
                ) {
                    $nodes[] = $expressionNode;
                }

                foreach (
                    $this->analyzeStatements($statement->stmts)
                    as $bodyNode
                ) {
                    $nodes[] = $bodyNode;
                }

                continue;
            }

            $nodes[] = $statement::class;
        }

        return $nodes;
    }
}