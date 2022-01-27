<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\CompatibilityLayer\Parser;

use PhpParser\Comment;
use PhpParser\Node;
use PhpParser\Node\Stmt\UseUse;

class DocblockVisitor extends RebrandingVisitor
{
    private array $resolvedUses = [];

    public function leaveNode(Node $node)
    {
        if ($node instanceof UseUse) {
            if ($node->alias !== null) {
                $this->resolvedUses[(string)$node->alias->name] = new Node\Name\FullyQualified($node->name->parts);
            } else {
                $this->resolvedUses[end($node->name->parts)] = new Node\Name\FullyQualified($node->name->parts);
            }
        }

        if (isset($node->getAttributes()['comments'])) {
            if ($node instanceof Node\AttributeGroup) {
                return;
            }

            $comments = $node->getAttributes()['comments'];

            /** @var \PhpParser\Comment $comment */
            foreach ($comments as &$comment) {
                $text = $comment->getText();
                $lines = explode("\n", $text);

                if (!$comment instanceof Comment\Doc) {
                    continue;
                }

                foreach ($lines as &$line) {
                    preg_match('/(\s*\/\*\*|\s*\*) @(var|param|see|throws|return) ([a-zA-Z0-9\\\\\\|&]+)(.*)/', $line, $match);

                    if (!empty($match)) {
                        $orTypes = explode('|', $match[3]);

                        foreach ($orTypes as &$orType) {
                            $andTypes = explode('&', $orType);

                            foreach ($andTypes as &$andType) {
                                if (isset($this->resolvedUses[$andType])) {
                                    $andType = '\\' . $this->resolvedUses[$andType];
                                } else {
                                    $resolvedType = $this->nameResolver->resolve(ltrim($andType, '\\'));

                                    if ($resolvedType !== null) {
                                        $andType = '\\' . $resolvedType;
                                    }
                                }
                            }

                            $orType = implode('&', $andTypes);
                        }

                        $line = sprintf('%s @%s %s%s', $match[1], $match[2], implode('|', $orTypes), $match[4]);
                    }
                }
                $newComment = new Comment\Doc(implode("\n", $lines));

                if ($comment->getText() !== $newComment->getText()) {
                    $comment = $newComment;
                }
            }

            $node->setAttribute('comments', $comments);
        }

        return $node;
    }
}
