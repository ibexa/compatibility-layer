<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\CompatibilityLayer\Rebranding;

class CssRebranding extends ResourceRebranding
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

        $output = preg_replace('/(["\'])ez(publish|platform)(["\'])/', '${1}ibexa${3}', $output);

        return $output;
    }

    public function getFileNamePatterns(): array
    {
        return [
            '*.css',
            '*.scss',
        ];
    }
}
