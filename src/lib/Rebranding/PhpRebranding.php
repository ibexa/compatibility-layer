<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\CompatibilityLayer\Rebranding;

use Exception;
use Ibexa\CompatibilityLayer\FullyQualifiedNameResolver\AggregateResolver;
use Ibexa\CompatibilityLayer\FullyQualifiedNameResolver\ClassMapResolver;
use Ibexa\CompatibilityLayer\FullyQualifiedNameResolver\PSR4PrefixResolver;
use Ibexa\CompatibilityLayer\Parser\ClassNameVisitor;
use Ibexa\CompatibilityLayer\Parser\DocblockVisitor;
use Ibexa\CompatibilityLayer\Parser\ExtensionVisitor;
use PhpParser\Lexer;
use PhpParser\Lexer\Emulative;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\PrettyPrinterAbstract;

class PhpRebranding implements RebrandingInterface
{
    private Parser $parser;
    private PrettyPrinterAbstract $printer;
    private Lexer $lexer;
    private AggregateResolver $nameResolver;

    public function __construct()
    {
        $this->nameResolver = new AggregateResolver([
            new ClassMapResolver(),
            new PSR4PrefixResolver(),
        ]);
        $this->lexer = new Emulative([
            'usedAttributes' => [
                'comments',
                'startLine', 'endLine',
                'startTokenPos', 'endTokenPos',
            ],
        ]);
        $this->parser = (new ParserFactory())->create(ParserFactory::ONLY_PHP7, $this->lexer);
        $this->printer = new Standard();
    }

    public function rebrand(string $input): string
    {
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new CloningVisitor());
        $traverser->addVisitor(new NameResolver(null, [
            'preserveOriginalNames' => true,
        ]));
        $traverser->addVisitor(new ExtensionVisitor());
        $traverser->addVisitor(new ClassNameVisitor($this->nameResolver));
        $traverser->addVisitor(new DocblockVisitor($this->nameResolver));

        try {
            $parsed = $this->parser->parse($input);
        } catch (Exception $exception) {
            return $input;
        }

        $output = $this->printer->printFormatPreserving(
            $traverser->traverse($parsed),
            $parsed,
            $this->lexer->getTokens()
        );

        return $output;
    }

    public function getFileNamePatterns(): array
    {
        return [
            '*.php',
        ];
    }
}
