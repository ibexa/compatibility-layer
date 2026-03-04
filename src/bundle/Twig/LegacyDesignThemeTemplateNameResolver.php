<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\CompatibilityLayer\Twig;

use Ibexa\DesignEngine\Templating\TemplateNameResolverInterface;
use Ibexa\DesignEngine\Templating\ThemeTemplateNameResolver;

class LegacyDesignThemeTemplateNameResolver extends ThemeTemplateNameResolver
{
    public const LEGACY_DESIGN_NAMESPACE = TemplateNameResolverInterface::EZ_DESIGN_NAMESPACE;

    public function resolveTemplateName($name)
    {
        if (!$this->isTemplateDesignNamespaced($name)) {
            return $name;
        }

        return str_replace(
            '@' . self::LEGACY_DESIGN_NAMESPACE,
            '@' . $this->getCurrentDesign(),
            $name
        );
    }

    public function isTemplateDesignNamespaced($name)
    {
        return (strpos($name, '@' . self::LEGACY_DESIGN_NAMESPACE) !== false)
            || (strpos($name, '@' . $this->getCurrentDesign()) !== false);
    }
}
