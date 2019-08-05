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
namespace Magestore\Affiliateplusprogram\Block;

    /**
     * Class Edit
     * @package Magestore\Affiliateplusprogram\Block\All
     */
/**
 * Class Program
 * @package Magestore\Affiliateplusprogram\Block
 */
class All extends AbstractProgram
{
    protected function _construct()
    {
        parent::_construct();

        $collection = $this->_programFactory->create()
            ->getCollection()
            ->setStoreId($this->getStoreId())
            ->addFieldToFilter('main_table.show_in_welcome', array('gt' => '0'))
            ->addFieldToFilter('main_table.program_id', array('nin' => $this->_helper->getJoinedProgramIds()));

        $group = $this->_customerSession->getCustomer()->getGroupId();
        $collection->getSelect()
            ->where("scope = 0 OR (scope = 1 AND FIND_IN_SET($group, customer_groups) )");
        // join program name and filter status
        $collection->getSelect()
            ->joinLeft(array('n' => $collection->getTable(\Magestore\Affiliateplusprogram\Setup\InstallSchema::SCHEMA_PROGRAM_VALUE)),
                "main_table.program_id = n.program_id AND n.attribute_code = 'name' AND n.store_id = " .
                $this->getStoreId(),
                array('name' => 'IFNULL(n.value, main_table.name)') //IF (n.value IS NULL, main_table.name, n.value))
            )->joinLeft(array('s' => $collection->getTable(\Magestore\Affiliateplusprogram\Setup\InstallSchema::SCHEMA_PROGRAM_VALUE)),
                "main_table.program_id = s.program_id AND s.attribute_code = 'status' AND s.store_id = " .
                $this->getStoreId(),
                array()
            )
            ->where('IFNULL(s.value, main_table.status) = 1');
//            ->where('IF(s.value IS NULL, main_table.status, s.value) = 1');
        $this->setCollection($collection);
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _prepareLayout()
    {
        parent::_prepareLayout();
        $pager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager', 'programs_pager')
            ->setTemplate('Magestore_Affiliateplus::html/pager.phtml')
            ->setCollection($this->getCollection());
        $this->setChild('programs_pager', $pager);

        $grid = $this->getLayout()->createBlock('Magestore\Affiliateplus\Block\Grid', 'programs_grid');

        $grid->addColumn(
            'select',
            [
                'header' => '<input type="checkbox" onclick="selectProgram(this);" />',
                'render' => 'getSelectProgram',
            ]
        );

        $grid->addColumn(
            'id',
            [
                'header' => __('No.'),
                'align' => 'left',
                'render' => 'getNoNumber',
            ]
        );

        $grid->addColumn(
            'name',
            [
                'header' => __('Program Name'),
                'render' => 'getProgramName',
                'index' => 'name',
//                'filter_index'  => 'IFNULL(n.value, main_table.name)',
//                'filter_index'  => 'IF (n.value IS NULL, main_table.name, n.value)',
                'filter_condition_callback' => [$this, '_filterProgramName'],
//                'searchable'    => true,
            ]
        );

        $grid->addColumn(
            'details',
            [
                'header' => __('Information'),
                'render' => 'getProgramDetails'
            ]
        );

        $grid->addColumn(
            'created_date',
            [
                'header' => __('Date Created'),
                'type' => 'date',
                'format' => \IntlDateFormatter::MEDIUM,
                'index' => 'created_date',
//                'searchable'    => true,
            ]
        );


        /*Changed By Adam: show priority 22/07/2014*/
        $grid->addColumn(
            'priority',
            [
                'header' => __('Priority'),
                'index' => 'priority',
//                'searchable'    => true,
                'filter_index' => 'IFNULL(n.value, main_table.priority)',
//                'filter_index'  => 'IF (n.value IS NULL, main_table.priority, n.value)',
                'width' => '50px'
            ]
        );

        $grid->addColumn(
            'action',
            [
                'header' => __('Action'),
                'type' => 'action',
                'action' => [
                    'label' => __('Join Program'),
                    'caption' => __('Join Program'),
                    'url' => 'affiliateplus/program/join',
                    'name' => 'id',
                    'field' => 'program_id'
                ]
            ]
        );

        $this->setChild('programs_grid', $grid);
        return $this;
    }

    /**
     * @param $row
     * @return string
     */
    public function getSelectProgram($row)
    {
        return '<input type="checkbox" name="program_ids[]" value="' . $row->getId() . '" />';
    }

    /**
     * @param $collection
     * @param $column
     */
    protected function _filterProgramName($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        $collection->getSelect()->where("IFNULL(n.value, main_table.name) LIKE '%$value%'");
    }

    /**
     * @param $collection
     * @param $column
     */
    protected function _filterProgramPriority($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        $collection->getSelect()->where("IFNULL(n.value, main_table.priority) LIKE '%$value%'");
    }
}
