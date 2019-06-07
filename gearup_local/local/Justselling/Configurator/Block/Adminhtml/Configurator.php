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
 * @copyright   Copyright (C) 2012 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
 */

class Justselling_Configurator_Block_Adminhtml_Configurator extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = "adminhtml_configurator";
        $this->_blockGroup = "configurator";
        $this->_headerText = Mage::helper("configurator")->__("Template Manager");
        $this->_addButtonLabel = Mage::helper("configurator")->__("Add Template");

        $obj = new Justselling_Configurator_Block_Loading;
        $obj->checkLicense();


		$this->_addButton('import', array(
			'label'     => Mage::helper("configurator")->__("Import Template"),
			'class'     => Mage::helper("adminhtml")->getUrl("prodconf/admin/import/", array("key" => Mage::getSingleton('adminhtml/url')->getSecretKey("admin","import"))),
			'id'		=> 'import_template',
			'onclick' =>  str_replace("index.php/", "", Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK, true))
		));


        $defaultFile = "var" .DS ."imports" .DS ."default.zip";
        if (file_exists(str_replace('//','/',$defaultFile))){
            $this->_addButton('import_default', array(
                'label'     => Mage::helper("configurator")->__("Start import by default.zip file"),
                'id'		=> 'import_default_template',
                'onclick' =>  "ConfiguratorTemplate.startImportByFile('" .Mage::helper("adminhtml")->getUrl("prodconf/admin/import/", array("key" => Mage::getSingleton('adminhtml/url')->getSecretKey("admin","import")))."filename/default.zip');"
            ));
        }

        parent::__construct();

    }

}