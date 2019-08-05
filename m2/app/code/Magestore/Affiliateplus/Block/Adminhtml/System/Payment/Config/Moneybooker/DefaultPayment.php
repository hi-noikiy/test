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

class DefaultPayment extends \Magento\Config\Block\System\Config\Form\Field
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
        $value = $this->_configHelper->getConfig('affiliateplus_payment/moneybooker/user_mechant_email_default', $storeId);
        return $value;
    }

    protected function _toHtml() {
        $value = $this->getValue();
        $storeId = $this->getRequest()->getParam('store', 0);
        $valueStore = $this->getValue($storeId);
        $select = 'selected="selected"';
        $selectYes = '';
        $selectNo = '';
        if ($value){
            $selectYes = $select;
        }else{
            $selectNo = $select;
        }
        $disabled = '';
        if($storeId && ($value == $valueStore)) $disabled = 'disabled';
        return '<select id="affiliateplus_payment_moneybooker_user_mechant_email_default" name="groups[moneybooker][fields][user_mechant_email_default][value]" onchange="changeValueSelect()" '.$disabled.' class=" select">
                <option value="1" '.$selectYes.'>Yes</option>
                <option value="0" '.$selectNo.'>No</option>
                </select>
                <script type="text/javascript">
                    function changeValueSelect(){
                        if($("affiliateplus_payment_moneybooker_user_mechant_email_default").value == "1"){
                            if($("row_affiliateplus_payment_moneybooker_moneybooker_email"))
                                $("row_affiliateplus_payment_moneybooker_moneybooker_email").style.display = "none";
                        }else{
                            if($("row_affiliateplus_payment_moneybooker_moneybooker_email"))
                                $("row_affiliateplus_payment_moneybooker_moneybooker_email").style.display = "";
                        }
                    }
                </script>
        ';
    }
}