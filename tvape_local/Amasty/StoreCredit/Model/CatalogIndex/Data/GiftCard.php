<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_StoreCredit
 */
class Amasty_StoreCredit_Model_CatalogIndex_Data_StoreCredit extends Mage_CatalogIndex_Model_Data_Simple
{
    public function getTypeCode()
    {
        return Amasty_StoreCredit_Model_Catalog_Product_Type_StoreCredit::TYPE_STORECREDIT_PRODUCT;
    }
}
