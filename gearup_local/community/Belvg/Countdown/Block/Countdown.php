<?php
/**
 * BelVG LLC.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 *
/**********************************************
 *        MAGENTO EDITION USAGE NOTICE        *
 **********************************************/
/* This package designed for Magento COMMUNITY edition
 * BelVG does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BelVG does not provide extension support in case of
 * incorrect edition usage.
/**********************************************
 *        DISCLAIMER                          *
 **********************************************/
/* Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future.
 **********************************************
 * @category   Belvg
 * @package    Belvg_Countdown
 * @copyright  Copyright (c) 2010 - 2012 BelVG LLC. (http://www.belvg.com)
 * @license    http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 */

class Belvg_Countdown_Block_Countdown extends Mage_Core_Block_Template
{
    /**
     * Get 'Countdown' object, filtered by type and entity id
     *
     * @param string
     * @param int Entity Id (example: if $type is 'product', $entity_id is Product id)
     * @return Mage_Countdown_Model_Countdown
     */
    public function getCountdown($type, $id)
    {
        return Mage::getModel('countdown/countdown')->getCountdown($type, $id);
    }

    /**
     * Should the timer be shown or not
     *
     * @param Mage_Countdown_Model_Countdown
     * @return boolean
     */
    public function isShow($countdown)
    {
        $show = FALSE;
        if ($countdown) {
            $on      = $countdown->getExpireDatetimeOn();
            $off     = $countdown->getExpireDatetimeOff();
            $current = Mage::helper('countdown')->getCurrentTime('sql');

            if (($off > $on && $current > $on && $current < $off) || ($off < $on && $current < $off)) {
                $show = TRUE;
            }
        }

        return $show;
    }
    public function isShowproduct($countdown)
    {
        $show = FALSE;
        if ($countdown->getExpireDatetimeOn()) {
            $on      = $countdown->getExpireDatetimeOn();
            $off     = $countdown->getExpireDatetimeOff();
            $current = Mage::helper('countdown')->getCurrentTime('sql');

            if ($current > $on ) {
                $show = TRUE;
            }
        }

        return $show;
    }
    

    /**
     * Embed html and js code, displaying the timer for �Countdown� object
     *
     * @param string
     * @param int Entity Id (example: if $type is 'product', $entity_id is Product id)
     * @return HTML
     */
    public function getItemClock($type, $id)
    {
        $countdown      = $this->getCountdown($type, $id);
        $current        = Mage::helper('countdown')->getCurrentTime('js');
        $finishRedirect = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);

        if ($this->isShow($countdown)) {
            $this->setproductprice($type, $id);
            $html = '
            <div class="timerbar">
                <p class="time" id="block_time_' . $type . '_' . $id . '"></p>
                <p class="message">closes in</p>
            </div>
            <script>
                jQblvg("#block_time_' . $type . '_' . $id . '").countdown("' . $countdown->getExpireDatetimeOff() . '", {
                    prefix:"",
                    finish:"' . Mage::helper('countdown')->__('FINISH') . '",
                    redirect:"' . $finishRedirect . '",
                    dateparse:"' . $current . '",
                    lang:' . Mage::helper('countdown')->getTimeLocale() . '
                });
            </script>';
        } else {
            $html = '';
        }

        return $html;
    }

    public function getHomeItemClock($type, $id)
    {
        $countdown      = $this->getCountdown($type, $id);
        $current        = Mage::helper('countdown')->getCurrentTime('js');
        $finishRedirect = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);

        if ($this->isShow($countdown)) {
            $this->setproductprice($type, $id);
            $html = '
            <div class="timerbar">
                <p class="time spec-offer" id="block_time_' . $type . '_' . $id . '"></p>
            </div>
            <script>
                jQblvg("#block_time_' . $type . '_' . $id . '").countdown("' . $countdown->getExpireDatetimeOff() . '", {
                    prefix:"",
                    finish:"' . Mage::helper('countdown')->__('FINISH') . '",
                    redirect:"' . $finishRedirect . '",
                    dateparse:"' . $current . '"
                });
            </script>';
        } else {
            //if(Mage::getBlockSingleton('page/html_header')->getIsHomePage()) {
                 $html = '
                    <div class="timerbar">
                        <p class="time spec-offer">00:00:00</p>
                    </div>';
           // } else {
               // $html = '';
           // }
        }

        return $html;
    }
    
    public function setproductprice($type, $id){
        $countdown = $this->getCountdown($type, $id);
        if($type == 'product'){
            $product = Mage::getmodel('catalog/product')->load($id);
            if($countdown->getOfferPrice() && $product->getSpecialPrice() != $countdown->getOfferPrice()){
                $product->setSpecialPrice($countdown->getOfferPrice());
                $storeid = Mage::app()->getStore()->getStoreId();
                Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
                $product->save();
                Mage::app()->setCurrentStore($storeid);
            }
            if (!$countdown->getIssetQty()) {
                $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($id);
                if ($countdown->getOfferQty() && $stockItem->getManageStock() && $stockItem->getQty() != $countdown->getOfferQty()) {
                    $qty = $countdown->getOfferQty();
                    $stockItem->setQty($qty);
                    $stockItem->setIsInStock((int)($qty > 0));
                    $stockItem->save();
                    $deal =Mage::getModel('countdown/countdown')->load($id,'entity_id');
                    $countdown->setIssetQty(1);
                    $countdown->save();
                }
            }
        }
    }
}