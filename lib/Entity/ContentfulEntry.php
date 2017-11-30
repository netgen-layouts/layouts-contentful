<?php

namespace Netgen\BlockManager\Contentful\Entity;

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
     * @param  string $name
     * @param  array  $arguments
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
     * Get id.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set id.
     *
     * @param string $id
     *
     * @return ContentfulEntry
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return ContentfulEntry
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get json.
     *
     * @return string
     */
    public function getJson()
    {
        return $this->json;
    }

    /**
     * Set isPublished.
     *
     * @param bool
     * @param mixed $isPublished
     *
     * @return ContentfulEntry
     */
    public function setIsPublished($isPublished)
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    /**
     * Get isPublished.
     *
     * @return bool
     */
    public function getIsPublished()
    {
        return $this->isPublished;
    }

    /**
     * Set isDeleted.
     *
     * @param bool
     * @param mixed $isDeleted
     *
     * @return ContentfulEntry
     */
    public function setIsDeleted($isDeleted)
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    /**
     * Get isDeleted.
     *
     * @return bool
     */
    public function getIsDeleted()
    {
        return $this->isDeleted;
    }

    /**
     * Set json.
     *
     * @param string $json
     *
     * @return ContentfulEntry
     */
    public function setJson($json)
    {
        $this->json = $json;

        return $this;
    }

    /**
     * @return \Symfony\Cmf\Component\Routing\RouteObjectInterface[]|\Doctrine\Common\Collections\ArrayCollection
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * @param \Symfony\Cmf\Component\Routing\RouteObjectInterface[]|\Doctrine\Common\Collections\ArrayCollection $routes
     */
    public function setRoutes($routes)
    {
        $this->routes = $routes;
    }

    /**
     * @param \Symfony\Cmf\Component\Routing\RouteObjectInterface $route
     *
     * @return $this
     */
    public function addRoute($route)
    {
        $this->routes[] = $route;

        return $this;
    }

    /**
     * @param \Symfony\Cmf\Component\Routing\RouteObjectInterface $route
     *
     * @return $this
     */
    public function removeRoute($route)
    {
        $this->routes->removeElement($route);

        return $this;
    }

    /**
     * @return \Contentful\Delivery\EntryInterface
     */
    public function getRemoteEntry()
    {
        return $this->remoteEntry;
    }

    public function getRevision()
    {
        return $this->remoteEntry->getRevision();
    }

    public function getUpdatedAt()
    {
        return $this->remoteEntry->getUpdatedAt();
    }

    public function getCreatedAt()
    {
        return $this->remoteEntry->getCreatedAt();
    }

    public function getSpace()
    {
        return $this->remoteEntry->getSpace();
    }

    public function getContentType()
    {
        return $this->remoteEntry->getContentType();
    }

    public function jsonSerialize()
    {
        return $this->remoteEntry->jsonSerialize();
    }

    /**
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
     * @param \Contentful\Delivery\Client $client
     */
    public function reviveRemoteEntry($client)
    {
        $this->remoteEntry = $client->reviveJson($this->json);
        $this->id = $this->remoteEntry->getSpace()->getId() . '|' . $this->remoteEntry->getId();

        $name_field = $this->remoteEntry->getContentType()->getDisplayField();
        $this->name = call_user_func(array($this->remoteEntry, 'get' . $name_field->getId()));
    }
}
