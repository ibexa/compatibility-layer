<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\CompatibilityLayer\ConfigResolver;

use Ibexa\Bundle\CompatibilityLayer\IbexaCompatibilityLayerBundle;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;

/**
 * Maps legacy namespace to the new one, but only if the parameter in the old namespace is not defined.
 * Explicit old definition may come from a custom project configuration.
 *
 * @internal
 */
final class BackwardCompatibleConfigResolver implements ConfigResolverInterface
{
    public const LEGACY_DEFAULT_NAMESPACE = 'ezsettings';

    private ConfigResolverInterface $chainConfigResolver;

    /** @var array<string, string */
    private array $configResolverNamespacesMap;

    public function __construct(ConfigResolverInterface $chainConfigResolver)
    {
        $this->chainConfigResolver = $chainConfigResolver;
        $this->configResolverNamespacesMap = require IbexaCompatibilityLayerBundle::MAPPINGS_PATH . '/config-resolver-namespaces-map.php';
    }

    public function getParameter(
        string $paramName,
        ?string $namespace = null,
        ?string $scope = null
    ) {
        if ($this->chainConfigResolver->hasParameter($paramName, $namespace, $scope)) {
            return $this->chainConfigResolver->getParameter($paramName, $namespace, $scope);
        }
        $namespace ??= self::LEGACY_DEFAULT_NAMESPACE;
        if ($this->chainConfigResolver->hasParameter($paramName, $namespace, $scope)) {
            return $this->chainConfigResolver->getParameter($paramName, $namespace, $scope);
        }

        return $this->chainConfigResolver->getParameter(
            $paramName,
            $this->configResolverNamespacesMap[$namespace] ?? $namespace,
            $scope
        );
    }

    public function hasParameter(
        string $paramName,
        ?string $namespace = null,
        ?string $scope = null
    ): bool {
        $resolvedNamespace = $this->configResolverNamespacesMap[$namespace] ?? $namespace;
        if (
            isset($this->configResolverNamespacesMap[$namespace]) &&
            true === $this->chainConfigResolver->hasParameter($paramName, $namespace, $scope)
        ) {
            return true;
        }

        return $this->chainConfigResolver->hasParameter($paramName, $resolvedNamespace, $scope);
    }

    public function setDefaultNamespace(string $defaultNamespace): void
    {
        $this->chainConfigResolver->setDefaultNamespace($defaultNamespace);
    }

    public function getDefaultNamespace(): string
    {
        return $this->chainConfigResolver->getDefaultNamespace();
    }
}
