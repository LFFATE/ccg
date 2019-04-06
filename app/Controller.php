<?php

use exceptions\FileAlreadyExistsException;
use readers\AddonXmlReader;
use generators\AddonXml\AddonXmlGenerator;
use generators\Language\LanguageGenerator;
use generators\FileGenerator;
use enums\Config as EnumConfig;
use terminal\Terminal;
use filesystem\Filesystem;
use mediators\GeneratorMediator;

/**
 * Enter point for all commands
 * @todo Find out a code splitting way for this class
 * It may be a several traits
 */
class Controller
{
    private $config;
    private $addonXmlGenerator;
    private $terminal;
    private $filesystem;

    function __construct(
        Config              $config,
        Terminal            $terminal,
        Filesystem          $filesystem
    )
    {
        $this->config               = $config;
        $this->terminal             = $terminal;
        $this->filesystem           = $filesystem;
    }

    /**
     * help:
     * help
     * Shows help for code generator commands
     * @param string $search - filter commands
     */
    private function help($search = '')
    {
        $this->showHelp($search);
    }

    private function showHelp($search = '')
    {
        $this->terminal->echo('Usage: php _tools/ccg/generator.php [generator] [command] [options]');

        $docs = $this->getHelp();

        array_walk($docs, function($doc) use ($search) {

            if ($search && strpos($doc['name'], $search) === false) {
                return;
            }

            $this->terminal->echo(str_repeat(PHP_EOL, 2));

            $docLines           = preg_split('/\r\n|\r|\n/', $doc['help']);
            $descriptionLines   = $docLines;

            unset($descriptionLines[0]);

            $this->terminal->success($docLines[0]);
            $this->terminal->echo(implode(PHP_EOL, $descriptionLines));
        });
    }

    private function getHelp()
    {
        $ref = new \ReflectionClass($this);
        $methods = $ref->getMethods();

        $docs = array_map(function(\ReflectionMethod $method) use ($ref) {
            $helpCommentPurified = '';
            $helpComment = self::_extractCommentForHelp($method->getDocComment());

            if ($helpComment) {
                $helpCommentPurified = self::_purifyCommentForHelp($helpComment[1]);
            }

            return [
                'name' => $method->getName(),
                'help' => $helpCommentPurified,
            ];
        }, $methods);

        $docsFiltered = array_values(
            array_filter($docs, function($doc) {
                return (bool) $doc['help'];
            })
        );

        return $docsFiltered;
    }

    /*
    +-------------------------------------------------------+
    | AddonXml                                              |
    +-------------------------------------------------------+
    +-------------------------------------------------------+*/
    /**
     * help:
     * addonXml create
     * creates addonXml structure and write it to file
     * @throws Exception if file already exists
     */
    private function addonXmlCreate()
    {
        $addonXmlGenerator = new AddonXmlGenerator($this->config);
        $languageGenerator = new LanguageGenerator($this->config);
        $generatorMediator = new GeneratorMediator();
        $generatorMediator->addGenerator($addonXmlGenerator);
        $generatorMediator->addGenerator($languageGenerator);

        $addonXmlFileGenerator = new FileGenerator($addonXmlGenerator, $this->filesystem);
        $languageFileGenerator = new FileGenerator($languageGenerator, $this->filesystem);

        $addonXmlFileGenerator->throwIfExists($addonXmlGenerator->getPath() . ' already exists. Remove it first if you want to replace it.');
        $languageFileGenerator->throwIfExists($languageGenerator->getPath() . ' already exists. Remove it first if you want to replace it.');

        $addonXmlGenerator->create();

        $addonXmlFileGenerator
            ->write()
            ->throwIfNotExists($addonXmlGenerator->getPath() . ' cannot be created.');
        $languageFileGenerator
            ->write()
            ->throwIfNotExists($languageGenerator->getPath() . ' cannot be created.');

        /**
         * results
         */
        $this->terminal->success($addonXmlGenerator->getPath() . ' was created');
        $this->terminal->diff(
            \Diff::toString(\Diff::compare('', $addonXmlGenerator->toString()))
        );

        $this->terminal->success($languageGenerator->getPath() . ' was created');
        $this->terminal->diff(
            \Diff::toString(\Diff::compare('', $languageGenerator->toString()))
        );
    }

