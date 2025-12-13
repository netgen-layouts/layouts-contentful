<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsContentfulBundle\Controller;

use Contentful\Delivery\Client\ClientInterface;
use Contentful\Delivery\Client\JsonDecoderClientInterface;
use Contentful\Delivery\Resource\DeletedEntry;
use Contentful\Delivery\Resource\Entry;
use Netgen\Bundle\LayoutsBundle\Controller\AbstractController;
use Netgen\Layouts\Contentful\Service\Contentful;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Throwable;

final class WebhookController extends AbstractController
{
    public function __construct(
        private Contentful $contentful,
    ) {}

    /**
     * Contentful webhook for clearing local caches.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException If the webhook request is not valid
     */
    public function __invoke(Request $request): Response
    {
        $content = $request->getContent();
        $spaceId = $request->headers->get('X-Space-Id', '');

        try {
            $client = $this->contentful->getClientBySpaceId($spaceId);
        } catch (Throwable) {
            throw new BadRequestHttpException('Invalid request');
        }

        if (!$client instanceof ClientInterface || !$client instanceof JsonDecoderClientInterface) {
            throw new BadRequestHttpException('Invalid request');
        }

        try {
            $remoteEntry = $client->parseJson($content);
        } catch (Throwable) {
            throw new BadRequestHttpException('Invalid request');
        }

        switch ($request->headers->get('X-Contentful-Topic')) {
            case Topic::EntryPublish->value:
                if (!$remoteEntry instanceof Entry) {
                    throw new BadRequestHttpException('Invalid request');
                }

                $this->contentful->refreshContentfulEntry($remoteEntry, $client);

                break;

            case Topic::EntryUnpublish->value:
                if (!$remoteEntry instanceof DeletedEntry) {
                    throw new BadRequestHttpException('Invalid request');
                }

                $this->contentful->unpublishContentfulEntry($remoteEntry);

                break;

            case Topic::EntryDelete->value:
                if (!$remoteEntry instanceof DeletedEntry) {
                    throw new BadRequestHttpException('Invalid request');
                }

                $this->contentful->deleteContentfulEntry($remoteEntry);

                break;

            case Topic::ContentTypePublish->value:
            case Topic::ContentTypeUnpublish->value:
            case Topic::ContentTypeDelete->value:
                $this->contentful->refreshContentTypeCache($client);

                break;

            default:
                throw new BadRequestHttpException('Invalid request');
        }

        return new Response();
    }
}
