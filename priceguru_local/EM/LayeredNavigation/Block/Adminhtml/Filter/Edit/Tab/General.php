<?php
class EM_LayeredNavigation_Block_Adminhtml_Filter_Edit_Tab_General extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm() {
		$model = Mage::registry('layerednavigation_filter');
		$form = new Varien_Data_Form();

		$fieldset = $form->addFieldset('base_fieldset', array(
			'legend' => Mage::helper('layerednavigation')->__('General Information')
		));

        $fieldset->addField('position', 'text', array(
            'name'      => 'position',
            'label'     => Mage::helper('layerednavigation')->__('Position'),
            'title'     => Mage::helper('layerednavigation')->__('Position'),
            'disabled'  => true,
        ));

        $fieldset->addField('title', 'text', array(
            'name'      => 'title',
            'label'     => Mage::helper('layerednavigation')->__('Title'),
            'title'     => Mage::helper('layerednavigation')->__('Title'),
            'disabled'  => true,
        ));

        $fieldset->addField('display_as', 'select', array(
            'name'      => 'display_as',
            'label'     => Mage::helper('layerednavigation')->__('Display as'),
            'title'     => Mage::helper('layerednavigation')->__('Display as'),
            'required'  => true,
            'options'   => Mage::helper('layerednavigation')->getDisplayOptions($model)
        ));

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', array(
                'name' => 'id',
            ));
        }

        $form->setValues($model->getData());
		$this->setForm($form);
		return parent::_prepareForm();
	}
}
