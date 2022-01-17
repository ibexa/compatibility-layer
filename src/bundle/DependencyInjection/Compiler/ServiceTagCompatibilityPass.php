<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\CompatibilityLayer\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @internal
 */
final class ServiceTagCompatibilityPass implements CompilerPassInterface
{
    /** @var array<string, string> */
    private $serviceTagNameMap;

    public function __construct()
    {
        $this->serviceTagNameMap = require __DIR__ . '/../../Resources/mappings/symfony-service-tag-name-map.php';
    }

    public function process(ContainerBuilder $container): void
    {
        foreach ($this->serviceTagNameMap as $legacyTag => $newTag) {
            $this->addNewTag($container, $legacyTag, $newTag);
        }
    }

    private function addNewTag(
        ContainerBuilder $containerBuilder,
        string $legacyTag,
        string $newTag
    ): void {
        $serviceIds = $containerBuilder->findTaggedServiceIds($legacyTag);
        foreach ($serviceIds as $serviceId => $tags) {
            $serviceDefinition = $containerBuilder->getDefinition($serviceId);
            // if a service has been already manually tagged, it is assumed it was fully and properly handled
            if ($serviceDefinition->hasTag($newTag)) {
                continue;
            }

            foreach ($tags as $tag) {
                $serviceDefinition->addTag($newTag, $tag);
            }
        }
    }
}
