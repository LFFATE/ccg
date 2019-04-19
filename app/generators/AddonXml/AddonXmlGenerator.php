<?php

namespace generators\AddonXml;

use generators\AddonXml\exceptions\InvalidAddonXmlException;
use generators\AddonXml\exceptions\InvalidContentException;
use generators\AddonXml\exceptions\DuplicateIdException;
use generators\Xml;
use mediators\AbstractMediator;
use mediators\ICanNotify;
use Config;

/**
 * @todo make functions like setSettings curry of one general function
 */
final class AddonXmlGenerator extends \generators\AbstractGenerator implements ICanNotify
{
    // readonly
    private $pathTemplate = '/app/addons/${addon}/addon.xml';
    private $templatePath = '';
    private $content;
    private $config;
    private $mediator;

    function __construct(
        Config $config
    )
    {
        $this->config = $config;
    }

    public function setMediator(AbstractMediator $mediator): void
    {
        $this->mediator = $mediator;
    }

    public function trigger(string $name, $data = [], $sender = null): void
    {
        $this->mediator && $this->mediator->trigger($name, $data, $this);
    }

    /**
     * @inheritdoc
     */
    public function getTemplateFilename(): string
    {
        return $this->templatePath;
    }

    /**
     * returns full path for addon.xml file
     * @return string
     */
    public function getPath(): string
    {
        $addon_id = $this->config->getOr('addon', 'addon.id');

        if (!$addon_id) {
            throw new \InvalidArgumentException('Addon id (name) not specified');
        }

        $path = $this->config->get('path')
            . $this->config->get('filesystem.output_path_relative')
            . str_replace('${addon}', $addon_id, $this->pathTemplate);

        return get_absolute_path($path);
    }

    /**
     * Set initial content for generator to work with
     * @param string $content - content of input xml file
     * @throws InvalidContentException if the $content is not valid xml content
     * @return AddonXmlGenerator
     */
    public function setContent(string $content)
    {
        try {
            $simpleXmlElement = new \SimpleXMLElement($content);
            $this->content = new XML($simpleXmlElement);
        } catch (\Exception $error) {
            throw new  InvalidContentException('Can\'t create xml from content');
        }

        return $this;
    }

    /**
     * Creates addon.xml content
     *
     * @return AddonXmlGenerator
     */
    public function create()
    {
        $this->createAddon();
        $this->setScheme();
        $this->setEditionType($this->config->get('addon.edition_type'));
        $this->setId($this->config->get('addon.id'));
        $this->setVersion($this->config->get('addon.version'));
        $this->setPriority($this->config->get('addon.priority'));
        $this->setStatus($this->config->get('addon.status'));
        $this->setAutoInstall($this->config->get('addon.auto_install'));
        $this->setSettings();
        // $this->setSettingsLayout();

        return $this;
    }

    /**
     * Creates root element for xml - addon
     * @return AddonXmlGenerator
     */
    public function createAddon()
    {
        $simpleXmlElement = new \SimpleXMLElement('<addon></addon>');
        $this->content = new XML($simpleXmlElement);
        $this->trigger(
            'addonxml.created',
            [
                'addon.id' => $this->config->getOr('addon', 'addon.id')
            ]
        );

        return $this;
    }

    /**
     * Retrieves addon xml element
     * @throws InvalidAddonXmlException if no addon node found
     * @return Xml - addon node
     */
    public function getAddon()
    {
        $addonElemenent = $this->content;

        if ('addon' !== $addonElemenent->getName()) {
            throw new InvalidAddonXmlException('No addon node found');
        }

        return $addonElemenent;
    }

    /**
     * <addon ... scheme="3.0">
     * @param string $scheme - scheme
     * @return AddonXmlGenerator
     */
    public function setScheme(string $scheme = '3.0')
    {
        $this->getAddon()->setAttribute('scheme', $scheme);

        return $this;
    }

