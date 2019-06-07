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



class Mirasvit_Rewards_Block_Adminhtml_Customer_Edit_Tabs_Transaction_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    /**
     * Mirasvit_Rewards_Block_Adminhtml_Customer_Edit_Tabs_Transaction_Grid constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('transactionGrid');
        $this->setDefaultSort('transaction_id');
        $this->setDefaultDir('DESC');

        $this->setUseAjax(false);

        $this->setEmptyText(Mage::helper('rewards')->__('No Records Found'));

    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/customer/edit/tab/customer_info_tabs_rewards_rewards', array('_current'=>true));
    }

    /**
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $customer = Mage::registry('current_customer');
        $collection = Mage::getModel('rewards/transaction')
            ->getCollection()
            ->addFieldToFilter('customer_id', $customer->getId());

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return Mirasvit_Rewards_Block_Adminhtml_Customer_Edit_Tabs_Transaction_Grid
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn('transaction_id', array(
                'header'    => Mage::helper('rewards')->__('ID'),
        //          'align'     => 'right',
        //          'width'     => '50px',
                'index'     => 'transaction_id',
                'filter_index'     => 'main_table.transaction_id',
            )
        );

        $this->addColumn('amount', array(
                'header'    => Mage::helper('rewards')->__('Balance Change'),
        //          'align'     => 'right',
        //          'width'     => '50px',
                'index'     => 'amount',
                'filter_index'     => 'main_table.amount',
            )
        );
        $this->addColumn('comment', array(
                'header'    => Mage::helper('rewards')->__('Comment'),
        //          'align'     => 'right',
        //          'width'     => '50px',
                'index'     => 'comment',
                'filter_index'     => 'main_table.comment',
            )
        );
        $this->addColumn('created_at', array(
                'header'    => Mage::helper('rewards')->__('Created At'),
        //          'align'     => 'right',
        //          'width'     => '50px',
                'index'     => 'created_at',
                'filter_index'     => 'main_table.created_at',
                'type'      => 'date',
            )
        );
        $this->addColumn('expires_at', array(
                'header'    => Mage::helper('rewards')->__('Expires At'),
        //          'align'     => 'right',
        //          'width'     => '50px',
                'index'     => 'expires_at',
                'filter_index'     => 'main_table.expires_at',
                'type'      => 'date',
            )
        );

        return parent::_prepareColumns();
    }

}
