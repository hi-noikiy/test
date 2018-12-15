<?php

class Ktpl_Repaircenter_Block_Adminhtml_Servicecenter_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('servicecenter_form', array('legend'=>Mage::helper('repaircenter')->__('Item information')));
     
      $fieldset->addField('service_name', 'text', array(
          'label'     => Mage::helper('repaircenter')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'service_name',
      ));

      $fieldset->addField('service_address', 'editor', array(
          'name'      => 'service_address',
          'label'     => Mage::helper('repaircenter')->__('Address'),
          'title'     => Mage::helper('repaircenter')->__('Address'),
          //'style'     => 'width:700px; height:400px;',
          'wysiwyg'   => false,
          'required'  => true,
      ));
      
      $fieldset->addField('service_latitude', 'text', array(
          'label'     => Mage::helper('repaircenter')->__('Latitude'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'service_latitude',
      ));

      $fieldset->addField('service_longitude', 'text', array(
          'label'     => Mage::helper('repaircenter')->__('Longitude'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'service_longitude',
      ));

      $fieldset->addField('service_status', 'select', array(
          'label'     => Mage::helper('repaircenter')->__('Status'),
          'name'      => 'service_status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('repaircenter')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('repaircenter')->__('Disabled'),
              ),
          ),
      ));
    
     
      if ( Mage::getSingleton('adminhtml/session')->getServicecenterData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getServicecenterData());
          Mage::getSingleton('adminhtml/session')->setServicecenterData(null);
      } elseif ( Mage::registry('servicecenter_data') ) {
          $form->setValues(Mage::registry('servicecenter_data')->getData());
      }
      return parent::_prepareForm();
  }
}