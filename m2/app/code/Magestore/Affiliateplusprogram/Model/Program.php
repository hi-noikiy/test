<?php

/**
 * Magestore.
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
namespace Magestore\Affiliateplusprogram\Model;

/**
 * Class Program
 * @package Magestore\Affiliateplusprogram\Model
 */
class Program extends \Magento\SalesRule\Model\Rule
{

    protected $_storeViewId =  null;
    protected $_eventPrefix = 'affiliateplus_program';
    protected $_eventObject = 'affiliateplus_program';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resourceConnection;
    /**
     * Model construct that should be used for object initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magestore\Affiliateplusprogram\Model\ResourceModel\Program');
    }

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\SalesRule\Model\CouponFactory $couponFactory,
        \Magento\SalesRule\Model\Coupon\CodegeneratorFactory $codegenFactory,
        \Magento\SalesRule\Model\Rule\Condition\CombineFactory $condCombineFactory,
        \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory $condProdCombineF,
        \Magento\SalesRule\Model\ResourceModel\Coupon\Collection $couponCollection,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->_objectManager = $objectManager;
        $this->_resourceConnection = $resourceConnection;
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $localeDate,
            $couponFactory,
            $codegenFactory,
            $condCombineFactory,
            $condProdCombineF,
            $couponCollection,
            $storeManager,
            $resource,
            $resourceCollection,
            $data
        );
    }


    /**
     * @return int
     */
    public function getStoreId()
    {
        return $this->_storeViewId;
    }

    /**
     * @param $storeViewId
     * @return $this
     */
    public function setStoreId($storeViewId)
    {
        $this->_storeViewId = $storeViewId;
        return $this;
    }

    /**
     * @param $table
     * @return string
     */
    protected function getTable($table)
    {
        return $this->_resourceConnection->getTableName($table);
    }
    /**
     * @return mixed
     */
    public function getStoreAttributes() {
        $storeAttribute = new \Magento\Framework\DataObject(
            [
            'store_attribute' => [
                'name',
                'affiliate_type',
                'status',
                'description',
                'commission_type',
                'commission',
                'sec_commission',
                'sec_commission_type',
                'secondary_commission',
                'discount_type',
                'discount',
                'sec_discount',
                'sec_discount_type',
                'secondary_discount',
                'customer_group_ids',
                'show_in_welcome',
                'use_tier_config',
                'max_level',
                'tier_commission',
                'use_sec_tier',
                'sec_tier_commission',
            ]
        ]
    );
        $this->_eventManager->dispatch($this->_eventPrefix . '_get_store_attributes', [
            $this->_eventObject => $this,
            'attributes' => $storeAttribute,
        ]
        );
        return $storeAttribute->getStoreAttribute();
    }

    /**
     * @return array
     */
    public function getTotalAttributes() {
        return [
            'total_sales_amount',
        ];
    }

    /**
     * @param int $id
     * @param null $field
     * @return $this
     */
    public function load($id, $field = null) {
        parent::load($id, $field);
        if ($this->getStoreId()){
            $this->loadStoreValue();
        }
        if (is_string($this->getData('tier_commission'))){
            $this->setData('tier_commission', unserialize($this->getData('tier_commission')));
        }
        if (is_string($this->getData('sec_tier_commission'))){
            $this->setData('sec_tier_commission', unserialize($this->getData('sec_tier_commission')));
        }
        return $this;
    }

    /**
     * @param null $storeId
     * @return $this
     */
    public function loadStoreValue($storeId = null) {
        if (!$storeId){
            $storeId = $this->getStoreId();
        }
        if (!$storeId){
            return $this;
        }
        $storeValues = $this->getModel('Magestore\Affiliateplusprogram\Model\Value')->getCollection()
            ->addFieldToFilter('program_id', $this->getId())
            ->addFieldToFilter('store_id', $storeId);

        foreach ($storeValues as $value) {
            $this->setData($value->getAttributeCode() . '_in_store', true);
            $this->setData($value->getAttributeCode(), $value->getValue());
        }

        foreach ($this->getStoreAttributes() as $attribute){
            if (!$this->getData($attribute . '_in_store'))
                $this->setData($attribute . '_default', true);
        }

            if (!$this->getData( 'total_sales_amount_in_store')) {
                $this->setData( 'total_sales_amount_in_store', true);
                $this->setData('total_sales_amount', 0.0001);
            }
        return $this;
    }

