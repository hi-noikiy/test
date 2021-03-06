<?php
/**
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Sociallogin
 * @module      Sociallogin
 * @author      Magestore Developer
 *
 * @copyright   Copyright (c) 2016 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 *
 */

class Magestore_Sociallogin_Block_Adminhtml_Twlogin_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Magestore_Sociallogin_Block_Adminhtml_Twlogin_Grid constructor.
     */
    public function __construct()
  {
      parent::__construct();
      $this->setId('twloginGrid');
      $this->setDefaultSort('twlogin_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

    /**
     * @return mixed
     */
    protected function _prepareCollection()
  {
      $collection = Mage::getModel('twlogin/twlogin')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

    /**
     * @return mixed
     */
    protected function _prepareColumns()
  {
      $this->addColumn('twlogin_id', array(
          'header'    => Mage::helper('sociallogin')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'twlogin_id',
      ));

      $this->addColumn('title', array(
          'header'    => Mage::helper('sociallogin')->__('Title'),
          'align'     =>'left',
          'index'     => 'title',
      ));

	  /*
      $this->addColumn('content', array(
			'header'    => Mage::helper('sociallogin')->__('Item Content'),
			'width'     => '150px',
			'index'     => 'content',
      ));
	  */

      $this->addColumn('status', array(
          'header'    => Mage::helper('sociallogin')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => array(
              1 => 'Enabled',
              2 => 'Disabled',
          ),
      ));
	  
        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('sociallogin')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('sociallogin')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('sociallogin')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('sociallogin')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('twlogin_id');
        $this->getMassactionBlock()->setFormFieldName('sociallogin');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('sociallogin')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('sociallogin')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('twlogin/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('sociallogin')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('sociallogin')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));
        return $this;
    }

    /**
     * @param $row
     * @return mixed
     */
    public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }

}