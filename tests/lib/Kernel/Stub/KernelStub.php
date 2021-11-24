<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\CompatibilityLayer\Kernel\Stub;

use Ibexa\Bundle\CompatibilityLayer\Kernel\BundleCompatibilityTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

final class KernelStub extends Kernel
{
    use BundleCompatibilityTrait;

    public function registerBundles(): iterable
    {
        yield from [];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        // Nothing to do
    }

    public function getIbexaContainerBuilder(): ContainerBuilder
    {
        return $this->getContainerBuilder();
    }
}
