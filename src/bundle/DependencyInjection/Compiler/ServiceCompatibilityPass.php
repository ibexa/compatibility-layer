<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\CompatibilityLayer\DependencyInjection\Compiler;

use Ibexa\CompatibilityLayer\FullyQualifiedNameResolverInterface;
use Ibexa\CompatibilityLayer\ServiceResolver\ServiceNameResolver;
use Symfony\Component\DependencyInjection\Alias;
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
        foreach ($container->getAliases() as $name => $alias) {
            $oldClassName = $this->fqcnNameResolver->resolve($name);

            if (!empty($oldClassName) && !$container->hasAlias($oldClassName)) {
                $container->setAlias($oldClassName, $name);
            }

            $oldServiceName = $this->serviceNameResolver->resolve($name);

            if (!empty($oldServiceName) && !$container->hasAlias($oldServiceName)) {
                $container->setAlias($oldServiceName, $alias);
            }
        }

        foreach ($container->getDefinitions() as $name => $definition) {
            $oldClassName = $this->fqcnNameResolver->resolve($name);
            $this->setAlias($oldClassName, $container, $name, $definition);

            $oldServiceName = $this->serviceNameResolver->resolve($name);
            if ($oldServiceName !== null) {
                if ($this->isController($definition) || $definition->isPublic()) {
                    $alias = new Alias($name, true);
                    $container->setAlias($oldServiceName, $alias);
                } else {
                    $container->setAlias($oldServiceName, $name);
                }
            }
        }
    }

    private function isFormType(string $name): bool
    {
        return is_a($name, FormTypeInterface::class, true);
    }

    private function setAlias(
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

            if ($this->isController($definition) || $definition->isPublic()) {
                $alias = new Alias($name, true);
                $container->setAlias($oldClassName, $alias);

                return;
            }

            if (!$container->hasDefinition($oldClassName)) {
                $container->setAlias($oldClassName, $name);
            }
        }
    }

    private function isController(Definition $definition): bool
    {
        return $definition->hasTag('controller.service_arguments');
    }
}
