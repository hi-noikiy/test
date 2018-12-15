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



class Mirasvit_Rma_Block_Adminhtml_Return_Address extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Mirasvit_Rma_Block_Adminhtml_Return_Address constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->_controller = 'adminhtml_return_address';
        $this->_blockGroup = 'rma';
        $this->_headerText = Mage::helper('rma')->__('Return Address');
        $this->_addButtonLabel = Mage::helper('rma')->__('Add New Address');
    }

    /**
     * @return string
     */
    public function getCreateUrl()
    {
        return $this->getUrl('*/*/add');
    }

    /************************/
}
