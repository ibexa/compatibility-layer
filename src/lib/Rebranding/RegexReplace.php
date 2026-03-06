<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\CompatibilityLayer\Rebranding;

final class RegexReplace
{
    private function __construct()
    {
    }

    public static function replace(string $pattern, string $replacement, string $subject): string
    {
        $result = preg_replace($pattern, $replacement, $subject);
        if (\is_string($result)) {
            return $result;
        }

        if (null === $result) {
            throw new \RuntimeException(
                sprintf('preg_replace() failed for pattern "%s" (preg_last_error=%d).', $pattern, preg_last_error())
            );
        }

        throw new \RuntimeException(
            sprintf('Unexpected preg_replace() result type "%s".', gettype($result))
        );
    }
}
