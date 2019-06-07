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



class Mirasvit_Rewards_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * @var Mage_Core_Model_Store $_currentStore
     */
    protected $_currentStore;

    /**
     * Sets current store for translation.
     *
     * @param Mage_Core_Model_Store $store
     * @return void
     */
    public function setCurrentStore($store)
    {
        $this->_currentStore = $store;
    }

    /**
     * Returns current store.
     *
     * @return Mage_Core_Model_Store
     */
    public function getCurrentStore()
    {
        if (!$this->_currentStore) {
            $this->_currentStore = Mage::app()->getStore();
        }

        return $this->_currentStore;
    }

    /**
     * @return array
     */
    public function getCoreStoreOptionArray()
    {
        $arr = Mage::getModel('core/store')->getCollection()->toArray();
        foreach ($arr['items'] as $value) {
            $result[$value['store_id']] = $value['name'];
        }

        return $result;
    }

    /************************/

    /**
     * Translates backend messages independently from backend locale.
     * param  string   Message to translate
     * param  string[] Infinite number of params for vsprintf
     * @return string
     */
    public function ____()
    {
        $args = func_get_args();
        $locale = Mage::getStoreConfig('general/locale/code', $this->getCurrentStore()->getId());
        $localeCsv = Mage::getBaseDir('locale').'/'.$locale.'/'.'Mirasvit_Rewards.csv';
        if (!file_exists($localeCsv)) {
            return call_user_func_array(array('Mirasvit_Rewards_Helper_Data', '__'), $args);
        }

        $translator = new Zend_Translate(
            array(
                'adapter' => 'csv',
                'content' => $localeCsv,
                'locale' => substr($locale, 0, 2),
                'delimiter' => ',',
                )
            );
        $msg = $translator->_($args[0]);
        if (is_array($msg)) {
            $msg = reset($msg);
        }
        unset($args[0]);

        return vsprintf($msg, $args);
    }

    /**
     * @return Mirasvit_Rewards_Model_Config
     */
    public function getConfig()
    {
        return Mage::getSingleton('rewards/config');
    }

    /**
     * @return string
     */
    public function getPointsName()
    {
        $unit = $this->getConfig()->getGeneralPointUnitName();
        $unit = str_replace(array('(', ')'), '', $unit);

        return $unit;
    }

    /**
     * @param int $points
     * @return string
     */
    public function formatPoints($points)
    {
        if ($this->getConfig()->getGeneralIsAllowPointsAsMoney()) {
            $rules = Mage::getModel('rewards/spending_rule')->getCollection()
                ->addFieldToFilter('is_active', true);

            if ($rules->count()) {
                $pointsMoney = array();
                foreach ($rules as $rule) {
                    $pointsMoney[] = ($points / $rule->getSpendPoints()) * $rule->getMonetaryStep();
                }
                return Mage::helper('core')->currency(max($pointsMoney), true, false);
            }
        }

        $unit = $this->getConfig()->getGeneralPointUnitName($this->getCurrentStore()->getId());
        if ($points == 1) {
            $unit = preg_replace("/\([^)]+\)/", '', $unit);
        } else {
            $unit = str_replace(array('(', ')'), '', $unit);
        }

        return $points.' '.$unit;
    }

    /**
     * @param float $value
     * @return string
     */
    public function formatCurrency($value)
    {
        return Mage::helper('core')->formatCurrency($value);
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     * @return bool
     */
    public function isMultiship($quote = null)
    {
        return false;
    }

    /**
     * @param int $storeId
     * @return int
     */
    public function getWebsiteId($storeId)
    {
        return Mage::getModel('core/store')->load($storeId)->getWebsiteId();
    }

    /**
     * @return bool
     */
    public function isAdmin()
    {
        return Mage::app()->getStore()->isAdmin() || Mage::getDesign()->getArea() == 'adminhtml';
    }

    /**
     * Highlights order link.
     * @param string $message
     * @param string $type
     * @return string
     */
    public function highlightOrdersInMessage($message, $type)
    {
        $orderMatches = array();
        preg_match_all('/#o\|([0-9]*)/is', $message, $orderMatches);

        if (count($orderMatches) && isset($orderMatches[1])) {
            foreach ($orderMatches[1] as $key => $incrementId) {
                $order = Mage::getModel('sales/order')->getCollection()
                    ->addFieldToFilter('main_table.increment_id', $incrementId)
                    ->getFirstItem();

                $url = false;
                if ($type == 'adminhtml') {
                    $url = Mage::helper('adminhtml')->getUrl(
                        'adminhtml/sales_order/view',
                        array('order_id' => $order->getId())
                    );
                } elseif ($type == 'frontend') {
                    $url = Mage::getUrl('sales/order/view', array('order_id' => $order->getId()));
                }

                if ($url && $order->getId()) {
                    $replace = "<a href='$url' target='_blank'>#$incrementId</a>";
                } else {
                    $replace = "#$incrementId";
                }

                $message = str_replace($orderMatches[0][$key], $replace, $message);
            }
        }

        return $message;
    }

}