    /**
     * help:
     * addonXml remove
     * removes file addon.xml
     * @throws Exception if file doesn't exists
     */
    private function addonXmlRemove()
    {
        $addonXmlGenerator      = new AddonXmlGenerator($this->config);
        $languageGenerator      = new LanguageGenerator($this->config);
        $generatorMediator      = new GeneratorMediator();
        $generatorMediator->addGenerator($addonXmlGenerator);
        $generatorMediator->addGenerator($languageGenerator);
        $addonXmlFileGenerator  = new FileGenerator($addonXmlGenerator, $this->filesystem);
        $languageFileGenerator  = new FileGenerator($languageGenerator, $this->filesystem);

        $addonXmlFileGenerator
            ->read()
            ->remove()
            ->throwIfExists($addonXmlGenerator->getPath() . ' cannot be removed.');

        $languageFileGenerator
            ->read()
            ->remove()
            ->throwIfExists($languageGenerator->getPath() . ' cannot be removed.');

        $this->terminal->success($addonXmlGenerator->getPath() . ' was removed');
        $this->terminal->diff(
            \Diff::toString(\Diff::compare($addonXmlGenerator->toString(), ''))
        );

        $this->terminal->success($languageGenerator->getPath() . ' was removed');
        $this->terminal->diff(
            \Diff::toString(\Diff::compare($languageGenerator->toString(), ''))
        );
    }

    /**
     * help:
     * addonXml update addon=<addon_id> set=<item> [...args]
     * Sets additional field to addon xml file
     * addon_id - id of the addon
     * set      - item to update at addon xml
     * ---
     *      settings item - si - <item id="date">...</item>
     *           args: section=<section_id> type=<type> id=<id> [dv=<default_value>] [v=<variants>]
     *              section         - id for the settings section
     *              type            - type of the item id: input, textarea, password, checkbox, selectbox, multiple select, multiple checkboxes, countries list, states list, file, info, header, template
     *              id              - id of the setting item
     *              default_value   - (df) - default value for setting item
     *              variants        - (v) - list of item value variants comma separated
     * ---
     *
     * see more @link [https://www.cs-cart.ru/docs/4.9.x/developer_guide/addons/scheme/scheme3.0_structure.html]
     * @throws Exception if file doesn't exists
     */
    private function addonXmlUpdate()
    {
        $addonXmlGenerator      = new AddonXmlGenerator($this->config);
        $languageGenerator      = new LanguageGenerator($this->config);
        $generatorMediator      = new GeneratorMediator();
        $generatorMediator->addGenerator($addonXmlGenerator);
        $generatorMediator->addGenerator($languageGenerator);
        $addonXmlFileGenerator  = new FileGenerator($addonXmlGenerator, $this->filesystem);
        $languageFileGenerator  = new FileGenerator($languageGenerator, $this->filesystem);

        $addonXmlFileGenerator
            ->throwIfNotExists($addonXmlGenerator->getPath() . ' not found.')
            ->read();

        $languageFileGenerator
            ->throwIfNotExists($languageGenerator->getPath() . ' not found.')
            ->read();

        $addon_xml_old_content  = $addonXmlGenerator->toString();
        $language_old_content   = $languageGenerator->toString();
        $set_item = $this->config->get('set');

        switch ($set_item)
        {
            case 'settings item':
            case 'si':
                $addonXmlGenerator->setSetting(
                    $this->config->get('section'),
                    $this->config->get('type'),
                    $this->config->get('id'),
                    $this->config->getOr('default_value', 'dv') ?: '',
                    (function($config) {
                        $variants = $config->getOr('variants', 'v');

                        return $variants ? explode(',', $variants) : [];
                    })($this->config)
                );
            break;
            default:
                throw new \BadMethodCallException('There is no such command: ' . $set_item);
        }

        $addonXmlFileGenerator
            ->write()
            ->throwIfNotExists();
        $languageFileGenerator
            ->write()
            ->throwIfNotExists();

        $this->terminal->diff(
            \Diff::toString(\Diff::compare($addon_xml_old_content, $addonXmlGenerator->toString()))
        );
        $this->terminal->diff(
            \Diff::toString(\Diff::compare($language_old_content, $languageGenerator->toString()))
        );
    }

    public function generate()
    {
        $generator  = $this->config->get(EnumConfig::$GENERATOR);
        $command    = $this->config->get(EnumConfig::$COMMAND);

        $this->handleCommand($generator, $command);

        return $this;
    }

    /**
     * addonXml create - $generator and $command
     * if addonXmlCreate not found
     * Then trying to call create(addonXml) $command($generator)
     */
    private function handleCommand($generator, $command)
    {
        $method = $generator . ucfirst($command);

        if (method_exists($this, $method)) {
            return $this->{$method}();
        } elseif(method_exists($this, $command)) {
            return $this->{$command}($generator);
        }

        throw new \BadMethodCallException('There is no such command or generator: ' . $method);
    }

    /**
     * @todo test
     */
    private static function _extractCommentForHelp(string $comment): array
    {
        preg_match('/\/\*{2}\s+\*\shelp:\s+(.*)/usmi', $comment, $matches);

        return $matches;
    }

    /**
     * @todo test
     */
    private static function _purifyCommentForHelp(string $comment): string
    {
        $clearedComment = trim(preg_replace('/(\*\s)|(\*\/)/sm', '', $comment));
        $indentedComment = implode(
            PHP_EOL . str_repeat(' ', 8),
            preg_split('/\r\n|\r|\n/', $clearedComment)
        );

        return $indentedComment;
    }
}
