<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Optimization
 */


class Amasty_Optimization_Model_Design_Package extends Amasty_Optimization_Model_Design_Package_Amasty_Pure
{
    /**
     * {@inheritdoc}
     */
    public function beforeMergeCss($file, $contents)
    {
        $this->_setCallbackFileDir($file);

        $cssImport = '/@import\\s+([\'"])(.*?)[\'"]/';
        $contents = preg_replace_callback($cssImport, array($this, '_cssMergerImportCallback'), $contents);

        $cssUrl = '/url\\(\\s*(?![^)]*(?:data:))([^\\)\\s]+)\\s*\\)?/';
        $contents = preg_replace_callback($cssUrl, array($this, '_cssMergerUrlCallback'), $contents);

        return $contents;
    }
}
