<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_StoreCredit
 */
class Amasty_StoreCredit_Block_Adminhtml_History extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_history';
        $this->_blockGroup = 'amstcred';
        $this->_headerText = Mage::helper('amstcred')->__('Credit Transactions History');
        parent::__construct();
    }

    public function getButtonsHtml($area = null)
    {
        $this->removeButton('add');
        parent::getButtonsHtml($area);
    }
}
