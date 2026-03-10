<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\CompatibilityLayer\Rebranding;

class JsRebranding extends ResourceRebranding
{
    public function rebrand(string $input): string
    {
        $output = $input;

        foreach ($this->bundleNameMap as $oldBundleName => $newBundleName) {
            $output = str_replace(
                'bundles/' . strtolower($oldBundleName),
                'bundles/' . strtolower($newBundleName),
                $output
            );
        }

        $output = str_replace(array_keys($this->routeNamesMap), array_values($this->routeNamesMap), $output);
        $output = preg_replace('/(["\'])ez(publish|platform)(["\'])/', '${1}ibexa${3}', $output);

        return $output;
    }

    public function getFileNamePatterns(): array
    {
        return [
            '*.js',
            '*.jsx',
        ];
    }
}
