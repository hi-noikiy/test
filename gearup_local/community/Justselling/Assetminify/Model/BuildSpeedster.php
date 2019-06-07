<?php

if (defined('COMPILER_INCLUDE_PATH')) {
    require_once COMPILER_INCLUDE_PATH . DS . 'minify' . DS . 'Minify' . DS .'Loader.php';
    require_once COMPILER_INCLUDE_PATH . DS . 'minify' . DS . 'Minify' . DS .'Build.php';
} else {
    require_once BP . DS . 'lib' . DS . 'minify' . DS . 'Minify' . DS . 'Loader.php';
    require_once BP . DS . 'lib' . DS . 'minify' . DS . 'Minify' . DS .'Build.php';
}
Minify_Loader::register();

class Justselling_Assetminify_Model_BuildSpeedster extends Minify_Build {
    public function __construct($arguments) {
        list($sources, $base) = $arguments;
        $max = 0;
        foreach ((array)$sources as $source) {
            if ($source instanceof Minify_Source) {
                $max = max($max, $source->lastModified);
            } elseif (is_string($source)) {
                if (0 === strpos($source, '//')) {
                    $source = $base . substr($source, 1);
                }
                if (is_file($source)) {
                    $max = max($max, filemtime($source));
                }
            }
        }
        $this->lastModified = $max;
        return $this;
    }

    public function getLastModified() {
        if (0 === stripos(PHP_OS, 'win')) {
            Minify::setDocRoot();
        }
        return $this->lastModified;
    }
}