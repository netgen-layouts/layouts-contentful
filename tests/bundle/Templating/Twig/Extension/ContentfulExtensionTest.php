<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsContentfulBundle\Tests\Templating\Twig\Extension;

use Netgen\Bundle\LayoutsContentfulBundle\Templating\Twig\Extension\ContentfulExtension;
use PHPUnit\Framework\TestCase;
use Twig\TwigFunction;

final class ContentfulExtensionTest extends TestCase
{
    /**
     * @var \Netgen\Bundle\LayoutsContentfulBundle\Templating\Twig\Extension\ContentfulExtension
     */
    private $extension;

    protected function setUp(): void
    {
        $this->extension = new ContentfulExtension();
    }

    /**
     * @covers \Netgen\Bundle\LayoutsContentfulBundle\Templating\Twig\Extension\ContentfulExtension::getFunctions
     */
    public function testGetFunctions(): void
    {
        self::assertNotEmpty($this->extension->getFunctions());
        self::assertContainsOnlyInstancesOf(TwigFunction::class, $this->extension->getFunctions());
    }
}
