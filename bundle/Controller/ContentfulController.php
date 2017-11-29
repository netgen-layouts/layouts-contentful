<?php

namespace Netgen\Bundle\ContentfulBlockManagerBundle\Controller;

use Contentful\Delivery\DynamicEntry;
use Contentful\Delivery\Synchronization\DeletedEntry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
* A custom controller to handle a content specified by a route.
*/
class ContentfulController extends Controller
{
    /*
     * Contentful topic constants (sent as X-Contentful-Topic header)
     */
    const PUBLISH = "ContentManagement.Entry.publish";
    const UNPUBLISH = "ContentManagement.Entry.unpublish";
    const DELETE = "ContentManagement.Entry.delete";

    /**
    * @param object $contentDocument the name of this parameter is defined
    *      by the RoutingBundle. You can also expect any route parameters
    *      or $template if you configured templates_by_class (see below).
    *
    * @return Response
    */
    public function viewAction($contentDocument)
    {
        if (!$contentDocument->getIsPublished() or $contentDocument->getIsDeleted())
            throw new NotFoundHttpException('Not found.');

        return $this->render('@NetgenContentfulBlockManager/contentful/content.html.twig', [
            'content' => $contentDocument,
        ]);
    }

    /**
     * Contentful callback for clearing local caches
     */
    public function callbackAction(Request $request)
    {
        $service = $this->container->get("netgen_block_manager.contentful.service");
        $logger = $this->container->get("logger");
        $content = $request->getContent();
        $spaceId = $request->headers->get("X-Space-Id");

        try {
            $client = $service->getClientBySpaceId($spaceId);
            $remote_entry = $client->reviveJson($content);
        } catch (Exception $e) {
            throw new BadRequestHttpException("Invalid request");
        }

        switch ($request->headers->get("X-Contentful-Topic")) {
            case $this::PUBLISH:
                if (! $remote_entry instanceof DynamicEntry)
                    throw new BadRequestHttpException("Invalid request");
                $service->refreshContentfulEntry($remote_entry);
                break;
            case $this::UNPUBLISH:
                if (! $remote_entry instanceof DeletedEntry)
                    throw new BadRequestHttpException("Invalid request");
                $service->unpublishContentfulEntry($remote_entry);
                break;
            case $this::DELETE:
                if (! $remote_entry instanceof DeletedEntry)
                    throw new BadRequestHttpException("Invalid request");
                $service->deleteContentfulEntry($remote_entry);
                break;
            default:
                throw new BadRequestHttpException("Invalid request");
        }

        return new Response("OK",Response::HTTP_OK);
    }
}
