<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\CompatibilityLayer\DependencyInjection\Compiler;

use Ibexa\Bundle\CompatibilityLayer\Twig\LegacyDesignThemeTemplateNameResolver;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @internal
 */
final class AssetThemeCompatibilityPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $container
            ->findDefinition('assets.packages')
            ->addMethodCall(
                'addPackage',
                [
                    LegacyDesignThemeTemplateNameResolver::DESIGN_NAMESPACE,
                    $container->findDefinition('ezdesign.asset_theme_package'),
                ]
            );
    }
}
