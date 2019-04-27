<?php

declare(strict_types=1);

namespace Netgen\Layouts\Contentful\Tests\Item\ValueConverter;

use Netgen\Layouts\Contentful\Entity\ContentfulEntry;
use Netgen\Layouts\Contentful\Item\ValueConverter\EntryValueConverter;
use PHPUnit\Framework\TestCase;
use stdClass;

final class EntryValueConverterTest extends TestCase
{
    /**
     * @var \Netgen\Layouts\Contentful\Item\ValueConverter\EntryValueConverter
     */
    private $valueConverter;

    protected function setUp(): void
    {
        $this->valueConverter = new EntryValueConverter();
    }

    /**
     * @covers \Netgen\Layouts\Contentful\Item\ValueConverter\EntryValueConverter::supports
     */
    public function testSupports(): void
    {
        self::assertTrue($this->valueConverter->supports(new ContentfulEntry()));
        self::assertFalse($this->valueConverter->supports(new stdClass()));
    }

    /**
     * @covers \Netgen\Layouts\Contentful\Item\ValueConverter\EntryValueConverter::getValueType
     */
    public function testGetValueType(): void
    {
        self::assertSame(
            'contentful_entry',
            $this->valueConverter->getValueType(
                new ContentfulEntry()
            )
        );
    }

    /**
     * @covers \Netgen\Layouts\Contentful\Item\ValueConverter\EntryValueConverter::getId
     */
    public function testGetId(): void
    {
        $entry = new ContentfulEntry();
        $entry->setId('abc');

        self::assertSame('abc', $this->valueConverter->getId($entry));
    }

    /**
     * @covers \Netgen\Layouts\Contentful\Item\ValueConverter\EntryValueConverter::getRemoteId
     */
    public function testGetRemoteId(): void
    {
        $entry = new ContentfulEntry();
        $entry->setId('abc');

        self::assertSame('abc', $this->valueConverter->getRemoteId($entry));
    }

    /**
     * @covers \Netgen\Layouts\Contentful\Item\ValueConverter\EntryValueConverter::getName
     */
    public function testGetName(): void
    {
        $entry = new ContentfulEntry();
        $entry->setName('Entry name');

        self::assertSame('Entry name', $this->valueConverter->getName($entry));
    }

    /**
     * @covers \Netgen\Layouts\Contentful\Item\ValueConverter\EntryValueConverter::getIsVisible
     */
    public function testGetIsVisible(): void
    {
        $entry = new ContentfulEntry();
        $entry->setIsPublished(true);

        self::assertTrue($this->valueConverter->getIsVisible($entry));
    }

    /**
     * @covers \Netgen\Layouts\Contentful\Item\ValueConverter\EntryValueConverter::getObject
     */
    public function testGetObject(): void
    {
        $entry = new ContentfulEntry();

        self::assertSame($entry, $this->valueConverter->getObject($entry));
    }
}
