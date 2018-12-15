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



class Mirasvit_Rma_Block_Adminhtml_Rma_Create extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_rma';
        $this->_mode = 'create';
        $this->_blockGroup = 'rma';

        parent::__construct();

        $this->setId('rma_rma_create');
        $this->removeButton('save');
        $this->removeButton('reset');
        if (Mage::getSingleton('rma/config')->getGeneralIsOfflineOrdersAllowed()) {
            $this->_addButton('reset', array(
                'label' => Mage::helper('adminhtml')->__('Create Offline'),
                'onclick' => 'setLocation(\''.$this->getOfflineUrl().'\')',
                'id' => 'create_rma',
            ), -1);
        }
    }

    /**
     * @return string
     */
    public function getHeaderText()
    {
        return Mage::helper('rma')->__('Create New RMA');
    }

    /**
     * @return string
     */
    public function getOfflineUrl()
    {
        return $this->getUrl('*/*/add', array(
                'orders_id' => -1,
                'customer_id' => Mage::app()->getRequest()->getParam('customer_id'),
                'ticket_id' => Mage::app()->getRequest()->getParam('ticket_id'),
            )
        );
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/*/add', array(
                'ticket_id' => Mage::app()->getRequest()->getParam('ticket_id'),
            )
        );
    }
}
