<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use generators\AddonXml\AddonXmlGenerator;
use generators\AddonXml\exceptions\InvalidContentException;
use generators\AddonXml\exceptions\DuplicateIdException;
use generators\Xml;
use filesystem\Filesystem;

/**
 * @todo remove dependency from filesystem use file_get_contents instead
 */
final class AddonXmlGeneratorTest extends TestCase
{
    private $generator;
    private $config;
    private $testFilename = ROOT_DIR . '/tests/sources/xml/test.xml';

    protected function setUp(): void
    {
        $this->config = new Config([
            'addon.id=sd_addon'
        ],
        [
            'addon.edition_type' => 'ROOT,ULT:VENDOR',
            'addon.version' => '4.9',
            'addon.priority' => '665',
            'addon.status' => 'active',
            'addon.auto_install' => 'ULTIMATE',
            'filesystem.output_path_relative' => './'
        ]);
        $this->generator = new AddonXmlGenerator($this->config);
    }

    public function testGetPath(): void
    {
        $this->assertSame(
            get_absolute_path(ROOT_DIR . $this->config->get('filesystem.output_path_relative') . 'app/addons/sd_addon/addon.xml'),
            $this->generator->getPath()
        );
    }

    /**
     * @covers generators\AddonXml\AddonXmlGenerator::toXml
     * @covers generators\AddonXml\AddonXmlGenerator::create
     */
    public function testCreate(): void
    {
        $xmlString = $this->generator->create()->toXml();
        $simpleXml = new \SimpleXMLElement($xmlString);
        $addon = new Xml($simpleXml);

        $idElement = $addon->getSingleElement('id');
        $this->assertEquals(
            (string) $idElement->getSelf(),
            'sd_addon'
        );

        $versionElement = $addon->getSingleElement('version');
        $this->assertEquals(
            (string) $versionElement->getSelf(),
            '4.9'
        );

        $priorityElement = $addon->getSingleElement('priority');
        $this->assertEquals(
            (string) $priorityElement->getSelf(),
            '665'
        );

        $statusElement = $addon->getSingleElement('status');
        $this->assertEquals(
            (string) $statusElement->getSelf(),
            'active'
        );

        $settingsElement = $addon->getSingleElement('settings');
        $this->assertNotEquals(
            $settingsElement,
            null
        );
    }

    /**
     * @covers generators\AddonXml\AddonXmlGenerator::setContent
     */
    public function testSetContent(): void
    {
        $filesystem = new Filesystem();
        $this->generator->setContent($filesystem->read($this->testFilename));
        $result = $this->generator->toXml();

        $this->assertXmlStringEqualsXmlFile(
            $this->testFilename,
            $result
        );

        $this->expectException(InvalidContentException::class);
        $this->generator->setContent('');
        $this->expectException(InvalidContentException::class);
        $this->generator->setContent(13);
    }

    /**
     * @covers generators\AddonXml\AddonXmlGenerator::createAddon
     */
    public function testCreateAddon(): void
    {
        $xml = $this->generator->createAddon()->toString();

        $actual = new DOMDocument;
        $actual->loadXML($xml);

        $expected = new DOMDocument;
        $expected->loadXML('<addon></addon>');

        $this->assertEqualXMLStructure(
            $expected->firstChild,
            $actual->firstChild,
            true
        );
    }

    /**
     * @covers generators\AddonXml\AddonXmlGenerator::setScheme
     */
    public function testSetScheme(): void
    {
        $test_value = '4.0';
        $xml = $this->generator->createAddon()->setScheme($test_value)->toString();
        $this->_testAddonAttribute($xml, 'scheme', $test_value);
    }

    /**
     * @covers generators\AddonXml\AddonXmlGenerator::setEditionType
     */
    public function testSetEditionType(): void
    {
        $test_value = 'ULT:VENDOR';
        $xml = $this->generator->createAddon()->setEditionType($test_value)->toString();
        $this->_testAddonAttribute($xml, 'edition_type', $test_value);
    }

    /**
     * @covers generators\AddonXml\AddonXmlGenerator::setId
     */
    public function testSetId(): void
    {
        $test_value = 'sd_addon_id';
        $xml = $this->generator->createAddon()->setId($test_value)->toString();
        $this->_testAddonNode($xml, 'id', $test_value);
    }


    /**
     * @covers generators\AddonXml\AddonXmlGenerator::setVersion
     */
    public function testSetVersion(): void
    {
        $test_value = '4.9.9.8';
        $xml = $this->generator->createAddon()->setVersion($test_value)->toString();
        $this->_testAddonNode($xml, 'version', $test_value);
    }

    /**
     * @covers generators\AddonXml\AddonXmlGenerator::setPriority
     */
    public function testSetPriority(): void
    {
        $test_value = '1000';
        $xml = $this->generator->createAddon()->setPriority($test_value)->toString();
        $this->_testAddonNode($xml, 'priority', $test_value);
    }

    /**
     * @covers generators\AddonXml\AddonXmlGenerator::setStatus
     */
    public function testSetStatus(): void
    {
        $test_value = 'active';
        $xml = $this->generator->createAddon()->setStatus($test_value)->toString();
        $this->_testAddonNode($xml, 'status', $test_value);
    }

