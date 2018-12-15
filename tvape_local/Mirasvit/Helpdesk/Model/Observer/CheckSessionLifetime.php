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



class Mirasvit_Helpdesk_Model_Observer_CheckSessionLifetime
{
    /**
     */
    public function execute()
    {
        $registryKey = '_singleton/'.Mage_Adminhtml_Controller_Action::SESSION_NAMESPACE.'/session';
        if (//if session exists
            Mage::registry($registryKey)
        ) {
            /** @var Mage_Admin_Model_Session $session */
            $session = Mage::getSingleton('admin/session');
            if (
                Mage::app()->getRequest()->isAjax() &&
                Mage::app()->getRequest()->getControllerModule() == 'Mirasvit_Helpdesk_Adminhtml'
            ) {
                $adminSessionLifetime = (int) Mage::getStoreConfig('admin/security/session_cookie_lifetime');
                if ($adminSessionLifetime > 60 && time() - $session->getStartTime() > $adminSessionLifetime) {
                    $session->getMessages(true);
                    $session->clear();
                    Mage::app()->getRequest()->setDispatched(false);
                }
            } else {
                $session->setStartTime(time());
            }
        }
    }
}
