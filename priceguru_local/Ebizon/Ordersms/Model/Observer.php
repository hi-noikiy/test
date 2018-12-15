<?php

require_once(Mage::getBaseDir('lib') . '/twilio-php-master/Services/Twilio.php');
class Ebizon_Ordersms_Model_Observer extends Varien_Object
{

    public function afterOrderSms(Varien_Event_Observer $observer) 
    {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $select = $connection->select()
                ->from('twilio_sms', array('*'))
                ->where('id=?', 1);
        $rowsArray = $connection->fetchAll($select);
        $rowArray = $connection->fetchRow($select);

        if (!empty($rowArray["status"]))
        {
            $order = $observer->getEvent()->getOrder();
            $orderid = $order->getIncrementId();
            $name = $order->getCustomerName();
            $telephone = $order->getBillingAddress()->getTelephone();
            //$telephone = $order->getShippingAddress()->getTelephone();
            $payment_method_code = $order->getPayment()->getMethodInstance()->getCode();

           
                try {
                    // set your AccountSid and AuthToken from www.twilio.com/user/account
                    $AccountSid = $rowArray["accounts_id"]; #"AC17a18fdb4d96c928668287c8852257be";
                    $AuthToken = $rowArray["auth_token"]; #"06c44f5463780c3ed53474c06748e29b";

                    $client = new Services_Twilio($AccountSid, $AuthToken);
                    $msg = $rowArray["order_sms"];
                    if ($order->getIscimorder()) {
                        $msg = $rowArray["cim_credit_sms"];
                    } 
                    $a= Mage::getUrl("survey/survey/customer-survey")."?id=".$orderid;
                    $url = $this->shorturl($a);
                    $msg = str_replace('{{url}}', $url, $msg);
                    $msg = str_replace('{{name}}', $name, $msg);
                    $msg = str_replace('{{orderid}}', $orderid, $msg);
                    $ini = substr($telephone,0,3);
                    if($ini =='230'){$telephone = '+'.$telephone; }
                    else if($ini != '+23'){$telephone = '+230'.$telephone;}
                    $message = $client->account->messages->create(array(
                        "From" => $rowArray["from_number"], #"+16016531707",
                        "To" =>  $telephone, //"+917042210955",
                        "Body" => $msg,
                    ));
                } catch (Exception $e) {
                   Mage::log("No:" . $telephone . ':' ."Order No:" . $orderid . ':'.'Error :'.$e->getMessage(), NULL, "mysms.log");
                    #echo 'Message: ' .$e->getMessage();
                }
            
        }
    }

    public function orderProcessing(Varien_Event_Observer $observer) {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $select = $connection->select()
                ->from('twilio_sms', array('*'))
                ->where('id=?', 1);
        $rowsArray = $connection->fetchAll($select);
        $rowArray = $connection->fetchRow($select);

        if (!empty($rowArray["status"])) {
            $shipment = $observer->getEvent()->getShipment();
            $order = $shipment->getOrder();
            $orderid = $order->getIncrementId();
            $name = $order->getCustomerName();
            $telephone = $order->getBillingAddress()->getTelephone();
            //$telephone = $order->getShippingAddress()->getTelephone();
            $payment_method_code = $order->getPayment()->getMethodInstance()->getCode();
            $stateProcessing = $order::STATE_COMPLETE;
           
           
                try {
                    // set your AccountSid and AuthToken from www.twilio.com/user/account
                    $AccountSid = $rowArray["accounts_id"]; #"AC17a18fdb4d96c928668287c8852257be";
                    $AuthToken = $rowArray["auth_token"]; #"06c44f5463780c3ed53474c06748e29b";

                    $client = new Services_Twilio($AccountSid, $AuthToken);
                    if($order->getState()=='new' &&  $order->getIscimorder()) {
                        $msg = $rowArray["cim_process_sms"];
                    } else if($order->getState()=='processing'){
                        $msg = $rowArray["order_complete_sms"];
                        if($order->getIscimorder()){    
                            $msg = $rowArray["cim_complete_sms"];
                        } 
                    }
                    if(!empty($msg) && $msg) {
                        $a= Mage::getUrl("survey/survey/customer-survey")."?id=".$orderid;
                        $url = $this->shorturl($a);
                        $url1 = "<a href='".$url."'> Click Here </a>";
                        $msg = str_replace('{{name}}', $name, $msg);
                        $msg = str_replace('{{orderid}}', $orderid, $msg);
                        $msg1 = str_replace('{{url}}', $url1, $msg);
                        $msg = str_replace('{{url}}', $url, $msg);
                        $ini = substr($telephone,0,3);
                        if($ini =='230'){$telephone = '+'.$telephone; }
                        else if($ini != '+23'){$telephone = '+230'.$telephone;}
                        $message = $client->account->messages->create(array(
                            "From" => $rowArray["from_number"], #"+16016531707",
                            "To" =>  $telephone, //"+917042210955",
                            "Body" => $msg,
                        )); 
                        if($order->getState()=='processing'){
                            $this->sendsuccess($order,$msg1);
                        }
                    }    
                    return true;
                } catch (Exception $e) {
                  Mage::log("No:" . $telephone . ':' ."Order No:" . $orderid . ':'.'Error :'.$e->getMessage(), NULL, "mysms.log");
                    //echo 'Message: ' .$e->getMessage();
                }
        }
    }

