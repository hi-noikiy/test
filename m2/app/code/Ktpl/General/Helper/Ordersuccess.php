<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ktpl\General\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;

class Ordersuccess extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_registry;
    protected $_orderFactory;
    protected $_scopeConfig;
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,       
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->_registry = $registry;
        $this->_orderFactory = $orderFactory;
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

     public function getGrandTotal($lastOrderId)
    {
        /** @var \Magento\Sales\Model\Order $order */

        $order = $this->_orderFactory->create()->loadByIncrementId($lastOrderId);
        return $order->getGrandTotal();
    }

    public function getArgs()
    {
        return $facebook_pixel = $this->_scopeConfig->getValue('ktplgeneral/general/facebook_pixel', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
   
}
