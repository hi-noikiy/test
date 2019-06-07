<?php

class Justselling_Assetminify_Model_Check extends Mage_Core_Model_Config_Data
{
    protected function _beforeSave() {
        if ($this->getValue()) {
            $selftester = Mage::getModel('assetminify/selftester');
            $selftester->main();
            if ($selftester->errorOccurred) {
                $msg = Mage::helper('core')->__(
                    'Selftest failed with: %s',
                    implode('<br/>', $selftester->messages)
                );
                Mage::throwException($msg);
            }
        }
        return parent::_beforeSave();
    }
}