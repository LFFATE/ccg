<?php

namespace controllers;

use terminal\Terminal;
use filesystem\Filesystem;
use \Config;

trait HelpTrait
{
    private $config;
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

    public function help($search = '')
    {
        $this->terminal->echo('Usage: php ccg.php [generator] [command] [options]');

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

    public function getHelp()
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
