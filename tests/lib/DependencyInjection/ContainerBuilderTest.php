<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\CompatibilityLayer\DependencyInjection;

use Ibexa\Bundle\CompatibilityLayer\DependencyInjection\ContainerBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder as SymfonyContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

/**
 * @covers \Symfony\Component\DependencyInjection\ContainerBuilder
 */
final class ContainerBuilderTest extends TestCase
{
    private const EXTENSION_CONFIG = ['foo' => 'bar'];
    public const EXTENSION_ALIAS = 'ibexa';

    private ContainerBuilder $containerBuilder;

    private Extension $extension;

    protected function setUp(): void
    {
        $this->extension = new class() extends Extension {
            public function getAlias(): string
            {
                return ContainerBuilderTest::EXTENSION_ALIAS;
            }

            public function load(array $configs, SymfonyContainerBuilder $container): void
            {
                // Nothing to do
            }
        };

        $this->containerBuilder = new ContainerBuilder();
        $this->containerBuilder->registerExtension($this->extension);
        $this->containerBuilder->loadFromExtension(self::EXTENSION_ALIAS, self::EXTENSION_CONFIG);
    }

    public function getLegacyExtensionNames(): iterable
    {
        yield ['ezpublish'];
        yield ['ezplatform'];
    }

    /**
     * @dataProvider getLegacyExtensionNames
     */
    public function testGetExtensionConfig(string $legacyExtensionName): void
    {
        self::assertSame(
            [self::EXTENSION_CONFIG],
            $this->containerBuilder->getExtensionConfig($legacyExtensionName)
        );
    }

    /**
     * @dataProvider getLegacyExtensionNames
     */
    public function testHasExtension(string $legacyExtensionName): void
    {
        self::assertTrue($this->containerBuilder->hasExtension($legacyExtensionName));
    }

    /**
     * @dataProvider getLegacyExtensionNames
     */
    public function testGetExtension(string $legacyExtensionName): void
    {
        self::assertSame(
            $this->extension,
            $this->containerBuilder->getExtension($legacyExtensionName)
        );
    }

    /**
     * @dataProvider getLegacyExtensionNames
     */
    public function testPrependExtensionConfig(string $legacyExtensionName): void
    {
        $prependedConfig = ['foo' => 'baz'];
        $this->containerBuilder->prependExtensionConfig($legacyExtensionName, $prependedConfig);

        self::assertSame(
            [
                $prependedConfig,
                self::EXTENSION_CONFIG,
            ],
            $this->containerBuilder->getExtensionConfig($legacyExtensionName)
        );
    }
}
