<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Phenix\Analyzer\AnalyzedNode;
use Phenix\Analyzer\Analyzer;
use Phenix\Analyzer\ExpressionAnalyzer;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use Phenix\Analyzer\AnalyzedNodePrinter;
use Phenix\Analyzer\BodyAnalyzer;
use Phenix\Analyzer\AttributeResolver;


$parser = (new ParserFactory())->createForNewestSupportedVersion();

$code = <<<'PHP'
<?php

use Phenix\Attributes\Client;
use Phenix\Attributes\Server;
use Phenix\Attributes\Shared;

// ============================================================
// 1. IF / ELSE
// ============================================================

#[Client]
function checkAge(int $age): string
{
    if ($age >= 18) {
        return 'Maior de idade';
    } else {
        return 'Menor de idade';
    }
}

// ============================================================
// 2. IF / ELSE IF / ELSE
// ============================================================

#[Client]
function classifyAge(int $age): string
{
    if ($age < 12) {
        return 'Criança';
    } elseif ($age < 18) {
        return 'Adolescente';
    } else {
        return 'Adulto';
    }
}

// ============================================================
// 3. OPERADORES ARITMÉTICOS
// ============================================================

#[Client]
function calculateScore(int $a, int $b): int
{
    $sum = $a + $b;
    $difference = $a - $b;
    $product = $a * $b;

    return $sum + $difference + $product;
}

// ============================================================
// 4. OPERADORES DE COMPARAÇÃO
// ============================================================

#[Client]
function compareNumbers(int $a, int $b): bool
{
    return $a > $b;
}

// ============================================================
// 5. OPERADORES LÓGICOS
// ============================================================

#[Client]
function canEnter(int $age, bool $active): bool
{
    return $age >= 18 && $active;
}

// ============================================================
// 6. NOT
// ============================================================

#[Client]
function isInactive(bool $active): bool
{
    return !$active;
}

// ============================================================
// 7. CONCATENAÇÃO
// ============================================================

#[Shared]
function createMessage(string $name, int $age): string
{
    return 'Nome: ' . $name . ', idade: ' . $age;
}

// ============================================================
// 8. CHAMADA DE FUNÇÃO
// ============================================================

#[Shared]
function normalizeName(string $name): string
{
    return strtoupper($name);
}

// ============================================================
// 9. CHAMADA DE FUNÇÃO COM ARGUMENTO
// ============================================================

#[Shared]
function createGreeting(string $name): string
{
    return 'Olá, ' . strtoupper($name);
}

// ============================================================
// 10. VARIÁVEL LOCAL
// ============================================================

#[Client]
function createFullName(string $firstName, string $lastName): string
{
    $fullName = $firstName . ' ' . $lastName;

    return $fullName;
}

// ============================================================
// 11. MÚLTIPLOS PARÂMETROS
// ============================================================

#[Client]
function calculateAverage(int $a, int $b, int $c): int
{
    return ($a + $b + $c) / 3;
}

// ============================================================
// 12. IF ANINHADO
// ============================================================

#[Client]
function classifyPerson(int $age): string
{
    if ($age >= 18) {
        if ($age >= 60) {
            return 'Idoso';
        }

        return 'Adulto';
    }

    return 'Menor';
}

// ============================================================
// 13. SERVER
// ============================================================

#[Server]
function saveUser(string $name): void
{
    $user = $name;
}

// ============================================================
// 14. SERVER COM RETORNO
// ============================================================

#[Server]
function getServerMessage(): string
{
    return 'Mensagem do servidor';
}

// ============================================================
// 15. SHARED
// ============================================================

#[Shared]
function formatUserName(string $name): string
{
    return strtoupper($name);
}

// ============================================================
// 16. FUNÇÃO SEM ATRIBUTO
// ============================================================

function normalFunction(): void
{
}

// ============================================================
// 17. BOOLEAN LITERAL
// ============================================================

#[Client]
function isActive(): bool
{
    return true;
}

// ============================================================
// 18. INTEGER LITERAL
// ============================================================

#[Client]
function getDefaultAge(): int
{
    return 18;
}

// ============================================================
// 19. STRING LITERAL
// ============================================================

#[Client]
function getDefaultName(): string
{
    return 'Pedro';
}

// ============================================================
// 20. EXPRESSÃO COMPLEXA
// ============================================================

#[Client]
function calculateEligibility(int $age, bool $active): bool
{
    if ($age >= 18 && $active) {
        return true;
    }

    return false;
}

// ============================================================
// 21. ARRAY DE ELEMENTOS
// ============================================================

#[Client]
function getName(array $user): string
{
    return $user['name'];
}

// ============================================================
// 22. PROPRIEDADES
// ============================================================

#[Client]
function getUserName(object $user): string
{
    return $user->name;
}

// ============================================================
// 23. MÉTODOS
// ============================================================

#[Client]
function getUserNameFromMethod(object $user): string
{
    return $user->getName();
}

#[Client]
function setUserName(object $user, string $name): void
{
    $user->setName($name);
}

// ============================================================
// 24. BREAK / CONTINUE
// ============================================================

#[Client]
function countWithBreak(): void
{
    for ($i = 0; $i < 10; $i++) {
        if ($i === 5) {
            break;
        }

        console_log($i);
    }
}

