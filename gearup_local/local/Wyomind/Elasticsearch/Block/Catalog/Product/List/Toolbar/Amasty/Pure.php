<?php /* added automatically by conflict fixing tool */ if (Mage::getConfig()->getNode('modules/MageWorx_SeoFriendlyLN/active')) {
            class Wyomind_Elasticsearch_Block_Catalog_Product_List_Toolbar_Amasty_Pure extends MageWorx_SeoFriendlyLN_Block_Catalog_Product_List_Toolbar {}
        } else if (Mage::getConfig()->getNode('modules/MageWorkshop_DetailedReview/active')) {
            class Wyomind_Elasticsearch_Block_Catalog_Product_List_Toolbar_Amasty_Pure extends MageWorkshop_DetailedReview_Block_Product_List_Toolbar {}
        } else if (Mage::getConfig()->getNode('modules/Hatimeria_Elastic/active')) {
            class Wyomind_Elasticsearch_Block_Catalog_Product_List_Toolbar_Amasty_Pure extends Hatimeria_Elastic_Block_Catalog_Product_List_Toolbar {}
        } else if (Mage::getConfig()->getNode('modules/Amasty_Sorting/active')) {
            class Wyomind_Elasticsearch_Block_Catalog_Product_List_Toolbar_Amasty_Pure extends Amasty_Sorting_Block_Catalog_Product_List_Toolbar {}
        } else if (Mage::getConfig()->getNode('modules/Amasty_Shopby/active')) {
            class Wyomind_Elasticsearch_Block_Catalog_Product_List_Toolbar_Amasty_Pure extends Amasty_Shopby_Block_Catalog_Product_List_Toolbar {}
        } else { class Wyomind_Elasticsearch_Block_Catalog_Product_List_Toolbar_Amasty_Pure extends Amasty_Sorting_Block_Catalog_Product_List_Toolbar {} } ?>