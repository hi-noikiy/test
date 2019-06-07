<?php

/**
 * justselling Germany Ltd. EULA
 * http://www.justselling.de/
 * Read the license at http://www.justselling.de/lizenz
 *
 * Do not edit or add to this file, please refer to http://www.justselling.de for more information.
 *
 * @category    justselling
 * @package     justselling_configurator
 * @copyright   Copyright � 2012 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
**/
 
class Justselling_Configurator_Block_Adminhtml_Configurator_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct()
	{
		parent::__construct();
		
		$this->_objectId = "id";
		$this->_blockGroup = "configurator";
		$this->_controller = "adminhtml_configurator";
		
		$this->_updateButton("save", "label", Mage::helper("configurator")->__("Save") );
		$this->_updateButton("delete", "label", Mage::helper("configurator")->__("Delete") );
	}
	
	protected function _prepareLayout()
	{
        $demo = Mage::helper("configurator")->getLocalConfiguration("demo");
        if ($demo !== 'true') {
            $this->addButton("export_button", array(
                'label' => Mage::helper('catalog')->__('Export'),
                'onclick' => 'setLocation(\'' . $this->getExportUrl() . '\')',
                'class' => 'export'
            ));
        }

		$this->addButton("duplicate_button", array(
			'label' => Mage::helper('catalog')->__('Duplicate'),
			'onclick' => 'setLocation(\'' . $this->getDuplicateUrl() . '\')',
			'class' => 'add'
		));

		$this->addButton("addoption_button", array(
			'label' => Mage::helper('catalog')->__('Add Option'),
			'class' => 'add add_new_defined_option'
		));


		$layout = parent::_prepareLayout();
		if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
			$this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
		}

		if (Mage::registry('configurator_data')) {
			// Get data from registry
			$template = Mage::registry('configurator_data');
			$data = $template->getData();
			
			// Decode serialized design array
            if (isset($data["design"])) {
                $design = unserialize( $data["design"]);
                if (isset($design["more_info_design"]))
                    $data["more_info_design"] = $design["more_info_design"];
                if (isset($design["blacklist_mode"]))
                    $data["blacklist_mode"] = $design["blacklist_mode"];
                if (isset($design["blacklist_text_display"]))
                    $data["blacklist_text_display"] = $design["blacklist_text_display"];
                if (isset($design["group_switch_before_validate"]))
                    $data["group_switch_before_validate"] = $design["group_switch_before_validate"];
                if (isset($design["blacklist_children_auto"])) {
                    $data["blacklist_children_auto"] = $design["blacklist_children_auto"];
                } else {
                    $data["blacklist_children_auto"] = 1; // Default Yes
                }
                if (isset($design["text2image_singleline"]))
                    $data["text2image_singleline"] = $design["text2image_singleline"];
                unset($data["design"]);
            }
			
			// Set new data array to registry
			$template->setData($data);
			Mage::unregister("configurator_data");
			Mage::register("configurator_data", $template);
		}		
		
		return $layout;
	}
	
	public function getDuplicateUrl()
    {
        return $this->getUrl('*/*/duplicate', array($this->_objectId => $this->getRequest()->getParam($this->_objectId)));
    }

    protected function getExportUrl()
    {
        return $this->getUrl('*/*/export', array($this->_objectId => $this->getRequest()->getParam($this->_objectId)));
    }
	
	public function getHeaderText()
	{
		if( Mage::registry("configurator_data") && Mage::registry("configurator_data")->getId() )
		{
			return Mage::helper("configurator")->__("Edit Template '%s'",$this->htmlEscape(Mage::registry("configurator_data")->getTitle()) );
		}
		else
		{
			return Mage::helper("configurator")->__("Add Template");
		}
	}
}