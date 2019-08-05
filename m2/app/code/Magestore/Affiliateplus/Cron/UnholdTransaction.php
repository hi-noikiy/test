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
namespace Magestore\Affiliateplus\Cron;

class UnholdTransaction
{
    /**
     * @var \Magestore\Affiliateplus\Helper\Data
     */
    protected $_dataHelper;
    /**
     * @var \Magestore\Affiliateplus\Model\Transaction
     */
    protected $_transactionModel;

    public function __construct(
        \Magestore\Affiliateplus\Helper\Data $dataHelper,
        \Magestore\Affiliateplus\Model\Transaction $transactionModel,
        \Ktpl\General\Helper\Data $ktplHelper
    )
    {
        $this->_dataHelper = $dataHelper;
        $this->_transactionModel = $transactionModel;
        $this->ktplHelper = $ktplHelper;
    }

    public function execute(){
        if (!$this->_dataHelper->isAffiliateModuleEnabled()){
            return;
        }
        $days = (int) $this->ktplHelper->getConfig('affiliateplus/commission/holding_period');
        $activeTime = time() - $days * 86400;
        $collection = $this->_transactionModel->getCollection()
            ->addFieldToFilter('status', \Magestore\Affiliateplus\Model\Transaction::TRANSACTION_ONHOLD)
            ->addFieldToFilter('holding_from', array('to' => date('Y-m-d H:i:s', $activeTime)));
        foreach ($collection as $transaction) {
            try {
                $transaction->unHold();
            } catch (\Exception $e) {

            }
        }
    }
}