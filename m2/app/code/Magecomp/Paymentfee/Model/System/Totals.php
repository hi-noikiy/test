<?php
/**
 * Magento Paymentfee extension
 *
 * @category   Magecomp
 * @package    Magecomp_Paymentfee
 * @author     Magecomp
 */
namespace Magecomp\Paymentfee\Model\System;

class Totals {

    const SURCHARGE_ON_SUBTOTAL = 'on-subtotal';
    const SURCHARGE_ON_SHIPPING = 'on-shipping';
    const SURCHARGE_ON_TAX = 'on-tax';
    const SURCHARGE_EXCLUDE_DISCOUNT = 'excl-discount';

    public function toOptionArray() {
        $returnArray= [];
        $returnArray[] = ['value'=>self::SURCHARGE_ON_SUBTOTAL, 'label'=>__('Subtotal')];
        $returnArray[] = ['value'=>self::SURCHARGE_ON_SHIPPING, 'label'=>__('Shipping')];
        $returnArray[] = ['value'=>self::SURCHARGE_ON_TAX, 'label'=>__('Tax')];
        $returnArray[] = ['value'=>self::SURCHARGE_EXCLUDE_DISCOUNT, 'label'=>__('Exclude Discount')];
        return $returnArray;
    }
}
