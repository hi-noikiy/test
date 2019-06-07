<?php
class Gearup_Elasticsearch_Helper_Indexer_Product extends Wyomind_Elasticsearch_Helper_Indexer_Product
{
    public function export($filters = array(),
                           $split = 2000)
    {
        try {
          

            set_time_limit(0); // export might be a bit slow

            $result = array();
            $product = Mage::getModel('catalog/product');
            $attributesByTable = $product->getResource()->loadAllAttributes($product)->getAttributesByTable();
            $mainTable = $product->getResource()->getTable('catalog_product_entity');
            $resource = $this->_getResource();
            $adapter = $this->_getAdapter();
            $product = new Varien_Object();
            $isEnterprise = Mage::helper('core')->isModuleEnabled('Enterprise_UrlRewrite');

            foreach (Mage::app()->getStores() as $store) {
                /** @var Mage_Core_Model_Store $store */
                if (!$store->getIsActive()) {
                    continue;
                }

                $storeId = (int)$store->getId();

                if (isset($filters['store_id'])) {
                    if (!is_array($filters['store_id'])) {
                        $filters['store_id'] = array($filters['store_id']);
                    }
                    if (!in_array($storeId, $filters['store_id'])) {
                        continue;
                    }
                }

                $categoryNames = $this->getCategoryNames($store);

                $taxHelper = Mage::helper('tax');
                $priceDisplayType = $taxHelper->getPriceDisplayType($store);
                $priceIncludeTax = $taxHelper->priceIncludesTax($store);
                $showPriceIncludingTax = in_array($priceDisplayType, array(
                    Mage_Tax_Model_Config::DISPLAY_TYPE_INCLUDING_TAX,
                    Mage_Tax_Model_Config::DISPLAY_TYPE_BOTH,
                ));
                $defaultGroupId = Mage_Customer_Model_Group::NOT_LOGGED_IN_ID;
                $customerTaxClass = Mage::getModel('customer/group')->getTaxClassId($defaultGroupId);

                $this->handleMessage(' > Exporting products of store %s', $store->getCode());

                $result[$storeId] = array();
                $select = $adapter->select()->from(array('e' => $mainTable), 'entity_id');

                // Filter products that are enabled for current store website
                $select->join(
                    array('product_website' => $resource->getTableName('catalog/product_website')), 'product_website.product_id = e.entity_id AND ' . $adapter->quoteInto('product_website.website_id = ?', $store->getWebsiteId()), array()
                );

                // Index only in stock products if showing out of stock products is not needed
                if (!$this->isIndexOutOfStockProducts($store)) {
                    $conditions = array("stock_status.product_id = e.entity_id",
                        $adapter->quoteInto("stock_status.website_id = ?", $store->getWebsiteId()),
                        "stock_status.stock_status=1"
                    );
                    $select->join(array("stock_status" => $resource->getTableName("cataloginventory_stock_status")), implode(" AND ", $conditions), null);
                }

                if (!empty($filters)) {
                    foreach ($filters as $field => $value) {
                        if ($field == 'store_id' || $value === null) {
                            continue;
                        }
                        if (is_array($value)) {
                            $select->where("e.$field IN (?)", $value);
                        } else {
                            $select->where("e.$field = ?", $value);
                        }
                    }
                }

                // Handle enabled products
                $attributeId = Mage::getSingleton('eav/entity_attribute')
                    ->getIdByCode(Mage_Catalog_Model_Product::ENTITY, 'status');
                if ($attributeId) {
                    $enabled = Mage_Catalog_Model_Product_Status::STATUS_ENABLED;
                    $select->join(
                        array('status' => $resource->getTableName('catalog_product_entity_int')), "status.attribute_id = $attributeId AND status.entity_id = e.entity_id", array()
                    );
                    $select->where('status.value = ?', $enabled);
                    $select->where('status.store_id IN (?)', array(0, $storeId));
                }

                // Fetch entity ids that match
                $allEntityIds = $adapter->fetchCol($select);
                $allEntityIds = array_unique($allEntityIds);
                $this->handleMessage(' > Found %d products', count($allEntityIds));

                $allEntityIds = array_chunk($allEntityIds, $split);
                $countChunks = count($allEntityIds);
                if ($countChunks > 1) {
                    $this->handleMessage(' > Split products array into %d chunks for better performances', $split);
                }
                $attrOptionLabels = array();

                // Loop through products
                foreach ($allEntityIds as $i => $entityIds) {
                    if ($countChunks > 1) {
                        $this->handleMessage(' > %d/%d', $i + 1, $countChunks);
                    }
                    $products = array();
                    foreach ($attributesByTable as $table => $allAttributes) {
                        $allAttributes = array_chunk($allAttributes, 25);
                        foreach ($allAttributes as $attributes) {
                            $select = $adapter->select()
                                ->from(array('e' => $mainTable), array('id' => 'entity_id', 'sku', 'type_id'));

                            foreach ($attributes as $attribute) {
                                if (!$this->isAttributeIndexable($attribute)) {
                                    continue;
                                }
                                $attributeId = $attribute->getAttributeId();
                                $attributeCode = $attribute->getAttributeCode();

                                if (!isset($attrOptionLabels[$attributeCode]) && $this->isAttributeUsingOptions($attribute)) {
                                    $options = $attribute->setStoreId($storeId)
                                        ->getSource()
                                        ->getAllOptions();
                                    foreach ($options as $option) {
                                        if (!$option['value']) {
                                            continue;
                                        }
                                        $attrOptionLabels[$attributeCode][$option['value']] = $option['label'];
                                    }
                                }
                                $alias1 = $attributeCode . '_default';
                                $select->joinLeft(
                                    array($alias1 => $adapter->getTableName($table)), "$alias1.attribute_id = $attributeId AND $alias1.entity_id = e.entity_id AND $alias1.store_id = 0", array()
                                );
                                $alias2 = $attributeCode . '_store';
                                $valueExpr = $adapter->getCheckSql("$alias2.value IS NULL", "$alias1.value", "$alias2.value");
                                $select->joinLeft(
                                    array($alias2 => $adapter->getTableName($table)), "$alias2.attribute_id = $attributeId AND $alias2.entity_id = e.entity_id AND $alias2.store_id = {$store->getId()}", array($attributeCode => $valueExpr)
                                );
                            }

                            $select->where('e.entity_id IN (?)', $entityIds);
                            $query = $adapter->query($select);

                            $imgSize = Mage::getStoreConfig('elasticsearch/product/image_size');
                            // $imgPlaceholder = (string) Mage::helper('catalog/image')->init(Mage::getModel('catalog/product'), 'small_image')->resize($imgSize, $imgSize);

                            while ($row = $query->fetch()) {
                                $row = array_filter($row, 'strlen');
                                $row['id'] = (int)$row['id'];
                                $productId = $row['id'];
                                if (!isset($products[$productId])) {
                                    $products[$productId] = array();
                                }
                                foreach ($row as $code => &$value) {
                                    if (isset($attributesByTable[$table][$code])) {
                                        $value = $this->_formatValue($attributesByTable[$table][$code], $value, $store);
                                    }
                                    if (isset($attrOptionLabels[$code])) {
                                        if (is_array($value)) {
                                            $label = array();
                                            foreach ($value as $val) {
                                                if (isset($attrOptionLabels[$code][$val])) {
                                                    $label[] = $attrOptionLabels[$code][$val];
                                                }
                                            }
                                            if (!empty($label)) {
                                                $row[$code] = $label;
                                            }
                                        } elseif (isset($attrOptionLabels[$code][$value])) {
                                            $row[$code] = $attrOptionLabels[$code][$value];
                                        }
                                    }
                                }
                                unset($value);
                                $products[$productId] = array_merge($products[$productId], $row);
                                if (!isset($products[$productId]['image']) || $products[$productId]['image'] == "") {
                                    //$products[$productId]['image'] = $imgPlaceholder;   
                                }
                            }
                        }
                    }

                    // custom options
                    if (Mage::getStoreConfig('elasticsearch/product/custom_options') === "1") {
                        $key = "custom_options";

                        $tableCpo = $resource->getTableName('catalog_product_option');
                        $tableCpot = $resource->getTableName('catalog_product_option_title');
                        $tableCpotv = $resource->getTableName('catalog_product_option_type_value');
                        $tableCpott = $resource->getTableName('catalog_product_option_type_title');
                        $tableCpotp = $resource->getTableName('catalog_product_option_type_price');

                        $select = $select = $adapter->select();
                        $select
                            ->from(array('cpo' => $tableCpo), array('product_id'))
                            ->columns(array("group_concat(`cpott`.`title`) as values"))
                            ->joinleft(
                                array('cpot' => $tableCpot), 'cpot.option_id=cpo.option_id AND cpot.store_id=0', array('option' => 'title', 'option_id', 'store_id')
                            )->joinleft(
                                array('cpotv' => $tableCpotv), 'cpotv.option_id = cpo.option_id', 'sku'
                            )->joinleft(
                                array('cpott' => $tableCpott), 'cpott.option_type_id=cpotv.option_type_id AND cpott.store_id=cpot.store_id', 'title AS value'
                            )->joinleft(
                                array('cpotp' => $tableCpotp), 'cpotp.option_type_id=cpotv.option_type_id AND cpotp.store_id=cpot.store_id', array('price', 'price_type')
                            )->order(array('product_id', 'cpotv.sort_order ASC'))
                            ->where('product_id IN (?)', $entityIds)
                            ->group(array("product_id", "cpot.title"));
                        $query = $adapter->query($select);
                        while ($row = $query->fetch()) {
                            if ($row['values'] != "") {
                                $productId = $row['product_id'];
                                $products[$productId][$key][] = explode(',', $row['values']);
                            }
                        }
                    }

                    // Add parent products in order to retrieve products that have associated products
                    $key = '_parent_ids';
                    $select = $adapter->select()
                        ->from($resource->getTableName('catalog_product_relation'), array('parent_id', 'child_id'))
                        ->where('child_id IN (?)', $entityIds);
                    $query = $adapter->query($select);
                    while ($row = $query->fetch()) {
                        $productId = $row['child_id'];
                        if (!isset($products[$productId][$key])) {
                            $products[$productId][$key] = array();
                        }
                        $products[$productId][$key][] = (int)$row['parent_id'];
                    }

                    // Add categories
                    $key = '_categories';
                    $columns = array(
                        'product_id' => 'product_id',
                        'category_ids' => new Zend_Db_Expr(
                            "TRIM(
                            BOTH ',' FROM CONCAT(
                                TRIM(BOTH ',' FROM GROUP_CONCAT(IF(is_parent = 0, category_id, '') SEPARATOR ',')),
                                ',',
                                TRIM(BOTH ',' FROM GROUP_CONCAT(IF(is_parent = 1, category_id, '') SEPARATOR ','))
                            )
                        )"),
                    );
                    $select = $adapter->select()
                        ->from(array($resource->getTableName('catalog_category_product_index')), $columns)
                        ->where('product_id IN (?)', $entityIds)
                        ->where('store_id = ?', $storeId)
                        ->where('category_id > 1')// ignore global root category
                        ->where('category_id != ?', $store->getRootCategoryId())// ignore store root category
                        ->group('product_id');
                    $query = $adapter->query($select);
                    while ($row = $query->fetch()) {
                        $categoryIds = explode(',', $row['category_ids']);
                        if (empty($categoryIds)) {
                            continue;
                        }
                        $productId = $row['product_id'];
                        if (!isset($products[$productId][$key])) {
                            $products[$productId][$key] = array();
                        }
                        foreach ($categoryIds as $categoryId) {
                            if (isset($categoryNames[$categoryId])) {
                                $products[$productId][$key][] = $categoryNames[$categoryId];
                            }
                        }
                        $products[$productId][$key] = array_values(array_unique($products[$productId][$key]));
                    }

                    // Add prices
                    $key = '_prices';
                    $least = $adapter->getLeastSql(array('prices.min_price', 'prices.tier_price'));
                    $minimalExpr = $adapter->getCheckSql('prices.tier_price IS NOT NULL', $least, 'prices.min_price');
                    $cols = array(
                        'customer_group_id',
                        'entity_id', 'price', 'final_price',
                        'minimal_price' => $minimalExpr,
                        'min_price', 'max_price', 'tier_price'
                    );
                    $select = $adapter->select()
                        ->from(array('prices' => $resource->getTableName('catalog_product_index_price')), $cols)
                        ->where('prices.entity_id IN (?)', $entityIds)
                        ->where('prices.website_id = ?', $store->getWebsiteId());
                    //->where('prices.customer_group_id = ?', $defaultGroupId);
                    $query = $adapter->query($select);
                    while ($row = $query->fetch()) {

                        $productId = $row['entity_id'];
                        $customerGroupId = $row['customer_group_id'];
                        unset($row['customer_group_id']);
                        unset($row['entity_id']);
                        $row['price'] = (float)number_format((float)$row['price'], 2, '.', '');
                        $row['final_price'] = (float)number_format((float)$row['final_price'], 2, '.', '');
                        if (null !== $row['minimal_price']) {
                            $row['minimal_price'] = (float)$row['minimal_price'];
                        }
                        if (null !== $row['min_price']) {
                            $row['min_price'] = (float)$row['min_price'];
                        }
                        if (null !== $row['max_price']) {
                            $row['max_price'] = (float)$row['max_price'];
                        }
                        if (null !== $row['tier_price']) {
                            $row['tier_price'] = (float)$row['tier_price'];
                        }
                        if (isset($row['group_price']) && null !== $row['group_price']) {
                            $row['group_price'] = (float)$row['group_price'];
                        }
                        /*if (isset($products[$productId]['tax_class_id'])) {
                            $taxClassId = $products[$productId]['tax_class_id'];
                            if ($taxClassId && !$priceIncludeTax) {
                                $product = new Varien_Object();
                                $product->setTaxClassId($taxClassId);
                                foreach ($row as &$price) {
                                    $price = $taxHelper->getPrice(
                                        $product, $price, $showPriceIncludingTax, null, null, $customerTaxClass, $store
                                    );
                                }
                            }
                        }*/
                        
                        if ($products[$productId]['type_id'] == "bundle" && ($taxHelper->displayPriceIncludingTax() || $taxHelper->displayBothPrices())) {
                            $_product = Mage::getModel('catalog/product')->load($productId);
                            $_product->setStoreId($storeId);
                            list($minPrice, $maxPrice) = $_product->getPriceModel()->getTotalPrices($_product, null, true, true);
                            $row['min_price'] = $minPrice;
                            $row['max_price'] = $maxPrice;
                        }

                        $products[$productId][$key . "_" . $customerGroupId] = $row;
                    }

                    if ($showPriceIncludingTax && $priceDisplayType == Mage_Tax_Model_Config::DISPLAY_TYPE_BOTH) {

                        $key = '_prices_ht';
                        $least = $adapter->getLeastSql(array('prices.min_price', 'prices.tier_price'));
                        $minimalExpr = $adapter->getCheckSql('prices.tier_price IS NOT NULL', $least, 'prices.min_price');
                        $cols = array(
                            'customer_group_id',
                            'entity_id', 'price', 'final_price',
                            'minimal_price' => $minimalExpr,
                            'min_price', 'max_price', 'tier_price'
                        );
                        $select = $adapter->select()
                            ->from(array('prices' => $resource->getTableName('catalog_product_index_price')), $cols)
                            ->where('prices.entity_id IN (?)', $entityIds)
                            ->where('prices.website_id = ?', $store->getWebsiteId());
                            //->where('prices.customer_group_id = ?', $defaultGroupId);
                        $query = $adapter->query($select);
                        while ($row = $query->fetch()) {
                            $productId = $row['entity_id'];
                            $customerGroupId = $row['customer_group_id'];
                            unset($row['customer_group_id']);
                            unset($row['entity_id']);
                            $row['price'] = (float)number_format((float)$row['price'], 2, '.', '');
                            $row['final_price'] = (float)number_format((float)$row['final_price'], 2, '.', '');
                            if (null !== $row['minimal_price']) {
                                $row['minimal_price'] = (float)$row['minimal_price'];
                            }
                            if (null !== $row['min_price']) {
                                $row['min_price'] = (float)$row['min_price'];
                            }
                            if (null !== $row['max_price']) {
                                $row['max_price'] = (float)$row['max_price'];
                            }
                            if (null !== $row['tier_price']) {
                                $row['tier_price'] = (float)$row['tier_price'];
                            }
                            if (isset($row['group_price']) && null !== $row['group_price']) {
                                $row['group_price'] = (float)$row['group_price'];
                            }

                            /*if (isset($products[$productId]['tax_class_id'])) {
                                $taxClassId = $products[$productId]['tax_class_id'];
                                if ($taxClassId) {
                                    $product->setTaxClassId($taxClassId);
                                    foreach ($row as &$price) {
                                        $price = $taxHelper->getPrice(
                                            $product, $price, false, null, null, $customerTaxClass, $store
                                        );
                                    }
                                    unset($price);
                                }
                            }*/
                            $products[$productId][$key . "_" . $customerGroupId] = $row;
                        }
                    }

                    // Add product URL
                    $key = '_url';
                    $suffix = '';

                    if ($isEnterprise) {
                        $entityType = Enterprise_Catalog_Model_Product::URL_REWRITE_ENTITY_TYPE;
                        $select = $adapter->select()
                            ->from(
                                array('url_key' => $resource->getTableName(array('catalog/product', 'url_key'))), array('product_id' => 'entity_id')
                            )
                            ->join(
                                array('url_rewrite' => $resource->getTableName('enterprise_urlrewrite/url_rewrite')), 'url_key.value_id = url_rewrite.value_id AND url_rewrite.entity_type = ' . $entityType, array('request_path')
                            )
                            ->where('entity_id IN (?) AND url_key.store_id IN (0, ' . $storeId . ')', $entityIds)
                            ->order("url_key.store_id ASC");

                        $suffix = $store->getConfig(Mage_Catalog_Helper_Product::XML_PATH_PRODUCT_URL_SUFFIX);
                        if ($suffix) {
                            if (strpos($suffix, ".") !== false) {
                                $suffix = $suffix;
                            } else {
                                $suffix = '.' . $suffix;
                            }
                        }
                    } else {
                        $select = $adapter->select()
                            ->from($resource->getTableName('core_url_rewrite'), array('product_id', 'request_path'))
                            ->where('store_id = ?', $storeId)
                            ->where('category_id IS NULL')
                            ->where("(options IS NULL OR options = '')")
                            ->where('product_id IN (?)', $entityIds);
                    }

                    $query = $adapter->query($select);
                    while ($row = $query->fetch()) {
                        $productId = $row['product_id'];
                        $row['product_id'] = (int)$row['product_id'];
                        $products[$productId][$key] = $store->getBaseUrl() . $row['request_path'] . $suffix;
                    }

                    $observerData = array(
                        'indexer' => $this,
                        'store' => $store,
                        'products' => $products,
                    );
                    $observerObject = new Varien_Object($observerData);
                    Mage::dispatchEvent('wyomind_elasticsearch_index_export', array("data" => $observerObject));
                    $products = $observerObject->getProducts();

                    if (!empty($products)) {
                        $result[$storeId] = array_merge($result[$storeId], $products);
                    }
                }

                $this->handleMessage(' > Products exported');
            }

            return $result;
        } catch (Exception $e) {
            throw $e;
        }
    }
}
	 