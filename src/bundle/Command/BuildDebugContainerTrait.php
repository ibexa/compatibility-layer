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
    protected $containerBuilder;

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

        if (!$kernel->isDebug() || !(new ConfigCache($kernel->getContainer()->getParameter('debug.container.dump'), true))->isFresh()) {
            $buildContainer = \Closure::bind(function () {
                $this->initializeBundles();

                return $this->buildContainer();
            }, $kernel, \get_class($kernel));
            $container = $buildContainer();
            $container->getCompilerPassConfig()->setRemovingPasses([]);
            $container->getCompilerPassConfig()->setAfterRemovingPasses([]);
            $container->compile();
        } else {
            (new XmlFileLoader($container = new ContainerBuilder(), new FileLocator()))->load($kernel->getContainer()->getParameter('debug.container.dump'));
            $locatorPass = new ServiceLocatorTagPass();
            $locatorPass->process($container);
        }

        return $this->containerBuilder = $container;
    }
}
