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



class Mirasvit_Helpdesk_Helper_Storeview
{
    /**
     * @param Mage_Core_Model_Abstract $object
     * @param $field
     * @param $value
     */
    public function setStoreViewValue($object, $field, $value)
    {
        $storeId = (int) $object->getStoreId();
        $serializedValue = $object->getData($field);
        $arr = $this->unserialize($serializedValue);

        if ($storeId === 0) {
            $arr[0] = $value;
        } else {
            $arr[$storeId] = $value;
            if (!isset($arr[0])) {
                $arr[0] = $value;
            }
        }
        $object->setData($field, serialize($arr));
    }

    /**
     * @param object $object
     * @param string $field
     *
     * @return string
     */
    public function getStoreViewValue($object, $field)
    {
        $storeId = $object->getStoreId();
        if ($storeId === null) {
            $storeId = Mage::app()->getStore()->getId();
        }
        $serializedValue = $object->getData($field);
        $arr = $this->unserialize($serializedValue);
        $defaultValue = null;
        if (isset($arr[0])) {
            $defaultValue = $arr[0];
        }

        if (isset($arr[$storeId])) {
            $localizedValue = $arr[$storeId];
        } else {
            $localizedValue = $defaultValue;
        }

        return $localizedValue;
    }

    public function unserialize($string)
    {
        if (strpos($string, 'a:') !== 0) {
            return array(0 => $string);
        }
        if (!$string) {
            return array();
        }
        try {
            return unserialize($string);
        } catch (Exception $e) {
            return array(0 => $string);
        }
    }
}
