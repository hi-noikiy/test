<?php

class Hatimeria_MassAction_Adminhtml_MassController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Do mass action changing the status for selected orders
     */
    public function massAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        $action = $this->getRequest()->getParam('action');
        $countDoneOrder = 0;
        $countNonDoneOrder = 0;
        foreach ($orderIds as $orderId) {
            $order = Mage::getModel('sales/order')->load($orderId);
            $order->setStatus($action)
                ->save();
            if ($order->getStatus() == $action) {
                $countDoneOrder++;
            } else {
                $countNonDoneOrder++;
            }
        }

        if (strpos($action, '_')) {
            $words = explode('_', $action);
            $action = implode(' ', $words);
        }

        if ($countNonDoneOrder) {
            if ($countDoneOrder) {
                $this->_getSession()->addError($this->__('%s order(s) status: %s', $countNonDoneOrder, $action));
            } else {
                $this->_getSession()->addError($this->__('The order(s) status cannot be: %sd', $action));
            }
        }
        if ($countDoneOrder) {
            $this->_getSession()->addSuccess($this->__('%s order(s) status: %s', $countDoneOrder, $action));
        }

        $this->_redirectReferer();
        return;
    }
} 