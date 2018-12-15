<?php

class ClubnetSEM_DisableShippingAddress_Block_Checkout_Onepage extends Mage_Checkout_Block_Onepage {
	protected function _getStepCodes() {
		return array('login', 'billing', 'shipping_method', 'payment', 'review');
	}
}