<?php

class Collinsharper_Beanstreaminterac_Model_Paymentaction
{
	public function toOptionArray()
	{
		return array(
			array(
				'value' => Mage_Paygate_Model_Authorizenet::ACTION_AUTHORIZE_CAPTURE, 
				'label' => Mage::helper('paygate')->__('Authorise and Capture')
			),
			array(
				'value' => Mage_Paygate_Model_Authorizenet::ACTION_AUTHORIZE, 
				'label' => Mage::helper('paygate')->__('Authorise')
			)
		);
	}
}

