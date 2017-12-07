<?php

namespace Netgen\BlockManager\Contentful\Tests\Item\ValueConverter;

use Netgen\BlockManager\Contentful\Entity\ContentfulEntry;
use Netgen\BlockManager\Contentful\Item\ValueConverter\EntryValueConverter;
use PHPUnit\Framework\TestCase;
use stdClass;

class EntryValueConverterTest extends TestCase
{
    /**
     * @var \Netgen\BlockManager\Contentful\Item\ValueConverter\EntryValueConverter
     */
    private $valueConverter;

    public function setUp()
    {
        $this->valueConverter = new EntryValueConverter();
    }

    /**
     * @covers \Netgen\BlockManager\Contentful\Item\ValueConverter\EntryValueConverter::supports
     */
    public function testSupports()
    {
        $this->assertTrue($this->valueConverter->supports(new ContentfulEntry()));
        $this->assertFalse($this->valueConverter->supports(new stdClass()));
    }

    /**
     * @covers \Netgen\BlockManager\Contentful\Item\ValueConverter\EntryValueConverter::getValueType
     */
    public function testGetValueType()
    {
        $this->assertEquals(
            'contentful_entry',
            $this->valueConverter->getValueType(
                new ContentfulEntry()
            )
        );
    }

    /**
     * @covers \Netgen\BlockManager\Contentful\Item\ValueConverter\EntryValueConverter::getId
     */
    public function testGetId()
    {
        $entry = new ContentfulEntry();
        $entry->setId(42);

        $this->assertEquals(42, $this->valueConverter->getId($entry));
    }

    /**
     * @covers \Netgen\BlockManager\Contentful\Item\ValueConverter\EntryValueConverter::getRemoteId
     */
    public function testGetRemoteId()
    {
        $entry = new ContentfulEntry();
        $entry->setId(42);

        $this->assertEquals(42, $this->valueConverter->getRemoteId($entry));
    }

    /**
     * @covers \Netgen\BlockManager\Contentful\Item\ValueConverter\EntryValueConverter::getName
     */
    public function testGetName()
    {
        $entry = new ContentfulEntry();
        $entry->setName('Entry name');

        $this->assertEquals('Entry name', $this->valueConverter->getName($entry));
    }

    /**
     * @covers \Netgen\BlockManager\Contentful\Item\ValueConverter\EntryValueConverter::getIsVisible
     */
    public function testGetIsVisible()
    {
        $entry = new ContentfulEntry();
        $entry->setIsPublished(true);

        $this->assertTrue($this->valueConverter->getIsVisible($entry));
    }

    /**
     * @covers \Netgen\BlockManager\Contentful\Item\ValueConverter\EntryValueConverter::getObject
     */
    public function testGetObject()
    {
        $entry = new ContentfulEntry();

        $this->assertEquals($entry, $this->valueConverter->getObject($entry));
    }
}
