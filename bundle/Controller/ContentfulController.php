<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsContentfulBundle\Controller;

use Netgen\Bundle\LayoutsBundle\Controller\AbstractController;
use Netgen\Layouts\Contentful\Entity\ContentfulEntry;
use Netgen\Layouts\Contentful\Service\Contentful;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @deprecated Deprecated since 1.1. Use ViewController or WebhookController instead.
 */
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
        $viewController = new ViewController();
        $viewController->setContainer($this->container);

        return $viewController($contentDocument);
    }

    /**
     * Contentful webhook for clearing local caches.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException If the webhook request is not valid
     */
    public function webhook(Request $request): Response
    {
        $webHookController = new WebhookController($this->contentful);
        $webHookController->setContainer($this->container);

        return $webHookController($request);
    }
}
