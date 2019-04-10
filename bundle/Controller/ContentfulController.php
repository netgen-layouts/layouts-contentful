<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsContentfulBundle\Controller;

use Contentful\Delivery\Resource\DeletedEntry;
use Contentful\Delivery\Resource\Entry;
use Netgen\Bundle\LayoutsBundle\Controller\AbstractController;
use Netgen\Layouts\Contentful\Entity\ContentfulEntry;
use Netgen\Layouts\Contentful\Service\Contentful;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

final class ContentfulController extends AbstractController
{
    // Contentful topic constants (sent as X-Contentful-Topic header)
    public const ENTRY_PUBLISH = 'ContentManagement.Entry.publish';
    public const ENTRY_UNPUBLISH = 'ContentManagement.Entry.unpublish';
    public const ENTRY_DELETE = 'ContentManagement.Entry.delete';

    public const CONTENT_TYPE_PUBLISH = 'ContentManagement.ContentType.publish';
    public const CONTENT_TYPE_UNPUBLISH = 'ContentManagement.ContentType.unpublish';
    public const CONTENT_TYPE_DELETE = 'ContentManagement.ContentType.delete';

    /**
     * @var \Netgen\Layouts\Contentful\Service\Contentful
     */
    private $contentful;

    public function __construct(Contentful $contentful)
    {
        $this->contentful = $contentful;
    }

    /**
     * Renders a Contentful entry.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException If provided Contentful entry doesn't exist
     */
    public function view(ContentfulEntry $contentDocument): Response
    {
        if (!$contentDocument->getIsPublished() || $contentDocument->getIsDeleted()) {
            throw new NotFoundHttpException();
        }

        return $this->render(
            '@NetgenLayoutsContentful/contentful/content.html.twig',
            [
                'content' => $contentDocument,
            ]
        );
    }

    /**
     * Contentful webhook for clearing local caches.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException If the webhook request is not valid
     */
    public function webhook(Request $request): Response
    {
        $content = (string) $request->getContent();
        $spaceId = $request->headers->get('X-Space-Id');
        $spaceId = is_array($spaceId) ? $spaceId[0] : $spaceId;

        try {
            $client = $this->contentful->getClientBySpaceId((string) $spaceId);
        } catch (Throwable $t) {
            throw new BadRequestHttpException('Invalid request');
        }

        if ($client === null) {
            throw new BadRequestHttpException('Invalid request');
        }

        try {
            $remoteEntry = $client->parseJson($content);
        } catch (Throwable $t) {
            throw new BadRequestHttpException('Invalid request');
        }

        switch ($request->headers->get('X-Contentful-Topic')) {
            case self::ENTRY_PUBLISH:
                if (!$remoteEntry instanceof Entry) {
                    throw new BadRequestHttpException('Invalid request');
                }

                $this->contentful->refreshContentfulEntry($remoteEntry);

                break;
            case self::ENTRY_UNPUBLISH:
                if (!$remoteEntry instanceof DeletedEntry) {
                    throw new BadRequestHttpException('Invalid request');
                }

                $this->contentful->unpublishContentfulEntry($remoteEntry);

                break;
            case self::ENTRY_DELETE:
                if (!$remoteEntry instanceof DeletedEntry) {
                    throw new BadRequestHttpException('Invalid request');
                }

                $this->contentful->deleteContentfulEntry($remoteEntry);

                break;
            case self::CONTENT_TYPE_PUBLISH:
            case self::CONTENT_TYPE_UNPUBLISH:
            case self::CONTENT_TYPE_DELETE:
                $this->contentful->refreshContentTypeCache($client);

                break;
            default:
                throw new BadRequestHttpException('Invalid request');
        }

        return new Response();
    }
}
