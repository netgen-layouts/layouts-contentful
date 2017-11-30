<?php

namespace Netgen\BlockManager\Contentful\Service;

use Contentful\Delivery\Client;
use Contentful\Delivery\EntryInterface;
use Contentful\Delivery\Query;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Netgen\BlockManager\Contentful\Entity\ContentfulEntry;
use RuntimeException;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Orm\Route;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;

final class Contentful
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @var \Contentful\Delivery\Client
     */
    private $defaultClient;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    private $fileSystem;

    /**
     * @var array
     */
    private $clientsConfig;

    /**
     * @var string
     */
    private $cacheDir;

    public function __construct(
        ContainerInterface $container,
        Client $defaultClient,
        EntityManagerInterface $entityManager,
        Filesystem $fileSystem,
        array $clientsConfig,
        $cacheDir
    ) {
        $this->container = $container;
        $this->defaultClient = $defaultClient;
        $this->entityManager = $entityManager;
        $this->fileSystem = $fileSystem;
        $this->clientsConfig = $clientsConfig;
        $this->cacheDir = $cacheDir;

        if (count($this->clientsConfig) === 0) {
            throw new RuntimeException('No Contentful clients configured');
        }
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
        /** @var \Contentful\Delivery\Client[] $clients */
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
            /** @var \Contentful\Delivery\Client $client */
            $client = $this->container->get($clientName['service']);
            foreach ($client->getContentTypes()->getItems() as $contentType) {
                /** @var \Contentful\Delivery\ContentType $contentType */
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
        $idList = explode('|', $id);
        if (count($idList) !== 2) {
            throw new Exception(
                sprintf(
                    'Item ID %s not valid.',
                    $id
                )
            );
        }

        /** @var \Contentful\Delivery\Client $client */
        $client = $this->getClientBySpaceId($idList[0]);

        $contentfulEntry = $this->findContentfulEntry($id);

        if ($contentfulEntry instanceof ContentfulEntry) {
            $contentfulEntry->reviveRemoteEntry($client);
        } else {
            $remoteEntry = $client->getEntry($idList[1]);

            if (!$remoteEntry instanceof EntryInterface) {
                throw new Exception(
                    sprintf(
                        'Entry with ID %s not found.',
                        $id
                    )
                );
            }

            $contentfulEntry = $this->buildContentfulEntry($remoteEntry, $id);
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
            /** @var \Contentful\Delivery\Client $client */
            $client = $this->container->get($clientDetails['service']);
            $clientsAndContentTypes[$client->getSpace()->getName()] = $clientName;
            foreach ($client->getContentTypes()->getItems() as $contentType) {
                /** @var \Contentful\Delivery\ContentType $contentType */
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
            /** @var \Contentful\Delivery\Client $client */
            $client = $this->container->get($clientName['service']);
            $contentTypes = array();
            foreach ($client->getContentTypes()->getItems() as $contentType) {
                /** @var \Contentful\Delivery\ContentType $contentType */
                $contentTypes[$contentType->getName()] = $contentType->getId();
            }
            $spaces[$client->getSpace()->getName()] = $contentTypes;
        }

        return $spaces;
    }

    /*
     ********** Syncing part ************
     */

    public function refreshContentfulEntry($remoteEntry)
    {
        $id = $remoteEntry->getSpace()->getId() . '|' . $remoteEntry->getId();
        $contentfulEntry = $this->findContentfulEntry($id);

        if ($contentfulEntry instanceof ContentfulEntry) {
            $contentfulEntry->setJson(json_encode($remoteEntry));
            $contentfulEntry->setIsPublished(true);
            $this->entityManager->persist($contentfulEntry);
            $this->entityManager->flush();
        } else {
            $contentfulEntry = $this->buildContentfulEntry($remoteEntry, $id);
        }

        return $contentfulEntry;
    }

    public function unpublishContentfulEntry($remoteEntry)
    {
        $id = $remoteEntry->getSpace()->getId() . '|' . $remoteEntry->getId();
        $contentfulEntry = $this->findContentfulEntry($id);
        if ($contentfulEntry instanceof ContentfulEntry) {
            $contentfulEntry->setIsPublished(false);
            $this->entityManager->persist($contentfulEntry);
            $this->entityManager->flush();
        }
    }

    public function deleteContentfulEntry($remoteEntry)
    {
        $id = $remoteEntry->getSpace()->getId() . '|' . $remoteEntry->getId();
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

    public function refreshSpaceCache($client)
    {
        $spacePath = $this->getSpaceCachePath($client);
        $this->fileSystem->dumpFile($spacePath . '/space.json', json_encode($client->getSpace()));
    }

    public function refreshContentTypeCache($client)
    {
        $spacePath = $this->getSpaceCachePath($client);
        $contentTypes = $client->getContentTypes(new Query());
        foreach ($contentTypes as $contentType) {
            /** @var \Contentful\Delivery\ContentType $contentType */
            $this->fileSystem->dumpFile($spacePath . '/ct-' . $contentType->getId() . '.json', json_encode($contentType));
        }
    }

    public function getSpaceCachePath($client)
    {
        $space = $client->getSpace();
        $spacePath = $this->cacheDir . $space->getId();
        if (!$this->fileSystem->exists($spacePath)) {
            $this->fileSystem->mkdir($spacePath);
        }

        return $spacePath;
    }

    private function findContentfulEntry($id)
    {
        $contentfulEntry = $this->entityManager->getRepository(ContentfulEntry::class)->find($id);

        return $contentfulEntry;
    }

    private function buildContentfulEntry($remoteEntry, $id)
    {
        $contentfulEntry = new ContentfulEntry($remoteEntry);
        $contentfulEntry->setIsPublished(true);
        $contentfulEntry->setJson(json_encode($remoteEntry));

        $route = new Route();
        $route->setName($id);
        $slug = '/' . $this->createSlugPart($contentfulEntry->getSpace()->getName());
        $slug .= '/' . $this->createSlugPart($contentfulEntry->getName());
        $route->setStaticPrefix($slug);
        $route->setDefault(RouteObjectInterface::CONTENT_ID, ContentfulEntry::class . ':' . $id);
        $route->setContent($contentfulEntry);
        $contentfulEntry->addRoute($route); // Create the back-link from content to route

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

        foreach ($entries as $remoteEntry) {
            $id = $remoteEntry->getSpace()->getId() . '|' . $remoteEntry->getId();
            $contentfulEntry = $this->findContentfulEntry($id);
            if (!$contentfulEntry instanceof ContentfulEntry) {
                $contentfulEntry = $this->buildContentfulEntry($remoteEntry, $id);
            } else {
                $contentfulEntry->reviveRemoteEntry($client);
            }
            $contentfulEntries[] = $contentfulEntry;
        }

        return $contentfulEntries;
    }
}
