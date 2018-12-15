<?php
class EM_LayeredNavigation_Block_Adminhtml_Filter_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'layerednavigation';
        $this->_controller = 'adminhtml_filter';
        
		$this->removeButton('delete');
        $this->_updateButton('save', 'label', Mage::helper('layerednavigation')->__('Save'));
        $this->_addButton('apply', array(
            'label'     => Mage::helper('adminhtml')->__('Apply'),
            'onclick'   => 'apply()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function apply(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText() {
		$model = Mage::registry('layerednavigation_filter');
        if($model && $model->getId()) {
            return Mage::helper('layerednavigation')->__("Edit Filter '%s'", $this->htmlEscape($model->getTitle()));
        } else {
			return '';
        }
    }
}