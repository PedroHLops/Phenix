<?php

declare(strict_types=1);

namespace Phenix\Analyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\Continue_;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Switch_;
use PhpParser\Node\Stmt\While_;

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
     * @param array<int, Stmt> $statements
     * @return AnalyzedNode[]
     */
    private function analyzeStatements(array $statements): array
    {
        $nodes = [];

        foreach ($statements as $statement) {
            if ($statement instanceof Expression && $statement->expr instanceof Assign) {
                $nodes[] = new AnalyzedNode(
                    type: $this->resolveType($statement),
                    kind: NodeKind::STATEMENT,
                    children: [
                        $this->expressionAnalyzer->analyze($statement->expr->var),
                        $this->expressionAnalyzer->analyze($statement->expr->expr),
                    ],
                );

                continue;
            }

            if ($statement instanceof Expression && $statement->expr instanceof Expr) {
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
                $nodes[] = $this->analyzeIf($statement);

                continue;
            }

            if ($statement instanceof While_) {
                $nodes[] = $this->analyzeWhile($statement);

                continue;
            }

            if ($statement instanceof Do_) {
                $nodes[] = $this->analyzeDoWhile($statement);

                continue;
            }

            if ($statement instanceof Foreach_) {
                $nodes[] = $this->analyzeForeach($statement);

                continue;
            }

            if ($statement instanceof For_) {
                $nodes[] = $this->analyzeFor($statement);

                continue;
            }

            if ($statement instanceof Switch_) {
                $nodes[] = $this->analyzeSwitch($statement);

                continue;
            }

            $nodes[] = new AnalyzedNode(
                type: $this->resolveType($statement),
                kind: NodeKind::STATEMENT,
            );
        }

        return $nodes;
    }

    private function analyzeIf(If_ $statement): AnalyzedNode
    {
        $children = [
            $this->expressionAnalyzer->analyze(
                $statement->cond
            ),
        ];

        /*
         * Corpo do IF
         */
        $children = [
            ...$children,
            ...$this->analyzeStatements($statement->stmts),
        ];

        /*
         * ELSEIF
         */
        foreach ($statement->elseifs as $elseIf) {
            $children[] = $this->analyzeElseIf($elseIf);
        }

        /*
         * ELSE
         */
        if ($statement->else instanceof Else_) {
            $children[] = $this->analyzeElse($statement->else);
        }

        return new AnalyzedNode(
            type: $this->resolveType($statement),
            kind: NodeKind::STATEMENT,
            children: $children,
        );
    }

    private function analyzeElseIf(ElseIf_ $statement): AnalyzedNode
    {
        $children = [
            $this->expressionAnalyzer->analyze(
                $statement->cond
            ),
            ...$this->analyzeStatements($statement->stmts),
        ];

        return new AnalyzedNode(
            type: $this->resolveType($statement),
            kind: NodeKind::STATEMENT,
            children: $children,
        );
    }

    private function analyzeElse(Else_ $statement): AnalyzedNode
    {
        return new AnalyzedNode(
            type: $this->resolveType($statement),
            kind: NodeKind::STATEMENT,
            children: $this->analyzeStatements(
                $statement->stmts
            ),
        );
    }

    private function analyzeWhile(While_ $statement): AnalyzedNode
    {
        $children = [
            $this->expressionAnalyzer->analyze(
                $statement->cond
            ),
            ...$this->analyzeStatements($statement->stmts),
        ];

        return new AnalyzedNode(
            type: $this->resolveType($statement),
            kind: NodeKind::STATEMENT,
            children: $children,
        );
    }

    private function analyzeDoWhile(Do_ $statement): AnalyzedNode
    {
        $children = [
            $this->expressionAnalyzer->analyze(
                $statement->cond
            ),
            ...$this->analyzeStatements($statement->stmts),
        ];

        return new AnalyzedNode(
            type: $this->resolveType($statement),
            kind: NodeKind::STATEMENT,
            children: $children,
        );
    }

    private function analyzeForeach(Foreach_ $statement): AnalyzedNode
    {
        $children = [
            $this->expressionAnalyzer->analyze(
                $statement->expr
            ),
        ];

        if ($statement->keyVar !== null) {
            $children[] = $this->expressionAnalyzer->analyze(
                $statement->keyVar
            );
        }

        $children[] = $this->expressionAnalyzer->analyze(
            $statement->valueVar
        );

        $children = [
            ...$children,
            ...$this->analyzeStatements($statement->stmts),
        ];

        return new AnalyzedNode(
            type: 'Foreach_',
            kind: NodeKind::STATEMENT,
            children: $children,
        );
    }

    private function analyzeFor(For_ $statement): AnalyzedNode
    {
        $children = [];

        /*
         * Inicialização
         */
        foreach ($statement->init as $expression) {
            $children[] = $this->expressionAnalyzer->analyze(
                $expression
            );
        }

        /*
         * Condição
         */
        foreach ($statement->cond as $expression) {
            $children[] = $this->expressionAnalyzer->analyze(
                $expression
            );
        }

        /*
         * Incremento
         */
        foreach ($statement->loop as $expression) {
            $children[] = $this->expressionAnalyzer->analyze(
                $expression
            );
        }

        /*
         * Corpo do FOR
         */
        $children = [
            ...$children,
            ...$this->analyzeStatements($statement->stmts),
        ];

        return new AnalyzedNode(
            type: $this->resolveType($statement),
            kind: NodeKind::STATEMENT,
            children: $children,
        );
    }

    private function analyzeSwitch(Switch_ $statement): AnalyzedNode
    {
        $children = [];

        // Condição do switch
        $children[] = $this->expressionAnalyzer->analyze(
            $statement->cond
        );

        // Cases
        foreach ($statement->cases as $case) {
            $caseChildren = [];

            // Valor do case
            if ($case->cond !== null) {
                $caseChildren[] = $this->expressionAnalyzer->analyze(
                    $case->cond
                );
            }

            // Statements dentro do case
            $caseChildren = array_merge(
                $caseChildren,
                $this->analyzeStatements($case->stmts)
            );

            $children[] = new AnalyzedNode(
                type: $case->cond === null ? 'Default' : 'Case',
                kind: NodeKind::STATEMENT,
                children: $caseChildren,
            );
        }

        return new AnalyzedNode(
            type: 'Switch',
            kind: NodeKind::STATEMENT,
            children: $children,
        );
    }

    private function resolveType(Stmt $statement): string
    {
        return match (true) {
            $statement instanceof Return_ => 'Return_',
            $statement instanceof If_ => 'If_',
            $statement instanceof ElseIf_ => 'ElseIf_',
            $statement instanceof Else_ => 'Else_',
            $statement instanceof While_ => 'While_',
            $statement instanceof Do_ => 'Do_',
            $statement instanceof Foreach_ => 'Foreach_',
            $statement instanceof For_ => 'For_',
            $statement instanceof Break_ => 'Break_',
            $statement instanceof Continue_ => 'Continue_',
            $statement instanceof Expression => 'Expression',
            $statement instanceof Switch_ => 'Switch_',
            default => $statement::class,
        };
    }
}