<?php

declare(strict_types=1);

namespace Phenix\Analyzer;


enum NodeKind: string {
    case STATEMENT = 'statement';
    case EXPRESSION = 'expression';
}