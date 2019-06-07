<?php

class MageWorkshop_ImportExportReview_Model_Resource_Profile extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('mageworkshop_importexportreview/profile', 'id');
    }
}