    /**
     * @covers generators\AddonXml\AddonXmlGenerator::setAutoInstall
     */
    public function testSetAutoInstall(): void
    {
        $test_value = 'MULTIVENDOR';
        $xml = $this->generator->createAddon()->setAutoInstall($test_value)->toString();
        $this->_testAddonNode($xml, 'auto_install', $test_value);
    }

    /**
     * @covers generators\AddonXml\AddonXmlGenerator::setSettings
     * @covers generators\AddonXml\AddonXmlGenerator::getSettings
     */
    public function testSetAndGetSettings(): void
    {
        $xml = $this->generator->createAddon()->setSettings()->toString();
        $this->_testAddonNode($xml, 'settings', '');

        $settingsElement = $this->generator->createAddon()->setSettings()->getSettings();
        $this->assertSame(
            'settings',
            $settingsElement->getName()
        );
    }

    /**
     * @covers generators\AddonXml\AddonXmlGenerator::setSettingsLayout
     */
    public function testSetSettingsLayout(): void
    {
        $xml =
            $this
                ->generator
                ->createAddon()
                ->setSettings()
                ->setSettingsLayout('separate')
                ->toString();

        $actual = new DOMDocument;
        $actual->loadXML($xml);

        $expected = new DOMDocument;
        $expected->loadXML('<addon><settings layout="separate"></settings></addon>');

        $this->assertEqualXMLStructure(
            $expected->firstChild,
            $actual->firstChild,
            true
        );
    }

    /**
     * @covers generators\AddonXml\AddonXmlGenerator::addSection
     */
    public function testAddSectionThrowsException(): void
    {
        $this->expectException(DuplicateIdException::class);
        
        $this
            ->generator
            ->createAddon()
            ->setSettings()
            ->addSection('defaults')
            ->addSection('secondary')
            ->addSection('defaults')
            ->toString();
    }

    /**
     * @covers generators\AddonXml\AddonXmlGenerator::addSection
     * @covers generators\AddonXml\AddonXmlGenerator::setSetting
     */
    public function testSetSetting(): void
    {
        $expected_xml = <<<EOD
        <addon>
            <settings>
                <sections>
                    <section id="defaults">
                        <items>
                            <item id="vendor_name">
                                <type>input</type>
                                <default_value>shemenyov</default_value>
                            </item>
                            <item id="counter">
                                <type>checkbox</type>
                                <default_value/>
                            </item>
                        </items>
                    </section>
                    <section id="secondary">
                        <items>
                            <item id="number_of_emails">
                                <type>selectbox</type>
                                <variants>
                                    <item id="one"></item>
                                    <item id="three"></item>
                                    <item id="ten"></item>
                                </variants>
                                <default_value>ten</default_value>
                            </item>
                        </items>
                    </section>
                </sections>
            </settings>
        </addon>
EOD;
        $xml =
            $this
                ->generator
                ->createAddon()
                ->setSettings()
                ->addSection('defaults')
                ->addSection('secondary')
                ->setSetting(
                    'defaults',
                    'input',
                    'vendor_name',
                    'petrov'
                )
                ->setSetting(
                    'defaults',
                    'input',
                    'vendor_name',
                    'sergeev'
                )
                ->setSetting(
                    'defaults',
                    'input',
                    'vendor_name',
                    'shemenyov'
                )
                ->setSetting(
                    'defaults',
                    'checkbox',
                    'counter'
                )
                ->setSetting(
                    'secondary',
                    'number_of_emails',
                    'selectbox',
                    'ten',
                    [
                        'one', 'three', 'ten'
                    ]
                )
                ->toString();

        $actual = new DOMDocument;
        $actual->loadXML($xml);

        $expected = new DOMDocument;
        $expected->loadXML($expected_xml);

        $this->assertEqualXMLStructure(
            $expected->firstChild,
            $actual->firstChild,
            true
        );
    }

    /**
     * @covers generators\AddonXml\AddonXmlGenerator::addSetting
     */
    public function testAddSetting(): void
    {
        $this->expectException(DuplicateIdException::class);
        $xml_duplicate_sections =
            $this
                ->generator
                ->createAddon()
                ->setSettings()
                ->addSection('defaults')
                ->addSection('secondary')
                ->addSection('defaults')
                ->addSetting(
                    'defaults',
                    'input',
                    'vendor_name',
                    'shemenyov'
                )
                ->addSetting(
                    'defaults',
                    'input',
                    'vendor_name',
                    'shemenyov'
                );
    }

    private function _testAddonNode(string $xml, string $name, string $value): void
    {
        $actual = new DOMDocument;
        $actual->loadXML($xml);

        $expected = new DOMDocument;
        $expected->loadXML("<addon><$name>$value</$name></addon>");

        $this->assertEqualXMLStructure(
            $expected->firstChild,
            $actual->firstChild,
            true
        );
    }

    private function _testAddonAttribute(string $xml, string $name, string $value): void
    {
        $actual = new DOMDocument;
        $actual->loadXML($xml);

        $expected = new DOMDocument;
        $expected->loadXML("<addon $name=\"$value\"></addon>");

        $this->assertEqualXMLStructure(
            $expected->firstChild,
            $actual->firstChild,
            true
        );
    }
}
