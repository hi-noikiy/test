<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/extension_rma
 * @version   2.4.7
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



class Mirasvit_Rma_Block_Adminhtml_Sales_Order_View_Tabs_Rma extends Mage_Adminhtml_Block_Widget
implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /** @var Mirasvit_Rma_Block_Adminhtml_Rma_Grid $grid */
    protected $grid;
    protected $gridHtml;
    protected function _prepareLayout()
    {
        $id = $this->getOrderId();

        /** @var Mirasvit_Rma_Block_Adminhtml_Rma_Grid $grid */
        $grid = $this->getLayout()->createBlock('rma/adminhtml_rma_grid');
        $grid->setId('rma_grid_internal');
        $grid->setActiveTab('RMA');
        $grid->addCustomFilter('order_id', $id);
        $grid->setFilterVisibility(false);
        $grid->setExportVisibility(false);
        $grid->setPagerVisibility(0);

        $grid->setTabMode(true);

        $this->grid = $grid;
        $this->gridHtml = $this->grid->toHtml();

        return parent::_prepareLayout();
    }

    public function getTabLabel()
    {
        return Mage::helper('rma')->__('RMA (%s)', $this->grid->getFormattedNumberOfRMA());
    }

    public function getTabTitle()
    {
        return Mage::helper('rma')->__('RMA');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }

    /**
     * @return int
     */
    protected function getOrderId()
    {
        return $this->getRequest()->getParam('order_id');
    }

    /**
     * @param int $orderId
     *
     * @return int
     */
    protected function getOrderCustomerId($orderId)
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order')->load($orderId);

        return $order->getCustomerId();
    }

    protected function _toHtml()
    {
        $id = $this->getOrderId();
        $customerId = $this->getOrderCustomerId($id);
        $rmaNewUrl = $this->getUrl('adminhtml/rma_rma/add', array('orders_id' => $id, 'customer_id' => $customerId));
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setClass('add')
            ->setType('button')
            ->setOnClick('window.location.href=\''.$rmaNewUrl.'\'')
            ->setLabel($this->__('Create RMA for this order'));

        if (Mage::helper('rma')->isReturnAllowed($id)) {
            $meetMessage = $this->__('Order meets RMA policy');
        } else {
            $meetMessage = $this->__('Order doesn\'t meet RMA policy');
        }

        return '<br>
        <div>'.$button->toHtml().'<div style="float:right;color:#eb5e00"><i>'.$meetMessage.'</i></div>
        <br><br>'.$this->gridHtml.'</div>';
    }
}
