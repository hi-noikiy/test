<?php
class BusinessKing_PaymentCharge_Model_System_Config_Group
{
    public function toOptionArray()
    {
        return Mage::getResourceModel('customer/group_collection')->loadData()->toOptionArray();
    }
}