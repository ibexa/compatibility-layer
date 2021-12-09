<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\CompatibilityLayer\Twig;

use Ibexa\Bundle\CompatibilityLayer\Twig\LegacyDesignThemeTemplateNameResolver;
use Ibexa\Core\MVC\ConfigResolverInterface;
use PHPUnit\Framework\TestCase;

final class LegacyDesignThemeTemplateNameResolverTest extends TestCase
{
    private const LEGACY_DESIGN = 'ezdesign';
    private const SITE_DESIGN = 'site';

    private LegacyDesignThemeTemplateNameResolver $templateNameResolver;

    protected function setUp(): void
    {
        $configResolverMock = $this->createMock(ConfigResolverInterface::class);
        $configResolverMock
            ->method('getParameter')
            ->with('design')
            ->willReturn(self::SITE_DESIGN);

        $this->templateNameResolver = new LegacyDesignThemeTemplateNameResolver(
            $configResolverMock
        );
    }

    /**
     * @dataProvider getDataForTestIsTemplateDesignNamespaced
     */
    public function testIsTemplateDesignNamespaced(string $name, bool $expectedIsNamespaced): void
    {
        self::assertSame(
            $expectedIsNamespaced,
            $this->templateNameResolver->isTemplateDesignNamespaced($name)
        );
    }

    public function getDataForTestIsTemplateDesignNamespaced(): iterable
    {
        yield self::LEGACY_DESIGN => [
            '@' . self::LEGACY_DESIGN . '/foo.html.twig',
            true,
        ];

        yield self::SITE_DESIGN => [
            '@' . self::SITE_DESIGN . '/bar.html.twig',
            true,
        ];

        yield 'foo' => [
            '@foo/bar.html.twig',
            false,
        ];
    }
}
