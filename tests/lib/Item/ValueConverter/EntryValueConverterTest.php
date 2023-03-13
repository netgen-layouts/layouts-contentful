<?php

declare(strict_types=1);

namespace Netgen\Layouts\Contentful\Tests\Item\ValueConverter;

use Netgen\Layouts\Contentful\Entity\ContentfulEntry;
use Netgen\Layouts\Contentful\Item\ValueConverter\EntryValueConverter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(EntryValueConverter::class)]
final class EntryValueConverterTest extends TestCase
{
    private EntryValueConverter $valueConverter;

    protected function setUp(): void
    {
        $this->valueConverter = new EntryValueConverter();
    }

    public function testSupports(): void
    {
        self::assertTrue($this->valueConverter->supports(new ContentfulEntry()));
        self::assertFalse($this->valueConverter->supports(new stdClass()));
    }

    public function testGetValueType(): void
    {
        self::assertSame(
            'contentful_entry',
            $this->valueConverter->getValueType(
                new ContentfulEntry(),
            ),
        );
    }

    public function testGetId(): void
    {
        $entry = new ContentfulEntry();
        $entry->setId('abc');

        self::assertSame('abc', $this->valueConverter->getId($entry));
    }

    public function testGetRemoteId(): void
    {
        $entry = new ContentfulEntry();
        $entry->setId('abc');

        self::assertSame('abc', $this->valueConverter->getRemoteId($entry));
    }

    public function testGetName(): void
    {
        $entry = new ContentfulEntry();
        $entry->setName('Entry name');

        self::assertSame('Entry name', $this->valueConverter->getName($entry));
    }

    public function testGetIsVisible(): void
    {
        $entry = new ContentfulEntry();
        $entry->setIsPublished(true);

        self::assertTrue($this->valueConverter->getIsVisible($entry));
    }

    public function testGetObject(): void
    {
        $entry = new ContentfulEntry();

        self::assertSame($entry, $this->valueConverter->getObject($entry));
    }
}
