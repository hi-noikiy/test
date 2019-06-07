<?php

class MageWorkshop_ImportExportReview_Block_Adminhtml_System_Config_ShareBetweenWebsites extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        if (count(Mage::app()->getWebsites()) > 1) {
            return parent::render($element);
        } else {
            $notice = $this->__('There is only one website in this Magento installation. This feature is applicable only for multi-website configuration');
            return <<<HTML
<ul class="messages">
    <li class="notice-msg"><ul>
        <li>$notice</li>
    </ul></li>
</ul>
HTML;
        }
    }
}