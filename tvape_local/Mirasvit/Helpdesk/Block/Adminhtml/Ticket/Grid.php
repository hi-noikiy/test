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
 * @package   mirasvit/extension_helpdesk
 * @version   1.5.4
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */


/**
 * @method bool getTabMode()
 * @method $this setTabMode(bool $param)
 */
class Mirasvit_Helpdesk_Block_Adminhtml_Ticket_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * @var array $_customFilters
     */
    protected $_customFilters = array();

    /**
     * @var array $_removeFilters
     */
    protected $_removeFilters = array();

    /**
     * @var $_activeTab
     */
    protected $_activeTab;

    /**
     * Mirasvit_Helpdesk_Block_Adminhtml_Ticket_Grid constructor.
     */
    public function __construct()
    {
        parent::__construct();
        // $archive = Mage::registry('is_archive')? '_archive': '';
        $archive = '';
        $this->setId('helpdesk_grid'.$archive);
        $this->setDefaultSort('last_activity');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
    }

    /**
     * @param string $field
     * @param bool $filter
     * @return Mirasvit_Helpdesk_Block_Adminhtml_Ticket_Grid
     */
    public function addCustomFilter($field, $filter = false)
    {
        if ($filter) {
            $this->_customFilters[$field] = $filter;
        } else {
            $this->_customFilters[] = $field;
        }

        return $this;
    }

    /**
     * @param string $field
     * @return Mirasvit_Helpdesk_Block_Adminhtml_Ticket_Grid
     */
    public function removeFilter($field)
    {
        $this->_removeFilters[$field] = true;

        return $this;
    }

    /**
     * @return array|string
     */
    public function getFormattedNumberOfTickets()
    {
        $allN = $this->getNumberOfTickets();
        $activeN = $this->getNumberOfActiveTickets();
        $number = array();
        if ($activeN) {
            $number[] = "<b>$activeN</b>";
        }
        if ($allN == 0 || $allN > $activeN) {
            $number[] = "$allN";
        }
        $number = implode('/', $number);

        return $number;
    }

    /**
     * @return int
     */
    private function getNumberOfTickets()
    {
        return $this->getCollection()->count();
    }

    /**
     * @return int
     */
    private function getNumberOfActiveTickets()
    {
        $n = 0;
        foreach ($this->getCollection() as $ticket) {
            /** @var Mirasvit_Helpdesk_Model_Ticket $ticket */
            if (!$ticket->isClosed() && !$ticket->getIsArchived()) {
                ++$n;
            }
        }

        return $n;
    }

    /**
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $user = Mage::getSingleton('admin/session')->getUser();
        $collection = Mage::getModel('helpdesk/ticket')
            ->getCollection()
            ->addFieldToFilter('is_spam', false)
            ->joinColors()
            ->joinOrders()
        ;
        if (!isset($this->_removeFilters['is_archived'])) {
            //by default we apply this filter
            $collection->addFieldToFilter('is_archived', Mage::registry('is_archive'));
        }

        Mage::helper('helpdesk/permission')->setTicketRestrictions($collection);
        foreach ($this->_customFilters as $key => $value) {
            if ((int) $key === $key && is_string($value)) {
                $collection->getSelect()->where($value);
            } else {
                $collection->addFieldToFilter($key, $value);
            }
        }
        if ($helpdeskUser = Mage::helper('helpdesk')->getHelpdeskUser($user)) {
            if ($helpdeskUser->getStoreId()) {
                $collection->addFieldToFilter('main_table.store_id', $helpdeskUser->getStoreId());
            }
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @param Mirasvit_Helpdesk_Model_Resource_Ticket_Collection $collection
     * @param string $column
     * @return void
     */
    protected function _filterSearchCondition($collection, $column)
    {
        if (!$query = $column->getFilter()->getValue()) {
            return;
        }
        Mage::register('helpdesk_search_query', $query);
        /** @var Mirasvit_Helpdesk_Model_Resource_Ticket_Collection $collection */
        $collection = $this->getCollection();
        $collection->getSearchInstance()->joinMatched($query, $collection, 'main_table.ticket_id');
    }

    /**
     * @param Mirasvit_Helpdesk_Model_Resource_Ticket_Collection $collection
     * @param string $column
     * @return void
     */
    protected function _filterLastMessageCondition($collection, $column)
    {
        if (!$query = $column->getFilter()->getValue()) {
            return;
        }
        Mage::register('helpdesk_search_query', $query);
        /** @var Mirasvit_Helpdesk_Model_Resource_Ticket_Collection $collection */
        $collection = $this->getCollection()
            ->joinLastMessages();
        $collection->getSearchInstance()->joinMatched($query, $collection, 'main_table.ticket_id');
    }

    /**
     * @return Mirasvit_Helpdesk_Block_Adminhtml_Ticket_Grid
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        $adminUser = Mage::getSingleton('admin/session')->getUser();
        $helpdeskUser = Mage::helper('helpdesk')->getHelpdeskUser($adminUser);
        $columns = Mage::getSingleton('helpdesk/config')->getTicketGridColumns($helpdeskUser->getStoreId());

        $this->addColumn('search', array(
            'header' => Mage::helper('helpdesk')->__('Search'),
            'index' => 'search',
            'align' => 'search-td',
            'header_css_class' => 'search-header',
            'filter_condition_callback' => array($this, '_filterSearchCondition'),
        ));

        if (in_array('code', $columns)) {
            $this->addColumn('code', array(
                    'header' => Mage::helper('helpdesk')->__('ID'),
                    'align' => 'left',
                    'index' => 'code',
                    'header_css_class' => 'code-header',
                    'column_css_class' => 'nowrap',
                )
            );
        }
        if (in_array('name', $columns)) {
            $this->addColumn('name', array(
                    'header' => Mage::helper('helpdesk')->__('Subject'),
                    'index' => 'name',
                    'header_css_class' => 'subject-header',
                )
            );
        }
        if (in_array('customer_name', $columns) && !$this->getTabMode()) {
                $this->addColumn('customer_name', array(
                    'header' => Mage::helper('helpdesk')->__('Customer Name'),
                    'index' => 'customer_name',
                )
                );
        }

        if (in_array('customer_email', $columns) && !$this->getTabMode()) {
            $this->addColumn('customer_email', array(
                    'header' => Mage::helper('helpdesk')->__('Customer Email'),
                    'index' => 'customer_email',
                    'type' => 'text',
                )
            );
        }

        if (in_array('order_number', $columns) && !$this->getTabMode()) {
            $this->addColumn('order_number', array(
                    'header' => Mage::helper('helpdesk')->__('Order Number'),
                    'index' => 'order_number',
                    'type' => 'text',
                    'filter_index' => 'order.increment_id'
                )
            );
        }

        if (in_array('last_reply_name', $columns)) {
            $this->addColumn('last_reply_name', array(
                'header' => Mage::helper('helpdesk')->__('Last Replier'),
                'index' => 'last_reply_name',
                )
            );
        }
        if (in_array('user_id', $columns)) {
            $this->addColumn('user_id', array(
                'header' => Mage::helper('helpdesk')->__('Owner'),
                'index' => 'user_id',
                'type' => 'options',
                'options' => Mage::helper('helpdesk')->getAdminUserOptionArray(),
                'column_css_class' => 'nowrap',
            )
            );
        }
        if (in_array('department_id', $columns) && !$this->getTabMode()) {
            $collection = Mage::getModel('helpdesk/department')->getCollection();
            Mage::helper('helpdesk/permission')->setDepartmentRestrictions($collection);
            $this->addColumn('department_id', array(
                'header' => Mage::helper('helpdesk')->__('Department'),
                'index' => 'department_id',
                'sort_index' => 'department.sort_order',
                'type' => 'options',
                'options' => $collection->getOptionArray(),
                'column_css_class' => 'nowrap',
            )
            );
        }
        if (in_array('store_id', $columns) && !$this->getTabMode()) {
            $this->addColumn('store_id', array(
            'header' => Mage::helper('helpdesk')->__('Store'),
            'index' => 'store_id',
            'type' => 'options',
            'options' => Mage::helper('helpdesk')->getCoreStoreOptionArray(),
            )
            );
        }
        if (in_array('status_id', $columns)) {
            $this->addColumn('status_id', array(
                'header' => Mage::helper('helpdesk')->__('Status'),
                'index' => 'status_id',
                'sort_index' => 'status.sort_order',
                'type' => 'options',
                'options' => Mage::getModel('helpdesk/status')->getCollection()->getOptionArray(),
                'renderer' => 'Mirasvit_Helpdesk_Block_Adminhtml_Ticket_Grid_Renderer_Highlight',
            )
            );
        }
        if (in_array('priority_id', $columns)) {
            $this->addColumn('priority_id', array(
                'header' => Mage::helper('helpdesk')->__('Priority'),
                'index' => 'priority_id',
                'sort_index' => 'priority.sort_order',
                'type' => 'options',
                'options' => Mage::getModel('helpdesk/priority')->getCollection()->getOptionArray(),
                'renderer' => 'Mirasvit_Helpdesk_Block_Adminhtml_Ticket_Grid_Renderer_Highlight',
            )
            );
        }
        if (in_array('reply_cnt', $columns) && !$this->getTabMode()) {
            $this->addColumn('reply_cnt', array(
                'header' => Mage::helper('helpdesk')->__('Replies'),
                'index' => 'reply_cnt',
                'type' => 'text',
                'align' => 'center',
            )
            );
        }
        if (in_array('created_at', $columns) && !$this->getTabMode()) {
            $this->addColumn('created_at', array(
                'header' => Mage::helper('helpdesk')->__('Created At'),
                'index' => 'created_at',
                'type' => 'datetime',
                'column_css_class' => 'nowrap',
                )
            );
        }
        if (in_array('updated_at', $columns) && !$this->getTabMode()) {
            $this->addColumn('updated_at', array(
                'header' => Mage::helper('helpdesk')->__('Updated At'),
            //          'align'     => 'right',
            //          'width'     => '50px',
                'index' => 'updated_at',
                'filter_index' => 'main_table.updated_at',
                'type' => 'datetime',
                )
            );
        }
        if (in_array('last_reply_at', $columns) && !$this->getTabMode()) {
            $this->addColumn('last_reply_at', array(
                'header' => Mage::helper('helpdesk')->__('Last Reply At'),
                'index' => 'last_reply_at',
                'type' => 'datetime',
                'column_css_class' => 'nowrap',
                )
            );
        }
        if (in_array('last_activity', $columns)) {
            $this->addColumn('last_activity', array(
                    'header' => Mage::helper('helpdesk')->__('Last Activity'),
                    'index' => 'last_reply_at',
                    'type' => 'text',
                    'column_css_class' => 'nowrap',
                    'frame_callback' => array($this, '_lastActivityFormat'),
                )
            );
        }

        if (in_array('tags', $columns)) {
            $this->addColumn('tags', array(
                    'header' => Mage::helper('helpdesk')->__('Tags'),
                    'index' => 'ticket_id',
                    'type' => 'text',
                    'frame_callback' => array($this, '_displayTags'),
                    'filter_condition_callback' => array($this, '_tagsFilterCallback'),
            ));
        }

        $collection = Mage::helper('helpdesk/field')->getStaffCollection();
        foreach ($collection as $field) {
            if (in_array($field->getCode(), $columns)) {
                $this->addColumn($field->getCode(), array(
                    'header' => Mage::helper('helpdesk')->__($field->getName()),
                    'index' => $field->getCode(),
                    'type' => $field->getGridType(),
                    'options' => $field->getGridOptions(),
                ));
            }
        }

        if (in_array('last_message', $columns)) {
            $this->addColumn('last_message', array(
                    'header' => Mage::helper('helpdesk')->__('Last Message'),
                    'index' => 'ticket_id',
                    'type' => 'text',
                    'frame_callback' => array($this, '_displayLastMessage'),
                    'filter_condition_callback' => array($this, '_filterLastMessageCondition'),
                )
            );
        }

        if ($this->getTabMode() || in_array('action', $columns)) {
            $this->addColumn('action',
                array(
                    'header' => Mage::helper('helpdesk')->__('Action'),
                    'width' => '50px',
                    'type' => 'action',
                    'getter' => 'getId',
                    'actions' => array(
                        array(
                            'caption' => Mage::helper('helpdesk')->__('View'),
                            'url' => array(
                                'base' => '*/helpdesk_ticket/edit',
                                'params'=>array('is_archive'=>'[:is_archive:]'),
                            ),
                            'target' => '_blank',
                            'field' => 'id',
                        ),
                    ),
                    'filter' => false,
                    'sortable' => false,
                    'frame_callback' => array($this, 'modifyTicketUrl'),
                ));
        }

        return parent::_prepareColumns();
    }

    /**
     * @param string                                  $renderedValue
     * @param Mirasvit_Helpdesk_Model_Ticket          $row
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @param bool                                    $isExport
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function modifyTicketUrl($renderedValue, $row, $column, $isExport)
    {
        return str_replace('[:is_archive:]', (int)$row->getIsArchived(), $renderedValue);
    }

    /**
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @return Mirasvit_Helpdesk_Block_Adminhtml_Ticket_Grid
     */
    protected function _setCollectionOrder($column)
    {
        $collection = $this->getCollection();
        if ($collection) {
            $columnIndex = $column->getFilterIndex() ?
                $column->getFilterIndex() : $column->getIndex();
            $columnIndex = $column->getSortIndex() ?
                $column->getSortIndex() : $columnIndex;
            $collection->setOrder($columnIndex, strtoupper($column->getDir()));
        }

        return $this;
    }

    /**
     * @param string $renderedValue
     * @param int $row
     * @param string $column
     * @param bool $isExport
     * @return string
     */
    public function _displayLastMessage($renderedValue, $row, $column, $isExport)
    {
        $ticket = Mage::getModel('helpdesk/ticket')->load($renderedValue);

        return '<span>'.Mage::helper('core/string')->truncate($ticket->getLastMessage()->getBodyPlain()).'</span>';
    }

    /**
     * @param string $renderedValue
     * @param int $row
     * @param string $column
     * @param bool $isExport
     * @return string
     */
    public function _displayTags($renderedValue, $row, $column, $isExport)
    {
        $ticket = Mage::getModel('helpdesk/ticket')->load($renderedValue);

        $tags = array();
        foreach ($ticket->getTags() as $tag) {
            $tags[] = $tag->getName();
        }

        return '<span>'. implode(', ', $tags) .'</span>';
    }

    public function _tagsFilterCallback($collection, $column)
    {
        $filter = explode(' ', $column->getFilter()->getValue());
        $filterArray = array();
        foreach ($filter as $keyword) {
            $filterArray[] = array('like' => '%'.$keyword.'%');
        }

        $collection->getSelect()
            ->joinLeft(array('tag_map' => $collection->getTable('helpdesk/ticket_tag')),
                'main_table.ticket_id = tag_map.tt_ticket_id', array())
            ->joinLeft(array('tag' => $collection->getTable('helpdesk/tag')),
                'tag_map.tt_tag_id = tag.tag_id', array())
        ;

        $collection->addFieldToFilter(
            array('tag.name'),
            array(
                $filterArray,
            )
        );

        $collection->getSelect()->group('main_table.ticket_id');

        return $this;
    }


    /**
     * @param string $renderedValue
     * @param int $row
     * @param string $column
     * @param bool $isExport
     * @return string
     */
    public function _lastActivityFormat($renderedValue, $row, $column, $isExport)
    {
        $timestamp = Mage::getModel('core/date')->timestamp($renderedValue);
        $time = Mage::getModel('core/date')->timestamp();
        $diff = $time - $timestamp;


        $cssClass = 'last-activity';

        if ($diff < 60 * 60) {
            $cssClass .= ' _1h';
        } elseif ($diff < 3 * 60 * 60) {
            $cssClass .= ' _3h';
        } elseif ($diff < 12 * 60 * 60) {
            $cssClass .= ' _12h';
        } elseif ($diff < 24 * 60 * 60) {
            $cssClass .= ' _24h';
        } elseif ($diff < 2 * 24 * 60 * 60) {
            $cssClass .= ' _2d';
        } elseif ($diff < 3 * 24 * 60 * 60) {
            $cssClass .= ' _3d';
        } elseif ($diff) {
            $cssClass .= ' _5d';
        }

        return '<span class="'.$cssClass.'">'.Mage::helper('helpdesk/string')->nicetime($timestamp).'</span>';
    }

    /**
     * @return Mirasvit_Helpdesk_Block_Adminhtml_Ticket_Grid
     */
    protected function _prepareMassaction()
    {
        if ($this->getTabMode()) {
            return $this;
        }
        $this->setMassactionIdField('ticket_id');
        $this->getMassactionBlock()->setFormFieldName('ticket_id');

        $this->getMassactionBlock()->addItem('status', array(
             'label' => Mage::helper('helpdesk')->__('Change Status'),
             'url' => $this->getUrl('*/*/massChange', array('_current' => true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('helpdesk')->__('Status'),
                         'values' => Mage::getSingleton('helpdesk/status')->toOptionArray(),
                     ),
             ),
        ));

        $this->getMassactionBlock()->addItem('owner', array(
             'label' => Mage::helper('helpdesk')->__('Change Owner'),
             'url' => $this->getUrl('*/*/massChange', array('_current' => true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'owner',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('helpdesk')->__('Owner'),
                         'values' => Mage::helper('helpdesk')->getAdminOwnerOptionArray(),
                     ),
             ),
        ));

        $this->getMassactionBlock()->addItem('Merge', array(
            'label' => Mage::helper('helpdesk')->__('Merge'),
            'url' => $this->getUrl('*/*/massMerge'),
            'confirm' => Mage::helper('helpdesk')->__('Are you sure? This action is not reversible.'),
        ));

        $this->getMassactionBlock()->addItem('archive', array(
            'label' => Mage::helper('helpdesk')->__('Archive'),
            'url' => $this->getUrl('*/*/massChange', array('archive' => 1)),
            'confirm' => Mage::helper('helpdesk')->__('Are you sure?'),
        ));

        $this->getMassactionBlock()->addItem('restore', array(
            'label' => Mage::helper('helpdesk')->__('Restore'),
            'url' => $this->getUrl('*/*/massRestore'),
            'confirm' => Mage::helper('helpdesk')->__('Are you sure?'),
        ));

        $this->getMassactionBlock()->addItem('spam', array(
            'label' => Mage::helper('helpdesk')->__('Mark as spam'),
            'url' => $this->getUrl('*/*/massChange', array('spam' => 1)),
            'confirm' => Mage::helper('helpdesk')->__('Are you sure?'),
        ));

        if (Mage::helper('helpdesk/permission')->isTicketRemoveAllowed()) {
            $this->getMassactionBlock()->addItem('delete', array(
                'label' => Mage::helper('helpdesk')->__('Delete'),
                'url' => $this->getUrl('*/*/massDelete'),
                'confirm' => Mage::helper('helpdesk')->__('Are you sure?'),
            ));
        }

        return $this;
    }

    /**
     * @param Mirasvit_Helpdesk_Model_Ticket $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl(
            'adminhtml/helpdesk_ticket/edit',
            array('id' => $row->getId(), 'is_archive' => (int)$row->getIsArchived())
        );
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

    /**
     * @param string $column
     * @return Mirasvit_Helpdesk_Block_Adminhtml_Ticket_Grid
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($this->getCollection()) {
            $field = ($column->getFilterIndex()) ? $column->getFilterIndex() : $column->getIndex();
            if ($column->getFilterConditionCallback()) {
                call_user_func($column->getFilterConditionCallback(), $this->getCollection(), $column);
            } else {
                $cond = $column->getFilter()->getCondition();
                if ($field && isset($cond)) {
                    if (strpos($field, '.') === false) {
                        $this->getCollection()->addFieldToFilter('main_table.' . $field, $cond);
                    } else {
                        $this->getCollection()->addFieldToFilter($field, $cond);
                    }
                }
            }
        }

        return $this;
    }
}
