<?php

namespace Netgen\BlockManager\Contentful\Entity;

use Contentful\Delivery\Client;
use Contentful\Delivery\EntryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use Symfony\Cmf\Component\Routing\RouteReferrersInterface;

final class ContentfulEntry implements RouteReferrersInterface, EntryInterface
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $json;

    /**
     * @var bool
     */
    private $isPublished;

    /**
     * @var bool
     */
    private $isDeleted;

    /**
     * @var \Symfony\Cmf\Component\Routing\RouteObjectInterface[]|\Doctrine\Common\Collections\ArrayCollection
     */
    private $routes;

    /**
     * Original Contentful entry.
     *
     * @var \Contentful\Delivery\DynamicEntry
     */
    private $remoteEntry;

    public function __construct(EntryInterface $remoteEntry)
    {
        $this->routes = new ArrayCollection();
        $this->setRemoteEntry($remoteEntry);
    }

    /**
     * @param string $name
     * @param array $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (0 !== strpos($name, 'get')) {
            trigger_error('Call to undefined method ' . __CLASS__ . '::' . $name . '()', E_USER_ERROR);
        }

        $ret = null;
        try {
            $ret = call_user_func(array($this->remoteEntry, $name));
        } catch (Exception $e) {
        }

        return $ret;
    }

    /**
     * Returns the entry ID.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the entry ID.
     *
     * @param string $id
     *
     * @return \Netgen\BlockManager\Contentful\Entity\ContentfulEntry
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Returns the entry name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the entry name.
     *
     * @param string $name
     *
     * @return \Netgen\BlockManager\Contentful\Entity\ContentfulEntry
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Returns the entry JSON representation.
     *
     * @return string
     */
    public function getJson()
    {
        return $this->json;
    }

    /**
     * Sets the JSON representation of the entry.
     *
     * @param string $json
     *
     * @return \Netgen\BlockManager\Contentful\Entity\ContentfulEntry
     */
    public function setJson($json)
    {
        $this->json = $json;

        return $this;
    }

    /**
     * Returns if the entry is published.
     *
     * @return bool
     */
    public function getIsPublished()
    {
        return $this->isPublished;
    }

    /**
     * Sets if the entry is published.
     *
     * @param bool $isPublished
     *
     * @return \Netgen\BlockManager\Contentful\Entity\ContentfulEntry
     */
    public function setIsPublished($isPublished)
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    /**
     * Returns if the entry is deleted.
     *
     * @return bool
     */
    public function getIsDeleted()
    {
        return $this->isDeleted;
    }

    /**
     * Sets if the entry is deleted.
     *
     * @param bool $isDeleted
     *
     * @return \Netgen\BlockManager\Contentful\Entity\ContentfulEntry
     */
    public function setIsDeleted($isDeleted)
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    /**
     * Sets the entry routes.
     *
     * @param \Symfony\Cmf\Component\Routing\RouteObjectInterface[]|\Doctrine\Common\Collections\ArrayCollection $routes
     */
    public function setRoutes(ArrayCollection $routes)
    {
        $this->routes = $routes;
    }

    public function getRoutes()
    {
        return $this->routes;
    }

    public function addRoute($route)
    {
        $this->routes[] = $route;

        return $this;
    }

    public function removeRoute($route)
    {
        $this->routes->removeElement($route);

        return $this;
    }

    /**
     * Returns the remote entry.
     *
     * @return \Contentful\Delivery\EntryInterface
     */
    public function getRemoteEntry()
    {
        return $this->remoteEntry;
    }

    /**
     * Returns the remote entry revision.
     *
     * @return int
     */
    public function getRevision()
    {
        return $this->remoteEntry->getRevision();
    }

    /**
     * Returns the date when the remote entry was last updated.
     *
     * @return \DateTimeInterface
     */
    public function getUpdatedAt()
    {
        return $this->remoteEntry->getUpdatedAt();
    }

    /**
     * Returns the date when the remote entry was created.
     *
     * @return \DateTimeInterface
     */
    public function getCreatedAt()
    {
        return $this->remoteEntry->getCreatedAt();
    }

    /**
     * Returns the remote entry space.
     *
     * @return \Contentful\Delivery\Space
     */
    public function getSpace()
    {
        return $this->remoteEntry->getSpace();
    }

    /**
     * Returns the remote entry content type.
     *
     * @return \Contentful\Delivery\ContentType
     */
    public function getContentType()
    {
        return $this->remoteEntry->getContentType();
    }

    public function jsonSerialize()
    {
        return $this->remoteEntry->jsonSerialize();
    }

    /**
     * Sets the remote entry.
     *
     * @param \Contentful\Delivery\EntryInterface $remoteEntry
     */
    public function setRemoteEntry($remoteEntry)
    {
        $this->remoteEntry = $remoteEntry;
        $this->id = $this->remoteEntry->getSpace()->getId() . '|' . $this->remoteEntry->getId();

        $name_field = $this->remoteEntry->getContentType()->getDisplayField();
        $this->name = call_user_func(array($this->remoteEntry, 'get' . $name_field->getId()));
    }

    /**
     * Revives the remote entry from provided client.
     *
     * @param \Contentful\Delivery\Client $client
     */
    public function reviveRemoteEntry(Client $client)
    {
        $this->remoteEntry = $client->reviveJson($this->json);
        $this->id = $this->remoteEntry->getSpace()->getId() . '|' . $this->remoteEntry->getId();

        $name_field = $this->remoteEntry->getContentType()->getDisplayField();
        $this->name = call_user_func(array($this->remoteEntry, 'get' . $name_field->getId()));
    }
}
