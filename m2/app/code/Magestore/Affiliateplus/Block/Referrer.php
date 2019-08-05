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
 * @package     Magestore_Affiliateplus
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */
namespace Magestore\Affiliateplus\Block;

/**
 * @category Magestore
 * @package  Magestore_Affiliateplus
 * @module   Affiliateplus
 * @author   Magestore Developer
 */
class Referrer extends AbstractTemplate
{
    /**
     * get Helper
     *
     * @return Magestore\Affiliateplus\Helper\Config
     */
    public function _getHelper(){
        return $this->_configHelper;
    }

    /**
     * Get Action Model
     * @return \Magestore\Affiliateplus\Model\Action
     */
    protected function _getActionModel(){
        return $this->_objectManager->create('Magestore\Affiliateplus\Model\Action');
    }

    /**
     * @return Collection \Magestore\Affiliateplus\Model\ResourceModel\Action\Collection
     */
    protected function _construct(){
        parent::_construct();
        $account = $this->_sessionModel->getAccount();
        $collection = $this->_getActionModel()->getCollection();
        if ($this->_getHelper()->getSharingConfig('balance') == 'store'){
            $collection->addFieldToFilter('store_id',$this->_storeManager->getStore()->getId());
        }
        $collection->addFieldToFilter('account_id',$account->getId())
            ->setCustomGroupSql(true);

        $collection->getSelect()->columns(array(
            'total_clicks'  => 'COUNT(action_id)',
            'unique_clicks' => 'SUM(is_unique)',
        ));
        $request = $this->getRequest();
        if ($request->getParam('click') == 'desc'){
            $collection->getSelect()->order('total_clicks DESC');
        }elseif ($request->getParam('click') == 'asc'){
            $collection->getSelect()->order('total_clicks ASC');
        }elseif ($request->getParam('unique') == 'desc'){
            $collection->getSelect()->order('unique_clicks DESC');
        }elseif ($request->getParam('unique') == 'asc'){
            $collection->getSelect()->order('unique_clicks ASC');
        }else{
            $collection->getSelect()->order('action_id DESC');
        }
        $collection->getSelect()->group(array('referer','landing_page','store_id'));

        $this->_eventManager->dispatch('affiliateplus_prepare_referers_collection',array(
            'collection'	=> $collection,
        ));

        $this->setCollection($collection);
    }

    /**
     *
     * @return \Magestore_Affiliateplus_Block_Referrer
     */
    public function _prepareLayout(){
        parent::_prepareLayout();
        $pager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager', 'sales_pager')
            ->setTemplate('Magestore_Affiliateplus::html/pager.phtml')
            ->setCollection($this->getCollection());
        $this->setChild('referer_pager',$pager);

        $grid = $this->getLayout()->createBlock('Magestore\Affiliateplus\Block\Grid', 'referer_grid');
        // prepare column
        $grid->addColumn('referer',
            [
                'header'	=> __('Traffic Source'),
                'align'		=> 'left',
                'render'	=> 'getReferer',
                'searchable'    => true,
            ]
        );

        $url = $this->getUrl('*/*/*');
        if ($this->getRequest()->getParam('click') == 'desc'){
            $header = '<a href="'.$url.'click/asc" class="sort-arrow-desc" title="'.__('ASC').'">'.__('Clicks').'</a>';
        }else {
            $header = '<a href="'.$url.'click/desc" class="sort-arrow-asc" title="'.__('DESC').'">'.__('Clicks').'</a>';
        }
        $grid->addColumn('total_clicks',
            [
                'header'	=> $header,
                'index'		=> 'total_clicks',
                'align'		=> 'left',
            ]
        );

        if ($this->getRequest()->getParam('unique') == 'desc'){
            $header = '<a href="'.$url.'unique/asc" class="sort-arrow-desc" title="'.__('ASC').'">'.__('Unique Clicks').'</a>';
        }else {
            $header = '<a href="'.$url.'unique/desc" class="sort-arrow-asc" title="'.__('DESC').'">'.__('Unique Clicks').'</a>';
        }
        $grid->addColumn('unique_clicks',
            [
                'header'	=> $header,// __('Unique Clicks'),
                'index'		=> 'unique_clicks',
                'align'		=> 'left',
            ]
        );

        if ($this->_getHelper()->getSharingConfig('balance') != 'store')
            $grid->addColumn('store_id',
                [
                    'header'	=> __('Store View'),
                    'index'		=> 'store_id',
                    'type'		=> 'options',
                    'options'	=> $this->getStoresOption(),
                    'searchable'    => true,
                ]
            );

        $grid->addColumn('landing_page',
            [
                'header'	=> __('Landing Page'),
                'render'	=> 'getUrlPath',
                'searchable'    => true,
                'index'     => 'landing_page',
            ]
        );

        $this->_eventManager->dispatch('affiliateplus_prepare_referers_columns',
            [
                'grid'	=> $grid
            ]
        );

        $this->setChild('referer_grid',$grid);
        return $this;
    }

