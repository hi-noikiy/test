<?php
// what?
class Collinsharper_Beanstreaminterac_Helper_Data extends Mage_Core_Helper_Abstract
{

	public function ReceiptInfo()
	{
		// $session = $this->getSession();
		// $order = Mage::getModel('sales/order');
		// $order->loadByIncrementId($session->getTransId());

		if(Mage::getSingleton('customer/session')->getStandardReceipt())
		{
			return (string)Mage::getSingleton('customer/session')->getStandardReceipt();
		}
		$order = Mage::getModel('sales/order')->loadByIncrementId(Mage::getSingleton('checkout/session')->getLastRealOrderId());
		foreach($order->getStatusHistoryCollection(true) as $_c)
		{
			$_comm = strip_tags($_c->getComment());
			if(strpos($_comm , "Transaction Id"))
			{
				$_comm = str_replace("\n","<br />\n", $_comm);
				mage::log("found out receipt data " . print_r($_comm,1));
				return $_comm;
				break;
			}
		}
		return false;
	}

    public function getUrlDetails()
    {

        $redirect = Mage::getUrl('interac/standard/redirect', array('_secure' => true));
        $funded = Mage::getUrl('interac/standard/success', array('_secure' => true));
        $nofun = Mage::getUrl('interac/standard/cancel', array('_secure' => true));
        echo <<<EOD
	<pre>
The urls for the interac setup will be as follows note that they should be https: if they are not you have misconfigured your site.

Redirect: {$redirect}
Success: {$funded}
Canceled: {$nofun}

If you need further support, please contact Collins Harper http://www.collinsharper.com/chlabs for support or additional custom developement.

EOD;
    }

}