<?php

declare(strict_types=1);

namespace Phenix\Analyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\ElseIf_;
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
     * @return AnalyzedNode[]
     */
    public function analyze(Function_ $function): array
    {
        return $this->analyzeStatements($function->stmts ?? []);
    }

    /**
     * @param array<int, \PhpParser\Node\Stmt> $statements
     * @return AnalyzedNode[]
     */
    private function analyzeStatements(array $statements): array
    {
        $nodes = [];

        foreach ($statements as $statement) {
            if ($statement instanceof Return_) {
                if ($statement->expr instanceof Expr) {
                    $nodes[] = new AnalyzedNode(
                        type: $this->resolveType($statement),
                        kind: NodeKind::STATEMENT,
                        children: [
                            $this->expressionAnalyzer->analyze(
                                $statement->expr
                            ),
                        ],
                    );

                    continue;
                }

                $nodes[] = new AnalyzedNode(
                    kind: NodeKind::STATEMENT,
                    type: $statement::class,
                );

                continue;
            }

            if ($statement instanceof If_) {
                $nodes[] = new AnalyzedNode(
                    type: $this->resolveType($statement),
                    kind: NodeKind::STATEMENT,
                    children: [
                        $this->expressionAnalyzer->analyze(
                            $statement->cond
                        ),
                        ...$this->analyzeStatements($statement->stmts),
                    ],
                );

                continue;
            }

            if ($statement instanceof ElseIf_) {
                $nodes[] = new AnalyzedNode(
                    type: $this->resolveType($statement),
                    kind: NodeKind::STATEMENT,
                    children: [
                        $this->expressionAnalyzer->analyze(
                            $statement->cond
                        ),
                        ...$this->analyzeStatements($statement->stmts),
                    ],
                );

                continue;
            }

            if ($statement instanceof Else_) {
                $nodes[] = new AnalyzedNode(
                    type: $this->resolveType($statement),
                    kind: NodeKind::STATEMENT,
                    children: [
                        $this->analyzeStatements($statement->stmts),
                    ],
                );

                continue;
            }

            $nodes[] = new AnalyzedNode(
                type: $statement::class,
                kind: NodeKind::STATEMENT,
            );
        }

        return $nodes;
    }

    private function resolveType(Stmt $statement): string
    {
        return match (True) {
            Return_::class === $statement::class => 'Return_',
            If_::class === $statement::class => 'If_',
            default => $statement::class,
        };
    }
}