<?php

class MageWorkshop_ImportExportReview_Block_Adminhtml_Profile_Edit_Form_Renderer_Notice extends Varien_Data_Form_Element_Abstract
{
    /**
     * @return string
     */
    public function getElementHtml()
    {
        $id    = $this->getId();
        $class = $this->getClass();
        $text  = $this->getText();

        return "<p style='padding: 8px 8px 8px 40px;' class='$class' id='$id'>$text</p>";
    }
}