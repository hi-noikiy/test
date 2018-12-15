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



class Mirasvit_Helpdesk_Block_Adminhtml_Permission extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->_controller = 'adminhtml_permission';
        $this->_blockGroup = 'helpdesk';
        $this->_headerText = Mage::helper('helpdesk')->__('Permissions');
        $this->_addButtonLabel = Mage::helper('helpdesk')->__('Add New Permission');
    }

    public function getCreateUrl()
    {
        return $this->getUrl('*/*/add');
    }

    /************************/
}
