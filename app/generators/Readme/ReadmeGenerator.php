<?php

namespace generators\Readme;

use Config;
use mediators\AbstractMediator;

/**
  * @property string $pathTemplate
  * @property string $content
  * @property Config $config
  */
final class ReadmeGenerator extends \generators\AbstractGenerator
{
    // readonly
    private $pathTemplate = 'app/addons/${addon}/README.md';
    private $templatePath = ROOT_DIR . '/resources/README.md';
    private $content = '';
    private $config;
    private $mediator;

    function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function getTemplateFilename(): string
    {
        return sanitize_filename($this->templatePath);
    }

    public function setMediator(AbstractMediator $mediator): void
    {
        $this->mediator = $mediator;
    }

    /**
     * @inheritdoc
     */
    public function getPath(): string
    {
        $addon_id = $this->config->getOr('addon', 'addon.id');

        if (!$addon_id) {
            throw new \InvalidArgumentException('Addon id (name) not specified');
        }

        $path = $this->config->get('filesystem.output_path')
            . str_replace('${addon}', $addon_id, $this->pathTemplate);

        return sanitize_filename($path);
    }

    /**
     * @inheritdoc
     *
     * @return LanguageGenerator
     */
    public function setContent(string $content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Replace placeholders by real data
     * @param string $content
     * 
     * @return string
     */
    public static function substituteData(string $content, array $data): string
    {
        return str_replace(
            [
                '${addon.id}',
                '${developer.company}',
                '${developer.name}',
            ],
            $data,
            $content
        );
    }

    /**
     * @inheritdoc
     */
    public function toString(): string
    {
        return self::substituteData($this->content, [
            parse_to_readable($this->config->get('addon.id')),
            $this->config->get('developer.company'),
            $this->config->get('developer.name')
        ]);
    }
}
