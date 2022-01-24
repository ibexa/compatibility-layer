<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\CompatibilityLayer\DependencyInjection\Security;

use Ibexa\Bundle\Rest\DependencyInjection\Security\RestSessionBasedFactory as BaseRestSessionBasedFactory;

class RestSessionBasedFactory extends BaseRestSessionBasedFactory
{
    public function getKey(): string
    {
        return 'ezpublish_rest_session';
    }
}
