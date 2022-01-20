<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\CompatibilityLayer\Event;

use Ibexa\CompatibilityLayer\FullyQualifiedNameResolver\AggregateResolver;
use Ibexa\CompatibilityLayer\FullyQualifiedNameResolver\ClassMapResolver;
use Ibexa\CompatibilityLayer\FullyQualifiedNameResolver\PSR4PrefixResolver;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EventDispatcher implements EventDispatcherInterface
{
    /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface */
    private $innerEventDispatcher;

    /** @var \Ibexa\CompatibilityLayer\FullyQualifiedNameResolver\AggregateResolver */
    private $fullyQualifiedNameResolver;

    public function __construct(EventDispatcherInterface $innerEventDispatcher)
    {
        $this->innerEventDispatcher = $innerEventDispatcher;
        $this->fullyQualifiedNameResolver = new AggregateResolver([
            new ClassMapResolver(),
            new PSR4PrefixResolver(),
        ]);
    }

    public function dispatch(object $event, string $eventName = null): object
    {
        return $this->innerEventDispatcher->dispatch($event, $eventName);
    }

    public function addListener(string $eventName, $listener, int $priority = 0): void
    {
        $newEventName = $this->fullyQualifiedNameResolver->resolve($eventName);
        if (null !== $newEventName) {
            $this->innerEventDispatcher->addListener($newEventName, $listener, $priority);
        }
        $this->innerEventDispatcher->addListener($eventName, $listener, $priority);
    }

    public function addSubscriber(EventSubscriberInterface $subscriber): void
    {
        $this->innerEventDispatcher->addSubscriber($subscriber);
    }

    public function removeListener(string $eventName, callable $listener): void
    {
        $this->innerEventDispatcher->removeListener($eventName, $listener);
    }

    public function removeSubscriber(EventSubscriberInterface $subscriber): void
    {
        $this->innerEventDispatcher->removeSubscriber($subscriber);
    }

    public function getListeners(string $eventName = null): array
    {
        return $this->innerEventDispatcher->getListeners($eventName);
    }

    public function getListenerPriority(string $eventName, $listener): ?int
    {
        return $this->innerEventDispatcher->getListenerPriority($eventName, $listener);
    }

    public function hasListeners(string $eventName = null): bool
    {
        return $this->innerEventDispatcher->hasListeners($eventName);
    }
}
