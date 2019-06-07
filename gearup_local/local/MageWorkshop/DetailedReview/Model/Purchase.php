<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <info@mage-review.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_DetailedReview
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (https://mage-review.com/)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <info@mage-review.com>
 */

/**
 * Class MageWorkshop_DetailedReview_Model_Purchase
 *
 * @method int getCustomerId()
 * @method MageWorkshop_DetailedReview_Model_Purchase setCustomerId(int $customerId)
 * @method string getCustomerEmail()
 * @method MageWorkshop_DetailedReview_Model_Purchase setCustomerEmail(string $customerEmail)
 * @method int getProductId()
 * @method MageWorkshop_DetailedReview_Model_Purchase setProductId(int $productId)
 * @method int getStoreId()
 * @method MageWorkshop_DetailedReview_Model_Purchase setStoreId(int $storeId)
 */

class MageWorkshop_DetailedReview_Model_Purchase extends Mage_Core_Model_Abstract
{
    /**
     * @var array
     */
    protected $_availableFields = array(
        'customer_email',
        'product_id',
        'created_at',
        'store_id'
    );

    public function _construct()
    {
        parent::_construct();
        $this->_init('detailedreview/purchase');
    }

    public function updateData()
    {
        $verifiedBuyer = Mage::getSingleton('index/indexer')
            ->getProcessByCode(MageWorkshop_DetailedReview_Helper_Config::DETAILEDREVIEW_VERIFIED_BUYER_INDEXER_CODE);
        try {
            if ($verifiedBuyer) {
                if ($verifiedBuyer->getMode() === Mage_Index_Model_Process::MODE_MANUAL) {
                    $this->getResource()->updateData();
                    $verifiedBuyer->changeStatus(Mage_Index_Model_Process::STATUS_PENDING);
                }
            }
        } catch (Exception $e) {
            $verifiedBuyer->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);
            Mage::logException($e);
        }
    }

    public function loadByAttributes($attributes)
    {
        $attributes = $this->_cropData($attributes);
        $this->setData($this->getResource()->loadByAttributes($attributes));
        return $this;
    }

    protected function _cropData(array $data)
    {
        $croppedValues = array();
        $allowedKeys = array_fill_keys($this->_availableFields, true);

        foreach ($data as $key => $value) {
            if (isset($allowedKeys[$key])) {
                $croppedValues[$key] = $value;
            }
        }

        return $croppedValues;
    }
}
