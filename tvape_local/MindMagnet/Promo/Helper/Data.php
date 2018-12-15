<?php
class MindMagnet_Promo_Helper_Data extends Mage_Core_Helper_Data
{

    CONST UPGRADE_POPUP_TITLE = 'mindmagnetpromo/upgrade/popup_title';
    CONST UPGRADE_POPUP_BUTTON_TITLE = 'mindmagnetpromo/upgrade/popup_button_title';
    CONST UPGRADE_POPUP_KEEP_FREE_GIFT_TITLE = 'mindmagnetpromo/upgrade/popup_free_gift_title';
    CONST UPGRADE_GIFT_BUTTON_TITLE = 'mindmagnetpromo/upgrade/upgrade_gift_button_title';
    
    public function getStoreId()
    {
        return Mage::app()->getStore()->getStoreId();
    }
    
    public function getUpgradePopupTitle()
    {
        return Mage::getStoreConfig(self::UPGRADE_POPUP_TITLE,$this->getStoreId());
    }
    
    public function getUpgradePopupButtonTitle()
    {
        return Mage::getStoreConfig(self::UPGRADE_POPUP_BUTTON_TITLE,$this->getStoreId());
    }
    
    public function getUpgradePopupKeepFreeGiftTitle()
    {
        return Mage::getStoreConfig(self::UPGRADE_POPUP_KEEP_FREE_GIFT_TITLE,$this->getStoreId());
    }

    public function getUpgradeGiftButtonTitle()
    {
        return Mage::getStoreConfig(self::UPGRADE_GIFT_BUTTON_TITLE,$this->getStoreId());
    }
}