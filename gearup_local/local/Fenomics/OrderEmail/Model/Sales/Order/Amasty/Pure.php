<?php /* added automatically by conflict fixing tool */ if (Mage::getConfig()->getNode('modules/Amasty_Orderstatus/active')) {
            class Fenomics_OrderEmail_Model_Sales_Order_Amasty_Pure extends Amasty_Orderstatus_Model_Sales_Order {}
        } else { class Fenomics_OrderEmail_Model_Sales_Order_Amasty_Pure extends Mage_Sales_Model_Order {} } ?>