    /**
     * @return $this
     */
    public function beforeSave() {
        $defaultProgram = $this->getModel('Magestore\Affiliateplusprogram\Model\Program')->load($this->getId());
        if ($storeId = $this->getStoreId()) {
            $storeAttributes = $this->getStoreAttributes();
            foreach ($storeAttributes as $attribute) {
                if ($this->getData($attribute . '_default')) {
                    $this->setData($attribute . '_in_store', false);
                } else {
                    $this->setData($attribute . '_in_store', true);
                    $this->setData($attribute . '_value', $this->getData($attribute));
                }
                if ($defaultProgram->getId()){
                    $this->setData($attribute, $defaultProgram->getData($attribute));
                }
            }
            if ($this->getId()) {
                    $attributeValue = $this->getModel('Magestore\Affiliateplusprogram\Model\Value')
                        ->loadAttributeValue($this->getId(), $storeId, 'total_sales_amount');
                    $delta = $this->getData('total_sales_amount') - $attributeValue->getValue();
                    if ($delta) {
                        try {
                            $attributeValue->setValue(abs($this->getData('total_sales_amount')));
                            $attributeValue->save();
                        } catch (\Exception $e) {
                            throw $e;
                        }
                    }
                    $this->setData('total_sales_amount', abs($this->getData('total_sales_amount')) + abs($delta));
            }
        }

        if (is_array($this->getData('tier_commission'))){
            $this->setData('tier_commission', serialize($this->getData('tier_commission')));
        }
        if (is_array($this->getData('tier_commission_value'))){
            $this->setData('tier_commission_value', serialize($this->getData('tier_commission_value')));
        }
        if (is_array($this->getData('sec_tier_commission'))){
            $this->setData('sec_tier_commission', serialize($this->getData('sec_tier_commission')));
        }
        if (is_array($this->getData('sec_tier_commission_value'))){
            $this->setData('sec_tier_commission_value', serialize($this->getData('sec_tier_commission_value')));
        }
        // Serialize conditions
        if ($this->getConditions()) {
            $this->setConditionsSerialized(json_encode($this->getConditions()->asArray()));
            $this->unsConditions();
        }

        // Serialize actions
        if ($this->getActions()) {
            $this->setActionsSerialized(json_encode($this->getActions()->asArray()));
            $this->unsActions();
        }

        if (is_array($this->getData('customer_group_ids'))) {
                $this->setCustomerGroupIds(join(',', $this->getCustomerGroupIds()));
            }
        return $this;
    }

    /**
     * @return mixed
     */
    public function afterSave() {
        $storeId = $this->getStoreId();
        if ($storeId) {
            $storeAttributes = $this->getStoreAttributes();
            foreach ($storeAttributes as $attribute) {
                $attributeValue = $this->getModel('Magestore\Affiliateplusprogram\Model\Value')
                    ->loadAttributeValue($this->getId(), $storeId, $attribute);

                if ($this->getData($attribute . '_in_store')) {
                    try {
                        $attributeValue->setValue($this->getData($attribute . '_value'))
                            ->save();
                    } catch (\Exception $e) {
                        throw $e;
                    }
                } elseif ($attributeValue && $attributeValue->getId()) {
                    try {
                        $attributeValue->delete();
                    } catch (\Exception $e) {
                        throw $e;
                    }
                }
            }
        }
        if (is_string($this->getData('tier_commission'))){
            $this->setData('tier_commission', unserialize($this->getData('tier_commission')));
        }
        if (is_string($this->getData('sec_tier_commission'))){
            $this->setData('sec_tier_commission', unserialize($this->getData('sec_tier_commission')));
        }
        return parent::afterSave();
    }

    /**
     * @return array
     */
    public function getAccountIds() {
        $accountCollection = $this->getModel('Magestore\Affiliateplusprogram\Model\ResourceModel\Account\Collection')
            ->addFieldToFilter('program_id', $this->getId());
        $accountIds = array();
        foreach ($accountCollection as $account){
            $accountIds[] = $account->getAccountId();
        }
        return $accountIds;
    }

