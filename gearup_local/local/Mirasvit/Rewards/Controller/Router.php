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


class Mirasvit_Rewards_Controller_Router extends Mage_Core_Controller_Varien_Router_Standard
{
    /**
     * @param Varien_Object $observer
     * @return void
     */
    public function addUrlsRouter($observer)
    {
        $front = $observer->getEvent()->getFront();
        $urlsRouter = new Mirasvit_Rewards_Controller_Router();
        $front->addRouter('rewards', $urlsRouter);
    }

    /**
     * @param Zend_Controller_Request_Http $request
     * @return bool
     */
    public function match(Zend_Controller_Request_Http $request)
    {
        if (!Mage::isInstalled()) {
            Mage::app()->getFrontController()->getResponse()
                ->setRedirect(Mage::getUrl('install'))
                ->sendResponse();
            exit;
        }
        $pathInfo = $request->getPathInfo();
        $identifier = trim($pathInfo, '/');
        $parts      = explode('/', $identifier);
        if (count($parts) >= 2 && $parts[0] == 'r') {
            $params = array('customer_id' => (int)$parts[1]);
            if (isset($parts[2])) {
                if (!isset($parts[3])) {
                    $params['category_id'] = (int)$parts[2];
                } else {
                    $params['product_id'] = (int)$parts[2];
                }
            }
            if (isset($parts[3])) {
                $params['category_id'] = (int)$parts[3];
            }

            $request
                ->setRouteName('rewards')
                ->setModuleName('rewards')
                ->setControllerName('referral')
                ->setActionName('referralVisit')
                ->setParams($params)
                ->setAlias(
                    Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS,
                    'rewards'
                );
            return true;
        }
        return false;
    }
}
