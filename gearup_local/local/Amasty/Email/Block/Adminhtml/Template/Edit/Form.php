<?php
/**
* @copyright Amasty.
*/  
class Amasty_Email_Block_Adminhtml_Template_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form(array(
          'id' => 'edit_form',
          'action' => $this->getUrl('*/*/send'),
          'method' => 'post'));

      $form->setUseContainer(true);
      $this->setForm($form);
      $hlp = Mage::helper('amemail');

      $fldInfo = $form->addFieldset('general', array('legend'=> $hlp->__('Email Information')));
      
      $fldInfo->addField('subj', 'text', array(
          'label'     => $hlp->__('Subject'),
          'name'      => 'subj',
          'required'  => true,
      ));
      
      $fldInfo->addField('txt', 'textarea', array(
          'label'     => $hlp->__('Message'),
          'name'      => 'txt',
          'required'  => true,
          'style'     => 'width:55em;height:25em;',
      ));
      
      $fldInfo->addField('customers', 'hidden', array(
          'name'      => 'customers',
      ));
      $fldInfo->addField('orders', 'hidden', array(
          'name'      => 'orders',
      ));

      if (Mage::registry('amemail_template')) {
          $form->setValues(Mage::registry('amemail_template')->getData());
      }
      
      return parent::_prepareForm();
  }
}