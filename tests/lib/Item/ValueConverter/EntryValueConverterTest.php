<?php

declare(strict_types=1);

namespace Netgen\BlockManager\Contentful\Tests\Item\ValueConverter;

use Netgen\BlockManager\Contentful\Entity\ContentfulEntry;
use Netgen\BlockManager\Contentful\Item\ValueConverter\EntryValueConverter;
use PHPUnit\Framework\TestCase;
use stdClass;

final class EntryValueConverterTest extends TestCase
{
    /**
     * @var \Netgen\BlockManager\Contentful\Item\ValueConverter\EntryValueConverter
     */
    private $valueConverter;

    public function setUp(): void
    {
        $this->valueConverter = new EntryValueConverter();
    }

    /**
     * @covers \Netgen\BlockManager\Contentful\Item\ValueConverter\EntryValueConverter::supports
     */
    public function testSupports(): void
    {
        $this->assertTrue($this->valueConverter->supports(new ContentfulEntry()));
        $this->assertFalse($this->valueConverter->supports(new stdClass()));
    }

    /**
     * @covers \Netgen\BlockManager\Contentful\Item\ValueConverter\EntryValueConverter::getValueType
     */
    public function testGetValueType(): void
    {
        $this->assertSame(
            'contentful_entry',
            $this->valueConverter->getValueType(
                new ContentfulEntry()
            )
        );
    }

    /**
     * @covers \Netgen\BlockManager\Contentful\Item\ValueConverter\EntryValueConverter::getId
     */
    public function testGetId(): void
    {
        $entry = new ContentfulEntry();
        $entry->setId('abc');

        $this->assertSame('abc', $this->valueConverter->getId($entry));
    }

    /**
     * @covers \Netgen\BlockManager\Contentful\Item\ValueConverter\EntryValueConverter::getRemoteId
     */
    public function testGetRemoteId(): void
    {
        $entry = new ContentfulEntry();
        $entry->setId('abc');

        $this->assertSame('abc', $this->valueConverter->getRemoteId($entry));
    }

    /**
     * @covers \Netgen\BlockManager\Contentful\Item\ValueConverter\EntryValueConverter::getName
     */
    public function testGetName(): void
    {
        $entry = new ContentfulEntry();
        $entry->setName('Entry name');

        $this->assertSame('Entry name', $this->valueConverter->getName($entry));
    }

    /**
     * @covers \Netgen\BlockManager\Contentful\Item\ValueConverter\EntryValueConverter::getIsVisible
     */
    public function testGetIsVisible(): void
    {
        $entry = new ContentfulEntry();
        $entry->setIsPublished(true);

        $this->assertTrue($this->valueConverter->getIsVisible($entry));
    }

    /**
     * @covers \Netgen\BlockManager\Contentful\Item\ValueConverter\EntryValueConverter::getObject
     */
    public function testGetObject(): void
    {
        $entry = new ContentfulEntry();

        $this->assertSame($entry, $this->valueConverter->getObject($entry));
    }
}
