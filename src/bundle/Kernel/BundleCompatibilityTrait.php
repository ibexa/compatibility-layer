<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\CompatibilityLayer\Kernel;

/**
 * Meant to be used in App Kernel extending {@see \Symfony\Component\HttpKernel\Kernel}.
 */
trait BundleCompatibilityTrait
{
    use BundleNameCompatibilityTrait;
    use BundleExtensionCompatibilityTrait;
}
