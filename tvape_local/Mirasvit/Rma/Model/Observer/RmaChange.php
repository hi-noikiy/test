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



class Mirasvit_Rma_Model_Observer_RmaChange
{

    /**
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function onRmaChange($observer)
    {
        $this->notifyRmaChange($observer->getRma());
    }


    /**
     * Sends notificator about RMA change.
     *
     * @param Mirasvit_Rma_Model_Rma $rma
     */
    public function notifyRmaChange($rma)
    {
        if ($rma->getStatusId() != $rma->getOrigData('status_id')) {
            $currentStore = Mage::helper('rma')->getStoreByOrder($rma->getOrder())->getId();
            Mage::app()->setCurrentStore(($currentStore) ? $currentStore : $rma->getStore()->getId());

            $status = $rma->getStatus();

            if ($message = $status->getCustomerMessage()) {
                $message = Mage::helper('rma/mail')->parseVariables($message, $rma);
                Mage::helper('rma/mail')->sendNotificationCustomerEmail($rma, $message);
            }

            if ($message = $status->getAdminMessage()) {
                $message = Mage::helper('rma/mail')->parseVariables($message, $rma);
                Mage::helper('rma/mail')->sendNotificationAdminEmail($rma, $message);
            }

            if ($message = $status->getHistoryMessage()) {
                $message = Mage::helper('rma/mail')->parseVariables($message, $rma);
                $isNotified = $status->getCustomerMessage() != '';
                $rma->addComment($message, true, false, false, $isNotified, true);
            }
            if ($status->getCustomerMessage() || $status->getHistoryMessage()) {
                if ($rma->getUser()) {
                    $rma->setLastReplyName($rma->getUser()->getName())
                        ->save();
                }
            }
        } elseif ($rma->getUserId() != $rma->getOrigData('user_id') && $rma->getStatus()->getAdminMessage()) {
            $status = $rma->getStatus();
            $message = $status->getAdminMessage();
            $message = Mage::helper('rma/mail')->parseVariables($message, $rma);
            Mage::helper('rma/mail')->sendNotificationAdminEmail($rma, $message);
        }
    }

}