<?php

/**
 * Hatimeria Orders Manager orders controller
 *
 * @category   Hatimeria
 * @package    Hatimeria_OrderManager
 */

class Hatimeria_OrderManager_Adminhtml_OrderController extends Mage_Adminhtml_Controller_Action
{

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/hordermanager');
    }
    /**
     * Init actions
     *
     * @return Hatimeria_OrderManager_Adminhtml_OrderController
     */
    protected function _initAction()
    {
        $helper = Mage::helper('hordermanager');

        // load layout, set active menu and breadcrumbs
        $this->loadLayout()
            ->_setActiveMenu('hordermanager/order')
            ->_addBreadcrumb($helper->__('Order Control'), $helper->__('Order Control'))
            ->_addBreadcrumb($helper->__('Order'), $helper->__('Order'));

        return $this;
    }

    /**
     * Index action
     */
    public function indexAction()
    {
        $this->_forward('showall');
    }

    /**
     * Add Order Action
     */
    public function addAction()
    {
        $this->_title($this->__('Order Manager'))->_title($this->__('Order'));

        // 1.1 Create order model
        $orderModel = Mage::getModel('hordermanager/order');
        Mage::register('current_order', $orderModel);

        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Show All Orders Action
     */
    public function showallAction()
    {
        $this->_title($this->__('Orders in Periods'))->_title($this->__('Order'));
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Save New Order action
     */
    public function saveAction()
    {
        $helper = Mage::helper('hordermanager');

        if ($post = $this->getRequest()->getPost()) {
            // deleted old order first
            $periodToDelete = Mage::getModel('hordermanager/order')->load($post['select_order'], 'order_id');
            $periodToDelete->delete();

            $periodHasOrder = Mage::getModel('hordermanager/order');
            $periodHasOrder->setOrderId($post['select_order']);
            $periodHasOrder->setPeriodId($post['select_period']);
            $periodHasOrder->save();

            $order = Mage::getModel('sales/order')->load($periodHasOrder->getOrderId());
            $orderId = $order->getId();
            $items = $order->getAllItems();
            foreach ($items as $item) {
                $itemId = $item->getItemId();
                Mage::getModel('hordermanager/item')
                    ->setItemId($itemId)
                    ->setOrderId($orderId)
                    ->save();
            }
            Mage::getSingleton('adminhtml/session')->addSuccess($helper->__('Order was successfully saved.'));
            $this->_redirect('*/adminhtml_period/index');
            return;
        } else {
            Mage::getSingleton('adminhtml/session')->addError($helper->__('Order was not saved.'));
            $this->_redirect('*/*/showall');
            return;
        }
    }
}