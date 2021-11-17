<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\CompatibilityLayer\FullyQualifiedNameResolver;

use Ibexa\Bundle\CompatibilityLayer\IbexaCompatibilityLayerBundle;
use Ibexa\CompatibilityLayer\FullyQualifiedNameResolverInterface;

final class PSR4PrefixResolver implements FullyQualifiedNameResolverInterface
{
    /** @var array[] */
    private $psr4map;

    public function __construct(bool $reverse = false)
    {
        /** @noinspection PhpIncludeInspection */
        $psr4map = require IbexaCompatibilityLayerBundle::MAPPINGS_PATH . \DIRECTORY_SEPARATOR . 'psr4-map.php';

        if ($reverse) {
            $psr4map = array_flip($psr4map);
        }

        // sort and reverse array to ensure the longest matching prefix is found first
        ksort($psr4map);
        $this->psr4map = array_reverse($psr4map);
    }

    public function resolve(string $fullyQualifiedName): ?string
    {
        foreach ($this->psr4map as $legacyPrefix => $newPrefix) {
            if (0 !== strpos($fullyQualifiedName, $legacyPrefix)) {
                continue;
            }

            return str_replace($legacyPrefix, $newPrefix, $fullyQualifiedName);
        }

        return null;
    }
}
