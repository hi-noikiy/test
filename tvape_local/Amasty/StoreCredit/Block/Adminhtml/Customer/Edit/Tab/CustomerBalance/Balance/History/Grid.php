<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_StoreCredit
 */
class Amasty_StoreCredit_Block_Adminhtml_Customer_Edit_Tab_CustomerBalance_Balance_History_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('historyGrid');
        $this->setUseAjax(true);
        $this->setDefaultSort('updated_at');
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/gridHistory', array('_current' => true));
    }


    protected function _prepareCollection()
    {
        $collection = Mage::getModel('amstcred/balanceHistory')
            ->getCollection()
            ->addFieldToFilter('customer_id', $this->getRequest()->getParam('id'));
        $this->setCollection($collection);

        $this->setDefaultSort('amstcred_updated_at');
        $this->setDefaultDir('desc');

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('amstcred_history_id', array(
            'header' => $this->__('ID'),
            'index' => 'history_id',
            'type' => 'number',
            //'filter'    => false,
            //'width'     => 200,
        ));

        $this->addColumn('amstcred_operation_name', array(
            'header' => $this->__('Operation Name'),
            'index' => 'operation_name',
            'getter' => 'getAdminhtmlOperationName',
            'type' => 'text',
        ));

        $this->addColumn('amstcred_comment', array(
            'header' => $this->__('Comment'),
            'index' => 'comment',
            'type' => 'text',
        ));


        $this->addColumn('amstcred_balance_delta', array(
            'header' => $this->__('Operation Value'),
            'width' => 50,
            'index' => 'balance_delta',
            //'filter_index'     => 'balance_delta',
            'type' => 'price',
            'currency_code' => 'USD',
            'renderer' => 'amstcred/adminhtml_renderer_currency',
        ));

        $this->addColumn('amstcred_remaining_credit', array(
            'header' => $this->__('Remaining Credit'),
            'width' => 50,
            'index' => 'balance_amount',
            'type' => 'price',
            'currency_code' => 'USD',
            'renderer' => 'amstcred/adminhtml_renderer_currency',
        ));


        $this->addColumn('amstcred_updated_at', array(
            'header' => $this->__('Operation Date'),
            'index' => 'updated_at',
            'type' => 'datetime',
            'width' => 200,
        ));

        $this->addColumn('amstcred_website_id', array(
            'header' => $this->__('Website'),
            'index' => 'website_id',
            'type' => 'options',
            'options' => Mage::getSingleton('adminhtml/system_store')->getWebsiteOptionHash(),
            'sortable' => false,
            'width' => 200,
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($item)
    {
        return '';
    }


}
