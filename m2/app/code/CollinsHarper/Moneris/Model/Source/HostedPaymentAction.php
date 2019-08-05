<?php
/**
 * Copyright © 2016 CollinsHarper. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace CollinsHarper\Moneris\Model\Source;

use Magento\Framework\Option\ArrayInterface;
use CollinsHarper\Moneris\Model\Method\Payment;

/**
 * Moneres OnSite Payment Method model.
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class HostedPaymentAction implements ArrayInterface
{
    public function toOptionArray()
    {
        $paymentActions = [
            Payment::PAYMENT_ACTION_CAPTURE => __('Purchase'),
            Payment::PAYMENT_PURCHASE => __('Preauthorization'),
        ];
        return $paymentActions;
    }
}
