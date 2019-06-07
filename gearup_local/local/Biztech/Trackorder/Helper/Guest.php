<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Biztech
 * @package     Biztech_Trackorder
 * @copyright   Copyright (c) 2006-2015
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Trackorder module guest helper
 *
 * @author      Gunjan <gunjan@krishtechnolabs.com>
 */
class Biztech_Trackorder_Helper_Guest extends Mage_Sales_Helper_Guest
{
    /**
     * Cookie params
     */
    protected $_cookieName  = 'guest-view';
    protected $_lifeTime    = 600;

    /**
     * Try to load valid order by $_POST or $_COOKIE
     *
     * @return bool|null
     */
    public function loadValidOrder()
    {
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            Mage::app()->getResponse()->setRedirect(Mage::getUrl('sales/order/history'));
            return false;
        }

        $post = Mage::app()->getRequest()->getPost();
        $errors = false;

        /** @var $order Mage_Sales_Model_Order */
        $order = Mage::getModel('sales/order');
        /** @var Mage_Core_Model_Cookie $cookieModel */
        $cookieModel = Mage::getSingleton('core/cookie');
        $errorMessage = 'Entered data is incorrect. Please try again.';

        if (empty($post) && !$cookieModel->get($this->_cookieName)) {
            Mage::app()->getResponse()->setRedirect(Mage::getUrl('sales/guest/form'));
            return false;
        } elseif (!empty($post) && isset($post['oar_order_id']) && isset($post['oar_type']))  {
            $type           = $post['oar_type'];
            $incrementId    = $post['oar_order_id'];
            $lastName       = $post['oar_billing_lastname'];
            $email          = $post['oar_email'];
            $zip            = $post['oar_zip'];

            if (empty($incrementId) || empty($lastName) || empty($type) || (!in_array($type, array('email', 'zip')))
                || ($type == 'email' && empty($email)) || ($type == 'zip' && empty($zip))) {
                $errors = true;
            }

            if (!$errors) {
                $order->loadByIncrementId($incrementId);
            }

            if ($order->getId()) {
                $billingAddress = $order->getBillingAddress();
                if ((strtolower($lastName) != strtolower($billingAddress->getLastname()))
                    || ($type == 'email'
                        && strtolower($email) != strtolower($billingAddress->getEmail()))
                    || ($type == 'zip'
                        && (strtolower($zip) != strtolower($billingAddress->getPostcode())))
                ) {
                    $errors = true;
                }
            } else {
                $errors = true;
            }

            if ($errors === false && !is_null($order->getCustomerId())) {
                $errorMessage = 'Please log in to view your order details.';
                $errors = true;
            }

            if (!$errors) {
                $toCookie = base64_encode($order->getProtectCode() . ':' . $incrementId);
                $cookieModel->set($this->_cookieName, $toCookie, $this->_lifeTime, '/');
            }
        } elseif ($cookieModel->get($this->_cookieName)) {
            $cookie = $cookieModel->get($this->_cookieName);
            $cookieOrder = $this->_loadOrderByCookie( $cookie );
            if( !is_null( $cookieOrder) ){
                $cookieModel->renew($this->_cookieName, $this->_lifeTime, '/');
                $order = $cookieOrder;
            } else {
                $errors = true;
            }
        }

        if (!$errors && $order->getId()) {
            Mage::register('current_order', $order);
            return true;
        }

        Mage::getSingleton('core/session')->addError($this->__($errorMessage));
        Mage::app()->getResponse()->setRedirect(Mage::getUrl('sales/guest/form'));
        return false;
    }

    /**
     * Try to load valid order by $_POST or $_COOKIE
     *
     * @return bool|null
     */
    public function loadValidOrderNative()
    {
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            Mage::app()->getResponse()->setRedirect(Mage::getUrl('sales/order/history'));
            return false;
        }

        $post = Mage::app()->getRequest()->getPost();
        $errors = false;

        /** @var $order Mage_Sales_Model_Order */
        $order = Mage::getModel('sales/order');
        /** @var Mage_Core_Model_Cookie $cookieModel */
        $cookieModel = Mage::getSingleton('core/cookie');
        $errorMessage = 'Entered data is incorrect. Please try again.';

        if (empty($post) && !$cookieModel->get($this->_cookieName)) {
            Mage::app()->getResponse()->setRedirect(Mage::getUrl('sales/guest/form'));
            return false;
        } elseif (!empty($post) && isset($post['oar_order_id']) && isset($post['oar_type']))  {
            $type           = $post['oar_type'];
            $incrementId    = $post['oar_order_id'];
            $lastName       = $post['oar_billing_lastname'];
            $email          = $post['oar_email'];
            $zip            = $post['oar_zip'];

            if (empty($incrementId) || empty($lastName) || empty($type) || (!in_array($type, array('email', 'zip')))
                || ($type == 'email' && empty($email)) || ($type == 'zip' && empty($zip))) {
                $errors = true;
            }

            if (!$errors) {
                $order->loadByIncrementId($incrementId);
            }

            if ($order->getId()) {
                $billingAddress = $order->getBillingAddress();
                if ((strtolower($lastName) != strtolower($billingAddress->getLastname()))
                    || ($type == 'email'
                        && strtolower($email) != strtolower($billingAddress->getEmail()))
                    || ($type == 'zip'
                        && (strtolower($zip) != strtolower($billingAddress->getPostcode())))
                ) {
                    $errors = true;
                }
            } else {
                $errors = true;
            }

            /*if ($errors === false && !is_null($order->getCustomerId())) {
                $errorMessage = 'Please log in to view your order details.';
                $errors = true;
            }*/

            if (!$errors) {
                $toCookie = base64_encode($order->getProtectCode() . ':' . $incrementId);
                $cookieModel->set($this->_cookieName, $toCookie, $this->_lifeTime, '/');
            }
        } elseif ($cookieModel->get($this->_cookieName)) {
            $cookie = $cookieModel->get($this->_cookieName);
            $cookieOrder = $this->_loadOrderByCookie( $cookie );
            if( !is_null( $cookieOrder) ){
                if( is_null( $cookieOrder->getCustomerId() ) ){
                    $cookieModel->renew($this->_cookieName, $this->_lifeTime, '/');
                    $order = $cookieOrder;
                } else {
                    $errorMessage = 'Please log in to view your order details.';
                    $errors = true;
                }
            } else {
                $errors = true;
            }
        }

        if (!$errors && $order->getId()) {
            Mage::register('current_order', $order);
            return true;
        }
        return false;
    }
}