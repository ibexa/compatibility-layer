<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\CompatibilityLayer\DependencyInjection\Compiler;

use Ibexa\CompatibilityLayer\BundleResolver\BundleNameResolver;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TwigPass implements CompilerPassInterface
{
    /** @var \Ibexa\CompatibilityLayer\BundleResolver\BundleNameResolver */
    private BundleNameResolver $bundleNameResolver;

    public function __construct(BundleNameResolver $bundleNameResolver)
    {
        $this->bundleNameResolver = $bundleNameResolver;
    }

    public function process(ContainerBuilder $container)
    {
        $loader = $container->getDefinition('twig.loader.native_filesystem');

        $methodCalls = $loader->getMethodCalls();

        foreach ($methodCalls as $methodCall) {
            if (!isset($methodCall[1][1])) {
                continue;
            }
            $newBundleName = $methodCall[1][1];
            $oldBundleName = $this->bundleNameResolver->resolve($newBundleName);
            if ($methodCall[0] === 'addPath' && $oldBundleName) {
                $newPath = $methodCall[1][0];
                $loader->addMethodCall('addPath', [$newPath, $oldBundleName]);
                $loader->addMethodCall('addPath', [$newPath, '!' . $oldBundleName]);
            }
        }
    }
}
