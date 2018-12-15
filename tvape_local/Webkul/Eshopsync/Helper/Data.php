<?php
/**
 * @category   Webkul
 * @package    Magento-Salesforce-Connector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
class Webkul_Eshopsync_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function deleteMappingData($model){
		$model_collection = Mage::getModel($model)->getCollection();
        if(count($model_collection)){
            foreach ($model_collection as $collection) {
                $collection->delete();
            }
        }
        return true;
	}

    public static function getFolderBySOAPClient($soap_client)
    {
        try
        {
            $query = "SELECT Id, Name FROM Folder WHERE Type = 'Document'";
            $response = $soap_client->query($query);
            $response = $response->records;
            $result = array();
            foreach ($response as $key => $folder)
            {
                $result[$key]['id'] = $folder->Id;
                $result[$key]['name'] = $folder->Name;
            }
        }
        catch(Exception $e)
        {
            $result = false;
        }
        return $result;
    }

    public static function getPriceBookBySOAPClient($soap_client)
    {
        try
        {
            $query = 'SELECT Id, Name FROM Pricebook2 WHERE IsActive = true AND IsStandard = false';
            $response = $soap_client->query($query);
            $response = $response->records;
            $result = array();
            foreach ($response as $key => $price_book)
            {
                $result[$key]['id'] = $price_book->Id;
                $result[$key]['name'] = $price_book->Name;
            }
        }
        catch(Exception $e)
        {
            $result = false;
        }
        return $result;
    }

    public static function getStandardPriceBookBySOAPClient($soap_client)
    {
        $result = false;
        try
        {
            $query = 'SELECT Id, Name FROM Pricebook2 WHERE IsStandard = true';
            $response = $soap_client->query($query);
            if ($response->done)
            {
                $result = $response->records[0]->Id;
            }
        }
        catch(Exception $e)
        {}
        return $result;
    }

    public static function getActivePriceBookValue()
    {
        $result = false;
        $salesforce_pricebooks = Mage::getStoreConfig('eshopsync/default/salesforce_pricebooks');
        $salesforce_pricebooks = Mage::helper('core')->jsonDecode($salesforce_pricebooks);
        foreach ($salesforce_pricebooks as $price_book)
        {
            $result = $price_book['id'];
            break;
        }
        return $result;
    }

    public static function uploadImageToSalesForce($client, $object, $magento_id, $description = false, $image_path = false)
    {
        $default_folder_id = Mage::getStoreConfig('eshopsync/default/folder');
        if ($default_folder_id && $image_path)
        {
            try
            {
                $mediaImage = new stdclass();

                $name = "magento_".$object."_".$magento_id;

                $mediaImage->Name = $name;

                if ($description){
                    $mediaImage->Description = $description;
                }
                $mediaImage->FolderId = $default_folder_id;
                $mediaImage->IsPublic = true;
                try{
                    $mediaImage->Body = base64_encode(file_get_contents($image_path));
                }catch(Exception $e){
                    return false;
                }
                $imageDoc = $client->upsert('Name', array($mediaImage), 'Document');
                if ($imageDoc[0]->success)
                    return $imageDoc[0]->id;
            }
            catch(Exception $e){}
        }
        return false;
    }

    public static function fetchMappingDetails($model, $magento_id)
    {
        $data = array();
        $mapping_collection = Mage::getModel($model)->getCollection()
                                        ->addFieldToFilter('magento_id',array('eq'=>$magento_id));
        if($mapping_collection->getSize()){
            $mapping = $mapping_collection->getFirstItem();
            $data = $mapping->getData();
        }
        return $data;
    }

    public static function decodeSalesforceLog($errorObject=false)
    {
        if($errorObject){
            foreach ($errorObject as $error) {
                return "[".$error->statusCode."]".$error->message;
            }
        }
    }

    public static function eshopsyncLog($message)
    {
        if($message)
            Mage::log($message, null, 'eshopsync_connector.log');
    }

		public static function getSyncNumber($str){
	    $collection = Mage::getModel('eshopsync/'.$str)->getCollection()
										->addFieldToFilter('error_hints', array('null' => true))
										->addFieldToFilter('magento_id', array('notnull' => true));
			$n = count($collection);
			return $n;
	  }

		// public static function getUnsyncNumber($str){
		// 	$collection = Mage::getModel('eshopsync/'.$str)->getCollection()
		// 								->addFieldToFilter('error_hints', array('notnull' => true));
		// 	$n = count($collection);
		// 	return $n;
	  // }
}