    /**
     * <addon ... edition_type="ROOT,ULT:VENDOR">
     * @param string $type - edition_type
     * @return AddonXmlGenerator
     */
    public function setEditionType(string $type = 'ROOT,ULT:VENDOR')
    {
        $this->getAddon()->setAttribute('edition_type', $type);

        return $this;
    }

    /**
     * <id>sample_addon</id>
     * @param string $value - content of id node
     * @return AddonXmlGenerator
     */
    public function setId(string $value = 'sample_addon')
    {
        $this->getAddon()->setUniqueChild('id', $value);

        return $this;
    }

    /**
     * <version>1.0</version>
     * @param string $value - content of version node
     * @return AddonXmlGenerator
     */
    public function setVersion(string $value = '4.9')
    {
        $this->getAddon()->setUniqueChild('version', $value);

        return $this;
    }

    /**
     * <priority>100</priority>
     * @param string $value - content of priority node
     * @return AddonXmlGenerator
     */
    public function setPriority(string $value = '1000')
    {
        $this->getAddon()->setUniqueChild('priority', $value);

        return $this;
    }

    /**
     * <status>active</status>
     * @param string $value - content of status node
     * @return AddonXmlGenerator
     */
    public function setStatus(string $value = 'active')
    {
        $this->getAddon()->setUniqueChild('status', $value);

        return $this;
    }

    /**
     * <auto_install>MULTIVENDOR,ULTIMATE</auto_install>
     * @param string $value - content of auto_install node
     * @return AddonXmlGenerator
     */
    public function setAutoInstall(string $value = 'MULTIVENDOR,ULTIMATE')
    {
        $this->getAddon()->setUniqueChild('auto_install', $value);

        return $this;
    }

    /**
     * <settings...>
     * @return AddonXmlGenerator
     */
    public function setSettings()
    {
        $this->getAddon()->setUniqueChild('settings', '');

        return $this;
    }

    /**
     * Retrieve settings node
     * @return Xml|null - settings node
     */
    public function getSettings()
    {
        return $this->getAddon()->getSingleElement('settings');
    }

    /**
     * <settings ... layout="separate">
     * @param string layout - 'popup' or 'separate'
     * @return AddonXmlGenerator
     */
    public function setSettingsLayout(string $value = 'popup')
    {
        $this->getSettings()->setAttribute('layout', $value);

        return $this;
    }

    /**
     * Adds section to settings element
     * @throws DuplicateIdException if section with same id already exists
     * @return AddonXmlGenerator
     */
    public function addSection(string $id)
    {
        if ($this->getSection($id)) {
            throw new DuplicateIdException('section with same id already exists: ' . $id);
        }

        $sections = $this->getSections();

        if (null === $sections) {
            $sections =
                $this
                    ->getAddon()
                    ->getSingleElement('settings')
                    ->addChild('sections');
        }

        $sections
            ->addChild('section')
            ->addAttribute('id', $id);

        $this->trigger(
            'addonxml.settingSection.added',
            [
                'addon.id'  => $this->config->getOr('addon', 'addon.id'),
                'id'        => $id
            ]
        );

        return $this;
    }

    /**
     * Retrieves section with the specified id
     * @param string $id - by this id function will find section
     * @return Xml|null
     */
    public function getSection(string $id)
    {
        return $this->getAddon()->getSingleElement('section', $id);
    }

    /**
     * Retrieves sections node
     * @return Xml|null
     */
    public function getSections()
    {
        return $this->getAddon()->getSingleElement('sections');
    }

