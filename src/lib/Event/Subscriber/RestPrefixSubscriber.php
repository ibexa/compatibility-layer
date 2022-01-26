<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\CompatibilityLayer\Event\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class RestPrefixSubscriber implements EventSubscriberInterface
{
    public const LEGACY_REST_PREFIX = '/api/ezp/v2';
    public const IBEXA_REST_PREFIX = '/api/ibexa/v2';

    private HttpKernelInterface $kernel;

    public function __construct(HttpKernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => ['onKernelRequest', 2000],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (strpos($request->getRequestUri(), self::LEGACY_REST_PREFIX) === 0) {
            $newRequest = $request->duplicate(
                null,
                null,
                null,
                null,
                null,
                array_merge($_SERVER, [
                    'REQUEST_URI' => str_replace(
                        self::LEGACY_REST_PREFIX,
                        self::IBEXA_REST_PREFIX,
                        $request->getRequestUri()
                    ),
                ])
            );
            $event->setResponse(
                $this->kernel->handle(
                    $newRequest,
                    $event->isMainRequest()
                        ? HttpKernelInterface::MAIN_REQUEST
                        : HttpKernelInterface::SUB_REQUEST
                )
            );
            $event->stopPropagation();
        }
    }
}
