<?php

namespace Netgen\Bundle\ContentfulBlockManagerBundle\Service;

use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Orm\Route;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Netgen\Bundle\ContentfulBlockManagerBundle\Entity\ContentfulEntry;
use Contentful\Delivery\EntryInterface;
use Contentful\Delivery\Query;


class Contentful {

    /**
     * @var \Contentful\Delivery\Client
     */
    private $default_client;

    /**
     * @var array
     */
    private $clients_config;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $entity_manager;

    public function __construct(
        array $clients_config,
        ContainerInterface $container,
        $entity_manager
    ) {
        $this->clients_config = $clients_config;
        $this->container = $container;
        $this->default_client = $this->container->get("contentful.delivery");
        $this->entity_manager = $entity_manager;

        if (count($this->clients_config) === 0) {
            throw new Contentful\Exception\ApiException(
                sprintf(
                    'No Contentful clients configured'
                )
            );
        }
    }

    public function getClientByName($name) {
        return $this->container->get($this->clients_config[$name]["service"]);
    }

    public function getSpaceByClientName($name) {
        return $this->clients_config[$name]["space"];
    }

    public function getClientBySpaceId($spaceId) {
        foreach ($this->clients_config as $clientName) {
            if ($clientName["space"] == $spaceId) {
                return $this->container->get($clientName["service"]);
            }
        }
        return null;
    }

    public function getClients() {
        /**
         * @var \Contentful\Delivery\Client[] $clients
         */
        $clients = array();
        foreach ($this->clients_config as $clientName) {
            $client = $this->container->get($clientName["service"]);
            $clients[] = $client;
        }
        return $clients;
    }

    public function getContentType($id) {
        foreach ($this->clients_config as $clientName) {
            /**
             * @var \Contentful\Delivery\Client $client
             */
            $client = $this->container->get($clientName["service"]);
            foreach ($client->getContentTypes()->getItems() as $contentType)
                if ($contentType->getId() == $id)
                    return $contentType;
        }
        return null;
    }

    public function getClientsNames() {
        return array_keys($this->clients_config);
    }

    public function __toString() {
        return "Contentful service wrapper";
    }

    /*
     ************** Content Entry part ****************
     */

    public function loadContentfulEntry($id) {

        $id_array = explode("|", $id);
        if (count($id_array) != 2) {
            throw new Exception(
                sprintf(
                    'Item ID %s not valid.',
                    $id
                )
            );
        }

        /**
         * @var \Contentful\Delivery\Client $client
         */
        $client = $this->getClientBySpaceId($id_array[0]);

        $contentfulEntry = $this->findContentfulEntry($id);

        if ($contentfulEntry instanceof ContentfulEntry) {
            $contentfulEntry->reviveRemoteEntry($client);

        } else {
            $remote_entry = $client->getEntry($id_array[1]);

            if (!$remote_entry instanceof EntryInterface) {
                throw new Exception(
                    sprintf(
                        'Entry with ID %s not found.',
                        $id
                    )
                );
            }

            $contentfulEntry = $this->buildContentfulEntry($remote_entry, $id);
        }

        if ($contentfulEntry->getIsDeleted()) {
            throw new Exception(
                sprintf(
                    'Entry with ID %s deleted.',
                    $id
                )
            );
        }

        return $contentfulEntry;
    }

    private function findContentfulEntry($id) {
        $contentfulEntry = $this->entity_manager->getRepository(ContentfulEntry::class)->find($id);

        return $contentfulEntry;
    }

    private function buildContentfulEntry($remote_entry, $id) {

        $contentfulEntry = new ContentfulEntry($remote_entry);
        $contentfulEntry->setIsPublished(true);
        $contentfulEntry->setJson(json_encode($remote_entry));

        $route = new Route();
        $route->setName($id);
        $slug  = "/" . $this->createSlug($contentfulEntry->getSpace()->getName());
        $slug .= "/" . $this->createSlug($contentfulEntry->getName());
        $route->setStaticPrefix($slug);
        $route->setDefault(RouteObjectInterface::CONTENT_ID, ContentfulEntry::class.":". $id);
        $route->setContent($contentfulEntry);
        $contentfulEntry->addRoute($route); // Create the backlink from content to route

        $this->entity_manager->persist($contentfulEntry);
        $this->entity_manager->persist($route);
        $this->entity_manager->flush();

        return $contentfulEntry;
    }

