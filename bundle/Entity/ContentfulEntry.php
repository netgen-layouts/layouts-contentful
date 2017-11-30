<?php

namespace Netgen\Bundle\ContentfulBlockManagerBundle\Entity;

use Contentful\Delivery\EntryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Cmf\Component\Routing\RouteReferrersInterface;

/**
 * @ORM\Table(name="contentful_entry")
 * @ORM\Entity
 */
class ContentfulEntry implements RouteReferrersInterface, EntryInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @ORM\Column(type="text")
     */
    private $json;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isPublished;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isDeleted;

    /**
     * @var EntryInterface
     * original entry
     */
    private $remote_entry;

    /**
     * @var RouteObjectInterface[]|ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Orm\Route", cascade={"persist", "remove"})
     */
    private $routes;

    public function __construct(EntryInterface $remote_entry)
    {
        $this->routes = new ArrayCollection();
        $this->setRemoteEntry($remote_entry);
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
            $ret = call_user_func(array($this->remote_entry, $name));
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
     * @return RouteObjectInterface[]|ArrayCollection
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * @param RouteObjectInterface[]|ArrayCollection $routes
     */
    public function setRoutes($routes)
    {
        $this->routes = $routes;
    }

    /**
     * @param RouteObjectInterface $route
     *
     * @return $this
     */
    public function addRoute($route)
    {
        $this->routes[] = $route;

        return $this;
    }

    /**
     * @param RouteObjectInterface $route
     *
     * @return $this
     */
    public function removeRoute($route)
    {
        $this->routes->removeElement($route);

        return $this;
    }

    /**
     * @return EntryInterface
     */
    public function getRemoteEntry()
    {
        return $this->remote_entry;
    }

    public function getRevision()
    {
        return $this->remote_entry->getRevision();
    }

    public function getUpdatedAt()
    {
        return $this->remote_entry->getUpdatedAt();
    }

    public function getCreatedAt()
    {
        return $this->remote_entry->getCreatedAt();
    }

    public function getSpace()
    {
        return $this->remote_entry->getSpace();
    }

    public function getContentType()
    {
        return $this->remote_entry->getContentType();
    }

    public function jsonSerialize()
    {
        return $this->remote_entry->jsonSerialize();
    }

    /**
     * @param EntryInterface $remote_entry
     */
    public function setRemoteEntry($remote_entry)
    {
        $this->remote_entry = $remote_entry;
        $this->id = $this->remote_entry->getSpace()->getId() . '|' . $this->remote_entry->getId();

        $name_field = $this->remote_entry->getContentType()->getDisplayField();
        $this->name = call_user_func(array($this->remote_entry, 'get' . $name_field->getId()));
    }

    /**
     * @param \Contentful\Delivery\Client $client
     */
    public function reviveRemoteEntry($client)
    {
        $this->remote_entry = $client->reviveJson($this->json);
        $this->id = $this->remote_entry->getSpace()->getId() . '|' . $this->remote_entry->getId();

        $name_field = $this->remote_entry->getContentType()->getDisplayField();
        $this->name = call_user_func(array($this->remote_entry, 'get' . $name_field->getId()));
    }
}
