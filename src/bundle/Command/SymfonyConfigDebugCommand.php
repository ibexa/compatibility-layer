<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\CompatibilityLayer\Command;

use Symfony\Bundle\FrameworkBundle\Command\ConfigDebugCommand;

/**
 * @see \Symfony\Bundle\FrameworkBundle\Command\ConfigDebugCommand
 */
class SymfonyConfigDebugCommand extends ConfigDebugCommand
{
    use BuildDebugContainerTrait;

    protected static $defaultName = 'debug:config';
}
