<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_StoreCredit
 */
class Amasty_StoreCredit_Adminhtml_Amstcred_CustomerController extends Mage_Adminhtml_Controller_Action
{


    public function formAction()
    {
        $this->_initCustomer();
        $this->loadLayout();
        $this->renderLayout();
    }

    public function gridHistoryAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('amstcred/adminhtml_customer_edit_tab_customerBalance_balance_history_grid')->toHtml()
        );
    }

    public function massUpdateCreditAction()
    {
        try {
            $amountDelta = $this->getRequest()->getParam('amstcred_amount_delta', 0);
            $storeId = $this->getRequest()->getParam('amstcred_store_id', null);
            $customerIds = $this->getRequest()->getParam('customer', array());

            if (!$amountDelta) {
                Mage::throwException(Mage::helper('amstcred')->__('Specify amount!'));
            }
            if (Mage::app()->isSingleStoreMode()) {
                $storeId = Mage::app()->getStore(true)->getId();
            }

            if (!$storeId) {
                Mage::throwException(Mage::helper('amstcred')->__('Specify store!'));
            }

            if (count($customerIds) <= 0) {
                Mage::throwException(Mage::helper('amstcred')->__('Please, select customers!'));
            }

            $websiteId = Mage::app()->getStore($storeId)->getWebsiteId();
            foreach ($customerIds as $customerId) {
                $balance = Mage::getModel('amstcred/balance')
                    ->setCustomerId($customerId)
                    ->setWebsiteId($websiteId)
                    ->setStoreId($storeId)
                    ->setAmountDelta($amountDelta)
                    //->setComment($data['comment'])
                    ->setAction(Amasty_StoreCredit_Model_BalanceHistory::ACTION_ADMIN);
                $balance->save();
            }
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('amstcred')->__("Сustomer's balances have been updated."));
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addException(
                $e,
                Mage::helper('amstcred')->__('An error occurred while updating customer balances.')
            );
        }

        $this->_redirect('adminhtml/customer');
    }


    protected function _initCustomer($idFieldName = 'id')
    {
        $customer = Mage::getModel('customer/customer')->load((int)$this->getRequest()->getParam($idFieldName));
        if (!$customer->getId()) {
            Mage::throwException(Mage::helper('amstcred')->__('Failed to initialize customer'));
        }
        Mage::register('current_customer', $customer);
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('customer/manage/amstcred');
    }
}
