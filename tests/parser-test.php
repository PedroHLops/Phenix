<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use PhpParser\ParserFactory;

$parser = (new ParserFactory())->createForNewestSupportedVersion();

$code = <<<'PHP'
<?php

function hello(string $name): string
{
    return "Hello, " . $name;
}
PHP;

$ast = $parser->parse($code);

echo "AST criada com sucesso!" . PHP_EOL;
echo "Nós encontrados: " . count($ast) . PHP_EOL;