<?php

class MageWorkshop_ImportExportReview_Model_System_Config_Source_Sync_Frequency
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => '*', 'label' => $this->_getHelper()->__('Every minute')),
            array('value' => '*/10', 'label' => $this->_getHelper()->__('Every 10 minutes')),
            array('value' => '*/30', 'label' => $this->_getHelper()->__('Every half an hour')),
            array('value' => 59, 'label' => $this->_getHelper()->__('Every hour')),
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            '*'    => $this->_getHelper()->__('Every minute'),
            '*/10' => $this->_getHelper()->__('Every 10 minutes'),
            '*/30' => $this->_getHelper()->__('Every half an hour'),
            59     => $this->_getHelper()->__('Every hour')
        );
    }

    /**
     * @return MageWorkshop_ImportExportReview_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('mageworkshop_importexportreview');
    }
}