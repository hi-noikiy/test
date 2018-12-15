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



class Mirasvit_Rma_Block_Adminhtml_Rma_SelectCustomer extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_rma_selectCustomer';
        $this->_blockGroup = 'rma';
        $this->_headerText = Mage::helper('rma')->__('Select Customer');
        if (Mage::getSingleton('rma/config')->getGeneralIsOfflineOrdersAllowed()) {
            $this->_addButtonLabel = Mage::helper('rma')->__('Create RMA for offline customer');
        }
        $this->_backButtonLabel = $this->__('Back');

        $this->_addBackButton();

        parent::__construct();
        if (!Mage::getSingleton('admin/session')->isAllowed('customer/manage')) {
            $this->removeButton('add');
        }

        $this->_addButton('guest_orders', array(
            'label' => Mage::helper('rma')->__('Guest Orders'),
            'onclick' => 'setLocation(\''.$this->getGuestUrl().'\')',
        ));
    }

    /**
     * @return string
     */
    public function getCreateUrl()
    {
        return $this->getUrl('*/*/add', array(
                'customer_id' => 0,
                'orders_id' => -1,
                'ticket_id' => Mage::app()->getRequest()->getParam('ticket_id'),
            )
        );
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/*/index');
    }

    /**
     * @return string
     */
    public function getGuestUrl()
    {
        return $this->getUrl('*/*/add', array(
                'customer_id' => 0,
                'ticket_id' => Mage::app()->getRequest()->getParam('ticket_id'),
            )
        );
    }

    /************************/
}
