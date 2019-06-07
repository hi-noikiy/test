<?php

/**
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Sociallogin
 * @module      Sociallogin
 * @author      Magestore Developer
 *
 * @copyright   Copyright (c) 2016 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 *
 */
class Gearup_Sociallogin_Block_Buttons extends Magestore_Sociallogin_Block_Buttons
{
    protected function _construct()
    {
        parent::_construct();
        $redirect = Mage::getUrl('checkout/onepage/index', array('goto'=>'billing_shipping'));
        // Redirect uri
        Mage::getSingleton('core/session')->setOyeSocialRedirect($redirect);
    }
}