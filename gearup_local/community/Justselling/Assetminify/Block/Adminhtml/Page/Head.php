<?php

class Justselling_Assetminify_Block_Adminhtml_Page_Head extends Justselling_Assetminify_Block_Page_Html_Head {
    protected function _getUrlModelClass()
    {
        return 'adminhtml/url';
    }

    public function getFormKey() {
        return Mage::getSingleton('core/session')->getFormKey();
    }
}
