<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <info@mage-review.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_Core
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (https://mage-review.com/)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <info@mage-review.com>
 */

class MageWorkshop_Core_Block_System_Config_Form_Fieldset_Core_Extensions extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{

    protected $_fieldRenderer;
    protected $modules = array();

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = $this->_getHeaderHtml($element);
        $this->modules = array_keys((array)Mage::getConfig()->getNode('modules')->children());
        sort($this->modules);
        $count = 0;
        foreach ($this->modules as $moduleName) {
            if (strstr($moduleName,'MageWorkshop_') === false) {
                continue;
            }
            if ($moduleName === 'MageWorkshop_Core') {
                continue;
            }
            $required = $this->modulesInDepends($moduleName);
            $count++;

            $enableLabel = $this->__("Enable");
            $moduleContainer = new Varien_Object();
            $moduleContainer->setModule($moduleName);
            Mage::dispatchEvent('mageworkshop_modules_before_load', array('module_container' => $moduleContainer));
            $marginLeft = 20;
            if($moduleContainer->getEnabled()) {
                $enableLabel = $this->__("Disable");
            }
            if($moduleContainer->getEnabled() == 'noOption') {
                $enableLabel = $this->__("");
                $marginLeft = 0;
            }
            $html .= $this->_getFieldHtml($element, $moduleName);
            $html = substr($html, 0, strrpos($html, '<td class="value"'));
            if (Mage::helper('core')->isModuleEnabled($moduleName)) {
                $enableUrl = '\''. $this->getUrl('adminhtml/mageworkshop_core_main/enable', array('package' => $moduleName)).'\'';
                $uninstallUrl = '\''. $this->getUrl('adminhtml/mageworkshop_core_main/uninstall', array('package' => $moduleName)).'\'';
                $message = (count($required)) ?
                    '<a  href="#" style="color: #cacaca; margin-left:20px;" onclick="alert(\''. $this->__("There are dependent MageWorkshop modules(%s) installed. You can not uninstall %s module now.", implode(',', $required), $moduleName).'\')">'. $this->__("Uninstall") . '</a>':
                    '<a  href="#" style="margin-left:'.$marginLeft.'px;" onclick="if(confirm(\''. $this->__("This will completely uninstall extension and delete all related information. Are you sure?").'\')){setLocation('.$uninstallUrl.');}">'. $this->__("Uninstall") . '</a>' ;
                $html .= '<td class="value">
                            <a  href="#" onclick="setLocation('.$enableUrl.')">'. $enableLabel . '</a>
                            ' . $message. '
                            </td>';
            } else {
                $html .= '<td class="value"><span>'.$this->__("It seems like module is disabled via Disable Modules Output.").'</span></td>';
            }

        }
        $html .= $this->_getFieldHtml($element, 'MageWorkshop_Core');
        if($count > 0) {
            $html = substr($html, 0, strRpos($html, '<td class="value"'));
            $html .= '<td class="value">
                    <a  href="#" style="color: #cacaca;" onclick="alert(\''. $this->__("There are dependent MageWorkshop modules installed. You can not uninstall MageWorkshop Core module now.").'\')">'.
                $this->__("Uninstall") . '</a></td>';
        } else {
            $uninstallUrl = '\''. $this->getUrl('adminhtml/mageworkshop_core_main/uninstall', array('package' => 'MageWorkshop_Core')).'\'';
            $html = substr($html, 0, strrpos($html, '<td class="value"'));
            $html .= '<td class="value"><a  href="#" onclick="if(confirm(\''. $this->__("This will completely uninstall extension and delete all related information. Are you sure?").'\')){setLocation('.$uninstallUrl.');}">'. $this->__("Uninstall") . '</a></td>';
        }
        $html .= $this->_getFooterHtml($element);

        return $html;
    }

    protected function _getFieldRenderer()
    {
        if (empty($this->_fieldRenderer)) {
            $this->_fieldRenderer = Mage::getBlockSingleton('adminhtml/system_config_form_field');
        }
        return $this->_fieldRenderer;
    }


    protected function _getFieldHtml($fieldset, $moduleName)
    {
        $id = $moduleName;
        $field = $fieldset->addField($id, 'label',
            array(
                'name' => $id,
                'label' => $moduleName,
                'value' => $moduleName,
            ))->setRenderer($this->_getFieldRenderer());
        return $field->toHtml();
    }

    protected function modulesInDepends($moduleName)
    {
        $moduleInDepends = array();
        $mageWorkshopModules = array_filter($this->modules, function ($var) {
                                    return (stripos($var, 'MageWorkshop_') !== false);
                               });

        foreach ($mageWorkshopModules as $module) {
            $moduleDepends = array_keys((array)Mage::getConfig()->getModuleConfig($module)->depends);
            foreach ($moduleDepends as $depend) {
                if($moduleName === $depend) {
                    $moduleInDepends[$depend] = $module;
                }
            }
        }

        return $moduleInDepends;

    }
}
