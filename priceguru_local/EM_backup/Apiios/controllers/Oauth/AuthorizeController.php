<?php
require_once 'app'.DS.'code'.DS.'core'.DS.'Mage'.DS.'Oauth'.DS.'controllers'.DS.'AuthorizeController.php';
class EM_Apiios_Oauth_AuthorizeController extends Mage_Oauth_AuthorizeController
{
    /**
     * Init confirm page
     *
     * @param bool $simple      Is simple page?
     * @return Mage_Oauth_AuthorizeController
     */
    public function confirmAction()
    {
        /** @var $helper Mage_Oauth_Helper_Data */
        Mage::app()->setCurrentStore($this->getRequest()->getParam('store',Mage::app()->getStore()->getId()));
        $helper = Mage::helper('oauth');
        $customerId = $this->getRequest()->getParam('customer_id');
        /** @var $session Mage_Customer_Model_Session */
        if (!$customerId) {
            //$session->addError($this->__('Please login to proceed authorization.'));
            $url = $helper->getAuthorizeUrl(Mage_Oauth_Model_Token::USER_TYPE_CUSTOMER);
            //$this->_redirectUrl($url);
            //return $this;
        }

       

        try {
            /** @var $server Mage_Oauth_Model_Server */
            $server = Mage::getModel('oauth/server');
            /** @var $token Mage_Oauth_Model_Token */
            $token = $server->authorizeToken($customerId, Mage_Oauth_Model_Token::USER_TYPE_CUSTOMER);
            $this->getResponse()->setBody($token->getVerifier());
        } catch (Mage_Core_Exception $e) {
            $result = array(
                'messages' => array(
                    'error' => array(
                        'code'  =>  404,
                        'message' => $e->getMessage()
                    )
                )
            );
            $this->getResponse()->setBody(Zend_Json::encode($result));
        } catch (Mage_Oauth_Exception $e) {
            $result = array(
                'messages' => array(
                    'error' => array(
                        'code'  =>  404,
                        'message' => $e->getMessage()
                    )
                )
            );
            $this->getResponse()->setBody(Zend_Json::encode($result));
        } catch (Exception $e) {
            $result = array(
                'messages' => array(
                    'error' => array(
                        'code'  =>  404,
                        'message' => $e->getMessage()
                    )
                )
            );
            $this->getResponse()->setBody(Zend_Json::encode($result));
        }
        //echo 'error';
        //$this->getResponse()->setBody($callback);
        return $this;
        
    }

    public function rejectAction(){
        /** @var $server Mage_Oauth_Model_Server */
        $server = Mage::getModel('oauth/server');

        /** @var $token Mage_Oauth_Model_Token */
        $token = $server->checkAuthorizeRequest();
    }
    
}
