<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\CompatibilityLayer\FullyQualifiedNameResolver;

use Ibexa\Bundle\CompatibilityLayer\IbexaCompatibilityLayerBundle;
use Ibexa\CompatibilityLayer\FullyQualifiedNameResolverInterface;

final class ClassMapResolver implements FullyQualifiedNameResolverInterface
{
    /** @var array */
    private $classMap;

    public function __construct(bool $reverse = false)
    {
        /** @noinspection PhpIncludeInspection */
        $this->classMap = require IbexaCompatibilityLayerBundle::MAPPINGS_PATH . \DIRECTORY_SEPARATOR . 'class-map.php';

        if ($reverse) {
            $this->classMap = array_flip($this->classMap);
        }
    }

    public function resolve(string $fullyQualifiedName): ?string
    {
        return $this->classMap[$fullyQualifiedName] ?? null;
    }

    public function getMap(): array
    {
        return $this->classMap;
    }
}
