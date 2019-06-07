<?php /* added automatically by conflict fixing tool */ if (Mage::getConfig()->getNode('modules/Amasty_Sorting/active')) {
            class Hatimeria_Elastic_Block_Catalog_Product_List_Toolbar_Amasty_Pure extends Amasty_Sorting_Block_Catalog_Product_List_Toolbar {}
        } else if (Mage::getConfig()->getNode('modules/Amasty_Shopby/active')) {
            class Hatimeria_Elastic_Block_Catalog_Product_List_Toolbar_Amasty_Pure extends Amasty_Shopby_Block_Catalog_Product_List_Toolbar {}
        } else { class Hatimeria_Elastic_Block_Catalog_Product_List_Toolbar_Amasty_Pure extends Mage_Catalog_Block_Product_List_Toolbar {} } ?>