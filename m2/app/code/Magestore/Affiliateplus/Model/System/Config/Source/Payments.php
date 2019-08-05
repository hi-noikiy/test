<?php

/**
 * Magestore.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Affiliateplus
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */
namespace Magestore\Affiliateplus\Model\System\Config\Source;

class Payments
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_requestInterface;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magestore\Affiliateplus\Helper\HelperAbstract
     */
    protected $_helperAbstract;

    /**
     * Payments constructor.
     * @param \Magestore\Affiliateplus\Helper\Payment $helperAbstract
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\RequestInterface $requestInterface
     */
    public function __construct(
        \Magestore\Affiliateplus\Helper\Payment $helperAbstract,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\RequestInterface $requestInterface
    ) {
       $this->_helperAbstract = $helperAbstract;
        $this->_storeManager = $storeManager;
        $this->_requestInterface = $requestInterface;
    }


    /**
     * @return array
     */
    public function toOptionArray()
    {
        $paymentMethods = [];
        $store = $this->_requestInterface->getParam('store');
        $availableMethods = $this->_helperAbstract->getAvailablePayment($store);
        foreach($availableMethods as $code => $method){
            $paymentMethods[$code] = $method->getLabel();
        }
        return $paymentMethods;
    }
}