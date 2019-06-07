<?php

class MageWorkshop_ImportExportReview_Block_Adminhtml_System_Config_Sync_Notice extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    /**
     * Return header comment part of html for fieldset
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getHeaderCommentHtml($element)
    {
        if (version_compare(phpversion(), '5.6.0', '>=')) {
            $rawPostDataValue = ini_get('always_populate_raw_post_data');
            if ($rawPostDataValue >= 0) {
                $notice = Mage::helper('mageworkshop_importexportreview')->__('Your are running PHP 5.6+. The "always_populate_raw_post_data" value should be set to "-1" in php.ini. Your current value is "%s"', $rawPostDataValue);
                return <<<HTML
<ul class="messages">
    <li class="notice-msg"><ul>
        <li>$notice</li>
    </ul></li>
</ul>
HTML;
            }
        }

        return parent::_getHeaderCommentHtml($element);
    }
}