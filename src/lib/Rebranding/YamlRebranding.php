<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\CompatibilityLayer\Rebranding;

class YamlRebranding extends ResourceRebranding
{
    public function rebrand(string $input): string
    {
        $output = parent::rebrand($input);

        $output = $this->rebrandExtension($output);
        $output = $this->rebrandRouteNames($output);
        $output = $this->rebrandServiceTagNames($output);
        $output = $this->replaceClassParameters($output);
        $output = $this->replaceContainerParameters($output);
        $output = $this->replaceConfigResolverNamespaces($output);

        return $output;
    }

    protected function rebrandExtension(string $input): string
    {
        $output = $input;

        foreach ($this->extensionMap as $oldExtension => $newExtension) {
            $output = preg_replace(
                '/^' . $oldExtension . ':$/m',
                $newExtension . ':',
                $output
            );
        }

        return $output;
    }

    protected function replaceClassParameters(string $input): string
    {
        $output = $input;

        foreach ($this->classParametersMap as $classParameter => $fqcn) {
            $output = preg_replace(
                '/["\']%' . preg_quote($classParameter) . '%["\']/',
                $fqcn,
                $output
            );

            $output = preg_replace(
                '/^\\s*' . preg_quote($classParameter) . ":.*\n/m",
                '',
                $output
            );

            $output = preg_replace(
                '/^(\\s+)' . preg_quote($fqcn) . ":\n((\\s+:[^\n]+)*)(\\s+)class: " . preg_quote($fqcn) . "\n/m",
                '${1}' . $fqcn . ":\n" . '${2}',
                $output
            );

            $output = preg_replace(
                '/^(\\s+)' . preg_quote($fqcn) . ":\n\n/m",
                '${1}' . $fqcn . ": ~\n\n",
                $output
            );
        }

        return $output;
    }

    protected function replaceContainerParameters(string $input): string
    {
        $output = $input;
        foreach ($this->containerParametersMap as $legacyParameter => $newParameter) {
            $output = preg_replace(
                '/%' . preg_quote($legacyParameter, '/') . '%/',
                "%$newParameter%",
                $output
            );

            $output = preg_replace(
                '/' . preg_quote($legacyParameter, '/') . ':/',
                "$newParameter:",
                $output
            );
        }

        return $output;
    }

    protected function rebrandRouteNames(string $input): string
    {
        $output = $input;

        foreach ($this->routeNamesMap as $oldRouteName => $newRouteName) {
            $output = preg_replace(
                '/^' . preg_quote($oldRouteName, '/') . ':$/m',
                $newRouteName . ':',
                $output
            );
        }

        return $output;
    }

    protected function rebrandServiceTagNames(string $input): string
    {
        $output = $input;

        foreach ($this->serviceTagNamesMap as $oldServiceTagName => $newServiceTagName) {
            $output = preg_replace(
                '/([^%@a-zA-Z0-9\._])' . preg_quote($oldServiceTagName, '/') . '([^a-zA-Z0-9\._])/',
                '${1}' . $newServiceTagName . '${2}',
                $output
            );
        }

        return $output;
    }

    private function replaceConfigResolverNamespaces(string $input): string
    {
        $output = $input;
        foreach ($this->configResolverNamespacesMap as $legacyNamespace => $newNamespace) {
            // pattern <spaces><legacy_namespace>.<any_site_access_string>.
            // edge-case: default config resolver parameters are grouped under namespace key param
            $output = preg_replace(
                '/( +|%)' . preg_quote($legacyNamespace, '/') . '(\.[a-zA-Z0-9_-]+\.|:)/',
                '${1}' . $newNamespace . '${2}',
                $output
            );
        }

        return $output;
    }

    public function getFileNamePatterns(): array
    {
        return [
            '*.yml',
            '*.yaml',
        ];
    }
}
