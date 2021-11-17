<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\CompatibilityLayer\DependencyInjection\Compiler\REST;

use Ibexa\Bundle\CompatibilityLayer\DependencyInjection\Compiler\ValueObjectVisitorTagCompatibilityPass;
use Ibexa\CompatibilityLayer\FullyQualifiedNameResolver\AggregateResolver;
use Ibexa\CompatibilityLayer\FullyQualifiedNameResolver\ClassMapResolver;
use Ibexa\CompatibilityLayer\FullyQualifiedNameResolver\PSR4PrefixResolver;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @covers \Ibexa\Bundle\CompatibilityLayer\DependencyInjection\Compiler\ValueObjectVisitorTagCompatibilityPass
 */
class ValueObjectVisitorTagCompatibilityPassTest extends AbstractCompilerPassTestCase
{
    public const SERVICE_ID = 'app.rest.value_object_visitor.foo';

    /**
     * @dataProvider getDataForTestProcess
     *
     * @throws \Exception
     */
    public function testProcess(string $type, string $expectedRebrandedType): void
    {
        $thirdPartyTypeNotToBeTouched = 'My\App\Type';
        $this->defineVisitorService([$type, $thirdPartyTypeNotToBeTouched]);

        $this->compile();

        $this->assertContainerBuilderHasService(self::SERVICE_ID);

        // check if the service is tagged with a new FQCN
        $this->assertContainerBuilderHasServiceDefinitionWithTag(
            self::SERVICE_ID,
            ValueObjectVisitorTagCompatibilityPass::SERVICE_TAG,
            [
                'type' => $expectedRebrandedType,
            ]
        );

        // check if the 3rd party tag remains untouched
        $this->assertContainerBuilderHasServiceDefinitionWithTag(
            self::SERVICE_ID,
            ValueObjectVisitorTagCompatibilityPass::SERVICE_TAG,
            [
                'type' => $thirdPartyTypeNotToBeTouched,
            ]
        );

        // check if the old tag still exists
        // @todo change to check if tag does not exist once we can safely replace tag instead of adding it
        $this->assertContainerBuilderHasServiceDefinitionWithTag(
            self::SERVICE_ID,
            ValueObjectVisitorTagCompatibilityPass::SERVICE_TAG,
            [
                'type' => $type,
            ]
        );
    }

    public function getDataForTestProcess(): iterable
    {
        yield 'from class-map' => [
            'eZ\Publish\API\Repository\Values\Content\VersionInfo',
            'Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo',
        ];

        yield 'from psr4-map' => [
            'eZ\Bundle\EzPublishCoreBundle\ApiLoader\CacheFactory',
            'Ibexa\Bundle\Core\ApiLoader\CacheFactory',
        ];
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(
            new ValueObjectVisitorTagCompatibilityPass(
                new AggregateResolver(
                    [
                        new ClassMapResolver(),
                        new PSR4PrefixResolver(),
                    ]
                )
            ),
            PassConfig::TYPE_BEFORE_OPTIMIZATION,
            128
        );
    }

    /**
     * @param string[] $types
     */
    protected function defineVisitorService(array $types): void
    {
        $definition = new Definition();
        foreach ($types as $type) {
            $definition->addTag(
                ValueObjectVisitorTagCompatibilityPass::SERVICE_TAG,
                ['type' => $type]
            );
        }
        $this->setDefinition(self::SERVICE_ID, $definition);
    }
}
