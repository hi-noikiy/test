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
     * @package Magestore\Affiliateplusprogram\Block\Program
     */
/**
 * Class Program
 * @package Magestore\Affiliateplusprogram\Block
 */
class Program extends AbstractProgram
{
    /**
     * @var array
     */
    protected $_commission_array = array();

    /**
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $collection = $this->_programFactory->create()
            ->getCollection();
        $collection->getSelect()
            ->join(
                array('account' => $collection->getTable(\Magestore\Affiliateplusprogram\Setup\InstallSchema::SCHEMA_PROGRAM_ACCOUNT)),
                'main_table.program_id = account.program_id',
                array(
                    'joined_at' => 'joined',
                )
            )
            ->where('account.account_id = ?', $this->_accountHelper->getAccount()->getId());
        // join program name and filter status
        $collection->getSelect()
            ->joinLeft(array('n' => $collection->getTable(\Magestore\Affiliateplusprogram\Setup\InstallSchema::SCHEMA_PROGRAM_VALUE)),
                "main_table.program_id = n.program_id AND n.attribute_code = 'name' AND n.store_id = " .
                $this->_storeManager->getStore()->getId(),
                array('program_name' => 'IFNULL(n.value, main_table.name)')
            )
            ->joinLeft(array('s' => $collection->getTable(\Magestore\Affiliateplusprogram\Setup\InstallSchema::SCHEMA_PROGRAM_VALUE)),
                "main_table.program_id = s.program_id AND s.attribute_code = 'status' AND s.store_id = " .
                $this->_storeManager->getStore()->getId(),
                array()
            )->where('IFNULL(s.value, main_table.status) = 1');
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

        // prepare column
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
                'filter_index' => 'main_table.name',
//                'searchable'    => true,
                'filter_condition_callback' => [$this, '_filterProgramName']
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
            'joined_at',
            [
                'header' => __('Joined On'),
                'type' => 'date',
                'format' => \IntlDateFormatter::MEDIUM,
                'index' => 'joined_at',
//                'searchable'    => true,
                'filter_index' => 'account.joined_at',
            ]
        );
        /*Changed By Adam: show priority 22/07/2014*/
        $grid->addColumn(
            'priority',
            [
                'header' => __('Priority'),
                'index' => 'priority',
//                'searchable'    => true,
                'filter_index' => 'main_table.priority',
                'width' => '50px',
                'filter_condition_callback' => [$this, '_filterProgramPriority']
            ]
        );
        $grid->addColumn(
            'action',
            [
                'header' => __('Action'),
                'type' => 'action',
                'getter' => 'getId',
                'action' => [
                    'label' => __('Opt out'),
                    'caption' => __('Opt out'),
                    'url' => 'affiliateplus/program/out',
                    'name' => 'id',
                    'field' => 'program_id'
                ]
            ]
        );
        $this->setChild('programs_grid', $grid);
        return $this;
    }

    /**
     * @return string
     */
    public function getAllProgramUrl()
    {
        return $this->getUrl('affiliateplus/program/all');
    }

    /**
     * @return bool
     */
    public function isShowDefaultProgram()
    {
        return (
            $this->_configHelper->getCommissionConfig('commission')
            && $this->_configHelper->getDiscountConfig('discount')
        );
    }

    /**
     * @return mixed
     */
    public function getDefaultProgramTotalCommission()
    {
        return $this->_commission_array[0];
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