    /**
     * @return bool
     */
    public function isAvailable() {
        if (!$this->getId() || !$this->getStatus()){
            return false;
        }
        if ($this->getValidFrom()){
            if (strtotime($this->getValidFrom()) > time()){
                return false;
            }
        }
        if ($this->getValidTo()){
            if (strtotime($this->getValidTo()) < time()){
                return false;
            }
        }

            if ($groupIds = $this->getData('customer_group_ids')) {
                if (is_string($groupIds)){
                    $groupIds = explode(',', $groupIds);
                }
                if (isset($groupIds[0]) && $groupIds[0] == 'Array') {
                    return true;
                }
                if (!in_array($this->getModel('Magento\Customer\Model\Session')->getCustomerGroupId(), $groupIds)) {
                    return false;
                }
            }


        return true;
    }

    /**
     * @return \Magento\SalesRule\Model\Rule\Condition\Combine
     */
    public function getConditionsInstance() {
        return $this->getModel('Magento\SalesRule\Model\Rule\Condition\Combine');
    }

    /**
     * @return mixed
     */
    public function getActionsInstance() {
        return $this->getModel('Magento\SalesRule\Model\Rule\Condition\Product\Combine');
    }

    /**
     * @param array $rule
     * @return $this
     */
    public function loadPost(array $rule) {
        $arr = $this->_convertFlatToRecursive($rule);
        if (isset($arr['conditions'])) {
            $this->getConditions()->setConditions([])->loadArray($arr['conditions'][1]);
        }
        if (isset($arr['actions'])) {
            $this->getActions()->setActions([])->loadArray($arr['actions'][1], 'actions');
        }
        return $this;
    }


    /**
     * @param $order
     * @return bool
     */
    public function validateOrder($order) {
        if (!$this->isAvailable()){
            return false;
        }
        if (($order) && (!$order->getQuote())) {
            $order->setQuote($order);
        }
        return $this->validate($order);
    }

    /**
     * @param $productId
     * @return array
     */
    public function getAllProgramsByItems($productId) {
        $affiliateInfo = $this->_objectManager->get('Magestore\Affiliateplus\Helper\Cookie')->getAffiliateInfo();
        $account = '';
        foreach ($affiliateInfo as $info) {
            if ($info['account']) {
                $account = $info['account'];
                break;
            }
        }

        $accountId = $account ? $account->getAccountId() : '';
        $programs = $this->getModel('Magestore\Affiliateplusprogram\Model\Program')->getCollection();
        if ($accountId) {
            $programs->getSelect()
                ->join(
                    ['magestore_affiliateplusprogram_account' => $this->getTable(
                         \Magestore\Affiliateplusprogram\Setup\InstallSchema::SCHEMA_PROGRAM_ACCOUNT
                    )
                    ],
                    'magestore_affiliateplusprogram_account.program_id = main_table.program_id',
                    [
                        'program_id' => 'program_id',
                        'account_id' => 'account_id',
                        'joined' => 'joined'
                    ]
                )
                ->where('magestore_affiliateplusprogram_account.account_id = ?', $accountId)
                ->order('main_table.priority DESC')
                ->order('magestore_affiliateplusprogram_account.joined DESC')
                ->order('main_table.program_id DESC')
            ;
        }
        $programByItems = array();
        foreach ($programs as $program) {
            if (in_array($productId, $this->_objectManager
                ->get('Magestore\Affiliateplusprogram\Helper\Data')
                ->getProgramProductIds($program->getId()))) {
                if ($program->getStatus() == 1)
                    $programByItems[] = $program->getId();
            }
        }
        return $programByItems;
    }

