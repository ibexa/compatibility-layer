<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\CompatibilityLayer\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder as SymfonyContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

/**
 * @internal
 */
final class ContainerBuilder extends SymfonyContainerBuilder
{
    public const EXTENSION_NAME_BC_MAP = [
        'ezpublish' => 'ibexa',
        'ezplatform' => 'ibexa',
    ];

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
        return self::EXTENSION_NAME_BC_MAP[$name] ?? $name;
    }
}
