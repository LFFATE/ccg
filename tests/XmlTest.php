<?php

declare(strict_types=1);
include 'autoloader.php';

use PHPUnit\Framework\TestCase;

use generators\XML;
use filesystem\Filesystem;

final class XmlTest extends TestCase
{
    private $xml;
    private $filesystem;
    private $testFilename = __DIR__ . '/sources/xml/test.xml';

    protected function setUp(): void
    {
        $this->filesystem = new Filesystem();

        $simpleXml = new \SimpleXMLElement(
            $this->filesystem->read($this->testFilename)
        );
        $this->xml = new Xml($simpleXml);
    }

    public function testCanBeCreatedFromSimpleXmlElement(): void
    {
        $this->assertInstanceOf(
            Xml::class,
            $this->xml
        );
    }

    /**
     * @covers generators\XML::getSingleElement
     * @covers generators\XML::getSelf
     */
    public function testGetSingleElement(): void
    {
        // by name
        $idElement = $this->xml->getSingleElement('id');
        $this->assertEquals(
            $idElement->getName(),
            'id'
        );

        $this->assertEquals(
            (string) $idElement->getSelf(),
            'sd_new_addon'
        );

        // by name and id
        $sectionElement = $this->xml->getSingleElement('section', 'section1');
        $this->assertEquals(
            $sectionElement->getName(),
            'section'
        );

        $nonExistingElement = $this->xml->getSingleElement('non-existing-element');
        $this->assertEquals(
            null,
            $nonExistingElement
        );
    }

    /**
     * @covers generators\XML::setAttribute
     * @covers generators\XML::getAttributeValue
     */
    public function testSetAndGetAttribute(): void
    {
        $sectionElement = $this->xml->getSingleElement('section', 'section1');
        $sectionElement->setAttribute('name', 'test');

        $this->assertEquals(
            $sectionElement->getAttributeValue('name'),
            'test'
        );
    }

    /**
     * @covers generators\XML::setUniqueChild
     * @covers generators\XML::getSelf
     * @covers generators\XML::getSingleElement
     * @covers generators\XML::getSelf
     */
    public function testSetUniqueChild(): void
    {
        // root
        $addonElement = $this->xml;

        // first check for initial value
        $this->assertEquals(
            (string) $addonElement->getSingleElement('id')->getSelf(),
            'sd_new_addon'
        );

        // now change the value
        $addonElement->setUniqueChild('id', 'sd_newer_addon');

        // now check for the value update
        $this->assertEquals(
            $addonElement->getSingleElement('id')->getSelf(),
            'sd_newer_addon'
        );

        // inside another element
        $compatibilityElement = $addonElement->getSingleElement('compatibility');

        // first check for initial value
        $this->assertEquals(
            (string) $compatibilityElement->getSingleElement('core_edition')->getSelf(),
            'ULTIMATE,MULTIVENDOR'
        );

        // now change the value
        $compatibilityElement->setUniqueChild('core_edition', 'ULTIMATE');

        // now check for the value update
        $this->assertEquals(
            $compatibilityElement->getSingleElement('core_edition')->getSelf(),
            'ULTIMATE'
        );
    }
}
