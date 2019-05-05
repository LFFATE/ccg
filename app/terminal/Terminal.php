<?php

namespace terminal;

/**
 * class for terminal/cli/bash output
 */
final class Terminal {
    private $buffer = [];
    private $argv;
    private $argc;
    private $arguments = [];

    public function __construct()
    {
        global $argv;
        global $argc;

        $this->argv = $argv;
        $this->argc = $argc;

        $this->parseArguments();
    }

    public function addBuffer(string $buffer)
    {
        $this->buffer[] = $buffer;
    }

    public function getBuffer(): string
    {
        return implode(PHP_EOL, $this->buffer);
    }

    public function echo(string $string, bool $to_buffer = false, bool $return = false)
    {
        if ($return) {
            return $string;
        }

        if ($to_buffer) {
            $this->addBuffer($string);
        } else {
            echo $string . PHP_EOL;
        }
    }

    public function diff(string $string, bool $to_buffer = false, bool $return = false)
    {
        $result_string = '';
        $separator = "\r\n";
        $line = strtok($string, $separator);

        while ($line !== false) {
            if (strpos($line, '-') === 0) {
                $result_string .= $this->error($line, false, true);
            } else if (strpos($line, '+') === 0) {
                $result_string .= $this->success($line, false, true);
            } else {
                $result_string .= $line;
            }

            $result_string .= PHP_EOL;

            $line = strtok($separator);
        }

        if ($to_buffer) {
            $this->setBuffer($result_string);
        } else {
            echo $result_string;
        }
    }

    public function info(string $string, bool $to_buffer = false, bool $return = false)
    {
        $output = "\e[46m" . $string . "\e[0m";

        if ($return) {
            return $output;
        } else {
            $this->echo($output, $to_buffer);
        }
    }

    public function success(string $string, bool $to_buffer = false, bool $return = false)
    {
        $output = "\e[32m" . $string . "\e[0m";

        if ($return) {
            return $output;
        } else {
            $this->echo($output, $to_buffer);
        }
    }

    public function warning(string $string, bool $to_buffer = false, bool $return = false)
    {
        $output = "\e[43m" . $string . "\e[0m";

        if ($return) {
            return $output;
        } else {
            $this->echo($output, $to_buffer);
        }
    }

    public function error(string $string, bool $to_buffer = false, bool $return = false)
    {
        $output = "\e[1;31m" . $string . "\e[0m";

        if ($return) {
            return $output;
        } else {
            $this->echo($output, $to_buffer);
        }
    }

    public function forceOutput()
    {
        $this->echo(
            $this->getBuffer()
        );
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
        callable $success_action,
        callable $cancel_action = null,
        string $question = "Are you sure?\n Type 'Y' to continue: ",
        string $confirmation_word = 'Y'
    ): void
    {
        $this->warning($question);
        $handle = fopen('php://stdin', 'r');
        $line   = fgets($handle);

        if(trim($line) != $confirmation_word) {
            $this->warning('ABORTING');
            call_user_func($cancel_action);
            exit;
        }

        fclose($handle);
        call_user_func($success_action);
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
        $handle = fopen('php://stdin', 'r');
        $line   = fgets($handle);
        $result = trim($line);

        call_user_func($result_action, $result ?: $default);
        fclose($handle);
    }

    public function expectParams(
        array $opts,
        callable $result_action
    )
    {
//         $options = getopt('', ['addon.id:']);
//         print_r($options);
// die();
//         $options = getopt(implode('', array_keys($opts)), $opts);
// print_r($options);
//         array_walk($options, function($option) use ($result_action) {
//             echo 2;
//             call_user_func($result_action, $option);
//         });
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
        // echo '->' . $generator; var_dump(preg_match('/[\w?!\-]+/ui', $generator));die();

        $arguments['generator'] = to_camel_case($generator);
        $arguments['command'] = $command;
// print_r($arguments);
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
}
