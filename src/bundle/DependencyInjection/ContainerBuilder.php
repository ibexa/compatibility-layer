<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\CompatibilityLayer\DependencyInjection;

use Ibexa\Bundle\CompatibilityLayer\IbexaCompatibilityLayerBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder as SymfonyContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

/**
 * @internal
 */
final class ContainerBuilder extends SymfonyContainerBuilder
{
    /** @var array<string, string> */
    private static $extensionNameMap;

    /** @var array<string, string> */
    private static $serviceNameMap;

    public function hasExtension(string $name): bool
    {
        return parent::hasExtension(
            $this->resolveExtensionName($name)
        );
    }

    public function getExtension(string $name): ExtensionInterface
    {
        return parent::getExtension(
            $this->resolveExtensionName($name)
        );
    }

    public function getExtensionConfig(string $name): array
    {
        return parent::getExtensionConfig(
            $this->resolveExtensionName($name)
        );
    }

    public function prependExtensionConfig(string $name, array $config): void
    {
        parent::prependExtensionConfig(
            $this->resolveExtensionName($name),
            $config
        );
    }

    private function resolveExtensionName(string $name): string
    {
        if (!isset(self::$extensionNameMap)) {
            /** @noinspection PhpIncludeInspection */
            self::$extensionNameMap = require IbexaCompatibilityLayerBundle::MAPPINGS_PATH
                . \DIRECTORY_SEPARATOR . 'symfony-extension-name-map.php';
        }

        return self::$extensionNameMap[$name] ?? $name;
    }

    public function getDefinition(string $id)
    {
        if (!isset(self::$serviceNameMap)) {
            /** @noinspection PhpIncludeInspection */
            self::$serviceNameMap = require IbexaCompatibilityLayerBundle::MAPPINGS_PATH
                . \DIRECTORY_SEPARATOR . 'services-to-fqcn-map.php';
        }

        try {
            return parent::getDefinition($id);
        } catch (ServiceNotFoundException $e) {
            if (isset(self::$serviceNameMap[$id])) {
                return parent::getDefinition(self::$serviceNameMap[$id]);
            }
        }

        throw $e;
    }

    public function hasDefinition(string $id)
    {
        if (!isset(self::$serviceNameMap)) {
            /** @noinspection PhpIncludeInspection */
            self::$serviceNameMap = require IbexaCompatibilityLayerBundle::MAPPINGS_PATH
                . \DIRECTORY_SEPARATOR . 'services-to-fqcn-map.php';
        }

        $hasDefinition = parent::hasDefinition($id);
        if (!$hasDefinition && isset(self::$serviceNameMap[$id])) {
            return parent::hasDefinition(self::$serviceNameMap[$id]);
        }
        return $hasDefinition;
    }


    public function has(string $id)
    {
        if (!isset(self::$serviceNameMap)) {
            /** @noinspection PhpIncludeInspection */
            self::$serviceNameMap = require IbexaCompatibilityLayerBundle::MAPPINGS_PATH
                . \DIRECTORY_SEPARATOR . 'services-to-fqcn-map.php';
        }

        $has = parent::has($id);
        if (!$has && isset(self::$serviceNameMap[$id])) {
            return parent::has(self::$serviceNameMap[$id]);
        }
        return $has;
    }

    public function get(string $id, int $invalidBehavior = ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE)
    {
        if (!isset(self::$serviceNameMap)) {
            /** @noinspection PhpIncludeInspection */
            self::$serviceNameMap = require IbexaCompatibilityLayerBundle::MAPPINGS_PATH
                . \DIRECTORY_SEPARATOR . 'services-to-fqcn-map.php';
        }

        try {
            return parent::get($id, $invalidBehavior);
        } catch (ServiceNotFoundException $e) {
            if (isset(self::$serviceNameMap[$id])) {
                return parent::get(self::$serviceNameMap[$id], $invalidBehavior);
            }
        }

        throw $e;
    }
}