    /**
     * Set setting with replace if exists
     * @param string $section_id - to which section (id) setting will belong
     * @param string type
     * @param string $id
     * @param string $default_value
     * @param array $variants - list of possible values
     * Full information about params [https://docs.cs-cart.com/4.9.x/developer_guide/addons/scheme/scheme3.0_structure.html]
     * @throws DuplicateIdException if setting with same id already exists
     *
     * @return AddonXmlGenerator
     */
    private function _setSetting(
        string $section_id = 'section1',
        string $type,
        string $id,
        string $default_value = '',
        array  $variants = []
    )
    {
        $itemElement = $this->content->getSingleElement('item', $id);
        if (null !== $itemElement) {
            $itemElement->remove();
        }

        $sectionElement = $this->getSection($section_id);

        if (null === $sectionElement) {
            $sectionElement = $this->addSection($section_id)->getSection($section_id);
        }

        $itemsElement = $sectionElement->getSingleElement('items');

        if (null === $itemsElement) {
            $itemsElement = $sectionElement->addChild('items');
        }

        $settingItem = $itemsElement->addChild('item');
        $settingItem->addAttribute('id', $id);
            $settingItem->addChild('type', $type);
            if ($variants) {
                $variantsElement = $settingItem->addChild('variants');
                    foreach($variants as $variant)
                    {
                        $variantElement = $variantsElement->addChild('item');
                            $variantElement->addAttribute('id', $variant);
                    }
                    unset($variantElement);
            }
            $settingItem->addChild('default_value', $default_value);

        return $this;
    }

    /**
     * Set setting with replace if exists
     * @param string $section_id - to which section (id) setting will belong
     * @param string type
     * @param string $id
     * @param string $default_value
     * @param array $variants - list of possible values
     * Full information about params [https://docs.cs-cart.com/4.9.x/developer_guide/addons/scheme/scheme3.0_structure.html]
     *
     * @return AddonXmlGenerator
     */
    public function setSetting(
        string $section_id = 'section1',
        string $type,
        string $id,
        string $default_value = '',
        array  $variants = []
    )
    {
        $this->_setSetting(
            $section_id,
            $type,
            $id,
            $default_value,
            $variants
        );

        $this->trigger(
            'addonxml.setting.updated',
            [
                'addon.id'      => $this->config->getOr('addon', 'addon.id'),
                'section_id'    => $section_id,
                'type'          => $type,
                'id'            => $id,
                'variants'      => $variants
            ]
        );

        return $this;
    }

    /**
     * Set setting without replace if exists
     * @param string $section_id - to which section (id) setting will belong
     * @param string type
     * @param string $id
     * @param string $default_value
     * @param array $variants - list of possible values
     * Full information about params [https://docs.cs-cart.com/4.9.x/developer_guide/addons/scheme/scheme3.0_structure.html]
     * @throws DuplicateIdException if setting with same id already exists
     *
     * @return AddonXmlGenerator
     */
    public function addSetting(
        string $section_id = 'section1',
        string $type,
        string $id,
        string $default_value = '',
        array  $variants = []
    )
    {
        $itemElement = $this->content->getSingleElement('item', $id);

        if (null !== $itemElement) {
            throw new DuplicateIdException('Item with same id already exists: ' . $id);
        }

        $this->_setSetting(
            $section_id,
            $type,
            $id,
            $default_value,
            $variants
        );

        $this->trigger(
            'addonxml.setting.added',
            [
                'addon.id'      => $this->config->getOr('addon', 'addon.id'),
                'section_id'    => $section_id,
                'type'          => $type,
                'id'            => $id,
                'variants'      => $variants
            ]
        );

        return $this;
    }

    /**
     * @todo test
     * Remove settings item
     * 
     * @param string $id
     * 
     * @return AddonXmlGenerator
     */
    public function removeSetting(string $id)
    {
        $itemElement = $this->content->getSingleElement('item', $id);
        if (null !== $itemElement) {
            $itemElement->remove();
        }

        $this->trigger(
            'addonxml.setting.removed',
            [
                'addon.id'      => $this->config->getOr('addon', 'addon.id'),
                'id'            => $id
            ]
        );

        return $this;
    }

    /**
     * @deprecated - Use toString instead
     */
    public function toXml(): string
    {
        return $this->toString();
    }

    /**
     * Converts generator result to string
     * @return string
     */
    public function toString(): string
    {
        $Dom = new \DOMDocument('1.0');
        $Dom->preserveWhiteSpace    = false;
        $Dom->formatOutput          = true;
        $Dom->loadXML($this->content->asXML());

        return $Dom->saveXML();
    }
}
