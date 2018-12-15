<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_StoreCredit
 */
class Amasty_StoreCredit_Model_BalanceHistory extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('amstcred/balanceHistory');
    }


    const ACTION_PAY_ORDER = 1;
    const ACTION_REFUND_ORDER = 2;
    const ACTION_ADMIN = 3;
    const ACTION_USER = 4;
    const ACTION_PURCHASE = 5;
    const ACTION_USER_SEND = 6;

    public function actionNames()
    {
        return array(
            self::ACTION_ADMIN => "Balance update by admin",
            self::ACTION_PAY_ORDER => "Payment for order %s",
            self::ACTION_PURCHASE => "Credit purchase, order %s",
            self::ACTION_REFUND_ORDER => "Refund of order %s",
            self::ACTION_USER => "Received from friend %s",
            self::ACTION_USER_SEND => "Send to friend %s",
        );
    }

    public function getActionName($action = null, $data = "")
    {
        if (is_null($action)) {
            $action = $this->getAction();
        }
        if ($data == "") {
            $data = $this->getFirstOperationData();
        }
        $helper = Mage::helper('amstcred');
        $actions = $this->actionNames();


        return isset($actions[$action]) ? $helper->__($actions[$action], $data) : "";
    }

    public function getAdminhtmlOperationName()
    {
        $_operationData = $this->getFirstOperationData();
        switch ($this->getAction()) {
            case Amasty_StoreCredit_Model_BalanceHistory::ACTION_PAY_ORDER:
            case Amasty_StoreCredit_Model_BalanceHistory::ACTION_REFUND_ORDER:
            case Amasty_StoreCredit_Model_BalanceHistory::ACTION_PURCHASE:
                $order = $this->getOrder();
                if ($order->getId()) {
                    $_url = Mage::getUrl('adminhtml/sales_order/view', array('order_id' => $order->getId()));
                    $_operationData = "<a target='_blank' href=\"{$_url}\">{$_operationData}</a>";
                }

                break;
        }

        return $this->getActionName(null, $_operationData);
    }


    public function getOrder()
    {
        if (!$this->hasOrder()) {
            switch ($this->getAction()) {
                case Amasty_StoreCredit_Model_BalanceHistory::ACTION_PAY_ORDER:
                case Amasty_StoreCredit_Model_BalanceHistory::ACTION_REFUND_ORDER:
                case Amasty_StoreCredit_Model_BalanceHistory::ACTION_PURCHASE:
                    $listData = $this->getOperationData();
                    $data = current($listData);
                    $orderIncremmentId = $data;
                    $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncremmentId);
                    $this->setOrder($order);
                    break;
                default:

                    break;
            }
        }


        return parent::getOrder();
    }


    public function setAction($action, $data = "")
    {
        parent::setAction($action);
        $this->setOperationName($this->getActionName($action, $data));
        return $this;
    }

    public function setOperationData($data)
    {

        if (is_array($data)) {
            $data = serialize($data);
        }
        parent::setOperationData($data);

        return $this;
    }

    public function getOperationData()
    {
        $data = parent::getOperationData();

        if ($data) {
            $data = unserialize($data);
        } else {
            $data = array();
        }

        return $data;
    }

    public function getFirstOperationData()
    {
        $data = $this->getOperationData();

        return current($data);
    }

    public function setBalanceModel(Amasty_StoreCredit_Model_Balance $balance)
    {
        $this
            ->setAction($balance->getAction(), $balance->getActionData())
            ->setBalanceId($balance->getId())
            ->setOperationData(array($balance->getActionData()))
            ->setComment($balance->getComment())
            ->setBalanceAmount($balance->getAmount())
            ->setBalanceDelta($balance->getAmountDelta());

        return $this;
    }

    protected function _beforeSave()
    {
        $this->setUpdatedAt(Mage::getModel('core/date')->gmtDate());
        return parent::_beforeSave();
    }


}
