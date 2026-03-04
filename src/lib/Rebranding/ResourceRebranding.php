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

    /** @var array<string, string> */
    protected array $bundleMap;

    /** @var array<string, string> */
    protected array $bundleNameMap;

    /** @var array<string, string> */
    protected array $extensionMap;

    /** @var array<string, string> */
    protected array $routeNamesMap;

    /** @var array<string, string> */
    protected array $servicesMap;

    /** @var array<string, string> */
    protected array $serviceTagNamesMap;

    /** @var array<string, string> */
    protected array $classParametersMap;

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
        $this->classParametersMap = require IbexaCompatibilityLayerBundle::MAPPINGS_PATH . \DIRECTORY_SEPARATOR . 'class-parameters-map.php';
    }

    public function rebrand(string $input): string
    {
        $output = $input;

        $pattern = '/([{<>\\s@(\[\\\\"\'])(([a-zA-Z_][a-zA-Z0-9_]*((\\\\)+|))+)/m';
        preg_match_all($pattern, $output, $matches);

        if (!empty($matches[2])) {
            sort($matches[2]);

            $possibleClassNames = array_unique(array_reverse($matches[2]));

            foreach ($possibleClassNames as $possibleClassName) {
                /** @var string $normalizedClassName */
                $normalizedClassName = preg_replace('/\\\\+/', '\\', $possibleClassName);
                if ($newClassName = $this->nameResolver->resolve($normalizedClassName)) {
                    if ($normalizedClassName !== $possibleClassName) {
                        $newClassName = str_replace('\\', '\\\\', $newClassName);
                    }
                    $output = str_replace($possibleClassName, $newClassName, $output);
                }
            }
        }

        foreach ($this->bundleMap as $oldBundle => $newBundle) {
            $output = $this->pregReplace('/([^[a-zA-Z0-9\\\\\/])' . preg_quote($oldBundle, '/') . '/', '${1}' . $newBundle, $output);
        }

        foreach ($this->bundleNameMap as $oldBundleName => $newBundleName) {
            $output = $this->pregReplace('/([^[a-zA-Z0-9\\\\])' . preg_quote($oldBundleName, '/') . '/', '${1}' . $newBundleName, $output);
        }

        foreach ($this->bundleNameMap as $oldBundleName => $newBundleName) {
            $output = str_replace(
                'bundles/' . strtolower($oldBundleName),
                'bundles/' . strtolower($newBundleName),
                $output
            );
        }

        foreach ($this->servicesMap as $oldServiceName => $newServiceName) {
            $output = $this->pregReplace(
                '/(?<!\.|_)' . preg_quote($oldServiceName, '/') . '(?=[\':]|$)/m',
                '${1}' . $newServiceName,
                $output
            );
            $output = $this->pregReplace(
                '/"@' . preg_quote($oldServiceName, '/') . '"/m',
                '\'@${1}' . $newServiceName . '\'',
                $output
            );
            $output = $this->pregReplace(
                '/id="' . preg_quote($oldServiceName, '/') . '"/m',
                'id="${1}' . $newServiceName . '"',
                $output
            );
        }

        $output = $this->pregReplace('/@ezdesign([\/\\\\])/', '@ibexadesign${1}', $output);
        $output = $this->pregReplace('/(["\'])ez(publish|platform)(["\'])/', '${1}ibexa${3}', $output);
        $output = str_replace('vnd.ez.api', 'vnd.ibexa.api', $output);
        $output = str_replace(RestPrefixSubscriber::LEGACY_REST_PREFIX, RestPrefixSubscriber::IBEXA_REST_PREFIX, $output);

        return $output;
    }

    /**
     * @param array<string, string> $classMap
     *
     * @return array<string, string>
     */
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

    private function getBundleName(string $fullClassName, bool $short = false): string
    {
        $parts = explode('\\', $fullClassName);
        /** @var string $className */
        $className = array_pop($parts);

        if (!$short) {
            return $className;
        }

        /** @var string $bundleName */
        $bundleName = preg_replace('/Bundle$/', '', $className);

        return $bundleName;
    }

    protected function pregReplace(string $pattern, string $replacement, string $subject): string
    {
        /** @var string $output */
        $output = preg_replace($pattern, $replacement, $subject);

        return $output;
    }
}
