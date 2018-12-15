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



class Mirasvit_Rma_Block_Adminhtml_Rma_Create_Order_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Returns current configuration object.
     *
     * @return Mirasvit_Rma_Model_Config
     */
    public function getConfig()
    {
        return Mage::getSingleton('rma/config');
    }

    /**
     * Constructor.
     * Constructs grid and sets default sort parameters.
     */
    public function _construct()
    {
        parent::_construct();
        $this->setId('rma_rma_create_order_grid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
    }

    /**
     * Prepares orders collection for stage 1 of RMA creating.
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $allowedStatuses = $this->getConfig()->getPolicyAllowInStatuses();
        /** @var Mage_Sales_Model_Resource_Order_Collection $collection */
        $collection = Mage::helper('rma/mage')->getOrderCollection();
        if (Mage::getVersion() >= '1.4.1.1') {
            $collection->addFieldToFilter('main_table.status', array('in' => $allowedStatuses));
        } else {
            $collection->addFieldToFilter('status', array('in' => $allowedStatuses));
        }

        if ($customerId = $this->getRequest()->getParam('customer_id')) {
            $collection->getSelect()
                ->where('main_table.customer_id = ?', $customerId);
        } elseif ($customerId === '0') {
            $collection->getSelect()
                ->where('main_table.customer_id IS NULL');
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Constructs columns for orders grid. Overrides standard method.
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('real_order_id', array(
            'header' => Mage::helper('sales')->__('Order #'),
            'width' => '80px',
            'type' => 'text',
            'index' => 'increment_id',
            'filter_index' => 'main_table.increment_id',
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header' => Mage::helper('sales')->__('Purchased From (Store)'),
                'index' => 'store_id',
                'type' => 'store',
                'store_view' => true,
                'display_deleted' => true,
            ));
        }

        $this->addColumn('created_at', array(
            'header' => Mage::helper('sales')->__('Purchased On'),
            'index' => 'created_at',
            'type' => 'datetime',
            'width' => '100px',
        ));

        $this->addColumn('billing_name', array(
            'header' => Mage::helper('sales')->__('Bill to Name'),
            'index' => 'billing_name',
        ));

        $this->addColumn('shipping_name', array(
            'header' => Mage::helper('sales')->__('Ship to Name'),
            'index' => 'shipping_name',
        ));

        $this->addColumn('base_grand_total', array(
            'header' => Mage::helper('sales')->__('G.T. (Base)'),
            'index' => 'base_grand_total',
            'type' => 'currency',
            'currency' => 'base_currency_code',
        ));

        $this->addColumn('grand_total', array(
            'header' => Mage::helper('sales')->__('G.T. (Purchased)'),
            'index' => 'grand_total',
            'type' => 'currency',
            'currency' => 'order_currency_code',
        ));

        return parent::_prepareColumns();
    }

    /**
     * Prepares mass actions for a grid.
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('selected_orders');
        $this->getMassactionBlock()->setUseSelectAll(true);

        $this->getMassactionBlock()->addItem('selected_orders', array(
            'label' => Mage::helper('sales')->__('Create'),
            'url' => $this->getUrl('*/*/massSelectOrders'),
        ));

        return $this;
    }

    /**
     * Creates URL, which is used to select order by the row click.
     *
     * @param Varien_Object $row
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/add', array('orders_id' => $row->getId(),
            'ticket_id' => Mage::app()->getRequest()->getParam('ticket_id'), ));
    }
}
