<?php

namespace readers;

interface IReader {
    private $fileName;
    private $fileContentXml;

    function __construct(string $fileName) {
    }

    private function setFileName(string $fileName) {
    }

    private function setFileContent($fileContent) {
    }

    public function getFileContent() {
    }

    public function getFileName(): string {
    }
}
