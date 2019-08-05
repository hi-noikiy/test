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
namespace Magestore\Affiliateplus\Block\Adminhtml\System\Payment\Config\Paypal;

class Signature extends \Magento\Config\Block\System\Config\Form\Field
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

    public function getValue() {
        $storeId = $this->getRequest()->getParam('store', 0);
        $value = $this->_configHelper->getConfig('affiliateplus_payment/paypal/api_signature', $storeId);
        return $value;
    }

    protected function _toHtml() {
        $value = $this->getValue();

        return '<input id="affiliateplus_payment_paypal_api_signature" name="groups[paypal][fields][api_signature][value]" value="'.$value.'" class=" input-text" type="text">

        <div style="width:100%; margin-top:7px;">
            <button style="float:left;"id="" type="button" class="" onclick="credentials()">
                <span>Get Credentials from PayPal</span>
            </button>
            <button style="float:right;" id="" type="button" class="" onclick="sandbox()">
                <span>Sandbox Credentials</span>
            </button>
        </div>
        <script type="text/javascript">
             function sandbox(){
                window.open(\'https://www.sandbox.paypal.com/us/cgi-bin/webscr?cmd=_login-api-run\', \'apiwizard\',\'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, ,left=100, top=100, width=380, height=470\'); return false;
             }
             function credentials(){
                window.open(\'https://www.paypal.com/us/cgi-bin/webscr?cmd=_login-api-run\', \'apiwizard\',\'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, ,left=100, top=100, width=380, height=470\'); return false;
             }
         </script>
        ';
    }
}