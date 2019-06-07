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
 * @package   mirasvit/extension_rewards
 * @version   1.1.35
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



class Mirasvit_Rewards_Block_Adminhtml_Referral_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('grid');
        $this->setDefaultSort('referral_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('rewards/referral')
            ->getCollection();
        $collection->addNameToSelect();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return $this
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn('referral_id', array(
            'header' => Mage::helper('rewards')->__('ID'),
            'index' => 'referral_id',
            'filter_index' => 'main_table.referral_id',
            )
        );
        $this->addColumn('customer', array(
            'header' => Mage::helper('rewards')->__('Affiliate Customer'),
            'index' => 'customer',
            'frame_callback' => array($this, '_customerFormat'),
            'filter_index' => new Zend_Db_Expr('CONCAT(`ce1`.`value`, \' \',`ce2`.`value`)'),
            )
        );
        $this->addColumn('new_customer', array(
            'header' => Mage::helper('rewards')->__('Referral Customer'),
            'index' => 'new_customer',
            'frame_callback' => array($this, '_newCustomerFormat'),
            'filter_index' => new Zend_Db_Expr('CONCAT(`ce3`.`value`, \' \',`ce4`.`value`)'),
            )
        );
        $this->addColumn('email', array(
            'header' => Mage::helper('rewards')->__('Referral Email'),
            'index' => 'email',
            'filter_index' => 'main_table.email',
            'frame_callback' => array($this, '_renderOfEmptyFields'),
            )
        );
        $this->addColumn('name', array(
            'header' => Mage::helper('rewards')->__('Referral Name'),
            'index' => 'name',
            'filter_index' => 'main_table.name',
            'frame_callback' => array($this, '_renderOfEmptyFields'),
            )
        );
        $this->addColumn('status', array(
            'header' => Mage::helper('rewards')->__('Status'),
            'index' => 'status',
            'filter_index' => 'main_table.status',
            'type' => 'options',
            'options' => Mage::getSingleton('rewards/config_source_referral_status')->toArray(),
            )
        );
        $this->addColumn('created_at', array(
            'header' => Mage::helper('rewards')->__('Created At'),
            'index' => 'created_at',
            'filter_index' => 'main_table.created_at',
            'type' => 'date',
            )
        );
        $this->addColumn('store_id', array(
            'header' => Mage::helper('rewards')->__('Store'),
            'index' => 'store_id',
            'filter_index' => 'main_table.store_id',
            'type' => 'options',
            'options' => Mage::helper('rewards')->getCoreStoreOptionArray(),
            )
        );

        return parent::_prepareColumns();
    }

    /**
     * @param string $renderedValue
     * @param array  $row
     * @param array  $column
     * @param bool   $isExport
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function _customerFormat($renderedValue, $row, $column, $isExport)
    {
        $url = Mage::helper('rewards/mage')->getBackendCustomerUrl($row['customer_id']);

        return "<a href='{$url}'>{$this->escapeHtml($row['customer_name'])}</a>";
    }

    /**
     * @param string $renderedValue
     * @param array  $row
     * @param array  $column
     * @param bool   $isExport
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function _newCustomerFormat($renderedValue, $row, $column, $isExport)
    {
        if ((int) $row['new_customer_id'] == 0) {
            return '';
        }
        $url = Mage::helper('rewards/mage')->getBackendCustomerUrl($row['new_customer_id']);

        return "<a href='{$url}'>{$this->escapeHtml($row['new_customer_name'])}</a>";
    }

    /**
     * @param string $renderedValue
     * @param array  $row
     * @param array  $column
     * @param bool   $isExport
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function _renderOfEmptyFields($renderedValue, $row, $column, $isExport)
    {
        if ($renderedValue == '') {
            return '-';
        }

        return $renderedValue;
    }

    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('referral_id');
        $this->getMassactionBlock()->setFormFieldName('referral_id');
        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('rewards')->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('rewards')->__('Are you sure?'),
        ));

        return $this;
    }

    /************************/
}
