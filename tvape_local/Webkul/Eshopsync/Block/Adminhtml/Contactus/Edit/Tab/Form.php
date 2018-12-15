<?php
/**
 * @category   Webkul
 * @package    Magento-Salesforce-Connector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
class Webkul_Eshopsync_Block_Adminhtml_Contactus_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('eshopsync_form', array('legend'=>Mage::helper('eshopsync')->__('Contact Us information')));

      $fieldset->addField('name', 'text', array(
          'label'     => Mage::helper('eshopsync')->__('Name'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'name',
      ));

      $fieldset->addField('email', 'text', array(
          'label'     => Mage::helper('eshopsync')->__('Email'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'email',
      ));

      $fieldset->addField('sforce_id', 'text', array(
          'label'     => Mage::helper('eshopsync')->__('Salesforce Id'),
          'class'     => 'required-entry',
          'required'  => true,
          'disabled'   => true,
          'name'      => 'sforce_id',
      ));

      $fieldset->addField('phone', 'text', array(
          'label'     => Mage::helper('eshopsync')->__('Telephone'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'phone',
      ));

      $fieldset->addField('comment', 'editor', array(
          'name'      => 'comment',
          'label'     => Mage::helper('eshopsync')->__('Comment'),
          'title'     => Mage::helper('eshopsync')->__('Comment'),
          'style'     => 'width:500px; height:150px;',
          'wysiwyg'   => false,
          'required'  => true,
      ));

      if ( Mage::getSingleton('adminhtml/session')->getEshopsyncData())
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getEshopsyncData());
          Mage::getSingleton('adminhtml/session')->setEshopsyncData(null);
      } elseif ( Mage::registry('eshopsync_data')) {
          $form->setValues(Mage::registry('eshopsync_data')->getData());
      }
      return parent::_prepareForm();
  }
}
