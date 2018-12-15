<?php

class EM_Link_Block_Adminhtml_Link_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('link_form', array('legend'=>Mage::helper('link')->__('Item information')));
     
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('link')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));

      $fieldset->addField('filename', 'file', array(
          'label'     => Mage::helper('link')->__('File'),
          'required'  => false,
          'name'      => 'filename',
	  ));
		
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('link')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('link')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('link')->__('Disabled'),
              ),
          ),
      ));
     
      $fieldset->addField('content', 'editor', array(
          'name'      => 'content',
          'label'     => Mage::helper('link')->__('Content'),
          'title'     => Mage::helper('link')->__('Content'),
          'style'     => 'width:700px; height:500px;',
          'wysiwyg'   => false,
          'required'  => true,
      ));
     
      if ( Mage::getSingleton('adminhtml/session')->getLinkData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getLinkData());
          Mage::getSingleton('adminhtml/session')->setLinkData(null);
      } elseif ( Mage::registry('link_data') ) {
          $form->setValues(Mage::registry('link_data')->getData());
      }
      return parent::_prepareForm();
  }
}