    /**
     *
     * @param type $row
     * @return type
     */
    public function getNoNumber($row){
        return sprintf('#%d',$row->getId());
    }

    /**
     *
     * @param type $row
     * @return type
     */
    public function getReferer($row){
        if ($row->getReferer())
            return sprintf('<a target="_blank" href="http://%s">%s</a>',$row->getReferer(),$row->getReferer());
        return __('N/A');
    }

    /**
     *
     * @param type $row
     * @return type
     */
    public function getUrlPath($row){
        return sprintf('<a href="%s">%s</a>',$this->getBaseUrl().trim($row->getLandingPage(),'/'),$row->getLandingPage());
    }

    /**
     *
     * @return type
     */
    public function getStoresOption(){
        $stores = array();
        foreach ($this->_storeManager->getStores() as $id => $store)
            /*edit by viet show available stores*/
            if($store->getIsActive()==1){
                $stores[$id] = $store->getName();
            }
        /*end by viet */
        return $stores;
    }

    /**
     *
     * @return type
     */
    public function getPagerHtml(){
        return $this->getChildHtml('referer_pager');
    }

    /**
     *
     * @return type
     */
    public function getGridHtml(){
        return $this->getChildHtml('referer_grid');
    }

    /**
     *
     * @return type
     */
    protected function _toHtml(){
        $this->getChildBlock('referer_grid')->setCollection($this->getCollection());
        return parent::_toHtml();
    }

    /**
     *
     * @return type
     */
    public function getTraffics()
    {
        $commissionInfo = $this->getCommissionInfo();
        $traffics = new \Magento\Framework\DataObject(array());
        $this->_eventManager->dispatch('affiliateplus_traffics_statistic',
            [
                'traffics' => $traffics
            ]
        );
        $data = $traffics->getData();
        $data[] = $commissionInfo;
        return $data;
    }

