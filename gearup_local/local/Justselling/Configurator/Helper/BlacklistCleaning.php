<?php
class Justselling_Configurator_Helper_BlacklistCleaning extends Mage_Core_Helper_Abstract
{
    public function clean($templateId){
        if ($templateId === null) {
            return false;
        }

		//clean blacklist
		$_start = microtime(true);
		$changedOptionArray = array();
		$allOptionsCollection = Mage::getModel("configurator/option")->getCollection();
		$allOptionsCollection->addFieldToFilter('template_id', $templateId);

		$allOptionIdsArray = array();
		$allOptionsArray = array();
		foreach($allOptionsCollection as $option){
			$allOptionIdsArray[$option->getId()] = $option->getId();
			$allOptionsArray[$option->getId()] = $option;
		}

        if (isset($allOptionIdsArray) && sizeof($allOptionIdsArray) > 0) {
            $allOptionValuesCollection = Mage::getModel("configurator/value")->getCollection();
            $allOptionValuesCollection->addFieldToFilter('option_id', $allOptionIdsArray);

            $allOptionValueIdsArray = array();
            $allOptionValuesArray = array();
            foreach($allOptionValuesCollection as $optionValue){
                $allOptionValueIdsArray[$optionValue->getId()] = $optionValue->getId();
                $allOptionValuesArray[$optionValue->getId()] = $optionValue;
            }
        }

		/* option blacklist */
        if (isset($allOptionIdsArray) && sizeof($allOptionIdsArray) > 0) {
            $allOptionBlacklistEntriesByOptionId = Mage::getModel("configurator/blacklist")->getCollection();
            $allOptionBlacklistEntriesByOptionId->addFieldToFilter("child_option_id", $allOptionIdsArray);

            foreach($allOptionBlacklistEntriesByOptionId as $blacklistEntry){
                $blacklistingOption = $allOptionValuesArray[$blacklistEntry->getOptionValueId()];
                $blacklistingOptionId = $blacklistingOption->getOptionId();
                $blacklistedOptionId = $blacklistEntry->getChildOptionId();

                $isParent = $this->isParentFromOptionId($blacklistingOptionId, $blacklistedOptionId, $allOptionsArray);
                if(!$isParent){
                    $changedOptionArray[$blacklistingOptionId] = $blacklistingOptionId;
                    $blacklistEntry->delete();
                    Mage::log('delete option blacklist while no parent found at option with id ' .$blacklistingOptionId);
                }
            }
        }

		/* option value blacklist */
        if (isset($allOptionValueIdsArray) && sizeof($allOptionValueIdsArray) > 0) {
            $allOptionValueBlacklistEntriesByOptionId = Mage::getModel("configurator/blacklist")->getCollection();
            $allOptionValueBlacklistEntriesByOptionId->addFieldToFilter("child_option_value_id", $allOptionValueIdsArray);

            foreach($allOptionValueBlacklistEntriesByOptionId as $blacklistEntry){
                $blacklistingOption = $allOptionValuesArray[$blacklistEntry->getOptionValueId()];
                $blacklistingOptionId = $blacklistingOption->getOptionId();
                $blacklistedOption = $allOptionValuesArray[$blacklistEntry->getChildOptionValueId()];
                $blacklistedOptionId = $blacklistedOption->getOptionId();

                $isParent = $this->isParentFromOptionId($blacklistingOptionId, $blacklistedOptionId, $allOptionsArray);
                if(!$isParent){
                    $changedOptionArray[$blacklistingOptionId] = $blacklistingOptionId;
                    $blacklistEntry->delete();
                    Mage::log('delete option value blacklist while no parent found at option with id ' .$blacklistingOptionId);
                }
            }
        }

		/* expression option value blacklist */
		if (isset($allOptionValueIdsArray) && sizeof($allOptionValueIdsArray) > 0) {
			$allOptionValueBlacklistEntriesByOptionId = Mage::getModel("configurator/optionblacklist")->getCollection();
			$allOptionValueBlacklistEntriesByOptionId->addFieldToFilter("child_option_value_id", $allOptionValueIdsArray);

			foreach($allOptionValueBlacklistEntriesByOptionId as $blacklistEntry){
				$blacklistingOptionId = $blacklistEntry->getOptionId();
				$blacklistedOption = $allOptionValuesArray[$blacklistEntry->getChildOptionValueId()];
				$blacklistedOptionId = $blacklistedOption->getOptionId();

				$isParent = $this->isParentFromOptionId($blacklistedOptionId, $blacklistingOptionId, $allOptionsArray);
				if(!$isParent){
					$changedOptionArray[$blacklistingOptionId] = $blacklistingOptionId;
					$blacklistEntry->delete();
					Mage::log('delete option value blacklist by expression while no parent found at option with id ' .$blacklistingOptionId);
				}
			}
		}


		if(isset($changedOptionArray) && sizeof($changedOptionArray) > 0){
			$normalChangeOptionIdArray = array();
			foreach($changedOptionArray as $changedOptionId){
				$normalChangeOptionIdArray[] = $changedOptionId;
			}

			// Clean Magento Zend Cache
			$cache = Mage::app()->getCache();
			$cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array("PRODCONF_OPTION_".$normalChangeOptionIdArray));
		}
		//Js_Log::log('cleanblacklist '. (microtime(true) - $_start), "profile", Zend_Log::DEBUG, true);
	}

	private  function isParentFromOptionId($parendId, $childId, $allOptionsArray){
		$isParentOption = false;

		$option = $allOptionsArray[$childId];
		if($option->getParentId()){
			$optionParent = $allOptionsArray[$option->getParentId()];
			$id = $optionParent->getId();
			if($parendId == $id){
				$isParentOption = true;
			}elseif(isset($id)){
				$isParentOption = $this->isParentFromOptionId($parendId, $id, $allOptionsArray);
			}
		}
		return $isParentOption;

	}
    
}