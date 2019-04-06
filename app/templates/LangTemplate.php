<?php

namespace template;

use template\ITemplate;

class LangTemplate implements ITemplate {
    private $templatePath = 'resources/langvars.xml';

    /**
     * @param array $data
     */
    public function parseTemplateWithData($data) {
        $template = @file_get_contents($this->templatePat);

        if (!$template) {
            throw new UnexpectedValueException;
        }
    }
}