    /**
     * @return mixed
     */
    protected function _getTransactionModel(){
        return $this->_objectManager->create('Magestore\Affiliateplus\Model\Transaction');
    }
    /**
     *
     * @return string
     */
    public function getCommissionInfo(){
        $info = array();
        $session = $this->_sessionModel;
        $date = date('Y-m-d');
        $week = date('W');
        $month = date('m');
        $year = date('Y');
        if($session->isLoggedIn()){
            $account = $session->getAccount();
            $dateCollection = $this->_getTransactionModel()->getCollection()
                ->addFieldToFilter('account_id',$account->getId())
            ;

            /*Changed By Adam (28/12/2015: fix issue of wrong commission when have multiple levels' transaction.
             * In referrer page, it calculates total commission by transaction that is total tier commissions. It should be calculate commission of each affiliate.
             */
            $this->_eventManager->dispatch('affiliateplus_referrer_sales_collection',
                [
                    'collection' => $dateCollection
                ]
            );

            $cond = 'SUM(commission)';
            if ($this->_configHelper->isModuleEnabled('Magestore_Affiliatepluslevel') && $this->_objectManager->create('Magestore\Affiliatepluslevel\Helper\Data')->isPluginEnabled()) {
                $cond = 'SUM(if(ts.commission is null, main_table.commission, ts.commission))';
            }

            $dateCollection ->getSelect()
                ->where("date(created_time)=?", $date)                      // Changed By Adam 29/10/2015: Fix issue of SUPEE 6788 - in Magento 1.9.2.2
                ->group("date(created_time)")                               // Changed By Adam 29/10/2015: Fix issue of SUPEE 6788 - in Magento 1.9.2.2
                ->columns(array('commission_total'=>$cond));
            $first = $dateCollection->getFirstItem();
            $info['today'] = $this->_dataHelper->formatPrice($first->getCommissionTotal());
            /*----------------------------------------------------------------*/
            $weekCollection = $this->_getTransactionModel()->getCollection()
                ->addFieldToFilter('account_id',$account->getId())
//                            ->addFieldToFilter('week(created_time, 1)',$week)         // Changed By Adam 29/10/2015: Fix issue of SUPEE 6788 - in Magento 1.9.2.2
            ;

            /*Changed By Adam (28/12/2015: fix issue of wrong commission when have multiple levels' transaction.
             * In referrer page, it calculates total commission by transaction that is total tier commissions. It should be calculate commission of each affiliate.
             */
            $this->_eventManager->dispatch('affiliateplus_referrer_sales_collection',
                [
                    'collection' => $weekCollection
                ]
            );

            $weekCollection ->getSelect()
                ->where("week(created_time, 1)=?", $week)                   // Changed By Adam 29/10/2015: Fix issue of SUPEE 6788 - in Magento 1.9.2.2
                ->group("week(created_time, 1)")                            // Changed By Adam 29/10/2015: Fix issue of SUPEE 6788 - in Magento 1.9.2.2
                ->columns(array('commission_total'=>$cond));
            $first = $weekCollection->getFirstItem();
            $info['week'] = $this->_dataHelper->formatPrice($first->getCommissionTotal());
            /*----------------------------------------------------------------*/
            $monthCollection = $this->_getTransactionModel()->getCollection()
                ->addFieldToFilter('account_id',$account->getId())
            ;

            /*Changed By Adam (28/12/2015: fix issue of wrong commission when have multiple levels' transaction.
             * In referrer page, it calculates total commission by transaction that is total tier commissions. It should be calculate commission of each affiliate.
             */
            $this->_eventManager->dispatch('affiliateplus_referrer_sales_collection',
                [
                    'collection' => $monthCollection
                ]
            );

            $monthCollection ->getSelect()
                ->where("month(created_time)=?", $month)                    // Changed By Adam 29/10/2015: Fix issue of SUPEE 6788 - in Magento 1.9.2.2
                ->group("month(created_time)")                              // Changed By Adam 29/10/2015: Fix issue of SUPEE 6788 - in Magento 1.9.2.2
                ->columns(array('commission_total'=>$cond));
            $first = $monthCollection->getFirstItem();
            $info['month'] = $this->_dataHelper->formatPrice($first->getCommissionTotal());
            /*----------------------------------------------------------------*/
            $yearCollection = $this->_getTransactionModel()->getCollection()
                ->addFieldToFilter('account_id',$account->getId())
            ;

            /*Changed By Adam (28/12/2015: fix issue of wrong commission when have multiple levels' transaction.
             * In referrer page, it calculates total commission by transaction that is total tier commissions. It should be calculate commission of each affiliate.
             */
            $this->_eventManager->dispatch('affiliateplus_referrer_sales_collection',
                [
                    'collection' => $yearCollection
                ]
            );

            $yearCollection ->getSelect()
                ->where("year(created_time)=?", $year)                      // Changed By Adam 29/10/2015: Fix issue of SUPEE 6788 - in Magento 1.9.2.2
                ->group("year(created_time)")                               // Changed By Adam 29/10/2015: Fix issue of SUPEE 6788 - in Magento 1.9.2.2
                ->columns(array('commission_total'=>$cond));
            $first = $yearCollection->getFirstItem();
            $info['year'] = $this->_dataHelper->formatPrice($first->getCommissionTotal());
            /*----------------------------------------------------------------*/
            $allCollection = $this->_getTransactionModel()->getCollection()
                ->addFieldToFilter('account_id',$account->getId())
            ;

            /*Changed By Adam (28/12/2015: fix issue of wrong commission when have multiple levels' transaction.
             * In referrer page, it calculates total commission by transaction that is total tier commissions. It should be calculate commission of each affiliate.
             */
            $this->_eventManager->dispatch('affiliateplus_referrer_sales_collection',
                [
                    'collection' => $allCollection
                ]
            );

            $allCollection  ->getSelect()
                ->group('account_id')
                ->columns(array('commission_total'=>$cond));
            $first = $allCollection->getFirstItem();
            $info['all'] = $this->_dataHelper->formatPrice($first->getCommissionTotal());
            $info['name']  =  'commission';
            $info['title']  =  'Commissions';
            return $info;
        }
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }
    
    public function setPrice($stringPrice){
        $price = (float) filter_var( $stringPrice, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
        return $this->formatPrice($price);
    }
}
