<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\CompatibilityLayer\BundleResolver;

use Ibexa\Bundle\CompatibilityLayer\IbexaCompatibilityLayerBundle;
use Ibexa\CompatibilityLayer\FullyQualifiedNameResolverInterface;

final class BundleNameResolver
{
    /** @var array<string, string> */
    private $bundleNamesMap;

    public function __construct(bool $reverse = false)
    {
        /** @noinspection PhpIncludeInspection */
        $bundleNamesMap = require IbexaCompatibilityLayerBundle::MAPPINGS_PATH . \DIRECTORY_SEPARATOR . 'bundle-name-map.php';

        $this->bundleNamesMap = $reverse ? array_flip($bundleNamesMap) : $bundleNamesMap;
    }

    public function resolve(string $bundleName, bool $useBundleSuffix = false): ?string
    {
        if ($useBundleSuffix) {
            $bundleName = substr($bundleName, 0, -strlen('Bundle'));
        }

        $newBundleName = $this->bundleNamesMap[$bundleName] ?? null;

        if ($newBundleName && $useBundleSuffix) {
            return $newBundleName . 'Bundle';
        }

        return $newBundleName;
    }
}
