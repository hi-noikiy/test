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



class Mirasvit_Rma_Helper_Process extends Mage_Core_Helper_Abstract
{
    /**
     * Returns current RMA Configuration object.
     *
     * @return Mirasvit_Rma_Model_Config
     */
    public function getConfig()
    {
        return Mage::getSingleton('rma/config');
    }

    /**
     * @param Mirasvit_Helpdesk_Model_Email $email
     * @param string                        $code
     *
     * @return bool|Mirasvit_Rma_Model_Rma
     *
     * @throws Exception
     */
    public function processEmail($email, $code)
    {
        $rma = false;
        $customer = false;
        $user = false;
        $triggeredByCustomer = true;

        // если у нас есть код, то ок
        // если кода нет, то такую ситуцию мы не обрабатываем

        $guestId = str_replace('RMA-', '', $code);
        //try to find RMA for this email
        $rmas = Mage::getModel('rma/rma')->getCollection()
                    ->addFieldToFilter('guest_id', $guestId)
                    ;
        if (!$rmas->count()) {
            //            echo 'Can\'t find a RMA by guest id '.$guestId;
            return false;
        }

        $rma = $rmas->getFirstItem();

        //try to find staff user for this email
        $users = Mage::getModel('admin/user')->getCollection()
            ->addFieldToFilter('email', $email->getFromEmail());
        if ($users->count()) {
            $user = $users->getFirstItem();
            $triggeredByCustomer = false;
            $rma->setUserId($user->getId());
            $rma->save();
        } else {
            $customers = Mage::getModel('customer/customer')->getCollection()
                ->addAttributeToSelect('*')
                ->addFieldToFilter('email', $email->getFromEmail());
            if ($customers->count()) {
                $customer = $customers->getLastItem(); //если мы можем найти кастомера по емейлу - ОК
            } else { //если кастомер ответил с другого емейла или это гость - создаем его временно
                $customer = new Varien_Object();
                $customer->setName($email->getSenderName());
                $customer->setEmail($email->getFromEmail());
            }
        }

        //add message to rma
        $body = Mage::helper('helpdesk/string')->parseBody($email->getBody(), $email->getFormat());
        $message = $rma->addComment($body, false, $customer, $user, true, true, true, $email);

        return $rma;
    }



}
