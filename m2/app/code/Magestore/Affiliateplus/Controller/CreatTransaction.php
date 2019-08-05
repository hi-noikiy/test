<?php

/**
 * Magestore.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Affiliateplus
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */
namespace Magestore\Affiliateplus\Controller\Index;

/**
 * Class CreatTransaction
 * @package Magestore\Affiliateplus\Controller\Index
 */
class CreatTransaction extends \Magestore\Affiliateplus\Controller\AbstractAction
{
    /**
     * Execute action
     */
    public function execute()
    {
        if(!$this->_dataHelper->isAffiliateModuleEnabled()){
            return $this->_redirect($this->getBaseUrl());
        }

        $params = $this->getRequest()->getParams();
        $hashCode = $params['hash_code'];
        $actionId = $params['action_id'];
        $action = $this->_objectManager_>create('Magestore\Affiliateplus\Model\Action')->load($actionId);
        $hashCodeCompare = md5($action->getCreatedDate() . $action->getId());
        if ($hashCode == $hashCodeCompare && !$action->getIsCommission()) {
            $isUnique = 1;
            $action->setIsUnique(1)->save();
            $this->_eventManager->dispatch('affiliateplus_save_action_before',
                [
                    'action' => $action,
                    'is_unique' => $isUnique,
                 ]
            );
        }
    }


}
