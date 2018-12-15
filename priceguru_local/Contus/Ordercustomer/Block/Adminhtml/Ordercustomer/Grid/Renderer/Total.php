<?php

/**
 * Magento
 *  'renderer' => 'gclone_rewarduser_block_adminhtml_rewarduser_grid_renderer_ordercnt',    
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Contus_Ordercustomer_Block_Adminhtml_Ordercustomer_Grid_Renderer_Total extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
/*
 *  Get Payment type by order id
 */
    public function render(Varien_Object $row) {     
            $price = $row->getData('price');
	    $order_id = $row->getData('increment_id');
	    $order = Mage::getModel('sales/order')->loadByIncrementId($order_id);
            $qty = $order->getTotalItemCount();
            
            $gift = $order->getBaseGiftVoucherDiscount();
            if($qty > 1){ $gift = $gift/$qty; }
            
            $shipping = $order->getShippingAmount();
            if($qty > 1){ $shipping = $shipping/$qty; }
            
            $reward_points = $order->getRewardpointsDiscount();
            if($qty > 1){ $reward_points = $reward_points/$qty; }
            
	    $total = $price + $shipping - $reward_points - $gift ;
	    if($total > 0) {
	    	$total = "Rs " . number_format($total, 2);	
	    } else {
	    	$total = "-";
	    }
	    
		return $total;
	}

}