<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\CompatibilityLayer\Event\Subscriber;

use Ibexa\Contracts\Rest\Event\BeforeParseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

final class RestMimeTypeSubscriber implements EventSubscriberInterface
{
    private const MIME_TYPE_HEADERS = ['accept', 'accept-patch', 'content-type'];

    private const LEGACY_MIME_TYPE = 'vnd.ez.api';
    private const IBEXA_MIME_TYPE = 'vnd.ibexa.api';

    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => ['onKernelRequest', 1000],
            BeforeParseEvent::class => ['onDispatchParsing', 0],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        foreach (self::MIME_TYPE_HEADERS as $mimeTypeHeader) {
            $headerValue = $request->headers->get($mimeTypeHeader);

            if (!empty($headerValue)) {
                $request->headers->set($mimeTypeHeader, str_replace(
                    self::LEGACY_MIME_TYPE,
                    self::IBEXA_MIME_TYPE,
                    $headerValue
                ));
            }
        }
    }

    public function onDispatchParsing(BeforeParseEvent $dispatchParsingEvent): void
    {
        $dispatchParsingEvent->setMediaType(
            str_replace(
                self::LEGACY_MIME_TYPE,
                self::IBEXA_MIME_TYPE,
                $dispatchParsingEvent->getMediaType()
            )
        );
    }
}
