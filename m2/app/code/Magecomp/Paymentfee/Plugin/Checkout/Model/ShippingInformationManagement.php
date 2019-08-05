<?php
namespace Magecomp\Paymentfee\Plugin\Checkout\Model;
use Magento\Checkout\Model\ShippingInformationManagement as Shipinfo;

class ShippingInformationManagement
{
    public function aftersaveAddressInformation(
	Shipinfo $shipping, 
	$result)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
		$checkoutsession = $om->get('Magento\Checkout\Model\Session');
		$quotedata = $checkoutsession->getQuote();
		
		//Reset Paymentfee
		$quotedata->setMcPaymentfeeAmount(0.0000);
        $quotedata->setBaseMcPaymentfeeAmount(0.0000);
        $quotedata->setMcPaymentfeeDescription();
		$quotedata->save();
		
        $addresses = $quotedata->getAllAddresses();
        if ($addresses) 
		{
            foreach ($addresses as $address) 
			{
				$curgtotal = ($address->getGrandTotal() - $address->getMcPaymentfeeAmount());
				$curbtotal = ($address->getBaseGrandTotal() - $address->getMcPaymentfeeAmount());
				$address->setMcPaymentfeeAmount(0.0000);
                $address->setBaseMcPaymentfeeAmount(0.0000);
				$address->setGrandTotal($curgtotal);
        		$address->setBaseGrandTotal($curbtotal);
				$address->save();
            }
        }
		
		//Recalculate Paymentfee
		$paymentfeefactory = $om->get('Magecomp\Paymentfee\Model\Quote\Address\Total\PaymentfeeFactory');
		foreach ($quotedata->getAllAddresses() as $address) 
		{
            	$paymentfeefactory->create()->calculate($address);
		}
			
		return $result;
    }
}