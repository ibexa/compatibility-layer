<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\CompatibilityLayer\Kernel;

use Ibexa\Bundle\CompatibilityLayer\DependencyInjection\ContainerBuilder as IbexaContainerBuilder;
use Ibexa\Tests\CompatibilityLayer\Kernel\Stub\KernelStub;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Ibexa\Bundle\CompatibilityLayer\Kernel\BundleExtensionCompatibilityTrait
 */
final class BundleExtensionCompatibilityTraitTest extends TestCase
{
    private KernelStub $appKernel;

    protected function setUp(): void
    {
        $this->appKernel = new KernelStub('dev', false);
    }

    public function testGetContainerBuilder(): void
    {
        self::assertInstanceOf(
            IbexaContainerBuilder::class,
            $this->appKernel->getIbexaContainerBuilder()
        );
    }
}
