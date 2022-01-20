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

    protected function rebrandRouteNames(string $input): string
    {
        $output = $input;

        foreach ($this->routeNamesMap as $oldRouteName => $newRouteName) {
            $output = preg_replace(
                '/^' . $oldRouteName . ':$/m',
                $newRouteName . ':',
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
