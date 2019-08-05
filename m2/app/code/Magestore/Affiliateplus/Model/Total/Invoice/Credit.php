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
namespace Magestore\Affiliateplus\Model\Total\Invoice;

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

    public function collect(\Magento\Sales\Model\Order\Invoice $invoice){
        if(!$this->_helper->isAffiliateModuleEnabled()){
            return $this;
        }
        $order = $invoice->getOrder();
        $baseOrderDiscount = $order->getBaseAffiliateCredit();
        $orderDiscount = $order->getAffiliateCredit();

        if ($invoice->getBaseGrandTotal() < 0.0001 || $baseOrderDiscount >= 0) {
            return $this;
        }
        $baseInvoicedDiscount = 0;
        $invoicedDiscount = 0;
        foreach ($order->getInvoiceCollection() as $_invoice) {
            $baseInvoicedDiscount += $_invoice->getBaseAffiliateCredit();
            $invoicedDiscount += $_invoice->getAffiliateCredit();
        }

        if ($invoice->isLast()) {
            $baseDiscount = $baseOrderDiscount - $baseInvoicedDiscount;
            $discount = $orderDiscount - $invoicedDiscount;
        } else {
//            edit by viet
            $baseOrderTotal = $order->getBaseSubtotalInclTax();// - $baseOrderDiscount;
            $baseDiscount = $baseOrderDiscount * $invoice->getBaseSubtotalInclTax() / $baseOrderTotal;
            $discount = $orderDiscount * $invoice->getBaseSubtotalInclTax() / $baseOrderTotal;
//            end by viet
            if ($baseDiscount < $baseOrderDiscount) {
                $baseDiscount = $baseOrderDiscount;
                $discount = $orderDiscount;
            }
        }
        if ($baseDiscount) {
            $baseDiscount = $invoice->roundPrice($baseDiscount);
            $discount = $invoice->roundPrice($discount);

            $invoice->setBaseAffiliateCredit($baseDiscount);
            $invoice->setAffiliateCredit($discount);

            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseDiscount);
            $invoice->setGrandTotal($invoice->getGrandTotal() + $discount);
        }
        return $this;
    }
}