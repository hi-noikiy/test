<?php

/**
 * 
 */
require_once 'Magestore/Sociallogin/controllers/AmazonloginController.php';

class Gearup_Sociallogin_AmazonloginController extends Magestore_Sociallogin_AmazonloginController {

    /**
     * @return mixed
     */
    protected function _loginPostRedirect() {
              $redirect = Mage::getSingleton('core/session')->getOyeSocialRedirect();
        return ($redirect) ? $redirect : parent::_loginPostRedirect();
    }

}
