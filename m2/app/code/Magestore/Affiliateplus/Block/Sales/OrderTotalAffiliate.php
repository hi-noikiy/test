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
namespace Magestore\Affiliateplus\Block\Sales;

class OrderTotalAffiliate extends \Magento\Sales\Block\Order\Totals
{
    protected $_order = '';
    /**
     * get Affiliate discount
     *
     * @return mixed
     */
    public function getAffiliateplusDiscount(){
        $order = $this->getOrder();
        return $order->getAffiliateplusDiscount();
    }

    /**
     * get Base Affiliateplus discount
     *
     * @return mixed
     */
    public function getBaseAffiliateplusDiscount(){
        $order = $this->getOrder();
        return $order->getBaseAffiliateplusDiscount();
    }

    /**
     * Init totals
     */
    public function initTotals(){
        $amount = floatval($this->getAffiliateplusDiscount());
        if($amount){
            $total = new \Magento\Framework\DataObject(
                [
                    'code' => 'affiliateplus',
                    'field' => 'affiliateplus_discount',
                    'value' => $amount,
                    'base_value'=> $this->getBaseAffiliateplusDiscount(),
                    'label' => __('Affiliateplus Discount')
                ]
            );
            $parent = $this->getParentBlock();
            $parent->addTotal($total,'subtotal');
        }
    }

    /**
     * get Affiliate Coupon label
     *
     * @return string
     */
    public function getAffiliateCouponLabel() {
        $order = $this->getOrder();
        if ($order->getAffiliateplusCoupon()) {
            return ' (' . $order->getAffiliateplusCoupon() . ')';
        } elseif ($order->getOrder()) {
            if ($order->getOrder()->getAffiliateplusCoupon()) {
                return ' (' . $order->getOrder()->getAffiliateplusCoupon() . ')';
            }
        }
        return '';
    }

    /**
     * get Order
     *
     * @return mixed
     */
    public function getOrder(){
        if(!$this->_order){
            $parent = $this->getParentBlock();
            if ($parent instanceof \Magento\Sales\Block\Adminhtml\Order\Invoice\Totals) {
                $order = $parent->getInvoice();
            } elseif ($parent instanceof Magento\Sales\Block\Adminhtml\Order\Creditmemo\Totals) {
                $order = $parent->getCreditmemo();
            } else {
                $order = $this->getParentBlock()->getOrder();
            }
            $this->_order = $order;
        }
        return $this->_order;
    }
}
