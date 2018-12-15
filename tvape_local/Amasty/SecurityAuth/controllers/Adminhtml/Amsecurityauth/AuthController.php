<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_SecurityAuth
 */

class Amasty_SecurityAuth_Adminhtml_Amsecurityauth_AuthController extends Mage_Adminhtml_Controller_Action
{

    public function indexAction(){
        $this->loadLayout();
        $this->renderLayout();
    }

    public function ajaxVerifyCodeAction()
    {
        $request = Mage::app()->getRequest();
        $userId = $request->getPost('user_id');
        $userAuth = Mage::getModel('amsecurityauth/auth')->load($userId);

        $secret = $request->getPost('secret');
        $code = $request->getPost('code', null);

        $valid = $userAuth->verifyCode($secret, $code, Mage::getStoreConfig('amsecurityauth/general/discrepancy'));

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode(array('result' =>$valid)));
    }

    protected function _isAllowed()
    {
        return Mage::getStoreConfig('amsecurityauth/general/active');
    }

}