<?php
class Gearup_Sds_AccountController extends Mage_Core_Controller_Front_Action
{
    public function contactugpcAction()
    {
        if (!$this->_validateFormKey()) {
            $response = array('msg'=>'Magento missing form key', 'sta'=>'2');
            $this->getResponse()->setBody(json_encode($response));
            return false;
        }

        if ($params = $this->getRequest()->getParams()) {
            /*$mail = new Zend_Mail();
            $mail->setBodyText($params['content']);
            $mail->setFrom($params['emailugpc'], 'contact');
            $mail->addTo('pa.slash@gmail.com', 'support');
            $mail->setSubject('Test Inchoo_SimpleContact Module for Magento');*/
            $postObject = new Varien_Object();
            $postObject->setData($params);
            try {
                //$mail->send();
                $sender = array('name' => 'Customer', 'email' => $params['email']);
                $mailTemplate = Mage::getModel('core/email_template');
                $recivers = explode(';', Mage::getStoreConfig('customer/ugpc/email'));
                foreach ($recivers as $reciver) {
                    $mailTemplate->setDesignConfig(array('area' => 'frontend'))
                        ->setReplyTo($params['email'])
                        ->sendTransactional(
                            7,
                            $sender,
                            $reciver,
                            null,
                            array('data' => $postObject)
                    );
                }
                //Mage::getSingleton('core/session')->addSuccess('Email and message are sent to support@gear-up.me.');
                $response = array('msg'=>'Thank you for your email, we will come back to you shortly.', 'sta'=>'1');
                $this->getResponse()->setBody(json_encode($response));
            }        
            catch(Exception $ex) {
                $response = array('msg'=>$ex->getmessage, 'sta'=>'2');
                $this->getResponse()->setBody(json_encode($response));
                return false;
            }
            //return $this->_redirectUrl(Mage::getBaseUrl().'ign-exclusive-price');
        }
    }

    public function addcartAction() {
       /* try {
            $product_list = array('UGPC2015');
            $cart = Mage::getSingleton('checkout/cart');
            foreach ($product_list as $productSku){
                $product = Mage::getModel('catalog/product');
                $product->load($product->getIdBySku($productSku));
                if ($product->getId()) {
                    $cart->addProduct($product, '1');
                } else {
                    Mage::getSingleton('core/session')->addError('Not found the product for add to cart.');
                    return $this->_redirectUrl(Mage::getBaseUrl().'ign-exclusive-price');
                }
            }
            $cart->save();
            Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
        } catch (Exception $e) {
            echo'<pre>';
            var_dump($e);
            echo'</pre>';
            die();
        }*/

        return $this->_redirectUrl(Mage::getBaseUrl());
    }
}