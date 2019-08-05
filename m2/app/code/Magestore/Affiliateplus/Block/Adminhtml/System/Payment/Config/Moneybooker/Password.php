<?php

/**
 * Magestore.
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
 * @package     Magestore_Affiliateplus
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

namespace Magestore\Affiliateplus\Block\Adminhtml\System\Payment\Config\Moneybooker;

class Password extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var \Magestore\Affiliateplus\Helper\Config
     */
    protected $_configHelper;

    /**
     * DefaultPayment constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magestore\Affiliateplus\Helper\Config $configHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magestore\Affiliateplus\Helper\Config $configHelper,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_configHelper = $configHelper;
    }

    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element){
        $this->setElement($element);
        $id = $element->getHtmlId();
        $label = $element->getLabel();
        $html = '<tr id="row_' . $id . '">';
        $html .= '<td class="label">'.$label.'</td>';
        $html .= '<td class="value">'.$this->_toHtml().'</td>';
        return $html;
    }

    public function getValue($storeId = 0) {
        $value = $this->_configHelper->getConfig('affiliateplus_payment/moneybooker/moneybooker_email', $storeId);
        return $value;
    }

    protected function _toHtml() {
        $value = $this->getValue();
        $storeId = $this->getRequest()->getParam('store', 0);
        $valueStore = $this->getValue($storeId);

        $disabled = '';
        $url = $this->getUrl('affiliateplusadmin/payment/verifyMoneybooker');
        if($storeId && ($value == $valueStore)) $disabled = 'disabled';

        return '<input onchange="changeValue()" id="affiliateplus_payment_moneybooker_moneybooker_password" name="groups[moneybooker][fields][moneybooker_password][value]" value="'.$valueStore.'" class=" input-text" type="password" '.$disabled.'>
            <p>
                <div id="button-verify-moneybooker-check">
                    <button id="btn-not-verified" onclick="verify();return false;"><span><span><span></span>Check Moneybooker Account</span></span></button>
                </div>
            </p>
            <script type="text/javascript">
                function verify(){
                    var url = "'.$url.'";
                    var use_default = $("affiliateplus_payment_moneybooker_user_mechant_email_default").value;
                    if(use_default)
                        url += "?default="+use_default;
                    var email = $("affiliateplus_payment_moneybooker_moneybooker_email").value;
                    if(email)
                        url += "&email="+email;
                    var password = $("affiliateplus_payment_moneybooker_moneybooker_password").value;
                    if(password)
                        url += "&password="+password;
                    var subject = $("affiliateplus_payment_moneybooker_notification_subject").value;
                    if(subject)
                        url += "&subject="+subject;
                    var note = $("affiliateplus_payment_moneybooker_notification_note").value;
                    if(note)
                        url += "&note="+note;

                    var request = new Ajax.Request(url,{
                        onSuccess: function(response){
                            if(response.responseText == 1){
                                alert("Moneybooker account is valid.");
                            }else{
                                alert(response.responseText);
                            }
                        }
                    });
                }
                function changeValue(){
                    $("btn-not-verified").style.display = "";
                    $("link-verified").style.display = "none";
                }
            </script>
        ';
    }
}