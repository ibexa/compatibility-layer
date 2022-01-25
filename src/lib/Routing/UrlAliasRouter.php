<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\CompatibilityLayer\Routing;

use Ibexa\Bundle\Core\Routing\UrlAliasRouter as RebrandedUrlAliasRouter;

class UrlAliasRouter extends RebrandedUrlAliasRouter
{
    public const URL_ALIAS_ROUTE_NAME = 'ez_urlalias';
}
