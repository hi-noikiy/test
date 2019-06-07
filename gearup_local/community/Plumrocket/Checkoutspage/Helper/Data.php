<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket_Checkoutspage
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

class Plumrocket_Checkoutspage_Helper_Data extends Plumrocket_Checkoutspage_Helper_Main
{
    const COUPON_CUSTOM_CODE = 'custom';

    protected $_order;
    protected $_quote;


    public function moduleEnabled($store = null)
    {
        return (bool)Mage::getStoreConfig('checkoutspage/general/enabled', $store);
    }

    public function sendEmailHistoryEnabled()
    {
        return $this->isModuleEnabled('Plumrocket_SendEmailHistory');
    }


    public function checkRewardPoints()
    {
        return (Mage::getConfig()->getModuleConfig('Plumrocket_Rewards') && (bool)Mage::getStoreConfig('rewards/general/enabled'));
    }


    public function useBetterOrderEmail($store = null)
    {
        return $this->moduleEnabled($store)
            && (bool)Mage::getStoreConfig('checkoutspage/order_email/enabled', $store);
    }


    public function getSecretKey($id = null, $date = null)
    {
        return sha1($this->getCustomerKey().$id.($id ? ( $date ? $date : date('Y-m-d')  ): '').((string)Mage::getConfig()->getNode('global/crypt/key')));
    }


    public function getOrder()
    {
        if (is_null($this->_order)) {
            $this->_order = Mage::getModel('sales/order')->load(Mage::getSingleton('checkout/session')->getLastOrderId());
            if (!$this->_order->getId()) {
                return $this->_order = false;
            }
            foreach($this->_order->getAllItems() as $item) {
                if (!$item->getProduct()) {
                    $item->setProduct( Mage::getModel('catalog/product')->load($item->getProductId()));
                }
            }
            Mage::register('current_order', $this->_order, true);
        }
        return $this->_order;
    }


    public function getQuote()
    {
        if (is_null($this->_quote)) {
            $this->_quote = Mage::getModel('sales/quote')->load(Mage::getSingleton('checkout/session')->getLastQuoteId());

            if (!$this->_quote->getId()) {
                return $this->_quote = false;
            }
        }

        return $this->_quote;
    }


    public function disableExtension()
    {
        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('core_write');
        $connection->delete($resource->getTableName('core/config_data'), array($connection->quoteInto('path IN (?)', array('checkoutspage/general/enabled','checkoutspage/coupon/coupon','checkoutspage/coupon/custom_coupon' ))));
        $config = Mage::getConfig();
        $config->reinit();
        Mage::app()->reinitStores();
    }


    public function canDisplayNextOrderPromoCode($order)
    {
        if (!$order) {
            return false;
        }

        $storeId = $order->getStoreId();

        if ($customer = $order->getCustomer()) {
            $customerGroupId = $customer->getGroupId();
        } elseif ($order->getCustomerId()) {
            $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
            $customerGroupId = $customer->getGroupId();
        } else {
            $customerGroupId = 0;
        }

        $cPref = 'checkoutspage/coupon/';
        return Mage::getStoreConfig($cPref.'enabled', $storeId)
            && (in_array($customerGroupId, explode(',', Mage::getStoreConfig($cPref.'customer_groups', $storeId)) ) )
            && (!Mage::getStoreConfig($cPref.'only_after_first_order', $storeId) || $this->_isCustomerFirstOrder($order));
    }




    protected function _isCustomerFirstOrder($order)
    {
        $orders = Mage::getSingleton('sales/order')->getCollection();

        if ($customerId = $order->getCustomerId()) {
            $orders->addFieldToFilter('customer_id', $customerId);
        } else {
            $orders
                ->addFieldToFilter('customer_email', $order->getCustomerEmail())
                ->addFieldToFilter('store_id', $order->getStoreId());
        }

        $orders
            ->addFieldToFilter('next_order_promo_code', array('neq' => ''))
            ->addFieldToFilter('entity_id', array('neq' => $order->getId()))
            ->setPageSize(1);

        return !count($orders);
    }


