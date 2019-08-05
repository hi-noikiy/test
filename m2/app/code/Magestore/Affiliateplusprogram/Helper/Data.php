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

namespace Magestore\Affiliateplusprogram\Helper;

class Data extends AbstractHelper
{
    protected $_cache = array();

    /**
     * Get Program Options
     * @return mixed
     */
    public function getProgramOptions()
    {
        if (!isset($this->_cache['program_options'])) {
            $options[0] = __('Affiliate Program');
            $programCollection = $this->_programCollectionFactory->create();

            foreach ($programCollection as $program) {
                $options[$program->getId()] = $program->getName();
            }
            $this->_cache['program_options'] = $options;
        }
        return $this->_cache['program_options'];
    }

    /**
     * Get Program Option as Array
     * @return mixed
     */
    public function getProgramOptionArray() {
        if (!isset($this->_cache['program_option_array'])) {
            $optionArray = array();
            foreach ($this->getProgramOptions() as $value => $label) {
                $optionArray[] = array(
                    'value' => $value,
                    'label' => $label,
                );
            }
            $this->_cache['program_option_array'] = $optionArray;
        }
        return $this->_cache['program_option_array'];
    }

    /**
     * get program ids that the logging in affiliate joined in
     * @return mixed
     */
    public function getJoinedProgramIds() {
        if (!isset($this->_cache['joined_program_ids'])) {
            $joinedPrograms = array(0);
            $joinedCollection = $this->_programAccountFactory->create()
                    ->getcollection()
                    ->addFieldToFilter('account_id', $this->_helperAccount->getAccount()->getId());
            foreach ($joinedCollection as $item){
                $joinedPrograms[] = $item->getProgramId();
            }
            $this->_cache['joined_program_ids'] = $joinedPrograms;
        }
        return $this->_cache['joined_program_ids'];
    }

    /**
     * Get Product Ids of the program
     * @param $programId
     * @param null $storeId
     * @return mixed
     */
    public function getProgramProductIds($programId, $storeId = null) {
        if (is_null($storeId)){
            $storeId = $this->_storeManager->getStore()->getId();
        }
        $cacheKey = 'program_' . $programId . '_product_ids_in_store_' . $storeId;
        if (isset($this->_cache[$cacheKey])) {
            return $this->_cache[$cacheKey];
        }
        $productIds = array();
        $categoryCollection = $this->_programCategoryFactory->create()
            ->getCollection()
            ->addFieldToFilter('program_id', $programId)
            ->addFieldToFilter('store_id', $storeId);
        if ($categoryCollection->getSize() == 0){
            $categoryCollection = $this->_programCategoryFactory->create()
                ->getCollection()
                ->addFieldToFilter('program_id', $programId)
                ->addFieldToFilter('store_id', 0);
        }
        $categoryIds = array();
        foreach ($categoryCollection as $category){
            $categoryIds[] = $category->getCategoryId();
        }
        if (count($categoryIds)) {
            $productCollection = $this->_productFactory->create()
                ->getCollection();
            $productCollection->getSelect()
                ->join(
                    array('c' => $productCollection->getTable('catalog_category_product_index')), 'e.entity_id = c.product_id', array()
                )->where('c.category_id IN (' . implode(',', $categoryIds) . ')')
                ->group('e.entity_id');
            $productIds = $productCollection->getAllIds();
        }

        $this->_cache[$cacheKey] = $productIds;
        return $this->_cache[$cacheKey];
    }

    /**
     * @param $accountId
     * @param null $order
     * @return $this
     */
    public function initProgram($accountId, $order = null) {
        if (isset($this->_cache["init_programs_$accountId"]))
            return $this;
        $joinedPrograms = $this->_programCollectionFactory->create();

        /**
         * - Lay theo priority cao hon
         * - Neu 2 program co cung priority thi se lay theo ngay sau cung ma affiliate join vao program
         * - Neu join cung ngay, cung priority thi se lay theo program_id cao hon
         */
        $joinedPrograms->getSelect()
            ->join(array('affiliateplusprogram_account'=>$joinedPrograms->getTable(\Magestore\Affiliateplusprogram\Setup\InstallSchema::SCHEMA_PROGRAM_ACCOUNT)), 'affiliateplusprogram_account.program_id = main_table.program_id', array('program_id'=>'program_id', 'account_id'=>'account_id', 'joined'=>'joined'))
            ->where('affiliateplusprogram_account.account_id = ?',$accountId)
            ->order('main_table.priority DESC')
            ->order('affiliateplusprogram_account.joined DESC')
            ->order('main_table.program_id DESC')
        ;
        /* End code*/

        $programs = array();
        $quote = null;
        if ($order)
            $quote = $order;
        else {
            if ($this->_checkoutSession->hasQuote()){
                $quote = $this->_checkoutCart->getQuote();
            }else{
                $quote = $this->_backendQuoteSession->getQuote();
            }
        }
        foreach ($joinedPrograms as $joinedProgram) {
            /* Edit By Jack */
            $storeId = $this->_storeManager->getStore()->getId();
            if(!$storeId){
                $storeId = $this->_backendQuoteSession->getStoreId();
            }
            $program = $this->_programFactory->create()
                ->setStoreId($storeId)
                ->load($joinedProgram->getProgramId());
            if ($program->validateOrder($quote)){
                $programs[] = $program;
            }
        }
        $this->_cache["init_programs_$accountId"] = $programs;
        return $this;
    }

