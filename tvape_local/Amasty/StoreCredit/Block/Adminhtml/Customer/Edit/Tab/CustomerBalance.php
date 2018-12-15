<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_StoreCredit
 */
class Amasty_StoreCredit_Block_Adminhtml_Customer_Edit_Tab_CustomerBalance extends Mage_Adminhtml_Block_Widget
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('am_customer_balance');
        $this->setTitle(Mage::helper('amstcred')->__('Store Credit'));
    }

    /**
     * Return Tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->getTitle();
    }

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTitle();
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return Mage::getSingleton('admin/session')->isAllowed('customer/manage/amstcred');
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        if (!$this->getRequest()->getParam('id')) {
            return true;
        }
        return false;
    }


    public function getTabClass()
    {
        return 'ajax';
    }

    public function getSkipGenerateContent()
    {
        return true;
    }

    public function getAfter()
    {
        return 'wishlist';
    }

    public function getTabUrl()
    {
        return $this->getUrl('*/amstcred_customer/form', array('_current' => true));
    }
}
