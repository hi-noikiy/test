<?php

/**
 * Magestore.
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
 * @package     Magestore_Affiliateplusprogram
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */
namespace Magestore\Affiliateplusprogram\Block\Adminhtml\Program\Edit\Tab;

use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Tab\TabInterface;

/**
 * Class Account
 * @package Magestore\Affiliateplusprogram\Block\Adminhtml\Program\Edit\Tab
 */
class Program extends \Magento\Backend\Block\Widget\Grid\Extended implements TabInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    /**
     * Account constructor.
     * @param Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param array $data
     */
    public function __construct(
        \Magestore\Affiliateplusprogram\Block\Adminhtml\Program\Edit\Tab\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->_backendSession = $context->getBackendSession();
        $this->_storeManager = $context->getStoreManager();
        $this->_objectManager =  $context->getObjectManager();
        parent::__construct
        (
            $context,
            $backendHelper,
            $data
        );
    }

    /**
     * @return void
     */
    protected function _construct() {
        parent::_construct();
        $this->setId('programGrid');
        $this->setDefaultSort('program_id');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        if ($this->getAccount() && $this->getAccount()->getId()){
            $this->setDefaultFilter(
                [
                    'in_programs' => 1
                ]
            );
        }
    }

    /**
     * @return array|null
     */

    /**
     * @param Column $column
     *
     * @return $this
     */
    protected function _addColumnFilterToCollection($column) {
        // Set custom filter for in category flag
        if ($column->getId() == 'in_programs'){
            $programIds = $this->_getSelectedPrograms();
            if (empty($programIds)) {
                $programIds = 0;
            }
            if ($column->getFilter()->getValue()){
                $this->getCollection()->addFieldToFilter('program_id', array('in'=>$programIds));
            }elseif ($programIds){
                $this->getCollection()->addFieldToFilter('program_id', array('nin'=>$programIds));
            }
            return $this;
        }else {
            parent::_addColumnFilterToCollection($column);
        }

        return $this;
    }

    /**
     * @return Grid
     */
    protected function getStore() {
        $storeId = (int) $this->getRequest()->getParam('store', 0);

        return $this->_storeManager->getStore($storeId);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection() {
        $collection = $this->_objectManager->create('Magestore\Affiliateplusprogram\Model\Program')->getCollection();
        if ($storeId = $this->getStore()->getId()){
            $collection->setStoreId($storeId);
        }
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }


    protected function _prepareColumns() {

        $currencyCode = $this->_storeManager->getStore()->getCurrentCurrency()->getCode();

        $this->addColumn(
            'in_programs',
            [
                'type' => 'checkbox',
                'name' => 'in_programs',
                'values' => $this->_getSelectedPrograms(),
                'index' => 'program_id',
                'header_css_class' => 'col-select col-massaction',
                'column_css_class' => 'col-select col-massaction',
                'align'            => 'center',
                'use_index' => true
            ]
        );
        $this->addColumn(
            'program_id',
            [
                'header' => __('ID'),
                'sortable' => true,
                'index' => 'program_id',
                'width'     => '60',
                'align' => 'right'
            ]
        );
        $this->addColumn(
            'program_name',
            [
                'header' => __('Program Name'),
                'index' => 'name',
                'align'     => 'left'
            ]
        );
        $this->addColumn(
            'program_num_account',
            [
                'header' => __('Number of Accounts'),
                'index' => 'num_account',
                'align'     => 'left'
            ]
        );

//        $this->addColumn(
//            'account_balance',
//            [
//                'header' => __('Balance'),
//                'width'     => '100px',
//                'align'     => 'right',
//                'index'     => 'balance',
//                'type'		=> 'price',
//                'currency_code' => $currencyCode,
//            ]
//        );

        $this->addColumn(
            'program_total_sales_amount',
            [
                'header' => __('Total Amount'),
                'align'     => 'left',
                'index'     => 'total_sales_amount',
                'type'		=> 'price',
                'currency_code' => $currencyCode,
            ]
        );

        $this->addColumn(
            'program_created_date',
            [
                'header' => __('Date Created'),
                'align'     => 'left',
                'index'     => 'created_date',
                'type'		=> 'date'
            ]
        );

        $this->addColumn(
            'program_status',
            [
                'header' => __('Status'),
                'align'     => 'left',
                'width'     => '80px',
                'index'     => 'status',
                'type'      => 'options',
                'options'   => \Magestore\Affiliateplusprogram\Model\Status::getAvailableStatuses()
            ]
        );

        $this->addColumn(
            'position',
            [
                'header' => __('Position'),
                'type' => 'number',
                'index' => 'position',
                'editable' => true,
                'filter' => false,
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl() {
        return $this->getUrl('*/*/programGrid',
            [
                '_current' => true,
                'id'		=>$this->getRequest()->getParam('id'),
                'store'		=>$this->getRequest()->getParam('store')
            ]
        );
    }

    /**
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\DataObject $row
     * @return string
     */
    public function getRowUrl($row) {
        return $this->getUrl('affiliateplusadmin/program/edit', array(
            'program_id' => $row->getId(),
            'store' => $this->getRequest()->getParam('store')
        ));
    }

    /**
     * @return array
     */
    protected function _getSelectedPrograms(){
        $programs = $this->getRequest()->getParam('oprogram');

        if (!is_array($programs)){
            $programs = array_keys($this->getSelectedRelatedPrograms());
        }
        return $programs;
    }

    /**
     * @return array
     */
    public function getSelectedRelatedPrograms(){
        $programs = array();
        $account = $this->getAccount();
        $programCollection = $this->_objectManager->create('Magestore\Affiliateplusprogram\Model\ResourceModel\Account\Collection')
            ->addFieldToFilter(
                'account_id',$account->getId()
            );
        foreach ($programCollection as $program){
            $programs[$program->getProgramId()] = ['position' => 0];
        }
        return $programs;
    }

    /**
     * @return mixed
     */
    public function getAccount(){
        return $this->_objectManager->create('Magestore\Affiliateplus\Model\Account')
            ->load($this->getRequest()->getParam('account_id'));
    }

    /*=======Required methods=========*/
    public function getTabLabel() {
        return __('Programs');
    }

    public function getTabTitle() {
        return __('Programs');
    }

    public function canShowTab() {
        return true;
    }

    public function isHidden() {
        return false;
    }
}
