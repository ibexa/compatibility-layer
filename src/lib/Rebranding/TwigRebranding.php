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
    /** @var array<string, string> */
    private array $twigFunctions;

    /** @var array<string, string> */
    private array $twigFilters;

    /** @var array<string, string> */
    private array $wildcardFunctions = [
        'ez_render_(.*?)_query_(.*?)' => 'ibexa_render_${1}_query_${2}',
        'ez_render_(.*?)_query' => 'ibexa_render_${1}_query',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->twigFunctions = require IbexaCompatibilityLayerBundle::MAPPINGS_PATH . \DIRECTORY_SEPARATOR . 'twig-functions-map.php';
        $this->twigFilters = require IbexaCompatibilityLayerBundle::MAPPINGS_PATH . \DIRECTORY_SEPARATOR . 'twig-filters-map.php';
    }

    /** @return list<string> */
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
            $output = RegexReplace::replace(
                '/(?<!_)' . preg_quote($oldFunction, '/') . '\(/m',
                $newFunction . '(',
                $output
            );
        }

        foreach ($this->wildcardFunctions as $matchOld => $matchNew) {
            $output = RegexReplace::replace(
                '/' . $matchOld . '\(/m',
                $matchNew . '(',
                $output
            );
        }

        return $output;
    }

    private function rebrandTwigFilters(string $output): string
    {
        foreach ($this->twigFilters as $oldFilter => $newFilter) {
            $output = RegexReplace::replace(
                '/\|' . preg_quote($oldFilter, '/') . '/m',
                '|' . $newFilter,
                $output
            );
        }

        return $output;
    }
}
