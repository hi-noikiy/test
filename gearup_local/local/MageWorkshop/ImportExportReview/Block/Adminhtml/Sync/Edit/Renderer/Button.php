<?php

class MageWorkshop_ImportExportReview_Block_Adminhtml_Sync_Edit_Renderer_Button extends Varien_Data_Form_Element_Abstract
{
    /**
     * @return string
     */
    public function getElementHtml()
    {
        $id = $this->getId();
        $label = $this->getLabel();
        $attributes = $this->serialize($this->getHtmlAttributes());
        $html = "<button type='button' id='$id' $attributes>$label</button>";
        $html.= $this->getAfterElementHtml();
        return $html;
    }

    /**
     * @param string $idSuffix
     * @return string
     */
    public function getLabelHtml($idSuffix = '')
    {
        return '';
    }
}