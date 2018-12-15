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



class Mirasvit_Helpdesk_Helper_Output extends Mage_Core_Helper_Abstract
{
    /**
     * @return string
     */
    public function getMessageAuthor($message)
    {
        $str = '';
        if ($message->getTriggeredBy() == Mirasvit_Helpdesk_Model_Config::CUSTOMER) {
            if ($message->getCustomerName() != '') {
                $str = $message->getCustomerName() . ', ';
            }
            $str .= $message->getCustomerEmail();
        }
        elseif ($message->getTriggeredBy() == Mirasvit_Helpdesk_Model_Config::USER) {
            $str = $message->getUserName() . ' ';
            if ($message->isThirdParty()) {
                $str .= $this->__('to %s (third party)', $message->getThirdPartyEmail());
            }
        }
        elseif ($message->getTriggeredBy() == Mirasvit_Helpdesk_Model_Config::THIRD) {
            $str = $this->__('%s %s (third party)', $message->getThirdPartyName(), $message->getThirdPartyEmail());
        }

        return $str;
    }
}