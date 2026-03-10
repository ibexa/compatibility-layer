<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\CompatibilityLayer\DependencyInjection\Compiler;

use Ibexa\CompatibilityLayer\FullyQualifiedNameResolverInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

final class ValueObjectVisitorTagCompatibilityPass implements CompilerPassInterface
{
    public const SERVICE_TAG = 'ezpublish_rest.output.value_object_visitor';

    /** @var \Ibexa\CompatibilityLayer\FullyQualifiedNameResolverInterface */
    private $nameResolver;

    public function __construct(FullyQualifiedNameResolverInterface $nameResolver)
    {
        $this->nameResolver = $nameResolver;
    }

    public function process(ContainerBuilder $container): void
    {
        $taggedServices = $container->findTaggedServiceIds(self::SERVICE_TAG);
        foreach ($taggedServices as $serviceId => $serviceTags) {
            $definition = $container->getDefinition($serviceId);
            $this->processServiceTags($serviceTags, $definition);
        }
    }

    private function processServiceTags($serviceTags, Definition $definition): void
    {
        foreach ($serviceTags as $serviceTag) {
            $newName = isset($serviceTag['type'])
                ? $this->nameResolver->resolve($serviceTag['type'])
                : null;

            if (null === $newName) {
                continue;
            }

            $serviceTag['type'] = $newName;
            $definition->addTag(self::SERVICE_TAG, $serviceTag);
        }
    }
}
