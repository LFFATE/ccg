<?php

namespace readers;
use readers\IReader;

class XmlReader implements IReader {
    private $fileName = null;
    private $fileContentXml = null;

    function __construct(string $fileName) {
        $this->setFileName($fileName);

        $fileContent    = @file_get_contents($this->getFileName());
        $fileContentXml = new SimpleXMLElement($fileContent);

        $this->setFileContent($fileContentXml);
    }

    private function setFileName(string $fileName): XmlReader {
        $this->fileName = $fileName;

        return $this;
    }

    private function setFileContent(SimpleXMLElement $fileContentXml): XmlReader {
        $this->fileContentXml = $fileContentXml ? $fileContentXml : '';

        return $this;
    }

    public function getFileContent(): SimpleXMLElement {

        return $this->fileContentXml ? $this->fileContentXml : new SimpleXMLElement();
    }

    public function getFileName(): string {
        return $this->fileName;
    }

    public function findNode() {

    }
}
