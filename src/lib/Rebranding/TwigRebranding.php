<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\CompatibilityLayer\Rebranding;

use Ibexa\Bundle\CompatibilityLayer\IbexaCompatibilityLayerBundle;

class TwigRebranding extends ResourceRebranding
{
    private array $twigFunctions;

    private array $twigFilters;

    public function __construct()
    {
        parent::__construct();
        $this->twigFunctions = require IbexaCompatibilityLayerBundle::MAPPINGS_PATH . \DIRECTORY_SEPARATOR . 'twig-functions-map.php';
        $this->twigFilters = require IbexaCompatibilityLayerBundle::MAPPINGS_PATH . \DIRECTORY_SEPARATOR . 'twig-filters-map.php';
    }

    public function getFileNamePatterns(): array
    {
        return [
            '*.twig',
        ];
    }

    public function rebrand(string $input): string
    {
        $output = parent::rebrand($input);

        $output = $this->rebrandRoutes($output);
        $output = $this->rebrandTwigFunctions($output);
        $output = $this->rebrandTwigFilters($output);

        return $output;
    }

    private function rebrandRoutes(string $output): string
    {
        return str_replace(array_keys($this->routeNamesMap), array_values($this->routeNamesMap), $output);
    }

    private function rebrandTwigFunctions(string $output): string
    {
        foreach ($this->twigFunctions as $oldFunction => $newFunction) {
            $output = preg_replace(
                '/' . $oldFunction . '\(/m',
                '${1}' . $newFunction . '(',
                $output
            );
        }

        return $output;
    }

    private function rebrandTwigFilters(string $output): string
    {
        foreach ($this->twigFilters as $oldFilter => $newFilter) {
            $output = preg_replace(
                '/\|' . $oldFilter . '/m',
                '${1}' . '|' .$newFilter ,
                $output
            );
        }

        return $output;
    }
}
