<?php
class EM_Slideshow3_Block_Adminhtml_Slider_Edit_Tab_General extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('slideshow3_general', array('legend'=>Mage::helper('slideshow3')->__('General')));

		$fieldset->addField('name', 'text', array(
		  'label'     => Mage::helper('slideshow3')->__('Name'),
		  'class'     => 'required-entry',
		  'required'  => true,
		  'name'      => 'name',
		));

		// status field
		$fieldset->addField('status_slideshow', 'select', array(
			'label'     => Mage::helper('slideshow3')->__('Status'),
			'title'     => Mage::helper('slideshow3')->__('Status'),
			'name'      => 'status_slideshow',
			'required'  => true,
			'options'   => array(
				'1' => Mage::helper('slideshow3')->__('Enabled'),
				'0' => Mage::helper('slideshow3')->__('Disabled'),
			),
		));
		$fieldset->addField('slider_type', 'select', array(
			'label'     => Mage::helper('slideshow3')->__('Type'),
			'title'     => Mage::helper('slideshow3')->__('Type'),
			'name'      => 'slider_type',
			'required'  => true,
			'options'   => array(
				'1' => Mage::helper('slideshow3')->__('Vertical'),
				'0' => Mage::helper('slideshow3')->__('Horizontal'),
			),
		));
		// $fieldset->addField('slider_type', 'radios', array(
		  // 'label'     => Mage::helper('slideshow3')->__('Slider Type'),
		  // 'class'     => 'slider_type',
		  // 'name'      => 'slider_type',
		  // 'values'    => array(
			  // array(
				  // 'value'     => 'fixed',
				  // 'label'     => Mage::helper('slideshow3')->__('Fixed'),
			  // ),
			  // array(
				  // 'value'     => 'responsitive',
				  // 'label'     => Mage::helper('slideshow3')->__('Responsitive'),
			  // ),
			  // array(
				  // 'value'     => 'fullwidth',
				  // 'label'     => Mage::helper('slideshow3')->__('Fullwidth'),
			  // ),
		  // ),
		// ));
		



      if ( Mage::getSingleton('adminhtml/session')->getSlideshow3Data() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getSlideshow3Data());
          Mage::getSingleton('adminhtml/session')->setSlideshow3Data(null);
      } elseif ( Mage::registry('slideshow3_data') ) {
          $form->setValues(Mage::registry('slideshow3_data')->getData());
      }
      return parent::_prepareForm();
  }
}