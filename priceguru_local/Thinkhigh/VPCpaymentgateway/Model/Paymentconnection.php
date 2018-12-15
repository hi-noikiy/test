<?php
class Thinkhigh_VPCpaymentgateway_Model_Paymentconnection {
    
    public function getPostData(){
           $_order = new Mage_Sales_Model_Order();
           $orderId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
           $_order->loadByIncrementId($orderId);
 
           $payment_data=$_order->getPayment();

           /*
           * Indicates the transaction type. This must be equal to 'pay' for a 2-Party or 3-Party payment
           * Required   Alphanumeric   1,8   1
           */
           $post_data['vpc_Version']=Mage::getStoreConfig('payment/vpcpaymentgateway/vpc_Version');
           
           /*
           * Authenticates the merchant on the Payment Server. This means that a merchant cannot access another merchant's Merchant Id.
           * The access code is provided when the merchant profile is registered with a Payment Provider
           * Required  Alphanumeric  1,16  pay 
           */
           $post_data['vpc_Command']=Mage::getStoreConfig('payment/vpcpaymentgateway/vpc_Command');
           
           /*
           * The unique Merchant Id assigned to a merchant by the Payment Provider. 
           * The Merchant ID identifies the merchant account against which settlements will be made
           * Required  Alphanumeric  1,16  TESTMERCHANT01
           */
           $post_data['vpc_Merchant']=Mage::getStoreConfig('payment/vpcpaymentgateway/vpc_Merchant');
           
           /*
           * Authenticates the merchant on the Payment Server. This means that a merchant cannot access another merchant's Merchant Id.
           * The access code is provided when the merchant profile is registered with a Payment Provider.
           * Required  Alphanumeric  8   6AQ89F3
           */
           $post_data['vpc_AccessCode']=Mage::getStoreConfig('payment/vpcpaymentgateway/vpc_AccessCode');
           
           /*
           * A unique value created by the merchant.               
           * Required  Alphanumeric 1,40  ORDER958743-1
           */   
           $post_data['vpc_MerchTxnRef']='MerchTxnRef_'.$orderId;
           
           /*
           * The merchant's identifier used to identify the order on the Payment Server. 
           * For example, a shopping cart number, an order number, or an invoice number.
           * This identifier will be displayed in the Transaction Search results in the Merchant Administration portal on the Payment Server.
           * Optional  Alphanumeric  0,34  ORDER958743
           */
           $post_data['vpc_OrderInfo']=$orderId;   
           
           /*
           * The amount of the transaction, expressed in the smallest currency unit.
           * The amount must not contain any decimal points, thousands separators or currency symbols.
           *  For example, $12.50 is expressed as 1250.
           * This value cannot be negative or zero. The maximum amount is 2147483647.
           * Required  Numeric  1,10  1250 
           */
          
           $post_data['vpc_Amount']=number_format($_order->getGrandTotal(), 2,'','');
           $post_data['vpc_Gateway']=Mage::getStoreConfig('payment/vpcpaymentgateway/vpc_Gateway');
           
           /*
           * It is recommended that the browser is returned to an SSL secured page. 
           * This will prevent the browser pop-up indicating that the cardholder is being returned to an unsecure site. 
           * If the cardholder clicks 'No' to continue, then neither the merchant nor the cardholder will obtain any receipt details.
           * Required  Alphanumeric  1,255  https://merchants_site/receipt.asp
           */
           $post_data['vpc_ReturnURL']=Mage::getUrl('vpcpaymentgateway/payment/response');
           if(isset($post_data['vpc_card']))//for 2-party transaction
                $post_data['vpc_card']=$payment_data->getVpcCard();
           
           /*
           * The number of the card used for the transaction. 
           * The format of the Card Number is based on the Electronic Commerce Modeling Language (ECML) and,
           *  in particular, must not contain white space or formatting characters.
           * Required  Numeric  15,19  5123456789012346 
           */
           if(isset($post_data['vpc_CardNum']))//for 2-party transaction
                $post_data['vpc_CardNum']=Mage::helper('core')->decrypt($payment_data->getVpcCardNum());
           
           /*
           * The expiry date of the card in the format YYMM. 
           * The value must be expressed as a 4-digit number (integer) with no white space or formatting characters. 
           * For example, an expiry date of May 2013 is represented as 1305.
           * Required  Numeric  4  1305
           */
           if(isset($post_data['vpc_CardExp']))//for 2-party transaction
                $post_data['vpc_CardExp']=$payment_data->getVpcCardExp();
           
           /*
           * The currency of the order expressed as an ISO 4217 alphanumeric code. This field is case-sensitive and must include uppercase
           *   characters only.
           * The merchant must be configured to accept the currency used in this field. 
           * To obtain a list of supported currencies and codes, please contact your Payment Provider.
           * Note: This field is required only if more than one currency is configured for the merchant.
           * Optional  Alpha  3  USD
           */
           $post_data['vpc_Currency']=Mage::app()->getStore()->getCurrentCurrencyCode();
           
           /*
           * vpc_Locale
           * Specifies the language used on the Payment Server pages that are displayed to the cardholder, 
           * in 3-Party transactions. Please check with your Payment Provider for the correct value to use.
           * In a 2-Party transaction the default value of 'en' is used.
           * Required  Alphanumeric  2,5  en
           */
           $post_data['vpc_Locale']=Mage::app()->getLocale()->getLocaleCode();
           
           if(isset($post_data['vpc_CardSecurityCode']))//for 2-party transaction
                $post_data['vpc_CardSecurityCode']=Mage::helper('core')->decrypt($payment_data->getVpcCardSecurityCode());
           
           $post_data['vpc_ReturnAuthResponseData']='Y';
           
           /*
           *  The field names are sorted in ascending of parameter name. Specifically, the sort order is:
           *     • Ascending order of parameter name using the ASCII collating sequence, for example, "Card" comes before "card"
           *     • Where one string is an exact substring of another, the smaller string should be ordered before the longer, for example, "Card"
           *        " should come before "CardNum".
           */ 
           ksort($post_data);
           
           return $post_data;
    }
	
}

?>