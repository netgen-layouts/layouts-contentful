<?php

namespace Netgen\Bundle\ContentfulBlockManagerBundle\Controller;

use Contentful\Delivery\DynamicEntry;
use Contentful\Delivery\Synchronization\DeletedEntry;
use Exception;
use Netgen\BlockManager\Contentful\Service\Contentful;
use Netgen\Bundle\BlockManagerBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ContentfulController extends Controller
{
    /*
     * Contentful topic constants (sent as X-Contentful-Topic header)
     */
    const ENTRY_PUBLISH = 'ContentManagement.Entry.publish';
    const ENTRY_UNPUBLISH = 'ContentManagement.Entry.unpublish';
    const ENTRY_DELETE = 'ContentManagement.Entry.delete';

    const CONTENT_TYPE_PUBLISH = 'ContentManagement.ContentType.publish';
    const CONTENT_TYPE_UNPUBLISH = 'ContentManagement.ContentType.unpublish';
    const CONTENT_TYPE_DELETE = 'ContentManagement.ContentType.delete';
    /**
     * @var \Netgen\BlockManager\Contentful\Service\Contentful
     */
    private $contentful;

    public function __construct(Contentful $contentful)
    {
        $this->contentful = $contentful;
    }

    /**
     * Renders a Contentful entry.
     *
     * @param object $contentDocument the Contentful entry which is being rendered
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException If provided Contentful entry doesn't exist
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function view($contentDocument)
    {
        if (!$contentDocument->getIsPublished() or $contentDocument->getIsDeleted()) {
            throw new NotFoundHttpException();
        }

        return $this->render(
            '@NetgenContentfulBlockManager/contentful/content.html.twig',
            array(
                'content' => $contentDocument,
            )
        );
    }

    /**
     * Contentful webhook for clearing local caches.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException If the webhook request is not valid
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function webhook(Request $request)
    {
        $content = $request->getContent();
        $spaceId = $request->headers->get('X-Space-Id');

        try {
            /** @var \Contentful\Delivery\Client $client */
            $client = $this->contentful->getClientBySpaceId($spaceId);
            $remote_entry = $client->reviveJson($content);
        } catch (Exception $e) {
            throw new BadRequestHttpException('Invalid request');
        }

        switch ($request->headers->get('X-Contentful-Topic')) {
            case $this::ENTRY_PUBLISH:
                if (!$remote_entry instanceof DynamicEntry) {
                    throw new BadRequestHttpException('Invalid request');
                }
                $this->contentful->refreshContentfulEntry($remote_entry);
                break;
            case $this::ENTRY_UNPUBLISH:
                if (!$remote_entry instanceof DeletedEntry) {
                    throw new BadRequestHttpException('Invalid request');
                }
                $this->contentful->unpublishContentfulEntry($remote_entry);
                break;
            case $this::ENTRY_DELETE:
                if (!$remote_entry instanceof DeletedEntry) {
                    throw new BadRequestHttpException('Invalid request');
                }
                $this->contentful->deleteContentfulEntry($remote_entry);
                break;
            case $this::CONTENT_TYPE_PUBLISH:
            case $this::CONTENT_TYPE_UNPUBLISH:
            case $this::CONTENT_TYPE_DELETE:
                $this->contentful->refreshContentTypeCache($client, new Filesystem());
                break;
            default:
                throw new BadRequestHttpException('Invalid request');
        }

        return new Response();
    }

    protected function checkPermissions()
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_ANONYMOUSLY');
    }
}
