<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\CompatibilityLayer\DependencyInjection\Compiler;

use Ibexa\CompatibilityLayer\FullyQualifiedNameResolverInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class AliasDecoratorCompatibilityPass implements CompilerPassInterface
{
    /** @var \Ibexa\CompatibilityLayer\FullyQualifiedNameResolverInterface */
    private $nameResolver;

    public function __construct(FullyQualifiedNameResolverInterface $nameResolver)
    {
        $this->nameResolver = $nameResolver;
    }

    public function process(ContainerBuilder $container): void
    {
        foreach ($container->getDefinitions() as $name => $definition) {
            if ($definition->getDecoratedService() === null) {
                continue;
            }
            [$decoratedId, $newId, $priority] = $definition->getDecoratedService();

            $newDecoratedId = $this->nameResolver->resolve($decoratedId);

            if ($newDecoratedId !== null) {
                $classExists = class_exists($decoratedId) && class_exists($newDecoratedId, false);
                if ($classExists) {
                    $definition->setDecoratedService(
                        $newDecoratedId,
                        $newId,
                        $priority
                    );
                    $container->setDefinition($name, $definition);
                }
            }
        }
    }
}
