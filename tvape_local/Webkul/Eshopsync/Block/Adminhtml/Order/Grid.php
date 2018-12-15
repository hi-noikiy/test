<?php
/**
 * @category   Webkul
 * @package    Magento-Salesforce-Connector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
class Webkul_Eshopsync_Block_Adminhtml_Order_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('eshopsyncGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('sales/order')
                      ->getCollection()
                      ->addAttributeToSelect('entity_id')
                      ->addAttributeToSelect('increment_id');
        $prefix = Mage::getConfig()->getTablePrefix();
        $collection->getSelect()->joinLeft(
          array('order' => $prefix.'wk_salesforce_eshopsync_order_mapping'),
          'order.magento_id = main_table.entity_id',
          array('magento_id','sforce_id','account_id','created_at','error_hints')
        );

        if($this->getRequest()->getParam('sync') == 1){
          $collection->getSelect()->where("order.error_hints is null and order.magento_id is not null");
        }
        elseif($this->getRequest()->getParam('unsync') == 1){
          $collection->getSelect()->where("order.error_hints is not null or order.magento_id is null");
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('entity_id', array(
            'header'    => Mage::helper('eshopsync')->__('ID'),
            'align'     =>'center',
            'width'     => '100px',
            'filter_index' => 'entity_id',
            'index'     => 'entity_id',
        ));

        $this->addColumn('increment_id', array(
          'header'    => Mage::helper('eshopsync')->__('Order #'),
          'align'     =>'center',
          'width'     => '100px',
          'index'     => 'increment_id',
        ));

        $this->addColumn('magento_id',array(
          'header'  =>Mage::helper('eshopsync')->__('Magento Order Id'),
          'align'   =>'center',
          'index'   =>'magento_id',
        ));

        $this->addColumn('sforce_id', array(
          'header'    => Mage::helper('eshopsync')->__('Salesforce Order Id'),
          'align'     =>'center',
          'index'     => 'sforce_id',
        ));

        $this->addColumn('account_id', array(
          'header'    => Mage::helper('eshopsync')->__('Salesforce Account Id'),
          'align'     =>'center',
          'index'     => 'account_id',
        ));

        $this->addColumn('created_at', array(
            'header'    => Mage::helper('eshopsync')->__('Created At'),
            'align'     =>'center',
            'index'     => 'created_at',
        ));

        $this->addColumn('link', array(
            'header'    => Mage::helper('eshopsync')->__('Status'),
            'align'     =>'center',
            'index'     => '',
            'filter' => false,
            'renderer' => 'Webkul_Eshopsync_Block_Adminhtml_Renderer_DisplayError'
        ));

    		$this->addExportType('*/*/exportCsv', Mage::helper('eshopsync')->__('CSV'));
    		$this->addExportType('*/*/exportXml', Mage::helper('eshopsync')->__('XML'));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('eshopsync_id');
        $this->getMassactionBlock()->setFormFieldName('eshopsync');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('eshopsync')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('eshopsync')->__('Are you sure?')
        ));
        return $this;
    }
}
