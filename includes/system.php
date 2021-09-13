<?php

class System {

    public $outputDirectory;

    public function __construct ($file, $location) {

        date_default_timezone_set ('America/Los_Angeles');
    
        if (!$settings = parse_ini_file($file, TRUE)) throw new exception('Unable to open ' . $file . '.');
        $this->outputDirectory = $location . '/' . $settings['system']['output'];
    }

}