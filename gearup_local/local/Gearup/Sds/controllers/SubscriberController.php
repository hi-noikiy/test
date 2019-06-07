<?php
class Gearup_Sds_SubscriberController extends Mage_Core_Controller_Front_Action
{
    public function newAction()
    {
        if ($this->getRequest()->isPost() && $this->getRequest()->getPost('email')) {
            $session            = Mage::getSingleton('core/session');
            $customerSession    = Mage::getSingleton('customer/session');
            $email              = (string) $this->getRequest()->getPost('email');

            try {
                $ownerId = Mage::getModel('customer/customer')
                        ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                        ->loadByEmail($email)
                        ->getId();
                if ($ownerId !== null && $ownerId != $customerSession->getId()) {
                    $response = array('sta'=>'0', 'msg'=>$this->__('This email address is already assigned to another user.'));
                    $this->getResponse()->setBody(json_encode($response));
                    return false;
                }

                $status = Mage::getModel('newsletter/subscriber')->subscribe($email);
                $response = array('sta'=>'1');
                $this->getResponse()->setBody(json_encode($response));
                return true;
            }
            catch (Mage_Core_Exception $e) {
                $response = array('sta'=>'0', 'msg'=>$this->__('There was a problem with the subscription: %s', $e->getMessage()));
                $this->getResponse()->setBody(json_encode($response));
                return false;
            }
            catch (Exception $e) {
                $response = array('sta'=>'0', 'msg'=>$this->__('There was a problem with the subscription.'));
                $this->getResponse()->setBody(json_encode($response));
                return false;
            }
        }
       return true;
    }
}