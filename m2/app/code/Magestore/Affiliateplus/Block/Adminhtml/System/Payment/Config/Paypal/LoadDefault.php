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

class LoadDefault extends \Magento\Config\Block\System\Config\Form\Field
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
        $value = $this->_configHelper->getConfig('affiliateplus_payment/paypal/sandbox_mode', $storeId);
        return $value;
    }

    protected function _toHtml() {
        $value = $this->getValue();
        $select = 'selected="selected"';
        $selectYes = '';
        $selectNo = '';
        if ($value)
            $selectYes = $select;
        else
            $selectNo = $select;

        $default = $this->_configHelper->getConfig('affiliateplus_payment/paypal/user_mechant_email_default');
        $style = '';
        if($default) $style = '$("row_affiliateplus_payment_paypal_paypal_email").style.display = "none";
                            $("row_affiliateplus_payment_paypal_api_username").style.display = "none";
                            $("row_affiliateplus_payment_paypal_api_password").style.display = "none";
                            $("row_affiliateplus_payment_paypal_api_signature").style.display = "none";
                            $("row_affiliateplus_payment_paypal_sandbox_mode").style.display = "none";';
        return '<select id="affiliateplus_payment_paypal_sandbox_mode" name="groups[paypal][fields][sandbox_mode][value]" class=" select">
                <option value="1" ' . $selectYes . '>Yes</option>
                <option value="0" ' . $selectNo . '>No</option>
                </select>
                <script type="text/javascript">
                    '.$style.'
                </script>';
    }
}