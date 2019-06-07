<?php /* added automatically by conflict fixing tool */ if (Mage::getConfig()->getNode('modules/Fenomics_OrderEmail/active')) {
            class Plumrocket_Checkoutspage_Model_Order_Amasty_Pure extends Mage_Sales_Model_Order {}
        } else if (Mage::getConfig()->getNode('modules/Amasty_Orderstatus/active')) {
            class Plumrocket_Checkoutspage_Model_Order_Amasty_Pure extends Mage_Sales_Model_Order {}
        } else { class Plumrocket_Checkoutspage_Model_Order_Amasty_Pure extends Mage_Sales_Model_Order {} } ?>