<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_StoreCredit
 */
class Amasty_StoreCredit_Model_Source_PriceType extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    public function getAllOptions()
    {
        $_helper = Mage::helper('amstcred');
        return array(
            array('value' => Amasty_StoreCredit_Model_StoreCredit::PRICE_TYPE_EQUAL, 'label' => $_helper->__('the whole credit value')),
            array('value' => Amasty_StoreCredit_Model_StoreCredit::PRICE_TYPE_PERCENT, 'label' => $_helper->__('percent of credit value')),
        );
    }
}
