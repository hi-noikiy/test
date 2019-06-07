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
class Magestore_Sociallogin_Block_Adminhtml_Field_Lib extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * render config row
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $id = $element->getHtmlId();
        $helper = Mage::helper('sociallogin');
        $author = is_dir(MAGENTO_ROOT . '/lib/Author') ? $helper->__("Installed") : $helper->__("Not Installed");
        $facebook = is_dir(MAGENTO_ROOT . '/lib/Facebook') ? $helper->__("Installed") : $helper->__("Not Installed");
        $facebookv = is_dir(MAGENTO_ROOT . '/lib/Facebookv3') ? $helper->__("Installed") : $helper->__("Not Installed");
        $foursquare = is_dir(MAGENTO_ROOT . '/lib/Foursquare') ? $helper->__("Installed") : $helper->__("Not Installed");
        $instagram = is_dir(MAGENTO_ROOT . '/lib/instagram') ? $helper->__("Installed") : $helper->__("Not Installed");
        $oauth = is_dir(MAGENTO_ROOT . '/lib/Oauth2') ? $helper->__("Installed") : $helper->__("Not Installed");
        $openid = is_dir(MAGENTO_ROOT . '/lib/OpenId') ? $helper->__("Installed") : $helper->__("Not Installed");
        $vk = is_dir(MAGENTO_ROOT . '/lib/Vk') ? $helper->__("Installed") : $helper->__("Not Installed");
        $yahoo = is_dir(MAGENTO_ROOT . '/lib/Yahoo') ? $helper->__("Installed") : $helper->__("Not Installed");
        $zend = is_dir(MAGENTO_ROOT . '/lib/Zend/Oauth') ? $helper->__("Installed") : $helper->__("Not Installed");
        $html = "<tr>";
        $html .= $helper->__("Several features of the extension require installing the Library. Please check and download here:");
        $html .= "</tr>";
        $html .= '<tr id="row_' . $id . '">';
        $html .= "<td class='label'>Author: $author</td>";
        $html .= "<td class='label'>Facebook: $facebook</td>";
        $html .= "<td class='label'>Facebookv3: $facebookv</td>";;
        $html .= "<td class='label'>Foursquare: $foursquare</td>";
        $html .= "<td class='label'>Instagram: $instagram</td>";
        $html .= "</tr>";
        $html .= "<tr>";
        $html .= "<td class='label'>Oauth2: $oauth</td>";
        $html .= "<td class='label'>OpenId: $openid</td>";
        $html .= "<td class='label'>Vk: $vk</td>";
        $html .= "<td class='label'>Yahoo: $yahoo</td>";
        $html .= "<td class='label'>Zend Oauth: $zend</td>";
        $html .= '<td class="value"><a href="https://github.com/Magestore/Sociallogin-Magento1-Lib" target="_bank">' . $element->getLabel() . '</a>';
        $html .= '</td></tr>';
        return $html;
    }
}