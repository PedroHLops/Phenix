<?php

declare(strict_types=1);

namespace Phenix\Analyzer;

use PhpParser\Node\Stmt\Function_;

final class AttributeResolver
{
    private const CLIENT = 'Phenix\\Attributes\\Client';
    private const SERVER = 'Phenix\\Attributes\\Server';
    private const SHARED = 'Phenix\\Attributes\\Shared';

    public function resolve(Function_ $node): Type
    {
        $type = null;

        foreach ($node->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attribute) {
                $resolvedName = $attribute->name->getAttribute('resolvedName');

                if ($resolvedName === null) {
                    $resolvedName = $attribute->name->toString();
                }

                $resolvedName = ltrim($resolvedName, '\\');

                $attributeType = match ($resolvedName) {
                    self::CLIENT => Type::CLIENT,
                    self::SERVER => Type::SERVER,
                    self::SHARED => Type::SHARED,
                    default => null,
                };

                if ($attributeType === null) {
                    continue;
                }

                if ($type !== null) {
                    throw new \LogicException(
                        sprintf(
                            'A função "%s" possui mais de um atributo de execução.',
                            $node->name->toString()
                        )
                    );
                }

                $type = $attributeType;
            }
        }

        return $type ?? Type::UNKNOWN;
    }
}