<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\CompatibilityLayer\Parser;

use PhpParser\Node;
use PhpParser\Node\Stmt\UseUse;

class ClassNameVisitor extends RebrandingVisitor
{
    protected array $namespaceAliases = [];

    public function leaveNode(Node $node)
    {
        if ($node instanceof UseUse) {
            if ($node->alias) {
                $this->namespaceAliases[(string)$node->alias->name] = $node->name;
            }
        } elseif ($node instanceof Node\Name) {
            return $this->getResolvedNameNode($node);
        } elseif ($node instanceof Node\Scalar\String_) {
            if ($node->getAttribute('parent') instanceof Node\Arg) {
                /** @var \PhpParser\Node\Arg $argument */
                $argument = $node->getAttribute('parent');
                if ($argument->getAttribute('parent') instanceof Node\Expr\FuncCall) {
                    /** @var \PhpParser\Node\Expr\FuncCall $funcCall */
                    $funcCall = $argument->getAttribute('parent');
                    $name = $funcCall->name;
                    if ($name instanceof Node\Name && (string)$name === 'class_alias') {
                        return $node;
                    }
                }
            }

            $pattern = '/([{<>\\s@(\[\\\\"\']|^)(([a-zA-Z_][a-zA-Z0-9_]*((\\\\)+|))+)/m';
            $string = (string)$node->value;

            preg_match_all($pattern, $string, $matches);
            sort($matches[2]);

            if (empty($matches[2])) {
                return $node;
            }

            $possibleClassNames = array_unique(array_reverse($matches[2]));

            foreach ($possibleClassNames as $possibleClassName) {
                $normalizedClassName = preg_replace('/\\\\+/', '\\', $possibleClassName);
                if ($newClassName = $this->nameResolver->resolve($normalizedClassName)) {
                    if ($normalizedClassName !== $possibleClassName) {
                        $newClassName = str_replace('\\', '\\\\', $newClassName);
                    }
                    $string = str_replace($possibleClassName, $newClassName, $string);
                }
            }
            if ($string !== (string)$node->value) {
                return new Node\Scalar\String_($string);
            }
        }

        return $node;
    }
}
