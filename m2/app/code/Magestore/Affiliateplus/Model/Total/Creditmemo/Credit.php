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
namespace Magestore\Affiliateplus\Model\Total\Creditmemo;

class Credit extends \Magento\Sales\Model\Order\Total\AbstractTotal{
    /**
     * @var \Magestore\Affiliateplus\Helper\Data
     */
    protected $_helper;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    public function __construct(
        \Magestore\Affiliateplus\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data=[]
    )
    {
        parent::__construct($data);
        $this->_helper = $helper;
        $this->_storeManager = $storeManager;
    }

    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditMemo){
        if(!$this->_helper->isAffiliateModuleEnabled()){
            return $this;
        }
        $order = $creditMemo->getOrder();
        $baseOrderDiscount = $order->getBaseAffiliateCredit();
        $orderDiscount = $order->getAffiliateCredit();

        if ($creditMemo->getBaseGrandTotal() < 0.0001 || $baseOrderDiscount >= 0) {
            return $this;
        }
        $baseInvoicedDiscount = 0;
        $invoicedDiscount = 0;
        foreach ($order->getCreditmemosCollection() as $_creditMemo) {
            $baseInvoicedDiscount += $_creditMemo->getBaseAffiliateCredit();
            $invoicedDiscount += $_creditMemo->getAffiliateCredit();
        }
        $baseOrderTotal = $order->getBaseSubtotalInclTax();// - $baseOrderDiscount;
        $baseDiscount = $baseOrderDiscount * $creditMemo->getBaseSubtotalInclTax() / $baseOrderTotal;
        $discount = $orderDiscount * $creditMemo->getBaseSubtotalInclTax() / $baseOrderTotal;
        if ($baseDiscount < $baseOrderDiscount) {
            $baseDiscount = $baseOrderDiscount;
            $discount = $orderDiscount;
        }
        if ($baseDiscount) {
            $baseDiscount = $creditMemo->roundPrice($baseDiscount);
            $discount = $creditMemo->roundPrice($discount);
            $creditMemo->setBaseAffiliateCredit($baseDiscount);
            $creditMemo->setAffiliateCredit($discount);

            $creditMemo->setBaseGrandTotal($creditMemo->getBaseGrandTotal() + $baseDiscount);
            $creditMemo->setGrandTotal($creditMemo->getGrandTotal() + $discount);

            $creditMemo->setAllowZeroGrandTotal(true);
        }
        return $this;
    }


}