<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\CompatibilityLayer\Rebranding;

use Exception;
use Ibexa\Bundle\CompatibilityLayer\IbexaCompatibilityLayerBundle;
use Ibexa\CompatibilityLayer\Event\Subscriber\RestPrefixSubscriber;
use Ibexa\CompatibilityLayer\FullyQualifiedNameResolver\AggregateResolver;
use Ibexa\CompatibilityLayer\FullyQualifiedNameResolver\ClassMapResolver;
use Ibexa\CompatibilityLayer\FullyQualifiedNameResolver\PSR4PrefixResolver;
use Ibexa\CompatibilityLayer\Parser\ClassNameVisitor;
use Ibexa\CompatibilityLayer\Parser\ClassParameterVisitor;
use Ibexa\CompatibilityLayer\Parser\DocblockVisitor;
use Ibexa\CompatibilityLayer\Parser\ExtensionVisitor;
use Ibexa\CompatibilityLayer\Parser\RouteNameVisitor;
use Ibexa\CompatibilityLayer\Parser\ServiceTagNameVisitor;
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

    private array $extensionMap;

    private array $routeNamesMap;

    private array $servicesMap;

    private array $serviceTagNamesMap;

    private array $classParametersMap;

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
        $this->extensionMap = require IbexaCompatibilityLayerBundle::MAPPINGS_PATH . \DIRECTORY_SEPARATOR . 'symfony-extension-name-map.php';
        $this->routeNamesMap = require IbexaCompatibilityLayerBundle::MAPPINGS_PATH . \DIRECTORY_SEPARATOR . 'route-names-map.php';
        $this->servicesMap = require IbexaCompatibilityLayerBundle::MAPPINGS_PATH . \DIRECTORY_SEPARATOR . 'services-to-fqcn-map.php';
        $this->serviceTagNamesMap = require IbexaCompatibilityLayerBundle::MAPPINGS_PATH . \DIRECTORY_SEPARATOR . 'symfony-service-tag-name-map.php';
        $this->classParametersMap = require IbexaCompatibilityLayerBundle::MAPPINGS_PATH . \DIRECTORY_SEPARATOR . 'class-parameters-map.php';
    }

    public function rebrand(string $input): string
    {
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new CloningVisitor());
        $traverser->addVisitor(new NameResolver(null, [
            'preserveOriginalNames' => true,
        ]));
        $traverser->addVisitor(new ExtensionVisitor($this->extensionMap));
        $traverser->addVisitor(new ClassNameVisitor($this->nameResolver));
        $traverser->addVisitor(new DocblockVisitor($this->nameResolver));
        $traverser->addVisitor(new RouteNameVisitor($this->routeNamesMap));
        $traverser->addVisitor(new ServiceTagNameVisitor($this->serviceTagNamesMap));
        $traverser->addVisitor(new ClassParameterVisitor($this->classParametersMap));

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

        $output = $this->rebrandServices($output);
        $output = str_replace('vnd.ez.api', 'vnd.ibexa.api', $output);
        $output = str_replace(RestPrefixSubscriber::LEGACY_REST_PREFIX, RestPrefixSubscriber::IBEXA_REST_PREFIX, $output);

        return $output;
    }

    public function getFileNamePatterns(): array
    {
        return [
            '*.php',
        ];
    }

    private function rebrandServices(string $input): string
    {
        $output = $input;

        foreach ($this->servicesMap as $oldServiceName => $newServiceName) {
            if (class_exists($newServiceName)) {
                $output = preg_replace(
                    '/(?<!\.|_)' . '\'' . preg_quote($oldServiceName) . '\'' . '/',
                    '${1}' . '\\' . $newServiceName . '::class',
                    $output
                );
            } else {
                $output = preg_replace(
                    '/(?<!\.|_)' . preg_quote($oldServiceName) . '(?=[\'\":]|$)/m',
                    '${1}' . $newServiceName,
                    $output
                );
            }
        }

        return $output;
    }
}
