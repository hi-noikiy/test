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
namespace Magestore\Affiliateplus\Block\Sales\Credit;

class Invoice extends \Magento\Sales\Block\Order\Totals
{
    /**
     * Init totals
     */
    public function initTotals() {
        $parent = $this->getParentBlock();
        $order = $parent->getInvoice();
        if($amount = floatval($order->getAffiliateCredit())){
            $total = new \Magento\Framework\DataObject([
                'code'  => 'affiliateplus_credit',
                'field' => 'affiliateplus_credit',
                'value' => $amount,
                'base_value'    => $order->getBaseAffiliateCredit(),
                'label' => __('Paid by Affiliate Credit'),
            ]);
            $parent->addTotal($total, 'subtotal');
        }
    }
}