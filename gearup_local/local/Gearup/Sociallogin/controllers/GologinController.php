<?php

require_once 'Magestore/Sociallogin/controllers/GologinController.php';

class Gearup_Sociallogin_GologinController extends Magestore_Sociallogin_GologinController {

    /**
     * @return mixed
     */
    protected function _loginPostRedirect() {
        $redirect = Mage::getSingleton('core/session')->getOyeSocialRedirect();
        return ($redirect) ? $redirect : parent::_loginPostRedirect();
    }

}
