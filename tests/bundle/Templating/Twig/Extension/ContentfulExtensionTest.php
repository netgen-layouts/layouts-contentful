<?php

namespace Netgen\Bundle\ContentfulBlockManagerBundle\Tests\Templating\Twig\Extension;

use Netgen\Bundle\ContentfulBlockManagerBundle\Templating\Twig\Extension\ContentfulExtension;
use PHPUnit\Framework\TestCase;
use Twig\TwigFunction;

class ContentfulExtensionTest extends TestCase
{
    /**
     * @var \Netgen\Bundle\ContentfulBlockManagerBundle\Templating\Twig\Extension\ContentfulExtension
     */
    private $extension;

    public function setUp()
    {
        $this->extension = new ContentfulExtension();
    }

    /**
     * @covers \Netgen\Bundle\ContentfulBlockManagerBundle\Templating\Twig\Extension\ContentfulExtension::getFunctions
     */
    public function testGetFunctions()
    {
        $this->assertNotEmpty($this->extension->getFunctions());

        foreach ($this->extension->getFunctions() as $function) {
            $this->assertInstanceOf(TwigFunction::class, $function);
        }
    }
}
