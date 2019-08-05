<?php
/**
 * Magento Paymentfee extension
 *
 * @category   Magecomp
 * @package    Magecomp_Paymentfee
 * @author     Magecomp
 */
namespace Magecomp\Paymentfee\Model\System;

class Producttypes 
{
    public function toOptionArray() 
	{
        $returnArray = [];
        $returnArray[] = ['value'=> 0, 'label'=>__('Per Order')];
        $returnArray[] = ['value'=> 1, 'label'=>__('Per Product')];
		$returnArray[] = ['value'=> 2, 'label'=>__('Specific Product')];                
        return $returnArray;
    }
}