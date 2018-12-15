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



class Mirasvit_Helpdesk_Block_Adminhtml_Notification_Indicator extends Mage_Adminhtml_Block_Widget_Container
{
    /**
     * @return Mirasvit_Helpdesk_Model_Config
     */
    protected function getConfig() {
        return Mage::getSingleton('helpdesk/config');
    }

    /**
     * @return int
     */
    public function getNotificationInterval()
    {
        return $this->getConfig()->getDesktopNotificationCheckPeriod();
    }

    /**
     * @return string
     */
    public function getCheckNotificationUrl()
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/helpdesk_ticket/checknotification');
    }

    /**
     * @return string
     */
    protected function _toHtml() {
        $indicatorShown = true;

        if($this->getConfig()->getDesktopNotificationIsIndicatorShown()) {
            $departments = Mage::getModel('helpdesk/department')->getCollection()
                ->addUserFilter(Mage::getSingleton('admin/session')->getUser()->getId());
            if(!count($departments)) {
                $indicatorShown = false;
            }
        }

        if (!$this->getConfig()->getDesktopNotificationIsActive() || !$indicatorShown) {
            return '';
        }
        return parent::_toHtml();
    }
}
