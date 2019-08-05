<?php
/**
 * Copyright © 2016 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Ktpl\Newsletterpopup\Block;

/**
 * Blog post info block
 */
class Popup extends \Magento\Newsletter\Block\Subscribe
{
	
	
    /**
     * Retrieve 1 if author page is enabled
     * @return int
     */
    public function isSubscribed()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession = $customerSession = $objectManager->create('Magento\Customer\Model\Session');
        $isOpenPopup=1;
        if($customerSession->isLoggedIn()){
            $isOpenPopup=0;
            $customerID=$customerSession->getCustomerId();
            $customerObj = $objectManager->create('Magento\Customer\Model\Customer')->load($customerID);
            $customerEmail = $customerObj->getEmail();
            $_subscriber = $objectManager->get('Magento\Newsletter\Model\Subscriber');
            $checkSubscriber = $_subscriber->loadByEmail($customerEmail);
            $checkSubscriber->isSubscribed();
            if (!$checkSubscriber->isSubscribed()) {
                $isOpenPopup=1;
            }
        }
        return $isOpenPopup;
    }

}
