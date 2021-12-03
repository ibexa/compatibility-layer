<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\CompatibilityLayer\Command;

use Symfony\Bundle\FrameworkBundle\Command\ConfigDumpReferenceCommand;

class SymfonyConfigDumpReferenceCommand extends ConfigDumpReferenceCommand
{
    use BuildDebugContainerTrait;

    protected static $defaultName = 'config:dump-reference';
}
