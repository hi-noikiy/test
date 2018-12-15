<?php
/**
 * MindMagnet Products Sort
 * Block Class
 *
 * Copyright (C) 2015-2016 MindMagnet <contact@blugento.com>
 * LICENSE: GNU General Public License for more details <http://opensource.org/licenses/gpl-license.php>
 *
 * @package MindMagnet_Sort
 * @author Simona Trifan <simona.plesuvu@mindmagnetsoftware.com>
 * @link http://www.blugento.com
 */

class MindMagnet_Sort_Block_Product_List_Toolbar extends Mage_Catalog_Block_Product_List_Toolbar
{
    public $_exceptions = array(
        'popularity',
        'value'
    );

    public function setCollection($collection)
    {
        parent::setCollection($collection);

        $order = $this->getCurrentOrder();
        $direction = $order == 'price' ? 'asc' : 'desc';
        if ($order) {
            if ($order == 'popularity')
            {
                $entityTypeId = Mage::getModel('eav/entity')->setType(Mage_Catalog_Model_Product::ENTITY)->getTypeId();
                $storeId = Mage::app()->getStore()->getStoreId();
                $attributeCode = 'popularity_multiplication';
                $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', $attributeCode);
                
                $this->getCollection()->getSelect()
                    ->joinLeft('catalog_product_entity_varchar AS cpev','cpev.entity_id = e.entity_id and cpev.attribute_id = "'. $attribute->getId() .'" and cpev.entity_type_id = "'. $entityTypeId .'"','')
                    ->joinLeft('report_event AS _table_views',' _table_views.object_id = e.entity_id and logged_at >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)','IF(cpev.value IS NOT NULL,( (cpev.value) * (COUNT(_table_views.event_id)) ),(COUNT(_table_views.event_id)) )  AS views')
                    ->group('e.entity_id')
                    ->order('views DESC');
            } else
            if ($order == 'value') {
                $this->getCollection()
                    ->setOrder('price', 'desc')->getSelect()->order('price desc');
            } else {
                $this->getCollection()->addAttributeToSelect(array($this->getCurrentOrder()), 'inner')
                    ->setOrder($this->getCurrentOrder(), $direction)->getSelect()->order($this->getCurrentOrder() . ' ' . $direction);
            }
        }

        return $this;
    }
    
    public function getSortEnabled()
    {
        if (!Mage::getStoreConfig('mindmagnet_sort/global_config/enabled')) {
            return false;
        }
        $current_category = Mage::registry('current_category');
        if (is_object($current_category)) {
            if (!$current_category->getData('mmsort_enabled')) {
                return false;
            }
        }

        return true;
    }

    public function getCurrentAttribute()
    {
        if (!$this->getSortEnabled()) {
            return null;
        }

        $order = $this->getCurrentOrder();
        $availableOrders = $this->getAvailableOrders();
        $exceptions = $this->_exceptions;

        if (!array_key_exists($order, $availableOrders)) {
            return null;
        }
        if (in_array($order, $exceptions)) {
            $description = Mage::getStoreConfig('mindmagnet_sort/global_config/' . $order . '_html');
            $image = Mage::getStoreConfig('mindmagnet_sort/global_config/' . $order . '_img');
            if (!$description) {
                $description = null;
            }
            if (!$image) {
                $image = null;
            } else {
                $image = 'mindmagnetsort/logo/' . $image;
            }
            return array(
                'label' => ucfirst(strtolower($order)),
                'description' => $description,
                'image' => $image
            );
        }
        $attributeModel = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', $order);

        $info = $attributeModel->getMmsortDescription();
        if (!$info) {
            $description = null;
        } else {
            $info = json_decode($info, true);
            $storeId = Mage::app()->getStore()->getStoreId();
            $description = isset($info['description'][$storeId]) ? $info['description'][$storeId] : null;
        }
        return array(
            'label' => $attributeModel->getStoreLabel(),
            'description' => $description,
            'image' => $attributeModel->getMmsortImage()
        );
    }

    public function getAvailableOrders()
    {
        $settings = Mage::getSingleton('catalog/layer')->getCurrentCategory()->getData('mmsort_attributes');
        if (!$settings) {
            return array();
        }
        $availableOrder = $this->_availableOrder;
        $settings = explode(',', $settings);
        $settings = array_map('trim', $settings);

        $exceptions = $this->_exceptions;
        $orders = array();
        foreach ($settings as $order) {
            if (!array_key_exists($order, $availableOrder) && !in_array($order, $exceptions)) {
                continue;
            }
            if (isset($availableOrder[$order])) {
                $orders[$order] = $availableOrder[$order];
            } else {
                $attributeModel = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', $order);
                $orders[$order] = $attributeModel->getStoreLabel();

            }
        }

        return $orders;
    }

}
