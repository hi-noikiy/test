<?php
/**
 * Mage SMS - SMS notification & SMS marketing
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the BSD 3-Clause License
 * It is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/BSD-3-Clause
 *
 * @category    TOPefekt
 * @package     TOPefekt_Magesms
 * @copyright   Copyright (c) 2012-2017 TOPefekt s.r.o. (http://www.mage-sms.com)
 * @license     http://opensource.org/licenses/BSD-3-Clause
 */
 class Topefekt_Magesms_Adminhtml_Magesms_HistoryController extends Topefekt_Magesms_Controller_Action { public function indexAction() { $this->_initAction(); $i8ee45e0018a32fb1a855b82624506e35789cc4d2 = $this->getLayout()->createBlock( 'Topefekt_Magesms_Block_Template', 'my_block_name_here', array('template' => 'topefekt/magesms/history.phtml') ); $this->getLayout()->getBlock('content')->append($i8ee45e0018a32fb1a855b82624506e35789cc4d2); $this->renderLayout(); return $this; } public function filterAction() { $iba20acc78644ac0e9cd48ea35d8ad03b058f6b5a = $this->getRequest()->getParams(); unset($iba20acc78644ac0e9cd48ea35d8ad03b058f6b5a['form_key']); $this->_redirect('*/*/', $iba20acc78644ac0e9cd48ea35d8ad03b058f6b5a); } public function deleteAction() { $i7af9c0bf5c8f0878a0f7c5463d75397834eda9fa = Mage::getSingleton('core/resource')->getTableName('magesms_smshistory'); Mage::getSingleton('core/resource')->getConnection('core_write')->query("TRUNCATE TABLE `$i7af9c0bf5c8f0878a0f7c5463d75397834eda9fa`"); Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('magesms')->__('SMS history was deleted.')); $this->_redirect('*/*/'); } public function exportCsvAction() { $i3ba0e99f358e315835fe8aca63713b157cd07b3f = Mage::getSingleton('core/resource')->getConnection('core_read'); $i7af9c0bf5c8f0878a0f7c5463d75397834eda9fa = $i3ba0e99f358e315835fe8aca63713b157cd07b3f->getTableName('magesms_smshistory'); $i7fb1970287c90ac449c81c05360a581fcdd5a6af = $i3ba0e99f358e315835fe8aca63713b157cd07b3f->describeTable($i7af9c0bf5c8f0878a0f7c5463d75397834eda9fa); $i25f6ca5af884f5fb6975c45037ba66d5f6838523 = ''; $ia61712c27ea241bd7a543dc2b02ea572274d0322 = array(); foreach ($i7fb1970287c90ac449c81c05360a581fcdd5a6af as $i8f64ce2d7476196ba335784d391aa0427bb41857=>$ia8a35a47a8e61218e15d1a33dac64bdc2449c01a) { $ia61712c27ea241bd7a543dc2b02ea572274d0322[] = '"'.$i8f64ce2d7476196ba335784d391aa0427bb41857.'"'; } $i25f6ca5af884f5fb6975c45037ba66d5f6838523.= implode(',', $ia61712c27ea241bd7a543dc2b02ea572274d0322)."\n"; $i70f8c28e8955b2f1f7dc0e997c564e780d249bea = Mage::getModel('magesms/smshistory')->getCollection(); foreach ($i70f8c28e8955b2f1f7dc0e997c564e780d249bea as $iff7e46827cbb6547116c592bf800f4687428abf9) { $ia61712c27ea241bd7a543dc2b02ea572274d0322 = array(); foreach ($iff7e46827cbb6547116c592bf800f4687428abf9->getData() as $i8f64ce2d7476196ba335784d391aa0427bb41857) { $ia61712c27ea241bd7a543dc2b02ea572274d0322[] = '"' . str_replace(array('"', '\\', "\n", "\r", "\n\r", "\r\n"), array('""', '\\\\', ' ', ' ', ' ', ' '), $i8f64ce2d7476196ba335784d391aa0427bb41857) . '"'; } $i25f6ca5af884f5fb6975c45037ba66d5f6838523.= implode(',', $ia61712c27ea241bd7a543dc2b02ea572274d0322)."\n"; } header('Content-Type: text/csv; charset=UTF-8'); header('Content-Disposition: attachment; filename="smshistory.csv'); die($i25f6ca5af884f5fb6975c45037ba66d5f6838523); } protected function _initAction() { parent::_initAction(); $this->_setActiveMenu('magesms/history') ->_title(Mage::helper('magesms')->__('SMS History')) ; return $this; } protected function _isAllowed() { return Mage::getSingleton('admin/session')->isAllowed('magesms/history'); } } 