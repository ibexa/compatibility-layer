<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\CompatibilityLayer\ServiceResolver;

use Ibexa\Bundle\CompatibilityLayer\IbexaCompatibilityLayerBundle;

final class ServiceNameResolver
{
    /** @var array<string, string> */
    private array $serviceNamesMap;

    public function __construct(bool $reverse = false)
    {
        /** @noinspection PhpIncludeInspection */
        $serviceNamesMap = require IbexaCompatibilityLayerBundle::MAPPINGS_PATH . \DIRECTORY_SEPARATOR . 'services-to-fqcn-map.php';

        $this->serviceNamesMap = $reverse ? array_flip($serviceNamesMap) : $serviceNamesMap;
    }

    public function resolve(string $name): ?string
    {
        return $this->serviceNamesMap[$name] ?? null;
    }

    public function getMap(): array
    {
        return $this->serviceNamesMap;
    }
}
