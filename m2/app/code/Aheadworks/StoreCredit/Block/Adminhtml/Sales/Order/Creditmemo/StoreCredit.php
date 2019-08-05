<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Block\Adminhtml\Sales\Order\Creditmemo;

use Aheadworks\StoreCredit\Model\Config;
use Magento\Framework\Registry;
use Magento\Backend\Block\Template\Context;

/**
 * Class StoreCredit
 *
 * @package Aheadworks\StoreCredit\Block\Adminhtml\Sales\Order\Creditmemo
 */
class StoreCredit extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'Aheadworks_StoreCredit::sales/order/creditmemo/storecredit.phtml';

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Config $config,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->config = $config;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve credit memo
     *
     * @return \Magento\Sales\Model\Order\Creditmemo
     */
    public function getCreditmemo()
    {
        return $this->coreRegistry->registry('current_creditmemo');
    }

    /**
     * Check whether can refund store credit to customer
     *
     * @return bool
     */
    public function canRefund()
    {
        if ($this->getCreditmemo()->getOrder()->getCustomerIsGuest() && !$this->isRefundOffline()) {
            return false;
        }
        return true;
    }

    /**
     * Retrieve value to refund on Store Credit
     *
     * @return float
     */
    public function getRefundToStoreCredit()
    {
        return $this->getCreditmemo()->getBaseAwStoreCreditRefundValue();
    }

    /**
     * Check that is auto refund or not
     *
     * @return bool
     */
    public function isStoreCreditRefundAutomatically()
    {
        return $this->config->isStoreCreditRefundAutomatically();
    }

    /**
     * Check that is offline refund or not
     *
     * @return bool
     */
    private function isRefundOffline()
    {
        if ($this->getCreditmemo()->getInvoice() && $this->getCreditmemo()->getInvoice()->getTransactionId()) {
            return false;
        }
        return true;
    }
}
