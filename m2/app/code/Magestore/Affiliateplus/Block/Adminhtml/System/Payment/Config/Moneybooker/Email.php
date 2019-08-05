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

class Email extends \Magento\Config\Block\System\Config\Form\Field
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

        $default = $this->_configHelper->getConfig('affiliateplus_payment/moneybooker/user_mechant_email_default');
        $defaultStore = $this->_configHelper->getConfig('affiliateplus_payment/moneybooker/user_mechant_email_default',$storeId);

        $style = '';
        $display = '';
        $disabled = '';
        $checked = '';
        if($defaultStore) $display = 'none';
        if($default == $defaultStore) $checked = 'checked';
        if($storeId && ($value == $valueStore)) $disabled = 'disabled';
        if($default) $style = '$("row_affiliateplus_payment_moneybooker_moneybooker_email").style.display = "none"';

        return '<input id="affiliateplus_payment_moneybooker_moneybooker_email" name="groups[moneybooker][fields][moneybooker_email][value]" value="'.$valueStore.'" class=" input-text" '.$disabled.' type="text">
                <script type="text/javascript">
                    $("row_affiliateplus_payment_moneybooker_moneybooker_email").style.display="'.$display.'";
                    if($("affiliateplus_payment_moneybooker_user_mechant_email_default_inherit"))
                        $("affiliateplus_payment_moneybooker_user_mechant_email_default_inherit").checked = "'.$checked.'"
                    '.$style.'
                </script>
        ';
    }
}