    public function getNextOrderPromoCode($order)
    {
        $nopc = $order->getNextOrderPromoCode();
        if ($nopc === '' || $nopc === null) {

            $storeId = $order->getStoreId();

            $coupon = false;
            $ruleId = 0;
            $ruleIds = array();
            $orderProducts = $order->getAllItems();
            foreach ($orderProducts as $item) {
            	$ruleIds[] = $item->getProduct()->getPrNextOrderCoupon();
            }
            if (!empty($ruleIds)) {
	            $ruleColl = Mage::getModel('salesrule/rule')->getCollection()
	            		->addWebsiteGroupDateFilter(Mage::app()->getWebSite()->getId(), $order->getCustomerGroupId())
	                    ->addFieldToFilter('coupon_type', array('in' => array(Mage_SalesRule_Model_Rule::COUPON_TYPE_SPECIFIC,Mage_SalesRule_Model_Rule::COUPON_TYPE_AUTO)))
	                    ->addFieldToFilter('is_active', true)
	                    ->addFieldToFilter('rule_id', array('in' => array(implode(',', $ruleIds))));

				$ruleColl->getSelect()->order('sort_order ASC')->limit('1');

	            $rule = $ruleColl->getFirstItem();
	            $ruleId = $rule->getId();

	            if ($code = $rule->getCode()) {
	            	$coupon = $code;
	        	} elseif ($ruleId) {
	        		$coupon = $this->generateCouponCode($rule);
	        	}
            }

            if (!$coupon) {

                $couponId = Mage::getStoreConfig('checkoutspage/coupon/coupon', $storeId);

                if ($couponId == self::COUPON_CUSTOM_CODE) {
                    $coupon = Mage::getStoreConfig('checkoutspage/coupon/custom_coupon', $storeId);
                } else {
                    $rule = Mage::getModel('salesrule/rule')->load($couponId);
                    $ruleId = $rule->getId();

                    if ( !$rule->getIsActive() || $rule->getCouponType()  == Mage_SalesRule_Model_Rule::COUPON_TYPE_NO_COUPON) {
                        $coupon = false;
                    } else if($code = $rule->getCouponCode()) {
                        $coupon = $code;
                    } else {
                    	$coupon = $this->generateCouponCode($rule);
                    }
                }
            }

            if ($coupon) {
            	if ($ruleId) {
            		$order->setPrNextOrderRuleId($ruleId);
            	}
                $order->setNextOrderPromoCode($coupon);
                if (true || $order->getId()) {
                    $order->save();
                }
            } else {
            	$order->setPrNextOrderRuleId(0);
                $order->setNextOrderPromoCode(false);
            }
        }

        return $order->getNextOrderPromoCode();
    }


    public function generateCouponCode($rule)
    {
    	$generator = Mage::getModel('salesrule/coupon_massgenerator');

        $data = array(
            'max_probability'   => 25,
            'max_attempts'      => 10,
            'uses_per_customer' => 1,
            'uses_per_coupon'   => 1,
            'qty'               => 1, //number of coupons to generate
            'length'            => 8, //length of coupon string
            /**
             * Possible values include:
             * Mage_SalesRule_Helper_Coupon::COUPON_FORMAT_ALPHANUMERIC
             * Mage_SalesRule_Helper_Coupon::COUPON_FORMAT_ALPHABETICAL
             * Mage_SalesRule_Helper_Coupon::COUPON_FORMAT_NUMERIC
             */
            'format'          => Mage_SalesRule_Helper_Coupon::COUPON_FORMAT_ALPHANUMERIC,
            'rule_id'         => $rule->getId(), //the id of the rule you will use as a template
        );

        $generator->validateData($data);
        $generator->setData($data);
        $generator->generatePool();


        $code = Mage::getResourceModel('salesrule/coupon_collection')
            ->addRuleToFilter($rule)
            ->addGeneratedCouponsFilter()
            ->getLastItem();

        return $code->getCode();
    }


    public function getRuleDescrioptinByOerder($order) 
    {
    	$ruleDescription = false;
    	if ($ruleId = $order->getPrNextOrderRuleId()) {
    		$rule = Mage::getModel('salesrule/rule')->load($ruleId);
    		$ruleDescription = $rule->getDescription();
    	}
    	return $ruleDescription;
    }



    public function getAdditionalOrderEmailVars($order, $vars = array())
    {
        if (!isset($vars['store'])) {
            $vars['store'] = Mage::app()->getStore($order->getStoreId());
        }

        if ($this->canDisplayNextOrderPromoCode($order)) {
            $vars['promo_code'] = $this->getNextOrderPromoCode($order);
        }

        if ($order->hasInvoices()) {
            $invoiceId = $order->getInvoiceCollection()->getFirstItem()->getId();
            $vars['print_invoice_url'] = Mage::getUrl('checkoutspage/order/printInvoice', array(
                'invoice_id' => $invoiceId,
                'secret' => Mage::helper('checkoutspage')->getSecretKey($invoiceId),
                '_store' => $order->getStoreId(),
            ));
        }

        if (Mage::getStoreConfig('checkoutspage/facebook/enabled')) {
            $vars['facebook'] = Mage::getStoreConfig('checkoutspage/facebook/facebook_url');
        }

        return $vars;
    }

    public function getFacebookUrl()
    {
        $url = Mage::getStoreConfig('checkoutspage/facebook/facebook_url');
        $_url = parse_url($url);
        if (!isset($_url['scheme'])) {
            $url = 'http://' . $url;
        }
        return $url;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return array
     */
    public function getShippingAddressAdditionalInfo(Mage_Sales_Model_Order $order)
    {
        return array(
            'email' => $order->getData('customer_email')
        );
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return array
     */
    public function getBillingAddressAdditionalInfo(Mage_Sales_Model_Order $order)
    {
        return array(
            'email' => $order->getData('customer_email')
        );
    }
}
