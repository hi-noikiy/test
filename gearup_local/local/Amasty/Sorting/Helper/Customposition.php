<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */


require_once 'Zend/Serializer/Adapter/PhpSerialize.php';

use Amasty_Sorting_Model_System_Config_Backend_Customposition as PositionModel;

class Amasty_Sorting_Helper_CustomPosition
{

    /**
     * @param $position
     * @return int
     */
    protected function fixPosition($position)
    {
        return (!empty($position) ? (int)$position : 0);
    }

    /**
     * @param $value
     * @return string
     */
    protected function _serializeValue($value)
    {
        if (is_numeric($value)) {
            $data = (int)$value;
            return (string)$data;
        } else if (is_array($value)) {
            $data = array();
            foreach ($value as $method => $position) {
                if (!array_key_exists($method, $data)) {
                    $data[$method] = $this->fixPosition($position);
                }
            }

            return Zend_Serializer::serialize($data);
        } else {
            return '';
        }
    }

    /**
     * @param $value
     * @return array
     */
    protected function unserializeValue($value)
    {
        if (is_string($value) && !empty($value)) {
            return (array) Mage::helper('amsorting')->unserialize($value);
        }

        return array();
    }

    /**
     * @param $value
     * @return bool
     */
    protected function isEncodedArrayFieldValue($value)
    {
        if (!is_array($value)) {
            return false;
        }

        unset($value['__empty']);
        foreach ($value as $row) {
            if (!is_array($row)
                || !array_key_exists(PositionModel::CUSTOM_POSITION, $row)
                || !array_key_exists(PositionModel::METHOD, $row)
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array $value
     * @return array
     */
    protected function encodeArrayFieldValue(array $value)
    {
        $result = array();
        foreach ($value as $method => $position) {
            $id = Mage::helper('core')->uniqHash('_');
            $result[$id] = array(
                PositionModel::METHOD => $method,
                PositionModel::CUSTOM_POSITION => $this->fixPosition($position),
            );
        }

        return $result;
    }

    /**
     * @param array $value
     * @return array
     */
    protected function _decodeArrayFieldValue(array $value)
    {
        $result = array();
        unset($value['__empty']);
        foreach ($value as $row) {
            if (!is_array($row)
                || !array_key_exists(PositionModel::METHOD, $row)
                || !array_key_exists(PositionModel::CUSTOM_POSITION, $row)
            ) {
                continue;
            }

            $method = $row[PositionModel::METHOD];
            $position = $this->fixPosition($row[PositionModel::CUSTOM_POSITION]);
            $result[$method] = $position;
        }

        array_multisort($result);
        return $result;
    }

    /**
     * @param null $store
     * @return int|null
     */
    public function getConfigValues($store = null)
    {
        $value = Mage::getStoreConfig('amsorting/general/custom_position', $store);
        $value = $this->unserializeValue($value);
        if ($this->isEncodedArrayFieldValue($value)) {
            $value = $this->_decodeArrayFieldValue($value);
        }

        foreach ($value as $method => &$position) {
            $position = $this->fixPosition($position);
        }

        return $value;
    }

    /**
     * @param $value
     * @return array
     */
    public function makeArrayFieldValue($value)
    {
        $value = $this->unserializeValue($value);
        $allValues = Mage::getSingleton('amsorting/catalog_config')->getAttributeUsedForSortByArray();
        $value = $this->mergeKeys($value, $allValues);
        if (!$this->isEncodedArrayFieldValue($value)) {
            $value = $this->encodeArrayFieldValue($value);
        }
        return $value;
    }

    /**
     * @param $value
     * @return array|string
     */
    public function makeStorableArrayFieldValue($value)
    {
        if ($this->isEncodedArrayFieldValue($value)) {
            $value = $this->_decodeArrayFieldValue($value);
        }
        $value = $this->_serializeValue($value);
        return $value;
    }

    /**
     * @param array $value
     * @param array $allValues
     * @return array
     */
    private function mergeKeys(array $value, array $allValues)
    {
        $arrayKeys = array_keys($allValues);
        foreach ($arrayKeys as $key) {
            if (!array_key_exists($key, $value)) {
                $value[$key] = 0;
            }
        }
        asort($value);

        return $value;
    }
}
