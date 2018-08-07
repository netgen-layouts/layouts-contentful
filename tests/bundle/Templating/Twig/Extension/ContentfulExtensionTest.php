<?php

declare(strict_types=1);

namespace Netgen\Bundle\ContentfulBlockManagerBundle\Tests\Templating\Twig\Extension;

use Netgen\Bundle\ContentfulBlockManagerBundle\Templating\Twig\Extension\ContentfulExtension;
use PHPUnit\Framework\TestCase;
use Twig\TwigFunction;

final class ContentfulExtensionTest extends TestCase
{
    /**
     * @var \Netgen\Bundle\ContentfulBlockManagerBundle\Templating\Twig\Extension\ContentfulExtension
     */
    private $extension;

    public function setUp(): void
    {
        $this->extension = new ContentfulExtension();
    }

    /**
     * @covers \Netgen\Bundle\ContentfulBlockManagerBundle\Templating\Twig\Extension\ContentfulExtension::getFunctions
     */
    public function testGetFunctions(): void
    {
        self::assertNotEmpty($this->extension->getFunctions());

        foreach ($this->extension->getFunctions() as $function) {
            self::assertInstanceOf(TwigFunction::class, $function);
        }
    }
}
