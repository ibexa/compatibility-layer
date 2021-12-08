<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\CompatibilityLayer\Twig;

use Ibexa\DesignEngine\Templating\ThemeTemplateNameResolver;

class LegacyDesignThemeTemplateNameResolver extends ThemeTemplateNameResolver
{
    public const DESIGN_NAMESPACE = 'ezdesign';
}
