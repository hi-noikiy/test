<?php

class Gearup_Sds_Model_Resource_Tracking extends Mage_Core_Model_Resource_Db_Abstract {

    public function _construct(){
        $this->_init("gearup_sds/tracking", "sds_tracking_id");
    }


    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        if ($object->getData()) {
            Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
            $product = Mage::getModel('catalog/product')->load($object->getProductId());
            $product->setSdsRed(0);
            $product->save();
        }
    }
}
