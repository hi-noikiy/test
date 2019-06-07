<?php
/**
 * justselling Germany Ltd. EULA
 * http://www.justselling.de/
 * Read the license at http://www.justselling.de/lizenz
 *
 * Do not edit or add to this file, please refer to http://www.justselling.de for more information.
 *
 * @category    justselling
 * @package     justselling_
 * @copyright   Copyright (c) 2013 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
 * @author      Bodo Schulte
 */
class Justselling_Configurator_Block_Adminhtml_Export_Form_ImportTrigger
    extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface {
    
    
    /**
     * @see Mage_Core_Block_Abstract::_prepareLayout()
     */
    protected function _prepareLayout() {
        return parent::_prepareLayout();
    }
    
    /**
     * Enter description here...
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
        return $element->getElementHtml();
    }
    
    /**
     * @see Varien_Data_Form_Element_Renderer_Interface::render()
     */
    public function render(Varien_Data_Form_Element_Abstract $element) {
		$html = '<table class="form-list" cellspacing="0"><colgroup class="label"/><colgroup class="value"/>';
		$html .= '<tbody><tr>';
		$html .= '<td class="label">'.$element->getLabelHtml().'</td>';
		$html .= '<td style="padding-top:5px;" class="value">';
        $html .= '  <input type="text" name="template-file" id="import-file" style="margin-right:10px; width:160px;" class="input-text">';
        $html .= '  <input type="button" value="'.$this->__('Start').'" onclick="javascript:window.location.href=\''.$this->getUrl('prodconf/admin/import').'?filename=\'+document.getElementById(\'import-file\').value; return false;"> &nbsp;';
        $html .= '  <p class="note">'.$this->__('Enter name of file which exists in &lt;magento&gt;/var/imports/').'</p>';
		$html .= '</td></tr></tbody></table>';
        return $html;
    }
}
?>