    private function createSlug($string) {
        return strtolower(trim(preg_replace('~[^0-9a-z]+~i', '-', html_entity_decode(preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($string, ENT_QUOTES, 'UTF-8')), ENT_QUOTES, 'UTF-8')), '-'));
    }

    /*
     ********** Content Entries part ************
     */

    public function getContentfulEntries($offset = 0, $limit = 25, $client = null, $query = null) {

        if ($client == null) {
            $client = $this->default_client;
        }

        if ($query == null) {
            $query = new Query();
            $query->setLimit($limit);
            $query->setSkip($offset);
        }

        return $this->buildContentfulEntries($client->getEntries($query), $client);
    }

    public function getContentfulEntriesCount($client = null, $query = null) {
        if ($client == null) {
            $client = $this->default_client;
        }

        if ($query == null) {
            $query = new Query();
        }

        return count($client->getEntries($query));
    }

    public function searchContentfulEntries($searchText, $offset = 0, $limit = 25, $client = null) {
        if ($client == null) {
            $client = $this->default_client;
        }

        $query = new Query();
        $query->setLimit($limit);
        $query->setSkip($offset);
        $query->where('query', $searchText);

        return $this->buildContentfulEntries($client->getEntries($query), $client);
    }

    public function searchContentfulEntriesCount($searchText, $client = null) {
        if ($client == null) {
            $client = $this->default_client;
        }

        $query = new Query();
        $query->where('query', $searchText);

        return count($client->getEntries($query));
    }

    private function buildContentfulEntries($entries, $client) {
        $contentfulEntries = array();

        foreach ($entries as $remote_entry) {
            $id = $remote_entry->getSpace()->getId() ."|". $remote_entry->getId();
            $contentfulEntry = $this->findContentfulEntry($id);
            if (!$contentfulEntry instanceof ContentfulEntry) {
                $contentfulEntry = $this->buildContentfulEntry($remote_entry, $id);
            } else {
                $contentfulEntry->reviveRemoteEntry($client);
            }
            $contentfulEntries[] = $contentfulEntry;
        }

        return $contentfulEntries;
    }

    /*
     ********** Choices for forms ************
     */

    public function getClientsAndContentTypesAsChoices() {
        $clientsAndContentTypes = array();
        foreach ($this->clients_config as $clientName => $clientDetails) {
            /**
             * @var \Contentful\Delivery\Client $service
             */
            $client = $this->container->get($clientDetails["service"]);
            $clientsAndContentTypes[$client->getSpace()->getName()] = $clientName;
            foreach ($client->getContentTypes()->getItems() as $contentType)
                $clientsAndContentTypes[">  ". $contentType->getName()] = $clientName."|".$contentType->getId();

        }
        return $clientsAndContentTypes;
    }

    public function getSpacesAsChoices() {
        $spaces = array();
        foreach ($this->clients_config as $clientName) {
            $spaces[$this->container->get($clientName["service"])->getSpace()->getName()] = $clientName["space"];
        }
        return $spaces;
    }

    public function getSpacesAndContentTypesAsChoices() {
        $spaces = array();
        foreach ($this->clients_config as $clientName) {
            /**
             * @var \Contentful\Delivery\Client $service
             */
            $client = $this->container->get($clientName["service"]);
            $contentTypes = array();
            foreach ($client->getContentTypes()->getItems() as $contentType)
                $contentTypes[$contentType->getName()] = $contentType->getId();
            $spaces[$client->getSpace()->getName()] = $contentTypes;
        }
        return $spaces;
    }

    /*
     ********** Syncing part ************
     */

    public function refreshContentfulEntry($remote_entry) {
        $id = $remote_entry->getSpace()->getId() . "|" . $remote_entry->getId();
        $contentfulEntry = $this->findContentfulEntry($id);

        if ($contentfulEntry instanceof ContentfulEntry) {
            $contentfulEntry->setJson(json_encode($remote_entry));
            $contentfulEntry->setIsPublished(true);
            $this->entity_manager->persist($contentfulEntry);
            $this->entity_manager->flush();

        } else {
            $contentfulEntry = $this->buildContentfulEntry($remote_entry, $id);
        }
        return $contentfulEntry;
    }

    public function unpublishContentfulEntry($remote_entry) {
        $id = $remote_entry->getSpace()->getId() . "|" . $remote_entry->getId();
        $contentfulEntry = $this->findContentfulEntry($id);
        if ($contentfulEntry instanceof ContentfulEntry) {
            $contentfulEntry->setIsPublished(false);
            $this->entity_manager->persist($contentfulEntry);
            $this->entity_manager->flush();
        }
    }

    public function deleteContentfulEntry($remote_entry) {
        $id = $remote_entry->getSpace()->getId() . "|" . $remote_entry->getId();
        $contentfulEntry = $this->findContentfulEntry($id);
        if ($contentfulEntry instanceof ContentfulEntry) {
            $contentfulEntry->setIsDeleted(true);
            $this->entity_manager->persist($contentfulEntry);

            foreach ($contentfulEntry->getRoutes() as $route) {
                $this->entity_manager->remove($route);
            }

            $this->entity_manager->flush();
        }
    }

}
