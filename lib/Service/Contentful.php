<?php

namespace Netgen\BlockManager\Contentful\Service;

use Contentful\Delivery\EntryInterface;
use Contentful\Delivery\Query;
use Exception;
use Netgen\BlockManager\Contentful\Entity\ContentfulEntry;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Orm\Route;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class Contentful
{
    /**
     * @var \Contentful\Delivery\Client
     */
    private $defaultClient;

    /**
     * @var array
     */
    private $clientsConfig;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        array $clientsConfig,
        ContainerInterface $container,
        $entityManager
    ) {
        $this->clientsConfig = $clientsConfig;
        $this->container = $container;
        $this->defaultClient = $this->container->get('contentful.delivery');
        $this->entityManager = $entityManager;

        if (count($this->clientsConfig) === 0) {
            throw new Contentful\Exception\ApiException(
                sprintf(
                    'No Contentful clients configured'
                )
            );
        }
    }

    public function __toString()
    {
        return 'Contentful service wrapper';
    }

    public function getClientByName($name)
    {
        return $this->container->get($this->clientsConfig[$name]['service']);
    }

    public function getSpaceByClientName($name)
    {
        return $this->clientsConfig[$name]['space'];
    }

    public function getClientBySpaceId($spaceId)
    {
        foreach ($this->clientsConfig as $clientName) {
            if ($clientName['space'] === $spaceId) {
                return $this->container->get($clientName['service']);
            }
        }

        return null;
    }

    public function getClients()
    {
        /**
         * @var \Contentful\Delivery\Client[]
         */
        $clients = array();
        foreach ($this->clientsConfig as $clientName) {
            $client = $this->container->get($clientName['service']);
            $clients[] = $client;
        }

        return $clients;
    }

    public function getContentType($id)
    {
        foreach ($this->clientsConfig as $clientName) {
            /**
             * @var \Contentful\Delivery\Client
             */
            $client = $this->container->get($clientName['service']);
            foreach ($client->getContentTypes()->getItems() as $contentType) {
                if ($contentType->getId() === $id) {
                    return $contentType;
                }
            }
        }

        return null;
    }

    public function getClientsNames()
    {
        return array_keys($this->clientsConfig);
    }

    /*
     ************** Content Entry part ****************
     */

    public function loadContentfulEntry($id)
    {
        $id_array = explode('|', $id);
        if (count($id_array) !== 2) {
            throw new Exception(
                sprintf(
                    'Item ID %s not valid.',
                    $id
                )
            );
        }

        /**
         * @var \Contentful\Delivery\Client
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

    /*
     ********** Content Entries part ************
     */

    public function getContentfulEntries($offset = 0, $limit = 25, $client = null, $query = null)
    {
        if ($client === null) {
            $client = $this->defaultClient;
        }

        if ($query === null) {
            $query = new Query();
            $query->setLimit($limit);
            $query->setSkip($offset);
        }

        return $this->buildContentfulEntries($client->getEntries($query), $client);
    }

    public function getContentfulEntriesCount($client = null, $query = null)
    {
        if ($client === null) {
            $client = $this->defaultClient;
        }

        if ($query === null) {
            $query = new Query();
        }

        return count($client->getEntries($query));
    }

    public function searchContentfulEntries($searchText, $offset = 0, $limit = 25, $client = null)
    {
        if ($client === null) {
            $client = $this->defaultClient;
        }

        $query = new Query();
        $query->setLimit($limit);
        $query->setSkip($offset);
        $query->where('query', $searchText);

        return $this->buildContentfulEntries($client->getEntries($query), $client);
    }

    public function searchContentfulEntriesCount($searchText, $client = null)
    {
        if ($client === null) {
            $client = $this->defaultClient;
        }

        $query = new Query();
        $query->where('query', $searchText);

        return count($client->getEntries($query));
    }

    /*
     ********** Choices for forms ************
     */

    public function getClientsAndContentTypesAsChoices()
    {
        $clientsAndContentTypes = array();
        foreach ($this->clientsConfig as $clientName => $clientDetails) {
            /**
             * @var \Contentful\Delivery\Client
             */
            $client = $this->container->get($clientDetails['service']);
            $clientsAndContentTypes[$client->getSpace()->getName()] = $clientName;
            foreach ($client->getContentTypes()->getItems() as $contentType) {
                $clientsAndContentTypes['>  ' . $contentType->getName()] = $clientName . '|' . $contentType->getId();
            }
        }

        return $clientsAndContentTypes;
    }

    public function getSpacesAsChoices()
    {
        $spaces = array();
        foreach ($this->clientsConfig as $clientName) {
            $spaces[$this->container->get($clientName['service'])->getSpace()->getName()] = $clientName['space'];
        }

        return $spaces;
    }

    public function getSpacesAndContentTypesAsChoices()
    {
        $spaces = array();
        foreach ($this->clientsConfig as $clientName) {
            /**
             * @var \Contentful\Delivery\Client
             */
            $client = $this->container->get($clientName['service']);
            $contentTypes = array();
            foreach ($client->getContentTypes()->getItems() as $contentType) {
                $contentTypes[$contentType->getName()] = $contentType->getId();
            }
            $spaces[$client->getSpace()->getName()] = $contentTypes;
        }

        return $spaces;
    }

    /*
     ********** Syncing part ************
     */

    public function refreshContentfulEntry($remote_entry)
    {
        $id = $remote_entry->getSpace()->getId() . '|' . $remote_entry->getId();
        $contentfulEntry = $this->findContentfulEntry($id);

        if ($contentfulEntry instanceof ContentfulEntry) {
            $contentfulEntry->setJson(json_encode($remote_entry));
            $contentfulEntry->setIsPublished(true);
            $this->entityManager->persist($contentfulEntry);
            $this->entityManager->flush();
        } else {
            $contentfulEntry = $this->buildContentfulEntry($remote_entry, $id);
        }

        return $contentfulEntry;
    }

    public function unpublishContentfulEntry($remote_entry)
    {
        $id = $remote_entry->getSpace()->getId() . '|' . $remote_entry->getId();
        $contentfulEntry = $this->findContentfulEntry($id);
        if ($contentfulEntry instanceof ContentfulEntry) {
            $contentfulEntry->setIsPublished(false);
            $this->entityManager->persist($contentfulEntry);
            $this->entityManager->flush();
        }
    }

    public function deleteContentfulEntry($remote_entry)
    {
        $id = $remote_entry->getSpace()->getId() . '|' . $remote_entry->getId();
        $contentfulEntry = $this->findContentfulEntry($id);
        if ($contentfulEntry instanceof ContentfulEntry) {
            $contentfulEntry->setIsDeleted(true);
            $this->entityManager->persist($contentfulEntry);

            foreach ($contentfulEntry->getRoutes() as $route) {
                $this->entityManager->remove($route);
            }

            $this->entityManager->flush();
        }
    }

    public function refreshSpaceCache($client, $fs)
    {
        $spacePath = $this->getSpaceCachePath($client, $fs);
        $fs->dumpFile($spacePath . '/space.json', json_encode($client->getSpace()));
    }

    public function refreshContentTypeCache($client, $fs)
    {
        $spacePath = $this->getSpaceCachePath($client, $fs);
        $contentTypes = $client->getContentTypes(new Query());
        foreach ($contentTypes as $contentType) {
            $fs->dumpFile($spacePath . '/ct-' . $contentType->getId() . '.json', json_encode($contentType));
        }
    }

    public function getSpaceCachePath($client, $fs)
    {
        $space = $client->getSpace();
        $spacePath = $this->container->getParameter('kernel.cache_dir') . '/contentful/' . $space->getId();
        if (!$fs->exists($spacePath)) {
            $fs->mkdir($spacePath);
        }

        return $spacePath;
    }

    private function findContentfulEntry($id)
    {
        $contentfulEntry = $this->entityManager->getRepository(ContentfulEntry::class)->find($id);

        return $contentfulEntry;
    }

    private function buildContentfulEntry($remote_entry, $id)
    {
        $contentfulEntry = new ContentfulEntry($remote_entry);
        $contentfulEntry->setIsPublished(true);
        $contentfulEntry->setJson(json_encode($remote_entry));

        $route = new Route();
        $route->setName($id);
        $slug = '/' . $this->createSlugPart($contentfulEntry->getSpace()->getName());
        $slug .= '/' . $this->createSlugPart($contentfulEntry->getName());
        $route->setStaticPrefix($slug);
        $route->setDefault(RouteObjectInterface::CONTENT_ID, ContentfulEntry::class . ':' . $id);
        $route->setContent($contentfulEntry);
        $contentfulEntry->addRoute($route); // Create the backlink from content to route

        $this->entityManager->persist($contentfulEntry);
        $this->entityManager->persist($route);
        $this->entityManager->flush();

        return $contentfulEntry;
    }

    private function createSlugPart($string)
    {
        return strtolower(trim(preg_replace('~[^0-9a-z]+~i', '-', html_entity_decode(preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($string, ENT_QUOTES, 'UTF-8')), ENT_QUOTES, 'UTF-8')), '-'));
    }

    private function buildContentfulEntries($entries, $client)
    {
        $contentfulEntries = array();

        foreach ($entries as $remote_entry) {
            $id = $remote_entry->getSpace()->getId() . '|' . $remote_entry->getId();
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
}
