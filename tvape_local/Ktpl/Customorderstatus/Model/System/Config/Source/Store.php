<?php
class Ktpl_Customorderstatus_Model_System_Config_Source_Store
{
    public function toOptionArray()
    {
        return Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true);
    }
}