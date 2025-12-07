<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsContentfulBundle\Controller;

use Netgen\Bundle\LayoutsBundle\Controller\AbstractController;
use Netgen\Layouts\Contentful\Entity\ContentfulEntry;
use Symfony\Component\HttpFoundation\Response;

final class ViewController extends AbstractController
{
    /**
     * Renders a Contentful entry.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException If provided Contentful entry doesn't exist
     */
    public function __invoke(ContentfulEntry $contentDocument): Response
    {
        if (!$contentDocument->isPublished || $contentDocument->isDeleted) {
            throw $this->createNotFoundException();
        }

        return $this->render(
            '@NetgenLayoutsContentful/contentful/content.html.twig',
            [
                'content' => $contentDocument,
            ],
        );
    }
}
