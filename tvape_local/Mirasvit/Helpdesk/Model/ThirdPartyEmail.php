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



class Mirasvit_Helpdesk_Model_ThirdPartyEmail extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('helpdesk/thirdPartyEmail');
    }

    /**
     * @param bool|false $emptyOption
     * @param bool|false $customOption
     * @return mixed
     */
    public function toOptionArray($emptyOption = false, $customOption = false)
    {
        return $this->getCollection()->toOptionArray($emptyOption, $customOption);
    }

    /**
     * @param array $data
     * @return Varien_Object
     */
    public function addData(array $data)
    {
        if (isset($data['name']) && strpos($data['name'], 'a:') !== 0) {
            $this->setName($data['name']);
            unset($data['name']);
        }

        return parent::addData($data);
    }

    /**
     * prepare collection for dropdowns.
     *
     * @param int|Mage_Core_Model_Store $store
     *
     * @return Mirasvit_Helpdesk_Model_Resource_ThirdPartyEmail_Collection|Mirasvit_Helpdesk_Model_ThirdPartyEmail[]
     */
    public function getPreparedCollection($store)
    {
        if (is_object($store)) {
            $store = $store->getStoreId();
        }

        return $this->getCollection()->addStoreFilter($store)->addActiveFilter();
    }
}
