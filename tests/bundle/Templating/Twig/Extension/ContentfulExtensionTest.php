<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsContentfulBundle\Tests\Templating\Twig\Extension;

use Netgen\Bundle\LayoutsContentfulBundle\Templating\Twig\Extension\ContentfulExtension;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Twig\TwigFunction;

#[CoversClass(ContentfulExtension::class)]
final class ContentfulExtensionTest extends TestCase
{
    private ContentfulExtension $extension;

    protected function setUp(): void
    {
        $this->extension = new ContentfulExtension();
    }

    public function testGetFunctions(): void
    {
        self::assertNotEmpty($this->extension->getFunctions());
        self::assertContainsOnlyInstancesOf(TwigFunction::class, $this->extension->getFunctions());
    }
}
