<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_StoreCredit
 */
class Amasty_StoreCredit_Block_Sales_Order_StoreCredit extends Mage_Core_Block_Template
{
    public function initTotals()
    {
        $total = new Varien_Object(array(
            'code' => $this->getNameInLayout(),
            'block_name' => $this->getNameInLayout(),
            'area' => $this->getArea()
        ));
        $this->getParentBlock()->addTotalBefore($total, array('customerbalance', 'grand_total'));
        return $this;
    }

    public function getLabelProperties()
    {
        return $this->getParentBlock()->getLabelProperties();
    }

    public function getValueProperties()
    {
        return $this->getParentBlock()->getValueProperties();
    }

    public function getOrder()
    {
        return $this->getParentBlock()->getOrder();
    }

    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

}
