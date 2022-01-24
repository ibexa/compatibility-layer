<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\CompatibilityLayer\Rebranding;

class XmlRebranding extends ResourceRebranding
{
    public function rebrand(string $input): string
    {
        $output = parent::rebrand($input);
        $output = str_replace(array_keys($this->routeNamesMap), array_values($this->routeNamesMap), $output);

        return $output;
    }

    public function getFileNamePatterns(): array
    {
        return [
            '*.xml',
        ];
    }
}
