<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\CompatibilityLayer\Command;

use Ibexa\Bundle\CompatibilityLayer\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder as SymfonyContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @internal
 *
 * @see \Symfony\Bundle\FrameworkBundle\Command\BuildDebugContainerTrait
 */
trait BuildDebugContainerTrait
{
    /**
     * Loads the ContainerBuilder from the cache.
     *
     * @throws \LogicException|\Exception
     */
    protected function getContainerBuilder(KernelInterface $kernel): SymfonyContainerBuilder
    {
        if ($this->containerBuilder) {
            return $this->containerBuilder;
        }

        /** @var string $debugContainerDump */
        $debugContainerDump = $kernel->getContainer()->getParameter('debug.container.dump');

        /** @var \Symfony\Component\HttpKernel\KernelInterface&\Ibexa\CompatibilityLayer\PHPStan\DebugContainerKernelInterface $debugKernel */
        $debugKernel = $kernel;

        if (!$debugKernel->isDebug() || !(new ConfigCache($debugContainerDump, true))->isFresh()) {
            $buildContainer = \Closure::bind(function () {
                $this->initializeBundles();

                return $this->buildContainer();
            }, $debugKernel, \get_class($debugKernel));
            $container = $buildContainer();
            $container->getCompilerPassConfig()->setRemovingPasses([]);
            $container->getCompilerPassConfig()->setAfterRemovingPasses([]);
            $container->compile();
        } else {
            (new XmlFileLoader($container = new ContainerBuilder(), new FileLocator()))->load($debugContainerDump);
            $locatorPass = new ServiceLocatorTagPass();
            $locatorPass->process($container);
        }

        return $this->containerBuilder = $container;
    }
}
