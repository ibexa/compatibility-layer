<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\CompatibilityLayer\Rebranding;

use Ibexa\Bundle\CompatibilityLayer\IbexaCompatibilityLayerBundle;
use Ibexa\CompatibilityLayer\Event\Subscriber\RestPrefixSubscriber;
use Ibexa\CompatibilityLayer\FullyQualifiedNameResolver\AggregateResolver;
use Ibexa\CompatibilityLayer\FullyQualifiedNameResolver\ClassMapResolver;
use Ibexa\CompatibilityLayer\FullyQualifiedNameResolver\PSR4PrefixResolver;
use Ibexa\CompatibilityLayer\FullyQualifiedNameResolverInterface;

abstract class ResourceRebranding implements RebrandingInterface
{
    protected FullyQualifiedNameResolverInterface $nameResolver;

    protected array $bundleMap;

    protected array $bundleNameMap;

    protected array $extensionMap;

    protected array $routeNamesMap;

    protected array $servicesMap;

    protected array $serviceTagNamesMap;

    public function __construct()
    {
        $classMapResolver = new ClassMapResolver();
        $psr4PrefixResolver = new PSR4PrefixResolver();
        $this->nameResolver = new AggregateResolver([
            $classMapResolver,
            $psr4PrefixResolver,
        ]);

        $this->bundleMap = $this->getBundleMap($classMapResolver->getMap());
        $this->bundleNameMap = $this->getBundleMap($classMapResolver->getMap(), true);
        $this->extensionMap = require IbexaCompatibilityLayerBundle::MAPPINGS_PATH . \DIRECTORY_SEPARATOR . 'symfony-extension-name-map.php';
        $this->routeNamesMap = require IbexaCompatibilityLayerBundle::MAPPINGS_PATH . \DIRECTORY_SEPARATOR . 'route-names-map.php';
        $this->servicesMap = require IbexaCompatibilityLayerBundle::MAPPINGS_PATH . \DIRECTORY_SEPARATOR . 'services-to-fqcn-map.php';
        $this->serviceTagNamesMap = require IbexaCompatibilityLayerBundle::MAPPINGS_PATH . \DIRECTORY_SEPARATOR . 'symfony-service-tag-name-map.php';
    }

    public function rebrand(string $input): string
    {
        $pattern = '/([>\\s@(\[\\\\"\'])(([a-zA-Z_][a-zA-Z0-9_]*(\\\\|))+)/m';

        preg_match_all($pattern, $input, $matches);

        sort($matches[2]);
        $possibleClassNames = array_unique(array_reverse($matches[2]));

        $output = $input;
        foreach ($possibleClassNames as $possibleClassName) {
            if ($newClassName = $this->nameResolver->resolve($possibleClassName)) {
                $output = preg_replace('/' . preg_quote($possibleClassName) . '/', $newClassName, $output);
            }
        }

        foreach ($this->bundleMap as $oldBundle => $newBundle) {
            $output = preg_replace('/([^[a-zA-Z0-9\\\\\/])' . preg_quote($oldBundle) . '/', '${1}' . $newBundle, $output);
        }

        foreach ($this->bundleNameMap as $oldBundleName => $newBundleName) {
            $output = preg_replace('/([^[a-zA-Z0-9\\\\])' . preg_quote($oldBundleName) . '/', '${1}' . $newBundleName, $output);
        }

        foreach ($this->bundleNameMap as $oldBundleName => $newBundleName) {
            $output = str_replace(
                'bundles/' . strtolower($oldBundleName),
                'bundles/' . strtolower($newBundleName),
                $output
            );
        }

        foreach ($this->servicesMap as $oldServiceName => $newServiceName) {
            $output = preg_replace(
                '/(?<!\.|_)' . preg_quote($oldServiceName) . '(?=[\':]|$)/m',
                '${1}' . $newServiceName,
                $output
            );
            $output = preg_replace(
                '/"@' . preg_quote($oldServiceName) . '"/m',
                '\'@${1}' . $newServiceName . '\'',
                $output
            );
            $output = preg_replace(
                '/id="' . preg_quote($oldServiceName) . '"/m',
                'id="${1}' . $newServiceName . '"',
                $output
            );
        }

        $output = preg_replace('/@ezdesign\//', '@ibexadesign/', $output);
        $output = preg_replace('/(["\'])ez(publish|platform)(["\'])/', '${1}ibexa${3}', $output);
        $output = str_replace('vnd.ez.api', 'vnd.ibexa.api', $output);
        $output = str_replace(RestPrefixSubscriber::LEGACY_REST_PREFIX, RestPrefixSubscriber::IBEXA_REST_PREFIX, $output);

        return $output;
    }

    protected function getBundleMap(array $classMap, bool $short = false): array
    {
        $bundleMap = [];
        $rawBundleMap = array_filter($classMap, static function (string $className): bool {
            return preg_match('/Bundle$/', $className) === 1;
        });

        foreach ($rawBundleMap as $old => $new) {
            $bundleMap[$this->getBundleName($old, $short)] = $this->getBundleName($new, $short);
        }

        return $bundleMap;
    }

    private function getBundleName(string $fullClassName, bool $short = false)
    {
        $parts = explode('\\', $fullClassName);
        $className = array_pop($parts);

        return $short
            ? preg_replace('/Bundle$/', '', $className)
            : $className;
    }
}
