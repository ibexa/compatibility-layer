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

        $output = $this->replace($this->routeNamesMap, $output);
        $output = $this->replace($this->serviceTagNamesMap, $output);

        foreach ($this->classParametersMap as $classParameter => $fqcn) {
            $output = preg_replace(
                '/%' . preg_quote($classParameter) . '%/',
                $fqcn,
                $output
            );

            $output = preg_replace(
                "/^\\s*<parameter key=[\"']" . preg_quote($classParameter) . "[\"']>.*<\/parameter>\n/m",
                '',
                $output
            );

            $output = preg_replace(
                "/^\\s*<parameters>\\s*<\/parameters>\n/m",
                '',
                $output
            );

            $output = preg_replace(
                "/^(\\s*)<service id=([\"'])" . preg_quote($fqcn) . "[\"']([^>]*) class=[\"']" . preg_quote($fqcn) . "[\"'](\n|)(\\s|)\\s*(>|)/m",
                '${1}<service id=${2}' . $fqcn . '${2}${3}${5}${6}',
                $output
            );
        }

        return $output;
    }

    public function getFileNamePatterns(): array
    {
        return [
            '*.xml',
        ];
    }

    protected function makeQuotedPattern(string $subject): string
    {
        return '/["\']' . preg_quote($subject) . '["\']/';
    }

    protected function makeQuotedReplacement(string $subject): string
    {
        return '"' . $subject . '"';
    }

    protected function replace(array $map, string $input): string
    {
        return str_replace(
            array_map([$this, 'makeQuotedPattern'], array_keys($map)),
            array_map([$this, 'makeQuotedReplacement'], array_values($map)),
            $input
        );
    }
}
