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



class Mirasvit_Rewards_Block_Adminhtml_Notification_Rule extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->_controller = 'adminhtml_notification_rule';
        $this->_blockGroup = 'rewards';
        $this->_headerText = Mage::helper('rewards')->__('Notification Rules');
        $this->_addButtonLabel = Mage::helper('rewards')->__('Add New Notification Rule');
    }

    public function getCreateUrl()
    {
        return $this->getUrl('*/*/add');
    }

    /************************/
}
