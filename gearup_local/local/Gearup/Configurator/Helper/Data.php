<?php

class Gearup_Configurator_Helper_Data extends Justselling_Configurator_Helper_Data {

    private $_configurator = false;
    private $_price;

    public function getOptionValueByIdOrTemplateId($id, $templateId = null) {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_read');

        // get all options
        $optionTable = Mage::getSingleton("core/resource")->getTableName('configurator/option');
        $select = $connection->select()
                ->from(
                array('co' => $optionTable), array('id', "template_id", "parent_id", "title", "type", "sort_order", "is_require", "is_visible", "apply_discount", "max_characters", "min_value", "max_value",
            "text_validate", "sku", "value", "placeholder", "option_group_id",
            "upload_type", "upload_maxsize", "upload_filetypes",
            "price", "operator", "alt_title", "operator_value_price", "decimal_place", "product_id", "expression", "url", "font", "font_size", "font_angle",
            "text_alignment", "font_color", "font_pos_x", "font_width_x", "font_width_y", "font_pos_y", "option_image", "default_value", "option_group_id", "product_attribute",
            "matrix_dimension_x", "matrix_operator_x", "matrix_dimension_y", "matrix_operator_y", "matrix_csv_delimiter",
            "listimage_hover", "listimage_style", "listimage_items_per_line", "matrix_filename", "frontend_type", "selectcombi_expression", "css_class", "sort_order_combiimage"
                )
        );
        $originalSelect = clone $select;

        if ($id) {
            $select->where('co.id = ?', $id)
                    ->order(array("co.sort_order ASC", "co.id ASC"));
        } elseif ($templateId) {
            $select->where('template_id = ?', $templateId)
                    ->order(array("co.sort_order ASC", "co.id ASC"));
        }

        $items = $connection->fetchAll($select);

        // prepaire options values and pricelist
        $allOptionIds = array();
        $optionArrayPos = array();
        $arrayPos = 0;
        foreach ($items as $i => $item) {
            $allOptionIds[] = $item['id'];
            $optionArrayPos[$item['id']] = $arrayPos;

            $items[$i]['values'] = array();
            $items[$i]['pricelist'] = array();

            $arrayPos = $arrayPos + 1;
        }

        // get all option values
        if (is_array($allOptionIds) && count($allOptionIds) > 0) {
            $ids = join(',', $allOptionIds);
            $productSDSAttr = Mage::getSingleton('catalog/product')->getResource()->getAttribute('same_day_shipping');
            $sdsTable = $productSDSAttr->getBackend()->getTable();
            $optionValueTable = Mage::getSingleton("core/resource")->getTableName('configurator/option_value');
            $optionValuesSelect = $connection->select()
                    ->from(
                            array('cov' => $optionValueTable), array("id as id",
                        "title as title",
                        "cov.value as value",
                        "sku as sku",
                        "option_id as option_id",
                        "price as price",
                        "sort_order as sort_order",
                        "is_recommended as is_recommended",
                        "if(cpev.value=1,1,0) as is_sds",
                        "product_id as product_id",
                        "if(csi.stock_status=1,1,0) as availability"
                            )
                    )->joinLeft(array('csi' => Mage::getSingleton("core/resource")->getTableName('cataloginventory/stock_status')), 'csi.product_id = cov.product_id')
                    ->joinLeft(array('cpev' => $sdsTable), 'cov.product_id = cpev.entity_id ANd (cpev.entity_type_id = ' . $productSDSAttr->getEntityTypeId() . ' AND cpev.attribute_id = ' . $productSDSAttr->getId() . ')',['cpev.value as cpev_value'])
                    ->where('cov.option_id IN (' . $ids . ')')
                    ->order(array("cov.sort_order ASC", "cov.value ASC"))
                    ->group('cov.id');
            $optionValues = $connection->fetchAll($optionValuesSelect);
        } else {
            $optionValues = array();
        }


        // set option values to option
        foreach ($optionValues as $optionValue) {
            $optionId = $optionValue['option_id'];
            $arrayPos = $optionArrayPos[$optionId];
            $items[$arrayPos]['values'][] = $optionValue;
        }


        if (is_array($allOptionIds) && count($allOptionIds) > 0) {
            // get all pricelist values
            $pricelistTable = Mage::getSingleton("core/resource")->getTableName('configurator/pricelist');
            $pricelistSelect = $connection->select()
                    ->from(
                    array('cp' => $pricelistTable), array(
                "id",
                "option_id",
                "operator",
                "value",
                "price"
                    )
            );
            $pricelistSelect->where('cp.option_id IN (' . $ids . ')');
            $pricelistSelect->order(array('CAST(`value` AS SIGNED) ASC'));

            $pricelistValues = $connection->fetchAll($pricelistSelect);
        } else {
            $pricelistValues = array();
        }

        // set pricelist entries to option
        foreach ($pricelistValues as $pricelist) {
            $optionId = $pricelist['option_id'];
            $arrayPos = $optionArrayPos[$optionId];
            $items[$arrayPos]['pricelist'][] = $pricelist;
        }


        if (count($items) > 0) {
            $templateId = $items[0]['template_id'];
            $groups = Mage::getModel("configurator/optiongroup")->getCollection();
            $groups->addFilter('template_id', $templateId);
        } else {
            $groups = array();
        }

        if ($id && $templateId) {
            $originalSelect->where('template_id = ?', $templateId)
                    ->order(array("co.sort_order ASC", "co.id ASC"));
            $itemsForSearch = $connection->fetchAll($originalSelect);
        } else {
            $itemsForSearch = $items;
        }

        foreach ($items as $key => $item) {
            $parentId = $item['parent_id'];
            $parentTitle = Mage::helper('configurator')->__('None');
            if ($parentId) {
                foreach ($itemsForSearch as $parentItem) {
                    if ($parentItem['id'] == $parentId) {
                        $parentTitle = $parentItem['title'];
                        break;
                    }
                }
            }
            $items[$key]['parent_title'] = $parentTitle;

            $defaultValue = $item['default_value'];
            $defaultTitle = Mage::helper('configurator')->__('no default');
            if ($defaultValue) {
                foreach ($item['values'] as $optionValue) {
                    if ($optionValue['id'] == $defaultValue) {
                        $defaultTitle = $optionValue['title'];
                        break;
                    }
                }
            }
            $items[$key]['default_title'] = $defaultTitle;

            $optionGroupId = $item['option_group_id'];
            $optionGroupTitle = "";
            if ($optionGroupId) {
                foreach ($groups as $group) {
                    if ($group->getId() == $optionGroupId) {
                        $optionGroupTitle = $group->getTitle();
                        break;
                    }
                }
            }
            $items[$key]['option_group'] = $optionGroupTitle;

            $frontendType = $item['frontend_type'];
            if ($frontendType) {
                $type = $item['type'];
                $items[$key]['type'] = $type . '-' . $frontendType;
            }
        }

        $items = array_values($items);
        return $items;
    }

