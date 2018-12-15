<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/extension_rma
 * @version   2.4.7
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



class Mirasvit_Rma_Block_Adminhtml_Rma_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * @var array
     */
    protected $_customFilters = array();

    /**
     * @var string
     */
    protected $_activeTab;

    /**
     * Mirasvit_Rma_Block_Adminhtml_Rma_Grid constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('rma_grid');
        $this->setDefaultSort('updated_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * @param string $field
     * @param string $filter
     * @return Mirasvit_Rma_Block_Adminhtml_Rma_Grid
     */
    public function addCustomFilter($field, $filter)
    {
        $this->_customFilters[$field] = $filter;

        return $this;
    }

    /**
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        /** @var Mirasvit_Rma_Model_Resource_Rma_Collection $collection */
        $collection = Mage::getModel('rma/rma')
            ->getCollection()
            ->joinRmaOrders()
        ;
        if (Mage::registry('is_archive') !== null) {
            $collection->addFieldToFilter('is_archived', Mage::registry('is_archive'));
        }
        foreach ($this->_customFilters as $key => $value) {
            if ($key == 'order_id') {
                $collection->addOrderIdFilter($value);
            } else {
                $collection->addFieldToFilter($key, $value);
            }
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return int
     */
    public function getFormattedNumberOfRMA()
    {
        $count = $this->getCollection()->count();
        if ($count == 0) {
            return 0;
        }

        return '<b>'.$count.'</b>';
    }

    /**
     * @return Mirasvit_Rma_Block_Adminhtml_Rma_Grid
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        $columns = Mage::getSingleton('rma/config')->getGeneralRmaGridColumns();

        if (in_array('increment_id', $columns)) {
            $this->addColumn('increment_id', array(
                    'header' => Mage::helper('rma')->__('RMA #'),
                    'index' => 'increment_id',
                    'filter_index' => 'main_table.increment_id',
                )
            );
        }
        if (in_array('order_increment_id', $columns)) {
            $this->addColumn('order_increment_id', array(
                'header' => Mage::helper('rma')->__('Order #'),
                'index' => 'order_increment_id',
                'filter_index' => 'orders.increment_id',
                'filter_condition_callback' => array($this, 'orderFilterCallback'),
                )
            );
        }
        if (in_array('customer_email', $columns)) {
            $this->addColumn('email', array(
                'header' => Mage::helper('rma')->__('Email'),
                'index' => 'email',
                )
            );
        }
        if (in_array('customer_name', $columns)) {
            $this->addColumn('name', array(
                'header' => Mage::helper('rma')->__('Customer Name'),
                'index' => array('firstname', 'lastname'),
                'type' => 'concat',
                'separator' => ' ',
                'filter_index' => new Zend_Db_Expr("CONCAT(firstname, ' ', lastname)"),
                'frame_callback' => array($this, 'filterXss'),
                )
            );
        }
        if (in_array('user_id', $columns)) {
            $this->addColumn('user_id', array(
                'header' => Mage::helper('rma')->__('Owner'),
                'index' => 'user_id',
                'filter_index' => 'main_table.user_id',
                'type' => 'options',
                'options' => Mage::helper('rma')->getAdminUserOptionArray(),
                )
            );
        }
        if (in_array('last_reply_name', $columns)) {
            $this->addColumn('last_reply_name', array(
                'header' => Mage::helper('rma')->__('Last Replier'),
                'index' => 'last_reply_name',
                'filter_index' => 'main_table.last_reply_name',
                'frame_callback' => array($this, '_lastReplyFormat'),
                )
            );
        }
        if (in_array('status_id', $columns)) {
            $this->addColumn('status_id', array(
                'header' => Mage::helper('rma')->__('Status'),
                'index' => 'status_id',
                'filter_index' => 'main_table.status_id',
                'type' => 'options',
                'options' => Mage::getModel('rma/status')->getOptionArray(),
                )
            );
        }
        if (in_array('created_at', $columns)) {
            $this->addColumn('created_at', array(
                'header' => Mage::helper('rma')->__('Created Date'),
                'index' => 'created_at',
                'filter_index' => 'main_table.created_at',
                'type' => 'datetime',
                )
            );
        }
        if (in_array('updated_at', $columns)) {
            $this->addColumn('updated_at', array(
                'header' => Mage::helper('rma')->__('Last Activity'),
                'index' => 'updated_at',
                'filter_index' => 'main_table.updated_at',
                'type' => 'datetime',
                'frame_callback' => array($this, '_lastActivityFormat'),
                )
            );
        }
        if (in_array('store_id', $columns)) {
            $this->addColumn('store_id', array(
                    'header' => Mage::helper('rma')->__('Store'),
                    'index' => 'store_id',
                    'filter_index' => 'main_table.store_id',
                    'type' => 'options',
                    'options' => Mage::helper('rma')->getCoreStoreOptionArray(),
                )
            );
        }
        if (in_array('items', $columns)) {
            $this->addColumn('items', array(
                    'header' => Mage::helper('rma')->__('Items'),
                    'column_css_class' => 'nowrap',
                    'type' => 'text',
                    'frame_callback' => array($this, '_itemsFormat'),
                    'filter_condition_callback' => array($this, '_itemsFilterCallback'),
                )
            );
        }
        if ($this->getTabMode() || in_array('action', $columns)) {
            $this->addColumn('action',
                array(
                    'header' => Mage::helper('rma')->__('Action'),
                    'width' => '50px',
                    'type' => 'action',
                    'getter' => 'getId',
                    'actions' => array(
                        array(
                            'caption' => Mage::helper('rma')->__('View'),
                            'url' => array(
                                'base' => '*/rma_rma/edit',
                            ),
                            'field' => 'id',
                        ),
                    ),
                    'filter' => false,
                    'sortable' => false,
                ));
        }

        $collection = Mage::helper('rma/field')->getStaffCollection();
        foreach ($collection as $field) {
            if (in_array($field->getCode(), $columns)) {
                $this->addColumn($field->getCode(), array(
                    'header' => Mage::helper('rma')->__($field->getName()),
                    'index' => $field->getCode(),
                    'type' => $field->getGridType(),
                    'options' => $field->getGridOptions(),
                ));
            }
        }

        if ($this->getExportVisibility() !== false) {
            $this->addExportType('*/*/exportCsv', Mage::helper('rma')->__('CSV'));
            $this->addExportType('*/*/exportXml', Mage::helper('rma')->__('XML'));
        }

        return parent::_prepareColumns();
    }

    /**
     * @param array $collection
     * @param string $column
     * @return Mirasvit_Rma_Block_Adminhtml_Rma_Grid
     */
    public function orderFilterCallback($collection, $column)
    {
        $filter = explode(' ', $column->getFilter()->getValue());
        $filterArray = array();
        foreach ($filter as $keyword) {
            $filterArray[] = array('like' => '%'.$keyword.'%');
        }

        $collection->addFieldToFilter(
            array('orders.increment_id', 'offline_orders.receipt_number'),
            array(
                $filterArray,
                $filterArray,
            )
        );

        return $this;
    }

    /**
     * @param Mirasvit_Rma_Block_Adminhtml_Rma_Grid   $renderedValue
     * @param Mirasvit_Rma_Model_Rma                  $rma
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @param bool                                    $isExport
     *
     * @return string
     */
    public function filterXss($renderedValue, $rma, $column, $isExport)
    {
        return Mage::helper('core')->escapeHtml($renderedValue);
    }

    /**
     * @param array $collection
     * @param string $column
     * @return Mirasvit_Rma_Block_Adminhtml_Rma_Grid
     */
    public function _itemsFilterCallback($collection, $column)
    {
        $filter = explode(' ', $column->getFilter()->getValue());
        $filterArray = array();
        foreach ($filter as $keyword) {
            $filterArray[] = array('like' => '%'.$keyword.'%');
        }

        $collection->getSelect()
            ->joinLeft(array('item' => $collection->getTable('rma/item')),
                'item.rma_id = main_table.rma_id', array())
            ->joinLeft(array('offline_item' => $collection->getTable('rma/offline_item')),
                'offline_item.rma_id = main_table.rma_id', array())
            ->joinLeft(array('item_cond' => $collection->getTable('rma/condition')),
                '(item_cond.condition_id = item.condition_id OR item_cond.condition_id = offline_item.condition_id)',
                array('main_table.item_condition' => 'item_cond.name'))
            ->joinLeft(array('item_reason' => $collection->getTable('rma/reason')),
                '(item_reason.reason_id = item.reason_id OR item_reason.reason_id = offline_item.reason_id)',
                array('main_table.item_reason' => 'item_reason.name'))
            ->joinLeft(array('item_resolution' => $collection->getTable('rma/resolution')),
                '(item_resolution.resolution_id = item.resolution_id OR ' .
                'item_resolution.resolution_id = offline_item.resolution_id)',
                array('main_table.item_resolution' => 'item_resolution.name'))
            ;

        $collection->getSelect()->columns(
            array('main_table.item_name' => 'IFNULL(`item`.`name`, `offline_item`.`name`)'));

        $collection->addFieldToFilter(
            array('item.name', 'offline_item.name', 'item_cond.name', 'item_reason.name', 'item_resolution.name'),
            array(
                $filterArray,
                $filterArray,
                $filterArray,
                $filterArray,
                $filterArray,
            )
        );

        $collection->getSelect()->group('main_table.rma_id');

        return $this;
    }

    /**
     * @param Varien_Object $item
     * @return string
     */
    private function _itemFormat($item)
    {
        $s = '<b>'.$item->getName().'</b>';
        $s .= ' / ';
        $s .= $item->getReasonName() ? $item->getReasonName() : '-';
        $s .= ' /  ';
        $s .= $item->getConditionName() ? $item->getConditionName() : '-';
        $s .= ' / ';
        $s .= $item->getResolutionName() ? $item->getResolutionName() : '-';
        return $s;
    }

    /**
     * @param Mirasvit_Rma_Block_Adminhtml_Rma_Grid   $renderedValue
     * @param Mirasvit_Rma_Model_Rma                  $rma
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @param bool                                    $isExport
     *
     * @return string
     */
    public function _itemsFormat($renderedValue, $rma, $column, $isExport)
    {
        $html = array();
        foreach ($rma->getItemCollection() as $item) {
            $html[] = $this->_itemFormat($item);
        }
        foreach ($rma->getOfflineItemCollection() as $item) {
            $html[] = $this->_itemFormat($item);
        }

        return implode('<br>', $html);
    }

    /**
     * @param Mirasvit_Rma_Block_Adminhtml_Rma_Grid   $renderedValue
     * @param Mirasvit_Rma_Model_Rma                  $rma
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @param bool                                    $isExport
     *
     * @return string
     */
    public function _lastReplyFormat($renderedValue, $rma, $column, $isExport)
    {
        $name = $rma->getLastReplyName();
        // If last comment is automated, assign Last Reply Name value to owner, if such exists
        $lastComment = $rma->getLastComment();
        if ($lastComment && !$lastComment->getUserId() && !$lastComment->getCustomerId()) {
            $name = $rma->getUserName();
        }
        $name = Mage::helper('core')->escapeHtml($name);

        if (!$rma->getIsAdminRead()) {
            $name .= ' <img src="'.$this->getSkinUrl('images/fam_newspaper.gif').'">';
        }

        return $name;
    }

    /**
     * @param Mirasvit_Rma_Block_Adminhtml_Rma_Grid   $renderedValue
     * @param Mirasvit_Rma_Model_Rma                  $rma
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @param bool                                    $isExport
     *
     * @return string
     */
    public function _lastActivityFormat($renderedValue, $rma, $column, $isExport)
    {
        return Mage::helper('rma/string')->nicetime(strtotime($rma->getUpdatedAt()));
    }

    /**
     * @return Mirasvit_Rma_Block_Adminhtml_Rma_Grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('rma_id');
        $this->getMassactionBlock()->setFormFieldName('rma_id');
        $statuses = array(
                array('label' => '', 'value' => ''),
                array('label' => $this->__('Disabled'), 'value' => 0),
                array('label' => $this->__('Enabled'), 'value' => 1),
        );
        /*
         * Proper redirect if mass action was conducted in Tab Mode.
         * If action in tab mode, current URL is:
         *  - ...mageadmin/sales_order/view/order_id/196/key/ff1e507218a2b78d0fa2c02bf0aa3ab5/?active_tab=RMA
         * But when mass action URL is opened, it changes to:
         *  - ...rmaadmin/adminhtml_rma/massDelete/
         * Thus, redirect after mass action will be to index page of RMA, so we need to pass back URL to controller.
        */
        $backUrl = strtr(base64_encode($this->getGridUrl()), '+/=', '-_,');
        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('rma')->__('Delete'),
            'url' => $this->getTabMode() ? $this->getUrl('adminhtml/rma_rma/massDelete', array('back_url' => $backUrl))
                                         : $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('rma')->__('Are you sure?'),
        ));

        $this->getMassactionBlock()->addItem('change_status', array(
            'label' => Mage::helper('rma')->__('Change Status'),
            'url' => $this->getUrl('*/*/massChange', array('_current' => true)),
            'additional' => array(
                'visibility' => array(
                    'name' => 'status',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => Mage::helper('rma')->__('Status'),
                    'values' => Mage::getSingleton('rma/status')->toOptionArray(),
                ),
            ),
            'confirm' => Mage::helper('rma')->__('Are you sure?'),
        ));
        $this->getMassactionBlock()->addItem('restore_archive', array(
            'label' => Mage::helper('rma')->__('Restore from archive'),
            'url' => $this->getTabMode()
                ? $this->getUrl('adminhtml/rma_rma/massRestore', array('back_url' => $backUrl))
                : $this->getUrl('*/*/massRestore'),
            'confirm' => Mage::helper('rma')->__('Are you sure?'),
        ));
        $this->getMassactionBlock()->addItem('archive', array(
            'label' => Mage::helper('rma')->__('Archive'),
            'url' => $this->getTabMode()
                ? $this->getUrl('adminhtml/rma_rma/massArchive', array('back_url' => $backUrl))
                : $this->getUrl('*/*/massArchive'),
            'confirm' => Mage::helper('rma')->__('Are you sure?'),
        ));

        if (Mage::registry('is_archive')) {
            $this->getMassactionBlock()->removeItem('archive');
        } elseif (Mage::registry('is_archive') !== null) {
            $this->getMassactionBlock()->removeItem('restore_archive');
        }

        return $this;
    }

    /**
     * @param int $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('adminhtml/rma_rma/edit', array('id' => $row->getId()));
    }

    /************************/

    /**
     * @param string $tabName
     * @return void
     */
    public function setActiveTab($tabName)
    {
        $this->_activeTab = $tabName;
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        if ($this->_activeTab) {
            return parent::getGridUrl().'?active_tab='.$this->_activeTab;
        }

        return parent::getGridUrl();
    }
}
