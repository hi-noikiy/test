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
 class Topefekt_Magesms_Block_Answers_Grid extends Mage_Adminhtml_Block_Widget_Grid { public function __construct() { parent::__construct(); $this->setSaveParametersInSession(false); $this->setDefaultSort('cas'); $this->setDefaultDir('DESC'); } protected function _prepareCollection() { $iff7e46827cbb6547116c592bf800f4687428abf9 = Mage::getResourceModel('magesms/answers_collection'); $this->setCollection($iff7e46827cbb6547116c592bf800f4687428abf9); parent::_prepareCollection(); return $this; } protected function _prepareColumns() { $this->addColumn('from', array( 'header'=>Mage::helper('magesms')->__('From number'), 'width' => '150px', 'index' => 'from', 'align' => 'right', ) ); $this->addColumn('text', array( 'index' => 'text', 'header'=>Mage::helper('magesms')->__('Text'), ) ); $this->addColumn('cas', array( 'header'=>Mage::helper('magesms')->__('Date'), 'width' => '150px', 'index' => 'cas', 'type' => 'datetime', ) ); $this->addColumn('smsc', array( 'header'=>Mage::helper('magesms')->__('SMS center'), 'width' => '150px', 'index' => 'smsc', 'align' => 'right', 'renderer' => 'Topefekt_Magesms_Block_Answers_Renderer_Smsc', ) ); if (Mage::getSingleton('admin/session')->isAllowed('magesms/answers/mark_as_read') || Mage::getSingleton('admin/session')->isAllowed('magesms/answers/remove')) { $this->addColumn('action', array( 'header' => Mage::helper('magesms')->__('Action'), 'width' => '200px', 'sortable' => false, 'filter' => false, 'type' => 'action', 'is_system' => true, 'renderer' => 'Topefekt_Magesms_Block_Answers_Renderer_Actions' ) ); } $this->addExportType('*/*/exportCsv', Mage::helper('magesms')->__('CSV')); $this->addExportType('*/*/exportExcel', Mage::helper('magesms')->__('Excel XML')); return parent::_prepareColumns(); } protected function _prepareMassaction() { $this->setMassactionIdField('IDs'); $this->getMassactionBlock()->setFormFieldName('answers'); if (Mage::getSingleton('admin/session')->isAllowed('magesms/answers/mark_as_read')) { $this->getMassactionBlock()->addItem('mark_as_read', array( 'label' => Mage::helper('magesms')->__('Mark as Read'), 'url' => $this->getUrl('*/*/massMarkAsRead', array('_current'=>true)), )); } if (Mage::getSingleton('admin/session')->isAllowed('magesms/answers/remove')) { $this->getMassactionBlock()->addItem('remove', array( 'label' => Mage::helper('magesms')->__('Remove'), 'url' => $this->getUrl('*/*/massRemove'), 'confirm' => Mage::helper('magesms')->__('Are you sure?') )); } return $this; } protected function getNoFilterMassactionColumn(){ return true; } public function getRowClass(Varien_Object $iebe3a16a01f87f9a4ebbb9731163db3e3e64cc3d) { return $iebe3a16a01f87f9a4ebbb9731163db3e3e64cc3d->getProhlednuto() ? 'read' : 'unread'; } public function getRowClickCallback() { return false; } } 