    public function getTemplateData($product) {
        $product = Mage::getModel('catalog/product')->load($product->getId());
        $customOption = $product->getOptions();

        foreach ($customOption as $o) {
            $optionType = $o->getType();
            if ($optionType == 'configurator') {
                $this->_configurator = true;
                $storeId = Mage::app()->getStore()->getStoreId();
                $templateModel = Mage::getModel('configurator/template');
                $templateId = $templateModel->getLinkedTemplateId($o->getOptionId(), $storeId);
                return $templateModel->load($templateId);
            }
        }
        return null;
    }

    public function isConfigurator() {
        return $this->_configurator;
    }

    public function getBasePriceWithDefaultOptions($_product,$taxHelper) {
    	$this->_price = 0;
        $product = '';
        $taxAmount= 0;
            $templateData = $this->getTemplateData($_product);
            $price = 0;
            if (isset($_product) && $templateData) { // Get preconfigures values when coming back from the cart            
                $templateOption = $this->getAllTemplateOptions($templateData->getId());
                foreach ($templateOption as $index) {
                    $defaultOptionId[] = ($index->getSortOrderCombiimage()) ? $index->getSortOrderCombiimage() : 0;
                }
                $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
                $optionValueTable = Mage::getSingleton("core/resource")->getTableName('configurator/option_value');
                $optionValuesSelect = $connection
                        ->select('*')
                        ->from(array('cov' => $optionValueTable), array('product_id', 'sort_order', 'option_id', 'id'));
                $optionValuesSelect->where('cov.id IN (' . implode(',', $defaultOptionId) . ')');
                $optionValuesSelect->order(array("cov.sort_order ASC", "cov.value ASC"));
                $optionValues = $connection->fetchAll($optionValuesSelect);
                foreach ($optionValues as $index) {
                    $product = Mage::getModel('catalog/product')->load($index['product_id']);
                    if ($product->getStockItem()->getIsInStock() && $product->getStatus() != 2) {
                    $setDOption = Mage::getModel('configurator/option')->load($index['option_id']);
                    if ($setDOption->getDefaultValue() != $setDOption->getSortOrderCombiimage()) {
                        $setDOption->setDefaultValue($index['id'])
                                ->save();
                        $cache = Mage::app()->getCache();
                        $cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array("PRODCONF_TEMPLATE_" . $templateData->getId()));
                    }
                            
                    $amt = $this->priceConvert($product->getFinalPrice());
                    $this->_price += $amt;
                } else {
                    try {

                        $productStatusAttr = Mage::getSingleton('catalog/product')->getResource()->getAttribute('status');
                        $statusTable = $productStatusAttr->getBackend()->getTable();
                        $optionValuesSelect = $connection->select()
                                        ->from(array('cov' => $optionValueTable), array('id', 'product_id'))
                                        ->joinLeft(array('csi' => Mage::getSingleton("core/resource")->getTableName('cataloginventory/stock_item')), 'csi.product_id = cov.product_id')
                                        ->joinLeft(array('cpei' => $statusTable), 'cpei.entity_id = cov.product_id')
                                        ->where('cov.sort_order > ?  AND csi.qty > 0 ', $index['sort_order'])
                                        ->where('option_id = ?', $index['option_id'])
                                        ->where('cpei.value != 2 AND cpei.entity_type_id  = ?', $productStatusAttr->getEntityTypeId())
                                        ->where('cpei.attribute_id = ?', $productStatusAttr->getId())
                                        ->order(array("cov.sort_order ASC", "cov.value ASC"))->limit(1);

                        $index2 = $connection->fetchRow($optionValuesSelect);

                        $product = Mage::getModel('catalog/product')->load($index2['product_id']);
                        $setDOption = Mage::getModel('configurator/option')->load($index['option_id']);

                        if ($index2['id'] != $setDOption->getDefaultValue()) {
                            $cache = Mage::app()->getCache();
                            $cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array("PRODCONF_TEMPLATE_" . $templateData->getId()));
                            $setDOption->setDefaultValue($index2['id'])
                                    ->save();
                        }
                        if ($product->getStockItem()->getIsInStock()) {
                            $amt = $this->priceConvert($product->getFinalPrice());                            
                            $this->_price += $amt;
                        }
                    } catch (Exception $e) {
                        
                    }
                }
            }
            
        }
        
        return [$this->_price,$taxHelper->getPrice($product, $this->_price,1)];//$this->priceConvert($this->_price, true);
    }

    protected function getAllTemplateOptions($template_id = NULL) {
        if (!$template_id) {
            $template_id = $this->getTemplateId();
        }
        $options = Mage::getModel("configurator/option")->getCollection();
        $options->addFieldToFilter("template_id", $template_id);
        $options->load();
        return $options;
    }

    public function priceConvert($price, $convertInto = false) {
        $CurrencyObj = Mage::app()->getStore();
        $baseCurrencyCode = $CurrencyObj->getBaseCurrencyCode();
        $currentCurrencyCode = $CurrencyObj->getCurrentCurrencyCode();
// Allowed currencies
        $allowedCurrencies = Mage::getModel('directory/currency')->getConfigAllowCurrencies();
        $rates = Mage::getModel('directory/currency')->getCurrencyRates($baseCurrencyCode, array_values($allowedCurrencies));
        // the price converted
        if ($convertInto) {
            return Mage::helper('rounding')->process(Mage::getModel('directory/currency')->load($baseCurrencyCode), $price / $rates[$currentCurrencyCode]);
        }
        return Mage::helper('rounding')->process(Mage::getModel('directory/currency')->load($currentCurrencyCode), $price * $rates[$currentCurrencyCode]);
    }

    /**
     * Disable add to cart if required options are missing from the configurator.
     * @param type $product
     */
    public function disableAddtoCart($product) {
        $customOption = $product->getOptions();
        $connection = Mage::getSingleton('core/resource')->getConnection('core_read');

        foreach ($customOption as $o) {
            $optionType = $o->getType();
            if ($optionType == 'configurator') {
                $ids = [];
                $templateId = Mage::getModel("configurator/template")->getLinkedTemplateId($o->getOptionId());
                $requirdOptionIds = Mage::getModel("configurator/option")->getCollection()
                        ->addFieldToSelect(['id', 'title'])
                        ->addFieldToFilter('template_id', $templateId)
                        ->addFieldToFilter('is_require', 1);
                foreach ($requirdOptionIds as $index):
                    $ids[$index['id']] = $index['title'];
                endforeach;

                $optionValueTable = Mage::getSingleton("core/resource")->getTableName('configurator/option_value');
                $optionValuesSelect = $connection->select()
                        ->from(
                                array('cov' => $optionValueTable), array("option_id ,count(option_id) as option_id_count")
                        )->joinLeft(array('csi' => Mage::getSingleton("core/resource")->getTableName('cataloginventory/stock_status')), 'csi.product_id = cov.product_id')
                        ->where('cov.option_id IN (' . implode(',', array_keys($ids)) . ') AND (csi.stock_status IS NULL OR csi.stock_status = 1) ')
                        ->order(array("cov.sort_order ASC", "cov.value ASC"))
                        ->group('cov.option_id');

                $optionValues = $connection->fetchAll($optionValuesSelect);
                if (count($optionValues) != count($requirdOptionIds) && count($requirdOptionIds) > 1) {
                    foreach($optionValues as $index):
                        if(in_array($index['option_id'],array_keys($ids)))
                                unset($ids[$index['option_id']]);
                    endforeach;

                    Mage::getSingleton("checkout/session")->addError(
                            Mage::helper('checkout')->__("Apologies, the required component <font color=\"black\">'%s'</font> is currently out of stock from '%s', Please contact our technical team at <a href='mailto:support@gear-up.me'>support@gear-up.me</a>",implode(',',$ids),$product->getName()));
                    return true;
                }
            }
        }
        return false;
    }

}
