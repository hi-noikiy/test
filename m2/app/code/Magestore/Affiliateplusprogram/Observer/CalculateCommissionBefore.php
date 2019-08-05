<?php
/**
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Affiliateplusprogram
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

namespace Magestore\Affiliateplusprogram\Observer;

use Magento\Framework\Event\ObserverInterface;

class CalculateCommissionBefore extends AbtractObserver implements ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->_helper->isPluginEnabled()) {
            return;
        }
        $order = $observer->getEvent()->getOrder();
        $order->setQuote($this->_getQuoteModel()->load($order->getQuoteId()));
        $items = $order->getAllItems();
        $affiliateInfo = $observer->getEvent()->getAffiliateInfo();
        $commissionObj = $observer->getEvent()->getCommissionObj();

        $commission = $commissionObj->getCommission();
        $orderItemIds = $commissionObj->getOrderItemIds();
        $orderItemNames = $commissionObj->getOrderItemNames();
        $commissionItems = $commissionObj->getCommissionItems();
        $extraContent = $commissionObj->getExtraContent();
        $tierCommissions = $commissionObj->getTierCommissions();
        $store = $this->getStore();

        foreach ($affiliateInfo as $info)
            if ($account = $info['account']) {
                if ($account->getUsingCoupon()) {
                    $program = $account->getUsingProgram();
                    if (!$program)
                        return $this;

                    /* Edit By Jack */
                    $storeId = $this->_backendSessionQuote->getStoreId();
                    if ($storeId && !$store->getId())
                        $program = $this->_programFactory->create()
                            ->setStoreId($storeId)
                            ->load($program->getId());
                    /* End Edit */

                    $commissionObj->setDefaultCommission(false);
                    if (!$program->validateOrder($order))
                        return $this;
                }
                foreach ($items as $item) {
                    if ($item->getParentItemId()) {
                        continue;
                    }
                    if ($account->getUsingCoupon()) {
                        if (!$program->validateItem($item))
                            continue;
                    } else {
                        if (in_array($item->getProductId(), $commissionItems))
                            continue;
                        $program = $this->_helper
                            ->initProgram($account->getId(), $order)
                            ->getProgramByItemAccount($item, $account);
                    }
                    if (!$program) {
                        continue;
                    }
                    $affiliateType = $program->getAffiliateType() ? $program->getAffiliateType() : $this->_getConfigHelper()->getCommissionConfig('affiliate_type');
                    /* Changed BY Adam for customize function: Commission for whole cart 22/07/2014 */
                    $baseItemsPrice = $this->_getBaseItemsPrice($items, $account, $program); // total price of the items that belong to this program.
                    /* Endcode */
                    $affiliateplusCommissionItem = '';
                    if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                        $childHasCommission = false;
                        foreach ($item->getChildrenItems() as $child) {
                            $baseProfit = $this->_getBaseProfit($affiliateType, $child);
                            if ($baseProfit <= 0){
                                continue;
                            }
                            $commissionType = $program->getCommissionType();
                            $commissionValue = floatval($program->getCommission());
                            if ($this->_cookieHelper->getNumberOrdered()) {
                                if ($program->getSecCommission()) {
                                    $commissionType = $program->getSecCommissionType();
                                    $commissionValue = floatval($program->getSecondaryCommission());
                                }
                            }
                            if (!$commissionValue){
                                continue;
                            }
                            $childHasCommission = true;
                            /* Changed BY Adam commission for whole cart 22/07/2014 */
                            if ($commissionType == \Magestore\Affiliateplus\Model\System\Config\Source\Fixedpercentage::COMMISSION_FIXED_AMOUNT_PER_CART) {
                                $commissionValue = min($commissionValue, $baseItemsPrice);
                                $itemPrice = $child->getQtyOrdered() * $child->getBasePrice() - $child->getBaseDiscountAmount() - $child->getBaseAffiliateplusAmount();
                                $itemCommission = $itemPrice * $commissionValue / $baseItemsPrice;
                            } elseif ($commissionType == \Magestore\Affiliateplus\Model\System\Config\Source\Fixedpercentage::COMMISSION_FIXED_AMOUNT_PER_ITEM) {
                                $itemCommission = min($child->getQtyOrdered() * $commissionValue, $baseProfit);
                            } else {
                                if ($commissionValue > 100){
                                    $commissionValue = 100;
                                }
                                if ($commissionValue < 0){
                                    $commissionValue = 0;
                                }
                                $itemCommission = $baseProfit * $commissionValue / 100;
                            }

                            // Changed By Adam 14/08/2014: Invoice tung phan
                            $affiliateplusCommissionItem .= $itemCommission . ",";
                            $commissionObject = new \Magento\Framework\DataObject(
                                [
                                    'profit' => $baseProfit,
                                    'commission' => $itemCommission,
                                    'tier_commission' => array(),
                                    'base_item_price' => $baseItemsPrice, // Added By Adam 22/07/2014
                                    'affiliateplus_commission_item' => $affiliateplusCommissionItem, // Added By Adam 14/08/2014
                                ]
                            );
                            $this->_eventManager->dispatch('affiliateplusprogram_calculate_tier_commission',
                                [
                                    'item' => $child,
                                    'account' => $account,
                                    'commission_obj' => $commissionObject,
                                    'program' => $program
                                ]
                            );
                            if ($commissionObject->getTierCommission()){
                                $tierCommissions[$child->getId()] = $commissionObject->getTierCommission();
                            }
                            $commission += $commissionObject->getCommission();
                            $child->setAffiliateplusCommission($commissionObject->getCommission());

                            // Changed By Adam 14/08/2014: Invoice tung phan
                            $child->setAffiliateplusCommissionItem($commissionObject->getAffiliateplusCommissionItem());

                            if (!isset($extraContent[$program->getId()]['total_amount'])){
                                $extraContent[$program->getId()]['total_amount'] = 0;
                            }
                            $extraContent[$program->getId()]['total_amount'] += $child->getBasePrice() * $child->getQtyOrdered();
                            if (!isset($extraContent[$program->getId()]['commission'])){
                                $extraContent[$program->getId()]['commission'] = 0;
                            }
                            $extraContent[$program->getId()]['commission'] += $commissionObject->getCommission();

                            $orderItemIds[] = $child->getProduct()->getId();
                            $orderItemNames[] = $child->getName();

                            $extraContent[$program->getId()]['order_item_ids'][] = $child->getProduct()->getId();
                            $extraContent[$program->getId()]['order_item_names'][] = $child->getName();
                        }
                        if ($childHasCommission) {
                            $commissionItems[] = $item->getProductId();

                            $extraContent[$program->getId()]['program_name'] = $program->getName();
                        }
                    } else {

                        $baseProfit = $this->_getBaseProfit($affiliateType, $item);
                        if ($baseProfit <= 0){
                            continue;
                        }
                        $commissionType = $program->getCommissionType();
                        $commissionValue = floatval($program->getCommission());
                        if ($this->_cookieHelper->getNumberOrdered()) {
                            if ($program->getSecCommission()) {
                                $commissionType = $program->getSecCommissionType();
                                $commissionValue = floatval($program->getSecondaryCommission());
                            }
                        }
                        if (!$commissionValue){
                            continue;
                        }
                        //jack
                        if ($item->getProduct())
                            $inProductId = $item->getProduct()->getId();
                        else
                            $inProductId = $item->getProductId();

                        $orderItemIds[] = $inProductId;
                        $orderItemNames[] = $item->getName();
                        $commissionItems[] = $item->getProductId();

                        /* Changed BY Adam: commission for whole cart 22/07/2014 */
                        if ($commissionType == \Magestore\Affiliateplus\Model\System\Config\Source\Fixedpercentage::COMMISSION_FIXED_AMOUNT_PER_CART) {
                            $commissionValue = min($commissionValue, $baseItemsPrice);
                            $itemPrice = $item->getQtyOrdered() * $item->getBasePrice() - $item->getBaseDiscountAmount() - $item->getBaseAffiliateplusAmount();
                            $itemCommission = $itemPrice * $commissionValue / $baseItemsPrice;
                        } elseif ($commissionType == \Magestore\Affiliateplus\Model\System\Config\Source\Fixedpercentage::COMMISSION_FIXED_AMOUNT_PER_ITEM) {
                            $itemCommission = min($item->getQtyOrdered() * $commissionValue, $baseProfit);
                        } else {
                            if ($commissionValue > 100){
                                $commissionValue = 100;
                            }
                            if ($commissionValue < 0){
                                $commissionValue = 0;
                            }
                            $itemCommission = $baseProfit * $commissionValue / 100;
                        }
                        // Changed By Adam 14/08/2014: Invoice tung phan
                        $affiliateplusCommissionItem .= $itemCommission . ",";
                        $commissionObject = new \Magento\Framework\DataObject(
                            [
                                'profit' => $baseProfit,
                                'commission' => $itemCommission,
                                'tier_commission' => array(),
                                'base_item_price' => $baseItemsPrice, // Added By Adam 22/07/2014
                                'affiliateplus_commission_item' => $affiliateplusCommissionItem, // Added By Adam 14/08/2014
                            ]
                        );
                        $this->_eventManager->dispatch('affiliateplusprogram_calculate_tier_commission',
                            [
                                'item' => $item,
                                'account' => $account,
                                'commission_obj' => $commissionObject,
                                'program' => $program
                            ]
                        );
                        if ($commissionObject->getTierCommission()){
                            $tierCommissions[$item->getProductId()] = $commissionObject->getTierCommission();
                        }

                        $commission += $commissionObject->getCommission();
                        $item->setAffiliateplusCommission($commissionObject->getCommission());

                        // Changed By Adam 14/08/2014: Invoice tung phan
                        $item->setAffiliateplusCommissionItem($commissionObject->getAffiliateplusCommissionItem());

                        $extraContent[$program->getId()]['program_name'] = $program->getName();

                        $extraContent[$program->getId()]['order_item_ids'][] = $inProductId;
                        $extraContent[$program->getId()]['order_item_names'][] = $item->getName();
                        if (!isset($extraContent[$program->getId()]['total_amount'])){
                            $extraContent[$program->getId()]['total_amount'] = 0;
                        }
                        $extraContent[$program->getId()]['total_amount'] += $item->getBasePrice() * $item->getQtyOrdered();
                        if (!isset($extraContent[$program->getId()]['commission'])){
                            $extraContent[$program->getId()]['commission'] = 0;
                        }
                        $extraContent[$program->getId()]['commission'] += $commissionObject->getCommission();
                    }
                }
                $commissionObj->setCommission($commission);
                $commissionObj->setOrderItemIds($orderItemIds);
                $commissionObj->setOrderItemNames($orderItemNames);
                $commissionObj->setCommissionItems($commissionItems);
                $commissionObj->setExtraContent($extraContent);
                $commissionObj->setTierCommissions($tierCommissions);
                return $this;
            }
        return $this;
    }

    /**
     * @return mixed
     */
    protected function _getConfigHelper(){
        return $this->_objectManager->create('Magestore\Affiliateplus\Helper\Config');
    }

    /**
     * @return mixed
     */
    protected function _getQuoteModel(){
        return $this->_objectManager->create('Magento\Quote\Model\Quote');
    }

    /**
     * @param $items
     * @param $account
     * @param $program
     * @return bool|int
     */
    protected function _getBaseItemsPrice($items, $account, $program){
        $baseItemsPrice = 0;
        foreach ($items as $_item) {
            if ($_item->getParentItemId()) {
                return false;
            }

            if (!$program->validateItem($_item)) {
                return false;
            }
            // Changed By Adam 01/08/2014: don't calculate the items that belong to higher program's priority
            if (!$account->getUsingCoupon() && $this->_helper->checkItemInHigherPriorityProgram($account->getId(), $_item, $program->getPriority())) {
                return false;
            }

            if ($_item->getHasChildren() && $_item->isChildrenCalculated()) {
                foreach ($_item->getChildrenItems() as $child) {
                    $baseItemsPrice += $_item->getQtyOrdered() * ($child->getQtyOrdered() * $child->getBasePrice() - $child->getBaseDiscountAmount() - $child->getBaseAffiliateplusAmount());
                }
            } elseif ($_item->getProduct()) {
                $baseItemsPrice += $_item->getQtyOrdered() * $_item->getBasePrice() - $_item->getBaseDiscountAmount() - $_item->getBaseAffiliateplusAmount();
            }
        }
        return $baseItemsPrice;
    }

    /**
     * @param $affiliateType
     * @param $item
     * @return mixed
     */
    protected function _getBaseProfit($affiliateType, $item){
        if ($affiliateType == \Magestore\Affiliateplus\Model\System\Config\Source\Type::XML_PATH_COMMISSION_TYPE_PROFIT){
            $baseProfit = $item->getBasePrice() - $item->getBaseCost();
        }else{
            $baseProfit = $item->getBasePrice();
        }
        $baseProfit = $item->getQtyOrdered() * $baseProfit - $item->getBaseDiscountAmount() - $item->getBaseAffiliateplusAmount();
        return $baseProfit;
    }
}