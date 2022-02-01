<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\CompatibilityLayer\HttpKernel\Controller;

use Ibexa\CompatibilityLayer\FullyQualifiedNameResolver\AggregateResolver;
use Ibexa\CompatibilityLayer\FullyQualifiedNameResolver\ClassMapResolver;
use Ibexa\CompatibilityLayer\FullyQualifiedNameResolver\PSR4PrefixResolver;
use Ibexa\CompatibilityLayer\FullyQualifiedNameResolverInterface;
use Ibexa\CompatibilityLayer\ServiceResolver\ServiceNameResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;

class ControllerResolver implements ControllerResolverInterface
{
    private ControllerResolverInterface $controllerResolver;

    private FullyQualifiedNameResolverInterface $fqcnNameResolver;

    private ServiceNameResolver $serviceNameResolver;

    public function __construct(
        ControllerResolverInterface $controllerResolver
    ) {
        $this->controllerResolver = $controllerResolver;

        $this->fqcnNameResolver = new AggregateResolver([
            new ClassMapResolver(),
            new PSR4PrefixResolver(),
        ]);
        $this->serviceNameResolver = new ServiceNameResolver();
    }

    /**
     * {@inheritdoc}
     */
    public function getController(Request $request)
    {
        if (!$controller = $request->attributes->get('_controller')) {
            return $this->controllerResolver->getController($request);
        }

        if (\is_array($controller)) {
            if (isset($controller[0]) && \is_string($controller[0]) && isset($controller[1])) {
                $controller[0] = $this->getNewControllerName($controller[0]);
                $request->attributes->set('_controller', $controller);

                return $this->controllerResolver->getController($request);
            }
        }

        if (\is_object($controller) || function_exists($controller)) {
            return $this->controllerResolver->getController($request);
        }

        $method = null;
        $separator = '';
        if (str_contains($controller, '::')) {
            $separator = '::';
        } elseif (str_contains($controller, ':')) {
            $separator = ':';
        }

        if ($separator !== '') {
            [$class, $method] = explode($separator, $controller, 2);
        } else {
            $class = $controller;
        }

        $newName = $this->getNewControllerName($class);
        $controller = implode($separator, $method ? [$newName, $method] : [$newName]);
        $request->attributes->set('_controller', $controller);

        return $this->controllerResolver->getController($request);
    }

    private function getNewControllerName(string $oldName): string
    {
        $newClass = $this->fqcnNameResolver->resolve($oldName);
        if ($newClass !== null) {
            return $newClass;
        }

        $newName = $this->serviceNameResolver->resolve($oldName);
        if ($newName !== null) {
            return $newName;
        }

        return $oldName;
    }
}
