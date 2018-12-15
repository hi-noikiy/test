<?php

class EM_AdvertiseLeft_Block_Adminhtml_AdvertiseLeft_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('advertiseleft_form', array('legend'=>Mage::helper('advertiseleft')->__('Item information')));
     
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('advertiseleft')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));

      $fieldset->addField('filename', 'file', array(
          'label'     => Mage::helper('advertiseleft')->__('File'),
          'required'  => false,
          'name'      => 'filename',
	  ));
		
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('advertiseleft')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('advertiseleft')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('advertiseleft')->__('Disabled'),
              ),
          ),
      ));
     
      $fieldset->addField('content', 'editor', array(
          'name'      => 'content',
          'label'     => Mage::helper('advertiseleft')->__('Content'),
          'title'     => Mage::helper('advertiseleft')->__('Content'),
          'style'     => 'width:700px; height:500px;',
          'wysiwyg'   => false,
          'required'  => true,
      ));
     
      if ( Mage::getSingleton('adminhtml/session')->getAdvertiseLeftData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getAdvertiseLeftData());
          Mage::getSingleton('adminhtml/session')->setAdvertiseLeftData(null);
      } elseif ( Mage::registry('advertiseleft_data') ) {
          $form->setValues(Mage::registry('advertiseleft_data')->getData());
      }
      return parent::_prepareForm();
  }
}