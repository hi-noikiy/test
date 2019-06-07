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
 * @package   mirasvit/extension_rewards
 * @version   1.1.35
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */


class Mirasvit_Rewards_ReferralController extends Mage_Core_Controller_Front_Action
{
    /**
     * @return Mage_Customer_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }

    /**
     * @return void
     */
    public function preDispatch()
    {
        parent::preDispatch();
        $action = $this->getRequest()->getActionName();
        if ($action != 'invite' && $action != 'referralVisit') {
            if (!Mage::getSingleton('customer/session')->authenticate($this)) {
                $this->setFlag('', 'no-dispatch', true);
            }
        }
    }

    /**
     * @return void
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->renderLayout();
    }

    /**
     * @return Mirasvit_Rewards_Model_Referral
     */
    protected function _initReferral()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            $referral = Mage::getModel('rewards/referral')->load($id);
            if ($referral->getId() > 0) {
                Mage::register('current_referral', $referral);
                return $referral;
            }
        }
    }

    /************************/

    /**
     * @return void
     */
    public function postAction()
    {
        $customer = Mage::getSingleton('rewards/customer')->getCurrentCustomer();
        $names = $this->getRequest()->getParam('name');
        $emails = $this->getRequest()->getParam('email');
        $isAjax = Mage::app()->getRequest()->isAjax();
        $invitations = array();
        if (is_array($emails)) {
            foreach ($emails as $key => $email) {
                if (empty($email) || empty($names[$key])) {
                    continue;
                }
                $invitations[$email] = $names[$key];
            }
        } else {
            $emails = explode(',', $emails);
            foreach ($emails as $email) {
                $invitations[trim($email)] = trim($email);
            }
        }
        $message = $this->getRequest()->getParam('message');
        if (!$message || trim($message) == '') {
            $message = Mage::getSingleton('rewardssocial/config')->getReferDefaultMessage() . '<br>' .
                $this->getRequest()->getParam('share-url');
        }
        unset($invitations['']);
        $result = array();
        if ($invitations) {
            $rejectedEmails = Mage::helper('rewards/referral')->frontendPost($customer, $invitations, $message);
        } else {
            $message = $this->__("Please enter correct email");
            if ($isAjax) {
                $result['message'][] = $message;
            } else {
                $this->_getSession()->addNotice($message);
            }
        }
        if (count($rejectedEmails)) {
            foreach ($rejectedEmails as $email) {
                $message = $this->__("Customer with email %s has been already invited to our store", $email);
                if ($isAjax) {
                    $result['message'][] = $message;
                } else {
                    $this->_getSession()->addNotice($message);
                }
            }
        }
        if (count($rejectedEmails) < count($invitations)) {
            $message = $this->__('Thank you for sharing! Your invitations are on their way to your friends. More friends = more Gear points!');
            if ($isAjax) {
                $result['message'][] = $message;
            } else {
                $this->_getSession()->addSuccess($message);
            }
        }
        if ($isAjax) {
            $this->getResponse()->setHeader('Content-type', 'application/json', true);
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        } else {
            $this->_redirect('*/*/');
        }
    }

    /**
     * @return void
     */
    public function inviteAction()
    {
        $id = (int)$this->getRequest()->getParam('id');
        $referral = Mage::getModel('rewards/referral')->load($id);
        if ($referral->getStatus() != Mirasvit_Rewards_Model_Config::REFERRAL_STATUS_SENT) {
            $this->_redirect('/');
            return;
        }
        $referral->setStatus(Mirasvit_Rewards_Model_Config::REFERRAL_STATUS_VISITED)
                 ->save();
        Mage::getSingleton('core/session')->setReferral($referral->getId());
        $this->_redirect('/');
    }

    /**
     * @return void
     */
    public function referralVisitAction()
    {
        $store = Mage::app()->getStore();
        $customerId = (int)$this->getRequest()->getParam('customer_id');
        if ($productId = (int)$this->getRequest()->getParam('product_id')) {
            $product = Mage::getModel('catalog/product')->load($productId);

            // Test for SEO-friendly URL generation
            $categoryId = (int)$this->getRequest()->getParam('category_id');
            $category = Mage::getResourceModel('core/url_rewrite')
                ->getRequestPathByIdPath('category/' . $categoryId, $store);
            $category = str_replace('.html', '', $category) . '/';

            if ($product->getTypeId() == 'simple') {
                $redirectLink = $category . Mage::getResourceModel('core/url_rewrite')
                        ->getRequestPathByIdPath('product/' . $productId, $store);
            } else {
                $redirectLink = Mage::getResourceModel('core/url_rewrite')
                        ->getRequestPathByIdPath('product/' . $productId . '/' . $categoryId, $store);
            }
        } elseif ($productId = (int)$this->getRequest()->getParam('category_id')) {
            $categoryId = (int)$this->getRequest()->getParam('category_id');
            $redirectLink = Mage::getResourceModel('core/url_rewrite')
                ->getRequestPathByIdPath('category/' . $categoryId, $store);
        } else {
            $redirectLink = '';
        }

        $redirectLink = Mage::getBaseUrl() . $redirectLink;

        if (Mage::getSingleton('core/session')->getReferral()) {
            $this->_redirectUrl($redirectLink);
            return;
        }
        $referral = Mage::getModel('rewards/referral')
            ->setCustomerId($customerId)
            ->setStatus(Mirasvit_Rewards_Model_Config::REFERRAL_STATUS_VISITED)
            ->setStoreId(Mage::app()->getStore()->getId())
            ->save();
        Mage::getSingleton('core/session')->setReferral($referral->getId());

        $this->_redirectUrl($redirectLink);
    }
}