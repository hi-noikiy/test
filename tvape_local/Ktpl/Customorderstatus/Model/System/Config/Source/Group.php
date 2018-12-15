<?php
class Ktpl_Customorderstatus_Model_System_Config_Source_Group
{
    public function toOptionArray()
    {
        return Mage::getResourceModel('customer/group_collection')->loadData()->toOptionArray();
    }
}