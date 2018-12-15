<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_StoreCredit
 */
class Amasty_StoreCredit_Block_Customer_Send extends Mage_Core_Block_Template
{
    /**
     * @return Varien_Object
     */
    public function getFormData()
    {
        $formData = new Varien_Object();
        $data = Mage::getSingleton('amstcred/session')->getSendFormData(true);
        if ($data) {
            $formData->setData($data);
        }
        return $formData;
    }

}
