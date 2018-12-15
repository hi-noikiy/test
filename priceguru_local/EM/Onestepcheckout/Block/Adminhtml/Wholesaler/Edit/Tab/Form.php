<?php

class EM_Onestepcheckout_Block_Adminhtml_Wholesaler_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('wholesaler_form', array('legend'=>Mage::helper('onestepcheckout')->__('Item information')));
     
      $fieldset->addField('name', 'text', array(
          'label'     => Mage::helper('onestepcheckout')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'name',
      ));

      $fieldset->addField('address', 'editor', array(
          'name'      => 'address',
          'label'     => Mage::helper('onestepcheckout')->__('Address'),
          'title'     => Mage::helper('onestepcheckout')->__('Address'),
          //'style'     => 'width:700px; height:400px;',
          'wysiwyg'   => false,
          'required'  => true,
      ));
		
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('onestepcheckout')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('onestepcheckout')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('onestepcheckout')->__('Disabled'),
              ),
          ),
      ));
    
     
      if ( Mage::getSingleton('adminhtml/session')->getWholesalerData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getWholesalerData());
          Mage::getSingleton('adminhtml/session')->setWholesalerData(null);
      } elseif ( Mage::registry('wholesaler_data') ) {
          $form->setValues(Mage::registry('wholesaler_data')->getData());
      }
      return parent::_prepareForm();
  }
}