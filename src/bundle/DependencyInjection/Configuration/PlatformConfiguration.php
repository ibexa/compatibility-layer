<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\CompatibilityLayer\DependencyInjection\Configuration;

use Ibexa\Bundle\CompatibilityLayer\DependencyInjection\Extension\PlatformExtension;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class PlatformConfiguration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder(PlatformExtension::EXTENSION_ALIAS);

        $rootNode = $treeBuilder->getRootNode();

        $rootNode->variablePrototype();

        return $treeBuilder;
    }
}

class_alias(PlatformConfiguration::class, 'EzSystems\EzPlatformCoreBundle\DependencyInjection\Configuration');
