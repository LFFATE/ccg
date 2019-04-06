<?php

namespace terminal;

/**
 * class for terminal/cli/bash output
 */
final class Terminal {
    private $buffer = [];

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
            } else if(strpos($line, '+') === 0) {
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
}
