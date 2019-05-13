<?php

namespace terminal;

/**
 * class for terminal/cli/bash output
 */
final class Terminal {
    private $argv;
    private $argc;
    private $arguments = [];
    protected $input_stream;
    protected $output_stream;
    const EOL = "\n";

    public function __construct($input_stream = STDIN, $output_stream = STDOUT)
    {
        global $argv;
        global $argc;

        $this->argv = $argv;
        $this->argc = $argc;

        $this->input_stream  = $input_stream;
        $this->output_stream = $output_stream;

        $this->parseArguments();
    }

    public function echo(string $string)
    {
        echo $string . self::EOL;
    }

    public function diff(string $string)
    {
        $separator = "\r\n";
        $line = strtok($string, $separator);

        while ($line !== false) {
            if (strpos($line, '-') === 0) {
                $this->error($line);
            } else if (strpos($line, '+') === 0) {
                $this->success($line);
            } else {
                $this->echo($line);
            }

            $line = strtok($separator);
        }
    }

    public function info(string $string)
    {
        $this->echo("\e[46m" . $string . "\e[0m");    
    }

    public function success(string $string)
    {
        $this->echo("\e[32m" . $string . "\e[0m");
    }

    public function warning(string $string)
    {
        $this->echo("\e[43m" . $string . "\e[0m");
    }

    public function error(string $string)
    {
        $this->echo("\e[1;31m" . $string . "\e[0m");
    }

    /**
     * Requests user for confirm his action
     * 
     * @param callable $success_action
     * @param callable $cancel_action
     * @param string $question
     * @param string $confirmation_word
     */
    public function confirm(
        callable    $success_action,
        callable    $cancel_action     = null,
        string      $question          = "Are you sure?\n Type 'Y' to continue: ",
        string      $confirmation_word = 'Y'
    ): void
    {
        $this->warning($question);
        fseek($this->input_stream, 0); 
        $line = fgets($this->input_stream);

        if (trim($line) !== $confirmation_word) {
            $this->warning('ABORTING');
            $cancel_action and call_user_func($cancel_action);
        } else {
            call_user_func($success_action);
        }
    }

    /**
     * Requests config item from user 
     */
    public function requestSetting(
        string $name,
        callable $result_action,
        $default = ''
    ): void
    {
        $this->echo("Please, set up $name");
        fseek($this->input_stream, 0);
        $line   = fgets($this->input_stream);
        $result = trim($line);

        call_user_func($result_action, $result ?: $default);
    }

    /**
     * Set arguments
     * 
     * @param array  $arguments
     * 
     * @return void
     */
    public function setArguments(array $arguments): void
    {
        $this->arguments = $arguments;
    }

    /**
     * Get arguments
     * 
     * @return array
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * Parse arguments from terminal to array
     * 
     * @return void
     */
    private function parseArguments(): void
    {
        $arguments = arguments(implode(' ', $this->argv));
        $controller = @$this->argv[1] ?: '';
        list($generator, $command) = array_pad(explode('/', $controller), 2, '');
        $generator = (1 === preg_match('/^[\w]+/ui', $generator)) ? $generator : '';

        $arguments['generator'] = to_studly_caps($generator);
        $arguments['command']   = $command;
        $this->setArguments($arguments);
    }

    /**
     * Cheks is terminal at autocomplete mode
     * 
     * @return bool
     */
    public function isAutocomplete()
    {
        return (
            isset($this->arguments['autocomplete'])
            && $this->arguments['autocomplete'] === 'y'
        );
    }

    /**
     * Echo autocompletes to terminal
     * 
     * @param array
     * 
     * @return Terminal
     */
    public function autocomplete(array $variants)
    {
        echo implode(' ', $variants) . ' ';

        return $this;
    }

    /**
     * Closes program execution
     * @codeCoverageIgnore
     */
    public function exit()
    {
        exit;
    }
}
