<?php
/**
 * @category   Webkul
 * @package    Magento-Salesforce-Connector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
class Webkul_Eshopsync_Block_Adminhtml_Category_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('eshopsync_form', array('legend'=>Mage::helper('eshopsync')->__('Item information')));

      $magento_categories = Mage::getSingleton('eshopsync/category')->getMageCategoryArray();
      $sforce_categories = Mage::getSingleton('eshopsync/category')->getSforceCategoryArray();

      $fieldset->addField('magento_id', 'select', array(
          'label'     => Mage::helper('eshopsync')->__('Magento Category'),
          'class'     => 'required-entry',
          'name'      =>'magento_id',
          'values'    => $magento_categories
      ));

      $fieldset->addField('sforce_id', 'select', array(
          'label'     => Mage::helper('eshopsync')->__('Salesforce Category'),
          'class'     => 'required-entry',
          'name'    =>'sforce_id',
          'values'    => $sforce_categories
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
