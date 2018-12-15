<?php
/**
 * @category   Webkul
 * @package    Magento-Salesforce-Connector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
class Webkul_Eshopsync_Block_Adminhtml_Category_Grid extends Mage_Adminhtml_Block_Widget_Grid
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
      //$collection = Mage::getModel('eshopsync/category')->getCollection();
      $collection = Mage::getModel('catalog/category')->getCollection();
      $prefix = Mage::getConfig()->getTablePrefix();
      $collection->getSelect()->joinLeft(
        array('cat' => $prefix.'wk_salesforce_eshopsync_category_mapping'),
        'cat.magento_id = e.entity_id',
        array('magento_id','sforce_id','created_by','created_at','need_sync','error_hints')
      );
      $collection->addAttributeToFilter('entity_id',array('nin' => 1));

      if($this->getRequest()->getParam('sync') == 1){
        $collection->getSelect()->where("cat.error_hints is null and cat.magento_id is not null");
      }
      elseif($this->getRequest()->getParam('unsync') == 1){
        $collection->getSelect()->where("cat.error_hints is not null or cat.magento_id is null");
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
          'filter_index' => 'e.entity_id',
          'index'     => 'entity_id',
      ));

      $this->addColumn('value', array(
          'header'    => Mage::helper('eshopsync')->__('Category Name'),
          'align'     =>'center',
          'index'     => 'value',
          'filter' => false,
          'renderer' => 'Webkul_Eshopsync_Block_Adminhtml_Renderer_Category'
      ));

      $this->addColumn('magento_id',array(
        'header'  =>Mage::helper('eshopsync')->__('Magento Category Id'),
        'align'   =>'center',
        'index'   =>'magento_id',
      ));

      $this->addColumn('sforce_id', array(
          'header'    => Mage::helper('eshopsync')->__('Salesforce Category Id'),
          'align'     =>'center',
          'index'     => 'sforce_id',
        ));

      $this->addColumn('created_by', array(
          'header'    => Mage::helper('eshopsync')->__('Created By'),
          'align'     =>'center',
          'index'     => 'created_by',
      ));

      $this->addColumn('created_at', array(
          'header'    => Mage::helper('eshopsync')->__('Created At'),
          'align'     =>'center',
          'index'     => 'created_at',
      ));

       $this->addColumn('need_sync',array(
        'header'  =>Mage::helper('eshopsync')->__('Need Sync(Status)'),
        'align'   =>'center',
        'index'   =>'need_sync',
      ));

    //   $this->addColumn('error_hints',array(
    //    'header'  =>Mage::helper('eshopsync')->__('Error Hints'),
    //    'align'   =>'left',
    //    'index'   =>'error_hints',
    //  ));

     $this->addColumn('link', array(
         'header'    => Mage::helper('eshopsync')->__('Status'),
         'align'     =>'center',
         'index'     => 'link',
         'filter' => false,
         'renderer' => 'Webkul_Eshopsync_Block_Adminhtml_Renderer_DisplayError'
     ));

		$this->addExportType('*/*/exportCsv', Mage::helper('eshopsync')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('eshopsync')->__('XML'));

      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('magento_id');
        $this->getMassactionBlock()->setFormFieldName('eshopsync');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('eshopsync')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('eshopsync')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('eshopsync/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('eshopsync')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('eshopsync')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));
        return $this;
    }

  // public function getRowUrl($row)
  // {
  //     return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  // }

}
