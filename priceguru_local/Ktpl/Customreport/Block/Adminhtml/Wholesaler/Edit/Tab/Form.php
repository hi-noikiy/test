<?php

class Ktpl_Customreport_Block_Adminhtml_Wholesaler_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('wholesaler_form', array('legend'=>Mage::helper('customreport')->__('Item information')));
     
      $fieldset->addField('name', 'text', array(
          'label'     => Mage::helper('customreport')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'name',
      ));

      $fieldset->addField('address', 'editor', array(
          'name'      => 'address',
          'label'     => Mage::helper('customreport')->__('Address'),
          'title'     => Mage::helper('customreport')->__('Address'),
          //'style'     => 'width:700px; height:400px;',
          'wysiwyg'   => false,
          'required'  => true,
      ));
		
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('customreport')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('customreport')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('customreport')->__('Disabled'),
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