    public function afterInvoiceSms(Varien_Event_Observer $observer) {

        $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $select = $connection->select()
                ->from('twilio_sms', array('*'))
                ->where('id=?', 1);
        $rowsArray = $connection->fetchAll($select);
        $rowArray = $connection->fetchRow($select);

        if (!empty($rowArray["status"])) {
            $order = $observer->getEvent()->getOrder();
            $orderid = $order->getIncrementId();
            $name = $order->getCustomerName();
            $telephone = $order->getBillingAddress()->getTelephone();
            //$telephone = $order->getShippingAddress()->getTelephone();
            $payment_method_code = $order->getPayment()->getMethodInstance()->getCode();
            $stateProcessing = $order::STATE_COMPLETE;

            $order_status = $order->getState();
            try {
                // set your AccountSid and AuthToken from www.twilio.com/user/account
                $AccountSid = $rowArray["accounts_id"]; #"ACd078c58292083b9ad3e06ddd153479e1";
                $AuthToken = $rowArray["auth_token"]; #"beb1fc9bc0d1b9c3e27627406806a3a5";

                $client = new Services_Twilio($AccountSid, $AuthToken);
                if($order->getState()=='new' &&  $order->getIscimorder()) {
                    $msg = $rowArray["cim_process_sms"];
                } else if($order->getState()=='processing'){
                    $msg = $rowArray["order_complete_sms"];
                     if($order->getIscimorder()){    
                        $msg = $rowArray["cim_complete_sms"];
                    } 
                }
                if(!empty($msg) && $msg) {
                    $a= Mage::getUrl("survey/survey/customer-survey")."?id=".$orderid;
                    $url = $this->shorturl($a);
                    $url1 = "<a href='".$url."'> Click Here </a>";
                    $msg = str_replace('{{name}}', $name, $msg);
                    $msg = str_replace('{{orderid}}', $orderid, $msg);
                    $msg1 = str_replace('{{url}}', $url1, $msg);
                    $msg = str_replace('{{url}}', $url, $msg);
                    $ini = substr($telephone,0,3);
                    if($ini =='230'){$telephone = '+'.$telephone; }
                    else if($ini != '+23'){$telephone = '+230'.$telephone;}
                    $message = $client->account->messages->create(array(
                        "From" => $rowArray["from_number"], #"+16016531707",
                        "To" =>  $telephone, //"+917042210955",
                        "Body" => $msg,
                    )); 
                    if($order->getState()=='processing'){
                            $this->sendsuccess($order,$msg1);
                    }
                }    
            } catch (Exception $e) {
               Mage::log("No:" . $telephone . ':' ."Order No:" . $orderid . ':'.'Error :'.$e->getMessage(), NULL, "mysms.log");
                #echo 'Message: ' .$e->getMessage();
            }
        }
    }
    public function shorturl($url){
        $service_url = 'https://www.googleapis.com/urlshortener/v1/url?key=AIzaSyCx7GNyJRlBOJlq-nTPuZ7pxB2UOeyFhxM';
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $service_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array("longUrl" => $url)));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($ch);

        curl_close($ch);

        $curl_response = json_decode($result, true);

        $id = isset($curl_response['id']) ? $curl_response['id'] : false;

        return $id;
    }
    
    public function sendsuccess($order,$msg1){
        $templateId = 25; 
        $senderName = Mage::getStoreConfig('trans_email/ident_support/name');  
        $senderEmail = Mage::getStoreConfig('trans_email/ident_support/email'); 
        $sender = array('name' => $senderName,
            'email' => $senderEmail);
        $email_template  = Mage::getModel('core/email_template')->loadDefault($templateId);
        
        // Set recepient information
        $recepientEmail = $order->getCustomerEmail();
        $recepientName =  $order->getCustomerName();
        $store = Mage::app()->getStore()->getId();
        $vars = array('msg' => $msg1);

        // Send Transactional Email
         if(!Mage::getModel('core/email_template')
                ->sendTransactional($templateId, $sender, $recepientEmail, $recepientName, $vars, $store)) {
            Mage::log($recepientEmail, null, 'ktpl_survey_fail-'.date("Y-m-d").'.log');
        }
        else{
            Mage::log($recepientEmail, null, 'ktpl_survey_success-'.date("Y-m-d").'.log');
        } 
    }
        
}
?>