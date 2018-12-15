<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_StoreCredit
 */
class Amasty_StoreCredit_Block_Adminhtml_Customer_Edit_Tab_CustomerBalance_Balance_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('amstcredBalanceGrid');
        $this->setUseAjax(true);
        $this->setDefaultSort('name');
        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('amstcred/balance')
            ->getCollection()
            ->addFieldToFilter('customer_id', $this->getRequest()->getParam('id'));
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('amount', array(
            'header' => Mage::helper('amstcred')->__('Balance'),
            'width' => 50,
            'index' => 'amount',
            'sortable' => false,
            'renderer' => 'amstcred/adminhtml_renderer_currency',
        ));

        $this->addColumn('website_id', array(
            'header' => Mage::helper('amstcred')->__('Website'),
            'index' => 'website_id',
            'sortable' => false,
            'type' => 'options',
            'options' => Mage::getSingleton('adminhtml/system_store')->getWebsiteOptionHash(),
        ));

        return parent::_prepareColumns();
    }
}
