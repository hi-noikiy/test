<?php /* added automatically by conflict fixing tool */ if (Mage::getConfig()->getNode('modules/Amasty_Email/active')) {
            class Fenomics_OrderEmail_Block_Adminhtml_Sales_Order_View_Amasty_Pure extends Amasty_Email_Block_Adminhtml_Sales_Order_View {}
        } else { class Fenomics_OrderEmail_Block_Adminhtml_Sales_Order_View_Amasty_Pure extends Mage_Adminhtml_Block_Sales_Order_View {} } ?>