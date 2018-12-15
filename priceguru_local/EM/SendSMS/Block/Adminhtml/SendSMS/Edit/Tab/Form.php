<?php

class EM_SendSMS_Block_Adminhtml_SendSMS_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('sendsms_form', array('legend'=>Mage::helper('sendsms')->__('Item information')));
     
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('sendsms')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));

      $fieldset->addField('filename', 'file', array(
          'label'     => Mage::helper('sendsms')->__('File'),
          'required'  => false,
          'name'      => 'filename',
	  ));
		
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('sendsms')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('sendsms')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('sendsms')->__('Disabled'),
              ),
          ),
      ));
     
      $fieldset->addField('content', 'editor', array(
          'name'      => 'content',
          'label'     => Mage::helper('sendsms')->__('Content'),
          'title'     => Mage::helper('sendsms')->__('Content'),
          'style'     => 'width:700px; height:500px;',
          'wysiwyg'   => false,
          'required'  => true,
      ));
     
      if ( Mage::getSingleton('adminhtml/session')->getSendSMSData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getSendSMSData());
          Mage::getSingleton('adminhtml/session')->setSendSMSData(null);
      } elseif ( Mage::registry('sendsms_data') ) {
          $form->setValues(Mage::registry('sendsms_data')->getData());
      }
      return parent::_prepareForm();
  }
}