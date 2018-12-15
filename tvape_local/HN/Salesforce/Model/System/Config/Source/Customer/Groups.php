<?php
/**
 * Created by PhpStorm.
 * User: canhnd
 * Date: 12/06/2017
 * Time: 14:24
 */
class HN_Salesforce_Model_System_Config_Source_Customer_Groups
{
    /**
     * Customer groups options array
     *
     * @var null|array
     */
    protected $_options;
    /**
     * Retrieve customer groups as array
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->_options) {
            $this->_options = Mage::getResourceModel('customer/group_collection')
                ->setRealGroupsFilter()
                ->loadData()->toOptionArray();
        }
        return $this->_options;
    }
}