<?php

declare(strict_types=1);
use Phenix\Analyzer\AnalyzedFunction;
use Phenix\Analyzer\AnalyzedNode;
use Phenix\Analyzer\AnalyzedParameter;
use Phenix\Analyzer\NodeKind;
use Phenix\Analyzer\Type;
use Phenix\Generator\JavaScriptGenerator;

require __DIR__ . '/../vendor/autoload.php';


$function = new AnalyzedFunction(
    name: 'calculateScore',
    parameters: [
        new AnalyzedParameter('a', 'int'),
        new AnalyzedParameter('b', 'int'),
    ],
    returnType: 'int',
    type: Type::CLIENT,
    bodyNodes: [
        new AnalyzedNode(
            type: 'Return_',
            kind: NodeKind::STATEMENT,
            children: [
                new AnalyzedNode(
                    type: 'String_',
                    kind: NodeKind::EXPRESSION,
                    value: 'Hello from Phenix!',
                ),
            ],
        ),
    ],
);

$generator = new JavaScriptGenerator();

echo $generator->generate($function);