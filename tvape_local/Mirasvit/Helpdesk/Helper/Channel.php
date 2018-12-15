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



class Mirasvit_Helpdesk_Helper_Channel extends Mage_Core_Helper_Abstract
{
    public function getLabel($code)
    {
        $channels = array(
            Mirasvit_Helpdesk_Model_Config::CHANNEL_FEEDBACK_TAB => 'Feedback Tab',
            Mirasvit_Helpdesk_Model_Config::CHANNEL_CONTACT_FORM => 'Contact Form',
            Mirasvit_Helpdesk_Model_Config::CHANNEL_CUSTOMER_ACCOUNT => 'Customer Account',
            Mirasvit_Helpdesk_Model_Config::CHANNEL_EMAIL => 'Email',
            Mirasvit_Helpdesk_Model_Config::CHANNEL_BACKEND => 'Backend',
        );
        if (isset($channels[$code])) {
            return $channels[$code];
        }
    }
}
