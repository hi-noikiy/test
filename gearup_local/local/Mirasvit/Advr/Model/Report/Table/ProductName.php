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
 * @package   mirasvit/extension_advr
 * @version   1.2.11
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



class Mirasvit_Advr_Model_Report_Table_ProductName implements Mirasvit_Advr_Model_Report_Table_JoinableInterface
{
    const TABLE_NAME = 'catalog_product_default_name_table';

    /**
     * @inheritdoc
     */
    public function join(Mirasvit_Advr_Model_Report_Abstract $collection, array $data = [])
    {
        if ($collection->isJoined(self::TABLE_NAME)) {
            return $collection;
        }

        $collection->joinRelatedDependencies('catalog/product');
        $product = Mage::getResourceSingleton('catalog/product');
        $attr = $product->getAttribute('name');

        $collection->getSelect()->joinLeft(
            array(self::TABLE_NAME => $attr->getBackend()->getTable()),
            implode(' AND ', array(
                self::TABLE_NAME.'.entity_id = catalog_product_table.entity_id',
                self::TABLE_NAME.'.entity_type_id = '.$product->getTypeId(),
                self::TABLE_NAME.'.attribute_id = '.$attr->getAttributeId(),
                self::TABLE_NAME.'.store_id = 0',
            )),
            array()
        );
        $collection->joinedTables[self::TABLE_NAME] = true;

        return $collection;
    }
}