    /**
     * @param $itemProduct
     * @param $account
     * @return null
     */
    public function getProgramByItemAccount($itemProduct, $account) {
        if (is_object($account)){
            $accountId = $account->getId();
        }else{
            $accountId = $account;
        }
        if (!isset($this->_cache["init_programs_$accountId"])){
            $this->initProgram($accountId);
        }
        $programs = $this->_cache["init_programs_$accountId"];
        if (count($programs))
            foreach ($programs as $program){
                if ($program->validateItem($itemProduct)){
                    return $program;
                }
            }
        return null;
    }

    /**
     * @param $product
     * @param $account
     * @return null
     */
    public function getProgramByProductAccount($product, $account) {
        return $this->getProgramByItemAccount($product, $account);
    }

    /**
     * @return bool
     */
    public function multilevelIsActive() {
        return $this->_helperAbstract->isModuleEnabled('Magestore_Affiliatepluslevel');
    }

    /**
     * @return mixed
     */
    public function getStandardCommissionPercent() {
        $storeId = $this->_storeManager->getStore()->getId();
        if($this->multilevelIsActive()){
            $perCommissions = $this->_helperAbstract->getConfig('affiliateplus/multilevel/commission_percentage', $storeId);
            $arrPerCommissions = explode(',', $perCommissions);
            return $arrPerCommissions[0];
        }
    }

    /**
     * Check the module is disable or enable
     * @return bool|mixed
     */
    public function isPluginEnabled()
    {
        if (!$this->_helperData->isAffiliateModuleEnabled()) {
            return false;
        }
        $check = $this->_helperData->getConfig('affiliateplus/program/enable');
        return $check;
    }

    /**
     * Check the module is disable or enable
     * @param null $store
     * @return bool
     */
    public function isModuleDisabled($store = null)
    {
        if ($this->_helperAccount->accountNotLogin()) {
            return TRUE;
        }
//        if($this->_helperAccount->isNotAvailableAccount()){
//            return true;
//        }
        $check = $this->_helperData->getConfig('affiliateplus/program/enable', $store);
        return !$check;
    }

    /**
     * Get config to display the default program in frontend
     * @return mixed
     */
    public function showDefault()
    {
        $storeId = $this->_storeManager->getStore()->getId();
        return $this->_helperData->getConfig("affiliateplus/program/show_default", $storeId);
    }

    /**
     * Check the item is in the higher priority or not
     * @param $accountId
     * @param $item
     * @param $priority
     * @return bool
     */
    public function checkItemInHigherPriorityProgram($accountId, $item, $priority)
    {
        if (!isset($this->_cache["init_programs_$accountId"])) {
            $this->initProgram($accountId);
        }
        $programs = $this->_cache["init_programs_$accountId"];
        if (count($programs) > 1) {
            foreach ($programs as $program) {
                if (($program->getPriority() > $priority) && $program->validateItem($item)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * get program in highest program by account id
     * @param $accountId
     * @return null
     */
    public function getProgramByMaxPriority($accountId)
    {
        $programs = $this->_programCollectionFactory->create()
            ->setOrder('priority', 'DESC');
        foreach ($programs as $program) {
            if ($program->getPriority() > 0 && $program->getStatus() == 1) {
                return $program;
            }
        }
        $programsJoined = $this->_joinedFactory->create()
            ->getCollection()
            ->addFieldToFilter('account_id', $accountId)
            ->setOrder('id', 'DESC');
        foreach ($programsJoined as $programJoined) {
            $programData = $this->_programFactory->create()
                ->load($programJoined->getProgramId());
            if ($programData->getStatus() == 1) {
                return $programData;
            }
        }
        return null;
    }
}