<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\CompatibilityLayer\Kernel;

use Ibexa\CompatibilityLayer\BundleResolver\BundleNameResolver;

/**
 * Meant to be used in App Kernel extending {@see \Symfony\Component\HttpKernel\Kernel}.
 */
trait BundleNameCompatibilityTrait
{
    protected ?BundleNameResolver $bundleNameResolver = null;
    protected string $compatibilityLayerBundleName = 'IbexaCompatibilityLayerBundle';

    public function getBundle(string $name)
    {
        if (!isset($this->bundles[$this->compatibilityLayerBundleName])) {
            return parent::getBundle($name);
        }

        if ($this->bundleNameResolver === null) {
            $this->bundleNameResolver = new BundleNameResolver();
        }

        $newBundleName = $this->bundleNameResolver->resolve($name, true);
        if (null !== $newBundleName) {
            trigger_deprecation(
                'ibexa/compatibility-layer',
                '4.0.0',
                sprintf('Support for old bundle names is deprecated, please update from %s, to %s', $name, $newBundleName)
            );

            return parent::getBundle($newBundleName);
        }

        return parent::getBundle($name);
    }
}
