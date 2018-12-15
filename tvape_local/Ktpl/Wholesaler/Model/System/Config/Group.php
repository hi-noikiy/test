<?php
class Ktpl_Wholesaler_Model_System_Config_Group
{
    public function toOptionArray()
    {
        return Mage::getResourceModel('customer/group_collection')->loadData()->toOptionArray();
    }
}