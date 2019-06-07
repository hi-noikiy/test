<?php /* added automatically by conflict fixing tool * if (Mage::getConfig()->getNode('modules/Fenomics_OrderEmail/active')) {
            class Magestore_Adminneworderemail_Block_Adminhtml_Sales_Order_View_Amasty_Pure extends Fenomics_OrderEmail_Block_Adminhtml_Sales_Order_View {}
        } else if (Mage::getConfig()->getNode('modules/Amasty_Email/active')) {
            class Magestore_Adminneworderemail_Block_Adminhtml_Sales_Order_View_Amasty_Pure extends Amasty_Email_Block_Adminhtml_Sales_Order_View {}
        } else { */ 
            class Magestore_Adminneworderemail_Block_Adminhtml_Sales_Order_View_Amasty_Pure extends Mage_Adminhtml_Block_Sales_Order_View {} ?>