<?php

class Template {

    private $dir= __DIR__ . '/templates/';
    private $template;

    public function __construct ($file) {
        $temp = $this->dir . $file . ".md";
        if (!file_exists($temp)) {
            exit("Template (" . $temp . ") doesn't exist.");
        } else {            
            $this->template=$temp;
        }
    }

    public function parse(array $tokens, array $values) {
        $content=file_get_contents($this->template);
        return str_replace($tokens, $values, $content);
    }
}