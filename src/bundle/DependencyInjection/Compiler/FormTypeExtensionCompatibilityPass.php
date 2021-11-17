<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\CompatibilityLayer\DependencyInjection\Compiler;

use Ibexa\CompatibilityLayer\Form\Extension\DependencyInjection;
use Ibexa\CompatibilityLayer\FullyQualifiedNameResolverInterface;
use Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class FormTypeExtensionCompatibilityPass implements CompilerPassInterface
{
    private const SERVICE_TAG = 'form.extension';

    /** @var \Ibexa\CompatibilityLayer\FullyQualifiedNameResolverInterface */
    private $nameResolver;

    public function __construct(FullyQualifiedNameResolverInterface $nameResolver)
    {
        $this->nameResolver = $nameResolver;
    }

    public function process(ContainerBuilder $container): void
    {
        $formExtensionDefinition = $container->getDefinition(self::SERVICE_TAG);
        $formExtensions = $formExtensionDefinition->getArgument(1);

        $formExtensionDefinition->setClass(DependencyInjection::class);

        $newFormExtensions = $formExtensions;

        foreach ($formExtensions as $extendedType => $extensions) {
            $newExtendedType = $this->nameResolver->resolve($extendedType);

            if (!empty($newExtendedType)) {
                $oldExtensions = !empty($formExtensions[$newExtendedType])
                    ? $formExtensions[$newExtendedType]->getValues()
                    : [];
                $newExtensions = $extensions->getValues();

                $newFormExtensions[$newExtendedType] = new IteratorArgument(
                    array_merge(
                        $oldExtensions,
                        $newExtensions
                    )
                );
            }
        }

        $formExtensionDefinition->setArgument(1, $newFormExtensions);
    }
}
