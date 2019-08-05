<?php
/**
 * Pdf config
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ktpl\General\Model\Order\Pdf;

/**
 * Class Config
 */
class Config extends \Magento\Sales\Model\Order\Pdf\Config
{
    /**
     * @var \Magento\Framework\Config\DataInterface
     */
    protected $_dataStorage;

    protected $_scopeConfig;

    protected $_order;

    const XML_PATH_SUBTOTAL     = 'sales/totals_sort/subtotal';
    const XML_PATH_DISCOUNT     = 'sales/totals_sort/discount';
    const XML_PATH_SHIPPING     = 'sales/totals_sort/shipping';
    const XML_PATH_TOTAL        = 'sales/totals_sort/grand_total';
    const XML_PATH_TAX          = 'sales/totals_sort/tax';
    const XML_PATH_WEEE         = 'sales/totals_sort/weee';
    const XML_PATH_STORE_CREDIT = 'sales/totals_sort/aw_store_credit';

    /**
     * @param \Magento\Framework\Config\DataInterface $dataStorage
     */
    public function __construct(\Magento\Framework\Config\DataInterface $dataStorage,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Model\Order $order)
    {
        $this->_dataStorage = $dataStorage;
        $this->_scopeConfig = $scopeConfig;
        $this->_order = $order;
    }

    /**
     * Get renderer configuration data by type
     *
     * @param string $pageType
     * @return array
     */
    public function getRenderersPerProduct($pageType)
    {
        return $this->_dataStorage->get("renderers/{$pageType}", []);
    }

    /**
     * Get list of settings for showing totals in PDF
     *
     * @return array
     */
    public function getTotals()
    {

        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $storeID    = ( $this->_order->getStore() ) ? $this->_order->getStore() : null;

        $sortOrder  = array();
        $sortOrder['subtotal'] = $this->_scopeConfig->getValue(self::XML_PATH_SUBTOTAL, $storeScope,$storeID);
        $sortOrder['discount'] = $this->_scopeConfig->getValue(self::XML_PATH_DISCOUNT, $storeScope,$storeID);
        $sortOrder['shipping'] = $this->_scopeConfig->getValue(self::XML_PATH_SHIPPING, $storeScope,$storeID);
        $sortOrder['grand_total'] = $this->_scopeConfig->getValue(self::XML_PATH_TOTAL, $storeScope,$storeID);
        $sortOrder['tax'] = $this->_scopeConfig->getValue(self::XML_PATH_TAX, $storeScope,$storeID);
        $sortOrder['weee'] = $this->_scopeConfig->getValue(self::XML_PATH_WEEE, $storeScope,$storeID);
        $sortOrder['aw_store_credit'] = $this->_scopeConfig->getValue(self::XML_PATH_STORE_CREDIT, $storeScope,$storeID);

        $configValue = $this->_dataStorage->get('totals', []);
        foreach ($configValue as $key => $config ) {
            if( isset($sortOrder[$key]) )
            $configValue[$key]['sort_order'] = $sortOrder[$key];
        }

        return $configValue;
    }
}