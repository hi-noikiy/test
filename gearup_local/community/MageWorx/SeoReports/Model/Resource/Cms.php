<?php
/**
 * MageWorx
 * MageWorx SeoReports Extension
 * 
 * @category   MageWorx
 * @package    MageWorx_SeoReports
 * @copyright  Copyright (c) 2017 MageWorx (http://www.mageworx.com/)
 */

class MageWorx_SeoReports_Model_Resource_Cms extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('mageworx_seoreports/cms', 'entity_id');
    }

}