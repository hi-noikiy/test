<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_StoreCredit
 */
class Amasty_StoreCredit_Block_Adminhtml_Sales_Items_Column_Name_StoreCredit
    extends Mage_Adminhtml_Block_Sales_Items_Column_Name
{

    public function getOrderOptions()
    {
        return array_merge($this->_getStoreCreditOptions(), parent::getOrderOptions());
    }

    protected function _prepareCustomOption($code)
    {
        if ($option = $this->getItem()->getProductOptionByCode($code)) {
            return $this->escapeHtml($option);
        }
        return false;
    }

    /**
     * Get gift card option list
     *
     * @return array
     */
    protected function _getStoreCreditOptions()
    {
        $result = array();


        $value = $this->_prepareCustomOption('amstcred_amount');

        if ($value) {
            $result[] = array(
                'label' => $this->__('Value'),
                'value' => Mage::helper('core')->currency($value, true, false)
            );
        }


        return $result;
    }


}
