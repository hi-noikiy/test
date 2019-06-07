<?php

class Gearup_Shippingffdx_Block_Adminhtml_Destination_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {
        parent::__construct();

        $this->_objectId = "id";
        $this->_blockGroup = "gearup_shippingffdx";
        $this->_controller = "adminhtml_destination";

        $this->_updateButton("save", "label", Mage::helper("gearup_sds")->__("Save Item"));
        $this->_updateButton("delete", "label", Mage::helper("gearup_sds")->__("Delete Item"));

        $this->_addButton("saveandcontinue", array(
            "label"     => Mage::helper("adminhtml")->__("Save And Continue Edit"),
            "onclick"   => "saveAndContinueEdit()",
            "class"     => "save",
        ), -100);

        $this->_formScripts[] = '
            function toggleEditor() {
                if (tinyMCE.getInstanceById("salesforce_content") == null) {
                    tinyMCE.execCommand("mceAddControl", false, "link_content");
                } else {
                    tinyMCE.execCommand("mceRemoveControl", false, "link_content");
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($("edit_form").action+"back/edit/");
            }
        ';
    }

    public function getHeaderText() {
        if ( Mage::registry("destination_data") && Mage::registry("destination_data")->getDestinationId() ) {
            return Mage::helper("gearup_sds")->__("Edit Item '%s'", $this->htmlEscape(Mage::registry("destination_data")->getDestination()));
        } else {
            return Mage::helper("gearup_sds")->__("Add Item");
        }
    }
}
