<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\CompatibilityLayer\Form\Extension;

use Psr\Container\ContainerInterface;
use Symfony\Component\Form\Exception\InvalidArgumentException;
use Symfony\Component\Form\FormExtensionInterface;
use Symfony\Component\Form\FormTypeGuesserChain;
use Symfony\Component\Form\FormTypeGuesserInterface;

/**
 * @internal
 *
 * @see \Symfony\Component\Form\Extension\DependencyInjection\DependencyInjectionInterface
 *
 * This is copy & paste of Symfony "form.extension" service with removed extension validation.
 */
final class DependencyInjection implements FormExtensionInterface
{
    /** @var \Symfony\Component\Form\FormTypeGuesserInterface */
    private $guesser;

    /** @var bool */
    private $guesserLoaded = false;

    /** @var \Psr\Container\ContainerInterface */
    private $typeContainer;

    /** @var iterable[] */
    private $typeExtensionServices;

    /** @var iterable */
    private $guesserServices;

    public function __construct(ContainerInterface $typeContainer, array $typeExtensionServices, iterable $guesserServices)
    {
        $this->typeContainer = $typeContainer;
        $this->typeExtensionServices = $typeExtensionServices;
        $this->guesserServices = $guesserServices;
    }

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getType(string $name)
    {
        if (!$this->typeContainer->has($name)) {
            throw new InvalidArgumentException(sprintf('The field type "%s" is not registered in the service container.', $name));
        }

        return $this->typeContainer->get($name);
    }

    public function hasType(string $name): bool
    {
        return $this->typeContainer->has($name);
    }

    /**
     * @see \Symfony\Component\Form\Extension\DependencyInjection\DependencyInjectionInterface::getTypeExtensions()
     */
    public function getTypeExtensions(string $name): array
    {
        return isset($this->typeExtensionServices[$name]) ? iterator_to_array($this->typeExtensionServices[$name]) : [];
    }

    public function hasTypeExtensions(string $name): bool
    {
        return isset($this->typeExtensionServices[$name]);
    }

    public function getTypeGuesser(): ?FormTypeGuesserInterface
    {
        if (!$this->guesserLoaded) {
            $this->guesserLoaded = true;
            $guessers = [];

            foreach ($this->guesserServices as $service) {
                $guessers[] = $service;
            }

            if ($guessers) {
                $this->guesser = new FormTypeGuesserChain($guessers);
            }
        }

        return $this->guesser;
    }
}
