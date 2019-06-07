<?php

class MageWorkshop_ImportExportReview_Block_Adminhtml_Profile_Edit_Form_Renderer_Mapping extends Varien_Data_Form_Element_Abstract
{
    /**
     * @return string
     */
    public function getElementHtml()
    {
        $html = "<div id='". $this->getId()."'>";
        $html .= '<input id="'.$this->getHtmlId().'_inDatabase" class="input-text" readonly="readonly" style="display:inline;" name="'.$this->getName()
            .'[code]" value="'.$this->getRatingCode().'" '.$this->serialize($this->getHtmlAttributes()).'/>'."\n";
        $html .= '<input id="'.$this->getHtmlId().'_inFile" class="input-text" style="display:inline;" name="'.$this->getName()
            .'[inFile]" value="'.$this->getMappingValue().'" '.$this->serialize($this->getHtmlAttributes()).'/>'."\n";
        $html .= '<input id="'.$this->getHtmlId().'_mappingId" type="hidden" name="'.$this->getName()
            .'[mappingId]" value="'.$this->getMappingEntityId().'" />'."\n";
        $html .= "</div>";

        $html.= $this->getAfterElementHtml();
        return $html;
    }
}