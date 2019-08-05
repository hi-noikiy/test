<?php

namespace Ktpl\SalesOrder\Observer;

use Magento\Framework\Event\ObserverInterface;

class SalesOrderPlaceAfter implements ObserverInterface
{
	protected $request;
    protected $_scopeConfig;
    protected $directory_list;
    protected $csvProcessor;
    protected $directoryHelper;

    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Filesystem\DirectoryList $directory_list,
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\Directory\Helper\Data $directoryHelper
    ) {
       $this->request           = $request;
       $this->_scopeConfig      = $scopeConfig;
       $this->directory_list    = $directory_list;
       $this->csvProcessor      = $csvProcessor;
       $this->directoryHelper   = $directoryHelper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $order = $observer->getEvent()->getOrder();
            $post = $this->request->getPostValue('order');

            if(isset($post['account']['po'])){
            	$order->setPo($post['account']['po']);
            }
            if(isset($post['account']['terms'])){
            	$order->setTerms($post['account']['terms']);
            }
            if(isset($post['account']['bin'])){
                $order->setBinno($post['account']['bin']);
            }
            if(isset($post['account']['shipping_notes'])){
            	$order->setShippingNotes($post['account']['shipping_notes']);
            }

            if(isset($post['account']['samples'])){
                $order->setSamples($post['account']['samples']);
            }

            if(isset($post['account']['bd'])){
                $order->setBusinessDevelopement($post['account']['bd']);
            }

            //Set Tax Code and Order Type :
            $store_id=$order->getStoreId();

            $regionData = $this->directoryHelper->getRegionData();
            $country_id = '';
            $regionName = '';
            $region_id  = '';
            $tax_code   = '';
            if(isset($post['shipping_address']['country_id'])){
                $country_id=$post['shipping_address']['country_id'];
            }
            if(isset($post['shipping_address']['region_id'])){
                $region_id=$post['shipping_address']['region_id'];
            }
            if(isset($regionData[$country_id][$region_id])){
                $regionName = @$regionData[$country_id][$region_id]['name']; /* code / name */
            }

            $order_type='';
            $wholesale_group=['Wholesale'=>2,'Tvapede Customer'=>16,'Cad To De Wholesaler'=>17];
            if(isset($post['account']['group_id']) && in_array($post['account']['group_id'],$wholesale_group)){
                $order_type='B2B';
            }else{
                $order_type='B2C';
            }

            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
            $filePath=$this->_scopeConfig->getValue("ktpl_salesorder/salesorder_general/texcode_file_upload", $storeScope,$store_id);
            $mediaPath = $this->directory_list->getPath('media').'/import/';
            $file = $mediaPath.''.$filePath;
            $taxCodeCSV = $this->csvProcessor->getData($file);

            switch ($store_id) {
                case '5':
                case '10':
                        $finalDeCSV = [];
                        foreach ($taxCodeCSV as $taxCode) {
                            $finalDeCSV[$taxCode[0]][$taxCode[1]]['country'] = $taxCode[2];
                            $finalDeCSV[$taxCode[0]][$taxCode[1]]['taxcode'] = $taxCode[3];
                        }

                        $anyneq     = $finalDeCSV['any']['neq'];
                        $anyCountry = explode('|', $anyneq['country']);
                        $b2beq      = $finalDeCSV['B2B']['eq'];
                        $b2bCountry = explode('|', $b2beq['country']);
                        $b2ceq      = $finalDeCSV['B2C']['eq'];
                        $b2cCountry = explode('|', $b2ceq['country']);
                        $emptyeq    = $finalDeCSV['Empty']['eq'];

                        if($country_id == 'DE') {
                            $detaxCode = isset($finalDeCSV['DE']['eq']['taxcode']) ? $finalDeCSV['DE']['eq']['taxcode'] : 'S-DE';
                            $tax_code = $detaxCode;

                        } elseif(!in_array($country_id, $anyCountry )){
                            $tax_code = $anyneq['taxcode'];
                        } elseif($order_type=='B2B' && in_array($country_id, $b2bCountry)) {
                            $tax_code = $b2beq['taxcode'];
                        } elseif($order_type=='B2C' && in_array($country_id, $b2cCountry)) {
                            $tax_code = $b2ceq['taxcode'];
                        } else {
                            $tax_code = $emptyeq['taxcode'];
                        }

                    break;

                default:
                    $finalTaxCode = [];
                    if(isset($taxCodeCSV) && COUNT($taxCodeCSV) > 0){
                        foreach ($taxCodeCSV as $code) {
                            $finalTaxCode[$code[1]][$code[2]] = $code[0];
                        }
                    }
                    if($country_id=='CA'){
                        if($region_id !='' && isset($regionName)){
                            if($regionName!='' && isset($finalTaxCode[$country_id][$regionName])){
                                $tax_code = $finalTaxCode[$country_id][$regionName];
                            }
                        }

                    }else{
                        $tax_code='CA-Zero';
                    }
                    break;
            }
            if($tax_code != ''){
                $order->setTaxCode($tax_code);
            }

            if(isset($post['account']['group_id'])){
                $order->setOrderType($order_type);
            }
        } catch (\Exception $e) {
            //error_log($e->getMessage());
        }
    }
}