#[Client]
function countWithContinue(): void
{
    for ($i = 0; $i < 10; $i++) {
        if ($i === 5) {
            continue;
        }

        console_log($i);
    }
}

// ============================================================
// 25. SWITCH
// ============================================================

#[Client]
function classifyStatus(int $status): string
{
    switch ($status) {
        case 1:
            return 'Ativo';

        case 2:
            return 'Pendente';

        default:
            return 'Desconhecido';
    }
}

// ============================================================
// 26. SWITCH COM BREAK
// ============================================================

#[Client]
function printStatus(int $status): void
{
    switch ($status) {
        case 1:
            console_log('Ativo');
            break;

        case 2:
            console_log('Pendente');
            break;

        default:
            console_log('Desconhecido');
            break;
    }
}

// ============================================================
// 27. DO WHILE
// ============================================================

#[Client]
function countWithDoWhile(): void
{
    $i = 0;

    do {
        console_log($i);

        $i++;
    } while ($i < 3);
}

// ============================================================
// 28. OPERADORES DE INCREMENTO / DECREMENTO
// ============================================================

#[Client]
function testIncrementOperators(): void
{
    $i = 0;

    $i++;
    $i--;

    ++$i;
    --$i;
}

// ============================================================
// 29. OPERADORES DE COMPARAÇÃO
// ============================================================

#[Client]
function compareAll(int $a, int $b): bool
{
    return $a == $b;
}

#[Client]
function compareDifferent(int $a, int $b): bool
{
    return $a != $b;
}

#[Client]
function compareGreaterOrEqual(int $a, int $b): bool
{
    return $a >= $b;
}

#[Client]
function compareSmallerOrEqual(int $a, int $b): bool
{
    return $a <= $b;
}

// ============================================================
// 30. OPERADORES LÓGICOS
// ============================================================

#[Client]
function testOr(bool $a, bool $b): bool
{
    return $a || $b;
}

#[Client]
function testNot(bool $active): bool
{
    return !$active;
}

// ============================================================
// 31. OPERADORES ARITMÉTICOS
// ============================================================

#[Client]
function calculateDivision(int $a, int $b): int
{
    return $a / $b;
}

#[Client]
function calculateSubtraction(int $a, int $b): int
{
    return $a - $b;
}

#[Client]
function calculateMultiplication(int $a, int $b): int
{
    return $a * $b;
}

// ============================================================
// 32. ARRAYS
// ============================================================

#[Client]
function getFirstName(array $users): string
{
    return $users[0];
}

#[Client]
function getUserNameByKey(array $user): string
{
    return $user['name'];
}

// ============================================================
// 33. ARRAY ANINHADO
// ============================================================

#[Client]
function getNestedValue(array $user): string
{
    return $user['profile']['name'];
}

// ============================================================
// 34. PROPRIEDADE + MÉTODO
// ============================================================

#[Client]
function getProfileName(object $user): string
{
    return $user->profile->name;
}

#[Client]
function callNestedMethod(object $user): string
{
    return $user->profile->getName();
}

// ============================================================
// 35. ATRIBUIÇÕES MÚLTIPLAS
// ============================================================

#[Client]
function calculateValues(int $a, int $b): int
{
    $sum = $a + $b;
    $result = $sum * 2;

    return $result;
}

// ============================================================
// 36. FUNÇÃO COM VÁRIAS EXPRESSÕES
// ============================================================

#[Client]
function processUser(string $name): string
{
    $normalized = strtoupper($name);

    console_log($normalized);

    return $normalized;
}

// ============================================================
// 37. FUNÇÃO COM CHAMADAS ENCADEADAS
// ============================================================

#[Client]
function getFormattedName(object $user): string
{
    return strtoupper($user->getName());
}

// ============================================================
// 38. RETURN SEM VALOR
// ============================================================

#[Client]
function stopProcessing(bool $condition): void
{
    if ($condition) {
        return;
    }

    console_log('Continuando');
}

// ============================================================
// 39. IF COM MÚLTIPLAS EXPRESSÕES
// ============================================================

#[Client]
function processAge(int $age): string
{
    if ($age >= 18) {
        console_log('Maior');

        return 'Adulto';
    }

    console_log('Menor');

    return 'Menor';
}

// ============================================================
// 40. FOR COM BREAK E CONTINUE
// ============================================================

#[Client]
function complexLoop(): void
{
    for ($i = 0; $i < 10; $i++) {
        if ($i === 2) {
            continue;
        }

        if ($i === 8) {
            break;
        }

        console_log($i);
    }
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
$printer = new AnalyzedNodePrinter();


foreach ($functions as $function) {
    echo "Nome: {$function->name}" . PHP_EOL;
    echo "Parâmetros: " . count($function->parameters) . PHP_EOL;
    foreach ($function->parameters as $parameter) {
        echo "  - {$parameter->name}";

        if ($parameter->type !== null) {
            echo ": {$parameter->type}";
        }

        echo PHP_EOL;
    }
    echo "Retorno: " . ($function->returnType ?? 'nenhum') . PHP_EOL;
    echo "Tipo: {$function->type->value}" . PHP_EOL;
    echo "Nós do corpo: " . PHP_EOL;
    echo $printer->print($function->bodyNodes);
    echo PHP_EOL;
}