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
class Justselling_Configurator_Block_Adminhtml_Log_Form_DownloadFiles
    extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface {
    
    
    /**
     * @see Mage_Core_Block_Abstract::_prepareLayout()
     */
    protected function _prepareLayout() {
        //$this->getLayout()->getBlock('head')->addItem('js_css', 'justselling/glsunibox/default.css');
        //$this->getLayout()->getBlock('head')->addItem('js', 'justselling/glsunibox/default.js');
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
		$storeCode   = Mage::app()->getRequest()->getParam('store');
		$storeId = (!is_null($storeCode)) ? Mage::getModel('core/store')->load($storeCode)->getId() : 0;
		$logFileNames = array();
		//$configuratorLogFileAbs = Mage::getBaseDir().DS.'var'.DS.'log'.DS.'configurator.log';
		if (file_exists($configuratorLogFileAbs)) {
			array_push($logFileNames, basename($configuratorLogFileAbs));
		}
        //$profilLogFileAbs = Mage::getBaseDir().DS.'var'.DS.'log'.DS.'profile.log';
        if (file_exists($profilLogFileAbs)) {
            array_push($logFileNames, basename($profilLogFileAbs));
        }
		if ($systemLog = Mage::getStoreConfig('dev/log/file', $storeId)) {
			array_push($logFileNames, $systemLog);
		}
		if ($exceptionLog = Mage::getStoreConfig('dev/log/exception_file', $storeId)) {
			array_push($logFileNames, $exceptionLog);
		}
		$html = '<table class="form-list adminbutton-list" cellspacing="0"><colgroup class="label"/><colgroup class="value"/>';
		$html .= '<tbody><tr>';
		$html .= '<td class="label">'.$element->getLabelHtml().'</td>';
		$html .= '<td style="padding-top:5px;">';
		foreach ($logFileNames as $logFileName) {
			$html .= $this->renderDownloadLogFile($logFileName);
		}
		$html .= '</td></tr></tbody></table>';
        return $html;
    }

	/**
	 * @param $logFileName
	 * @return string
	 */
	private function renderDownloadLogFile($logFileName) {
		$html  = '<input type="button" value="'.$logFileName.'" onclick="javascript:window.location.href=\''.$this->getUrl('prodconf/admin/logDownload', array('l'=>$logFileName)).'\'; return false;"> &nbsp;';
		return $html;
	}

}
?>