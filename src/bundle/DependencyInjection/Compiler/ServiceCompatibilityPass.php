<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\CompatibilityLayer\DependencyInjection\Compiler;

use Ibexa\CompatibilityLayer\ServiceResolver\ServiceNameResolver;
use Ibexa\CompatibilityLayer\FullyQualifiedNameResolverInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Form\FormTypeInterface;

final class ServiceCompatibilityPass implements CompilerPassInterface
{
    /** @var \Ibexa\CompatibilityLayer\FullyQualifiedNameResolverInterface */
    private $fqcnNameResolver;

    private ServiceNameResolver $serviceNameResolver;

    public function __construct(
        FullyQualifiedNameResolverInterface $fqcnNameResolver,
        ServiceNameResolver $serviceNameResolver
    ) {
        $this->fqcnNameResolver = $fqcnNameResolver;
        $this->serviceNameResolver = $serviceNameResolver;
    }

    public function process(ContainerBuilder $container): void
    {
        foreach ($container->getAliases() as $name => $definition) {
            $oldClassName = $this->fqcnNameResolver->resolve($name);

            if (!empty($oldClassName) && !$container->hasAlias($oldClassName)) {
                $container->setAlias($oldClassName, $name);
            }

            $oldServiceName = $this->serviceNameResolver->resolve($name);

            if (!empty($oldServiceName) && !$container->hasAlias($oldServiceName)) {
                $container->setAlias($oldServiceName, $name);
            }
        }

        foreach ($container->getDefinitions() as $name => $definition) {
            $oldClassName = $this->fqcnNameResolver->resolve($name);
            $this->setAlias($oldClassName, $container, $name, $definition);

            $oldServiceName = $this->serviceNameResolver->resolve($name);
            if ($oldServiceName !== null) {
                $container->setAlias($oldServiceName, $name);
            }
        }
    }

    private function isFormType(string $name): bool
    {
        return is_a($name, FormTypeInterface::class, true);
    }

    protected function setAlias(
        ?string $oldClassName,
        ContainerBuilder $container,
        string $name,
        Definition $definition
    ): void {
        if (!empty($oldClassName) && !$container->hasDefinition($oldClassName)) {
            if ($this->isFormType($name)) {
                $classExists = class_exists($name) && class_exists($oldClassName, false);
                if ($classExists) {
                    if ($container->hasAlias($oldClassName)) {
                        $container->removeAlias($oldClassName);
                    }

                    $newDefinition = clone $definition;
                    $newDefinition->setClass($oldClassName);
                    $container->setDefinition($oldClassName, $newDefinition);
                }
            }

            if (!$container->hasDefinition($oldClassName)) {
                $container->setAlias($oldClassName, $name);
            }
        }
    }
}
