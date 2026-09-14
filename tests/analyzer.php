<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Phenix\Analyzer\Analyzer;
use Phenix\Analyzer\ExpressionAnalyzer;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use Phenix\Analyzer\AnalyzedFunction;
use Phenix\Analyzer\BodyAnalyzer;
use Phenix\Analyzer\AttributeResolver;

$parser = (new ParserFactory())->createForNewestSupportedVersion();

$code = <<<'PHP'
<?php

use Phenix\Attributes\Client;
use Phenix\Attributes\Server;
use Phenix\Attributes\Shared;

#[Client]
function hello(string $name): string
{
    if ($name === 'Pedro') {
        return 'Olá, Pedro';
    }

    return "Hello, " . $name;
}

#[Server]
function saveUser(string $name): void
{
}

#[Shared]
function formatName(string $name): string
{
    return strtoupper($name);
}

function normalFunction(): void
{
}
PHP;

$ast = $parser->parse($code);

$traverser = new NodeTraverser();

$traverser->addVisitor(
    new NameResolver()
);

$ast = $traverser->traverse($ast);

$bodyAnalyzer = new BodyAnalyzer(
    new ExpressionAnalyzer(),
);

$analyzer = new Analyzer(
    new AttributeResolver(),
    $bodyAnalyzer
);

$functions = $analyzer->analyze($ast);

foreach ($functions as $function) {
    echo "Nome: {$function->name}" . PHP_EOL;
    echo "Parâmetros: {$function->parameters}" . PHP_EOL;
    echo "Tipo: {$function->type->value}" . PHP_EOL;
    echo "Nós do corpo: " . PHP_EOL;
    echo " - " . implode("\n - ", $function->bodyNodes) . PHP_EOL;
    echo PHP_EOL;
}