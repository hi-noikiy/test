<?php
/**
 * Magento Paymentfee extension
 *
 * @category   Magecomp
 * @package    Magecomp_Paymentfee
 * @author     Magecomp
 */
namespace Magecomp\Paymentfee\Model\System;

class Addresstypes 
{
    public function toOptionArray() 
	{
        $returnArray= [];
        $returnArray[] = ['value'=>'shipping', 'label'=>__('Shipping Address')];
        $returnArray[] = ['value'=>'billing', 'label'=>__('Billing Address')];        
        return $returnArray;
    }
}