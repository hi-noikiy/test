<?php

/**
 * FENOMICS extension for Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade
 * the Fenomics_OrderEmail module to newer versions in the future.
 * If you wish to customize the Fenomics GTM module for your needs
 * please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Fenomics
 * @package    Fenomics_OrderEmail
 * @copyright  Copyright (C) 2014 FENOMICS GmbH (http://www.fenomics.de/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 *
 * @category Fenomics
 * @package Fenomics_OrderEmail
 * @subpackage Helper
 * @author Wolfgang Embach <w.embach@fenomics.de>
 */
require_once 'Mage/Adminhtml/controllers/Sales/OrderController.php';

class Fenomics_OrderEmail_Adminhtml_Sales_OrderController extends Mage_Adminhtml_Sales_OrderController
{
    //
    function IscustomerEmailExists($email, $storeId = null)
    {
        $customer = Mage::getModel('customer/customer');
        
        if ($storeId) {
             $customer->setWebsiteId($storeId);
        }
        
        //
        $customer->loadByEmail($email);
        if ($customer->getId()) {
            return $customer->getId();
        }
        return false;
    }
    
    //
    public function fenemailAction()
    {
        
        //
        $orderId = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($orderId);
        $storeId = $order->getStoreId();
        
        //
        $value = $_REQUEST['value'];
        
        $order->setData('customer_email', $value)->save();
        $antwort = str_pad($value, 50) . str_pad("Email-Adresse wurde gespeichert.", 400);
        
        // Kunden mit updaten, wenn die Email noch nicht vorhanden ist!
        $customerId = $order->getCustomerId();
        $customer = Mage::getModel('customer/customer')->load($customerId);
        if ($customer->getId()) {
            
            //
            $cust_exist = $this->IscustomerEmailExists($value, $storeId);
            
            if (!$cust_exist) {
                $customer->setEmail($value);
                $customer->save();
            }
        }
        
        $this->getResponse()->setBody($antwort);
    }

    //
    public function fenstatusAction()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($orderId);
        
        $value = $_REQUEST['value'];
        
        $order->setData('status', $value);
        
        $_cuser = Mage::getSingleton('admin/session')->getUser()->getUsername();
        $comment = 'Set status on order status function by user "' . $_cuser . '" !';
        $history = $order->addStatusHistoryComment($comment, false); // no sense to set $status again
        $history->setIsCustomerNotified(0);
        $order->save();
        
        $antwort = str_pad($order->getStatus(), 50) . str_pad("Status has been saved.", 40);
        $this->getResponse()->setBody($antwort);
    }
}
