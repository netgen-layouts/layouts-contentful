<?php

namespace Netgen\Bundle\ContentfulBlockManagerBundle\Controller;

use Contentful\Delivery\DynamicEntry;
use Contentful\Delivery\Synchronization\DeletedEntry;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ContentfulController extends Controller
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
     * Renders a Contentful entry.
     *
     * @param object $contentDocument the Contentful entry which is being rendered
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException If provided Contentful entry doesn't exist
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewAction($contentDocument)
    {
        if (!$contentDocument->getIsPublished() or $contentDocument->getIsDeleted()) {
            throw new NotFoundHttpException('Not found.');
        }

        return $this->render('@NetgenContentfulBlockManager/contentful/content.html.twig', array(
            'content' => $contentDocument,
        ));
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
    public function webhookAction(Request $request)
    {
        /**
         * @var \Netgen\BlockManager\Contentful\Service\Contentful
         */
        $service = $this->container->get('netgen_block_manager.contentful.service');
        $content = $request->getContent();
        $spaceId = $request->headers->get('X-Space-Id');

        try {
            /** @var \Contentful\Delivery\Client $client */
            $client = $service->getClientBySpaceId($spaceId);
            $remote_entry = $client->reviveJson($content);
        } catch (Exception $e) {
            throw new BadRequestHttpException('Invalid request');
        }

        switch ($request->headers->get('X-Contentful-Topic')) {
            case $this::ENTRY_PUBLISH:
                if (!$remote_entry instanceof DynamicEntry) {
                    throw new BadRequestHttpException('Invalid request');
                }
                $service->refreshContentfulEntry($remote_entry);
                break;
            case $this::ENTRY_UNPUBLISH:
                if (!$remote_entry instanceof DeletedEntry) {
                    throw new BadRequestHttpException('Invalid request');
                }
                $service->unpublishContentfulEntry($remote_entry);
                break;
            case $this::ENTRY_DELETE:
                if (!$remote_entry instanceof DeletedEntry) {
                    throw new BadRequestHttpException('Invalid request');
                }
                $service->deleteContentfulEntry($remote_entry);
                break;
            case $this::CONTENT_TYPE_PUBLISH:
            case $this::CONTENT_TYPE_UNPUBLISH:
            case $this::CONTENT_TYPE_DELETE:
                $service->refreshContentTypeCache($client, new Filesystem());
                break;
            default:
                throw new BadRequestHttpException('Invalid request');
        }

        return new Response('OK', Response::HTTP_OK);
    }
}
