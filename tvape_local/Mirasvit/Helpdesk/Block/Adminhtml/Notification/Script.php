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



class Mirasvit_Helpdesk_Block_Adminhtml_Notification_Script extends Mage_Core_Block_Abstract {

    public function addNotificationScript()
    {
        $moduleName = Mage::app()->getRequest()->getControllerModule();
        if($moduleName == 'Mirasvit_Helpdesk_Adminhtml') {
            $parent = $this->getParentBlock();
            $parent->addJs('mirasvit/code/helpdesk/adminhtml/notification.js');
        }
    }

}