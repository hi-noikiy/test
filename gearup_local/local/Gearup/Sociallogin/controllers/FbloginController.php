<?php

require_once 'Magestore/Sociallogin/controllers/FbloginController.php';

class Gearup_Sociallogin_FbloginController extends Magestore_Sociallogin_FbloginController {

    /**
     * @return mixed
     */
    protected function _loginPostRedirect() {
        $redirect = Mage::getSingleton('core/session')->getOyeSocialRedirect();
        return ($redirect) ? $redirect : parent::_loginPostRedirect();
    }

}