    /**
     * @param $item
     * @return bool
     */
    public function validateItem($item) {
        if (!$this->isAvailable())
            return false;
        if ($item instanceof \Magento\Catalog\Model\Product) {
            $_item = $this->getModel('Magento\Sales\Model\Order\Item')->setProduct($item);
            $item = $_item;
        }
        if ($item->getProduct()){
            $productId = $item->getProduct()->getId();
        } else{
            $productId = $item->getProductId();
        }
        $programByItems = $this->getAllProgramsByItems($productId);
        if (!in_array($productId, $this->_objectManager->get('Magestore\Affiliateplusprogram\Helper\Data')->getProgramProductIds($this->getId()))) {
            if ($item->getProduct()->getId()) {
                if (!in_array($item->getProduct()->getId(), [1216, 1217]))
                    return false;
            } else {
                return false;
            }
        }
        $session = $this->getModel('Magento\Checkout\Model\Session');
        $isUseCoupon = $session->getAffiliateCouponCode();
        if (!isset($isUseCoupon)) {
            if ($this->_objectManager->get('Magestore\Affiliateplus\Helper\Data')->isAdmin() && $this->getId() != $programByItems[0] && count($programByItems) > 1) {
                return false;
            }
        }
        return $this->getActions()->validate($item);
    }

    /**
     * @return $this
     */
    public function setProgramIsProcessed() {
        $this->getResource()->setProgramIsProcessed($this);
        return $this;
    }

    /**
     * @return $this
     * @throws Exception
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function orgSave() {
        /**
         * Direct deleted items to delete method
         */
        if ($this->isDeleted()) {
            return $this->delete();
        }
            if (!$this->_hasModelChanged()) {
                return $this;
            }
        $this->_getResource()->beginTransaction();
        try {
            $this->_orgBeforeSave();
            if ($this->_dataSaveAllowed) {
                $this->_getResource()->save($this);
                $this->afterSave();
            }
            $this->_getResource()->addCommitCallback([$this, 'afterCommitCallback'])
                ->commit();
            $this->_hasDataChanges = false;
            $dataCommited = false;
        } catch (\Exception $e) {
            $this->_getResource()->rollBack();
            $this->_hasDataChanges = true;
            throw $e;
        }
        if ($dataCommited ) {
            $this->_afterSaveCommit();
        }
        return $this;
    }

    /**
     * @return $this
     */
    protected function _orgBeforeSave() {
        $defaultProgram = $this->getModel('Magestore\Affiliateplusprogram\Model\Program')->load($this->getId());
        if ($storeId = $this->getStoreId()) {
            $storeAttributes = $this->getStoreAttributes();
            foreach ($storeAttributes as $attribute) {
                if ($this->getData($attribute . '_default')) {
                    $this->setData($attribute . '_in_store', false);
                } else {
                    $this->setData($attribute . '_in_store', true);
                    $this->setData($attribute . '_value', $this->getData($attribute));
                }
                if ($defaultProgram->getId())
                    $this->setData($attribute, $defaultProgram->getData($attribute));
            }
            if ($this->getId()) {
                    $attributeValue = $this->getModel('Magestore\Affiliateplusprogram\Model\Value')
                        ->loadAttributeValue($this->getId(), $storeId, 'total_sales_amount');
                $delta = $this->getData('total_sales_amount') - $attributeValue->getValue();
                    if ($delta) {
                        try {
                            $attributeValue->setValue(abs($this->getData('total_sales_amount')));
                            $attributeValue->save();
                        } catch (\Exception $e) {
                            throw $e;
                        }
                    }
                    $this->setData('total_sales_amount', abs($defaultProgram->getData('total_sales_amount')) + abs($delta));
            }
        }
        if (is_array($this->getData('tier_commission'))){
            $this->setData('tier_commission', serialize($this->getData('tier_commission')));
        }
        if (is_array($this->getData('tier_commission_value'))){
            $this->setData('tier_commission_value', serialize($this->getData('tier_commission_value')));
        }
        if (is_array($this->getData('sec_tier_commission'))){
            $this->setData('sec_tier_commission', serialize($this->getData('sec_tier_commission')));
        }
        if (is_array($this->getData('sec_tier_commission_value'))){
            $this->setData('sec_tier_commission_value', serialize($this->getData('sec_tier_commission_value')));
        }
        \Magento\Framework\Model\AbstractModel::beforeSave();
        if($this->getData('customer_group_ids')){
            if (is_array($this->getCustomerGroupIds())) {
                $this->setCustomerGroupIds(join(',', $this->getCustomerGroupIds()));
            }
        }

        return $this;
    }

    /**
     * @param $model
     * @return mixed
     */
    public function getModel($model){
        return $this->_objectManager->create($model);
    }


}
