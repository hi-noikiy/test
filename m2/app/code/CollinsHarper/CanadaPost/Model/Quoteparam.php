<?php
/**
 * Copyright © 2015 Collins Harper. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace CollinsHarper\CanadaPost\Model;


use Magento\Framework\DataObject\IdentityInterface;

/**
 * Canada Post Sell Online (* deprecated)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Quoteparam extends \Magento\Framework\Model\AbstractModel implements IdentityInterface
{

    const CACHE_TAG = 'cpcanadapost_quoteparam';

    /**
     *
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;
    
    /**
     *
     * @var string
     */
    protected $_eventPrefix = self::CACHE_TAG;



    protected function _construct()
    {
        $this->_init('CollinsHarper\CanadaPost\Model\ResourceModel\Quoteparam');
    }

    /**
     *
     * @param int $quote_id
     * @param bool $signature
     * @param bool $coverage
     * @param float $coverage_amount
     * @param bool $card_for_pickup
     * @param bool $do_not_safe_drop
     * @param bool $leave_at_door
     * @param int $cp_office_id
     * @param string $est_delivery_date
     * @return Collinsharper_Canpost_Model_Quote_Param
     */
    public function updateForQuote(
        $quote_id,
        $signature,
        $coverage,
        $coverage_amount,
        $card_for_pickup,
        $do_not_safe_drop,
        $leave_at_door,
        $cp_office_id = null,
        $est_delivery_date = 0
    )
    {

        $item = $this->getByQuoteId($quote_id);

        if (!$item->getId()) {

            $item = $this;

            $item->setMagentoQuoteId($quote_id);

        }

        $item->setSignature($signature)
            ->setCoverage($coverage)
            ->setCardForPickup($card_for_pickup)
            ->setDoNotSafeDrop($do_not_safe_drop)
            ->setLeaveAtDoor($leave_at_door);

        if (!empty($cp_office_id)) {

            $item->setCpOfficeId($cp_office_id);

        }

        if (!empty($est_delivery_date)) {

            $item->setEstDeliveryDate(strtotime($est_delivery_date));

        }

        if ($coverage && !empty($coverage_amount)) {

            $item->setCoverageAmount($coverage_amount);

        }

        $item->save();

        return $item;

    }

    /**
     * 
     * @param int $quote_id
     * @param string $est_delivery_date
     * @return \CollinsHarper\CanadaPost\Model\Quoteparam
     */
    public function updateEstDeliveryDate($quote_id, $est_delivery_date)
    {

        $item = $this->getByQuoteId($quote_id);

        if (!$item->getId()) {

            $item = $this;

            $item->setMagentoQuoteId($quote_id);

        }

        if (!empty($est_delivery_date)) {

            $item->setEstDeliveryDate(strtotime($est_delivery_date))->save();

        }

        return $item;

    }

    
    public function getByQuoteId($quote_id)
    {

        return $this->getCollection()->addFieldToFilter('magento_quote_id', $quote_id)->getFirstItem();

    }


    /**
     * 
     * @param int $quote_id
     * @param array $data
     * @return array
     */
    public function getParamsByQuote($quote_id, $data = array())
    {

        $params = $this->getByQuoteId($quote_id);

        if ($params->getId()) {

            $data['signature'] = $params->getSignature();

            $data['coverage'] = $params->getCoverage();

            $data['coverage_amount'] = $params->getCoverageAmount();

            $data['office_id'] = $params->getCpOfficeId();

            $data['card_for_pickup'] = $params->getCardForPickup();

            $data['do_not_safe_drop'] = $params->getDoNotSafeDrop();

            $data['leave_at_door'] = $params->getLeaveAtDoor();

            $data['est_delivery_date'] = $params->getEstDeliveryDate();

        } else {

            $data['signature'] = 0;

            $data['coverage'] = 0;

            $data['coverage_amount'] = 0;

            $data['office_id'] = 0;

            $data['card_for_pickup'] = 0;

            $data['do_not_safe_drop'] = 0;

            $data['leave_at_door'] = 0;

            $data['est_delivery_date'] = 0;

        }

        return $data;

    }


    /**
     * 
     * @param int $quote_id
     * @param array $data
     */
    public function setParams($quote_id, $data)
    {

        $params = $this->getByQuoteId($quote_id);

        if ($params->getId()) {

            foreach ($data as $par => $value) {

                if ($par == 'office_id') {

                    $par = 'cp_office_id';

                }

                $params->setData($par, $value);

            }

            $params->save();

        }

    }

    public function getIdentifier()
    {
        return $this->getData(self::IDENTIFIER);
    }

    public function resetParams($quote_id)
    {

        $params = $this->getByQuoteId($quote_id);

        if ($params->getId()) {

            $params->setSignature(0)
                ->setCoverage(0)
                ->setCoverageAmount(0)
                ->setCpOfficeId(NULL)
                ->setCardForPickup(0)
                ->setDoNotSafeDrop(0)
                ->setLeaveAtDoor(0)
                ->setEstDeliveryDate(0)
                ->save();

        }

    }

    /**
     * Return identifiers for object
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }



}
