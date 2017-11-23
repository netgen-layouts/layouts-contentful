<?php

namespace Netgen\ContentfulBlockManagerBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
* A custom controller to handle a content specified by a route.
*/
class ContentfulController extends Controller
{
    /**
    * @param object $contentDocument the name of this parameter is defined
    *      by the RoutingBundle. You can also expect any route parameters
    *      or $template if you configured templates_by_class (see below).
    *
    * @return Response
    */
    public function viewAction($contentDocument)
    {
        return $this->render('@NetgenContentfulBlockManager/contentful/content.html.twig', [
            'content' => $contentDocument,
        ]);
    }
}
