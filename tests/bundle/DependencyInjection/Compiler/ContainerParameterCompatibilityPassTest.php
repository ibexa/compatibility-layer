<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\CompatibilityLayer\DependencyInjection\Compiler;

use Ibexa\Bundle\CompatibilityLayer\DependencyInjection\Compiler\ContainerParameterCompatibilityPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ContainerParameterCompatibilityPassTest extends AbstractCompilerPassTestCase
{
    private const NEW_PARAMETER_NAME = 'ibexa.commerce.erp.config.debug_mapping_messages';
    private const LEGACY_PARAMETER_NAME = 'silver_erp.config.debug_mapping_messages';

    private const DEFAULT_PARAMETER_VALUE = 'default debug_mapping_messages';
    private const CUSTOM_PARAMETER_VALUE = 'project debug_mapping_messages';

    public function testProcessOverridesNewParameterWithExistingLegacyOne(): void
    {
        // default settings
        $this->setParameter(
            self::NEW_PARAMETER_NAME,
            self::DEFAULT_PARAMETER_VALUE
        );

        // project settings
        $this->setParameter(
            self::LEGACY_PARAMETER_NAME,
            self::CUSTOM_PARAMETER_VALUE
        );

        $this->compile();

        // expected result
        $this->assertContainerBuilderHasParameter(
            self::NEW_PARAMETER_NAME,
            self::CUSTOM_PARAMETER_VALUE
        );
    }

    public function testProcessSetsLegacyParameterWithExistingNewParameterValue(): void
    {
        // product default setting
        $this->setParameter(
            self::NEW_PARAMETER_NAME,
            self::DEFAULT_PARAMETER_VALUE
        );

        $this->compile();

        // expected legacy parameter value
        $this->assertContainerBuilderHasParameter(
            self::LEGACY_PARAMETER_NAME,
            self::DEFAULT_PARAMETER_VALUE
        );
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new ContainerParameterCompatibilityPass());
    }
}
