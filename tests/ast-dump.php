<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use PhpParser\Node;
use PhpParser\NodeDumper;
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

$dumper = new NodeDumper();

echo $dumper->dump($ast) . PHP_EOL;