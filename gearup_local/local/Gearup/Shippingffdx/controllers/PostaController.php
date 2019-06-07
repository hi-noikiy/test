<?php

class Gearup_Shippingffdx_PostaController extends Mage_Core_Controller_Front_Action {

    public function cityListAction() {
        $countryCode = $this->getRequest()->getParam('country');
        $postaHelper = new Posta_Helper();
        $degit3CountryCode = $postaHelper->get3DigitCountryCode($countryCode);
        $this->getResponse()->clearHeaders()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($postaHelper->getCityList($degit3CountryCode));
    }

    public function phoneCodesAction() {
        $this->getResponse()->clearHeaders()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(file_get_contents(Mage::getBaseDir('lib').'/Posta/CountryPhoneCodes.json'));
    }

}
