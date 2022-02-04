<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\CompatibilityLayer\DependencyInjection\Compiler;

use Ibexa\Bundle\CompatibilityLayer\IbexaCompatibilityLayerBundle;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class ContainerParameterCompatibilityPass implements CompilerPassInterface
{
    /** @var array<string, string> */
    private array $containerParameterNameMap;

    public function __construct()
    {
        $this->containerParameterNameMap = require IbexaCompatibilityLayerBundle::MAPPINGS_PATH . '/container-parameters-map.php';
    }

    public function process(ContainerBuilder $container): void
    {
        foreach ($this->containerParameterNameMap as $legacyParameterName => $newParameterName) {
            // BC use case: set - legacy parameter explicitly set by a project takes precedence
            if ($container->hasParameter($legacyParameterName)) {
                $container->setParameter(
                    $newParameterName,
                    $container->getParameter($legacyParameterName)
                );
            } elseif ($container->hasParameter($newParameterName)) {
                // BC use case: fetch - all non-set legacy parameters are set using the new parameter value
                $container->setParameter(
                    $legacyParameterName,
                    $container->getParameter($newParameterName)
                );
            }
        }
    }
}
