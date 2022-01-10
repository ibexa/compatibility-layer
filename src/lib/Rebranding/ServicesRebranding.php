<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\CompatibilityLayer\Rebranding;

use Ibexa\Bundle\CompatibilityLayer\IbexaCompatibilityLayerBundle;

class ServicesRebranding extends ResourceRebranding
{
    /** @var array<string, string> */
    protected array $servicesMap;

    public function __construct()
    {
        $this->servicesMap = require IbexaCompatibilityLayerBundle::MAPPINGS_PATH . \DIRECTORY_SEPARATOR . 'services-to-fqcn-map.php';
        parent::__construct();
    }

    public function rebrand(string $input): string
    {
        $output = $input;
        foreach ($this->servicesMap as $oldServiceName => $newServiceName) {
            $output = preg_replace(
                '/(?<!\.|_)' . preg_quote($oldServiceName) . '(?=[\'\":]|$)/m',
                '${1}' . $newServiceName,
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
            '*.twig',
        ];
    }
}
