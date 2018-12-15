<?php

/**
 * @author    Amasty Team
 * @copyright Copyright (c) Amasty (http://www.amasty.com)
 * @package   Amasty_Fpc
 */
class Amasty_Fpccrawler_Model_Queue extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('amfpccrawler/queue');
    }
}
