<?php

class MageWorkshop_ImportExportReview_Helper_ImportExport extends Mage_Core_Helper_Abstract
{
    /** @var array $_storeIds */
    protected static $_storeIds     = array();
    /** @var array $_websiteIds */
    protected static $_websiteIds   = array();
    /** @var array $_prosConsData */
    protected static $_prosConsData = array();
    /** @var Varien_Object $_ratingData */
    protected static $_ratingData;
    /** @var Varien_Object $_ratingOptionData */
    protected static $_ratingOptionData;
    /** @var Varien_Object $_productsDataObject */
    protected static $_productsDataObject;
    /** @var Varien_Object $_customersDataObject */
    protected static $_customersDataObject;
    /** @var Varien_Object $_uniqueIds */
    protected static $_uniqueIdsObject;

    /**
     * @param $type
     * @param $dataType
     * @param $storeIds
     * @param int $wroteBy
     * @param $doCreateProsCons
     * @param int $status
     * @return array
     */
    public function createProsCons(
        $type,
        $dataType,
        $storeIds,
        $doCreateProsCons,
        $wroteBy = MageWorkshop_DetailedReview_Model_Source_Common_Wroteby::CUSTOMER,
        $status = 1
    ) {
        $prosConsData = $this->_getProsConsData();
        $importData   = explode('@', $dataType);
        $data         = array();

        foreach ($importData as $item) {
            if (isset($prosConsData[$type][$item])) {
                $data[] = $prosConsData[$type][$item];
            } elseif ($doCreateProsCons) {
                $newItem = Mage::getModel('detailedreview/review_proscons');
                $newItem->setEntityType($type)
                    ->setName($item)
                    ->setWroteBy($wroteBy)
                    ->setStoresIds($storeIds)
                    ->setStatus($status)
                    ->save();
                $newItemId                  = $newItem->getId();
                $data[]                     = $newItemId;
                $prosConsData[$type][$item] = $newItemId;
                self::$_prosConsData = $prosConsData;
            }
        }
        
        return $data;
    }

    /**
     * @param $ratingCode
     * @param $storeIds
     * @param $profileId
     * @param $doCreateRating
     * @return int
     */
    public function createRating($ratingCode, $storeIds, $profileId, $doCreateRating)
    {
        $ratingId         = 0;
        $ratingDataObject = $this->_getRatingDataObject($profileId);
        if ($ratingDataObject->hasData($ratingCode)) {
            $ratingId = $ratingDataObject->getData($ratingCode);
        } elseif ($doCreateRating) {
            /** @var Mage_Rating_Model_Rating $rating */
            $rating = Mage::getModel('rating/rating');
            $rating->setRatingCode($ratingCode)
                ->setStores($storeIds)
                ->setStoresIds($storeIds)
                ->setEntityId($rating->getEntityIdByCode(Mage_Rating_Model_Rating::ENTITY_PRODUCT_CODE))
                ->save();
            $ratingId = $rating->getId();
            $ratingDataObject->setData($ratingCode, $ratingId);
            self::$_ratingData = $ratingDataObject;
        }

        return $ratingId;
    }

    /**
     * @param $ratingId
     * @param $optionValue
     * @return int
     * @throws Exception
     */
    public function createOption($ratingId, $optionValue)
    {
        $ratingOptionDataObject = $this->_getRatingOptionsDataObject();
        if (!$ratingOptionDataObject->hasData($ratingId)) {
            $ids = array();
            for ($i = 1; $i <= 5; $i++) {
                $optionModel = Mage::getModel('rating/rating_option');
                $optionModel->setCode($i)
                    ->setValue($i)
                    ->setRatingId($ratingId)
                    ->setPosition($i)
                    ->save();
                $ids[$i] = $optionModel->getId();
            }
            $ratingOptionDataObject->setData($ratingId, $ids);
        }
        $optionId = $ratingOptionDataObject->getData("$ratingId/$optionValue");

        return $optionId;
    }

    /**
     * @param $data
     * @param bool $useFullPath
     * @param int $store
     * @param int $profileId
     * @param null $doCreateRating
     * @param null $doCreateProsCons
     * @param null $maxWidth
     * @param null $maxHeight
     * @throws MageWorkshop_ImportExportReview_DuplicateException
     * @throws MageWorkshop_ImportExportReview_MissingProductException
     */
    public function saveRow(
        $data,
        $useFullPath      = true,
        $store            = 0,
        $profileId        = 0,
        $doCreateRating   = null,
        $doCreateProsCons = null,
        $maxWidth         = null,
        $maxHeight        = null
    ) {
        $storeIds          = $this->_getStoresIds($store);
        if ($this->_reviewAlreadyExists($data, $storeIds)) {
            throw new MageWorkshop_ImportExportReview_DuplicateException('Review with title: "' . $data['title'] . '"", for product with sku: "'. $data['sku'] . '" already exists');
        }
        $defaultStoreId    = Mage::app()->getWebsite(true)->getDefaultGroup()->getDefaultStoreId();
        $websiteIds        = $this->_getWebsiteIds($storeIds);
        $customerData      = $this->_getCustomerDataObject(false, $websiteIds);
        $productData       = $this->_getProductDataObject(false, $websiteIds);

        if ($productData->hasData((string) $data['sku'])) {
            $productId     = $productData->getData($data['sku']);
            $customerEmail = $data['customer_email'] ? $data['customer_email'] : null;
            $customerId    = $customerData->hasData($customerEmail) ? $customerData->getData($customerEmail) : null;
            $image         = isset($data['image']) ? $this->_prepareImage($data['image'], $useFullPath, $maxWidth, $maxHeight) : null;
            $status        = isset($data['status_id']) ? $data['status_id'] : 1;
            /** @var Mage_Review_Model_Review $review */
            $review = Mage::getModel('review/review');
            $review->setCreatedAt($data['created_at'])
                ->setEntityPkValue($productId)
                ->setEntityId(1)
                ->setStatusId($status)
                ->setTitle($data['title'])
                ->setDetail($data['detail'])
                ->setStoreId($defaultStoreId)
                ->setStores($storeIds)
                ->setCustomerId($customerId)
                ->setNickname($data['nickname'])
                ->setResponse($data['response'])
                ->setRecommendTo($data['recommend_to'])
                ->setCustomerEmail($customerEmail)
                ->setVideo($data['video'])
                ->setImage($image)
                ->setIsImported(true);

            $pros = isset($data['pros'])
                ? $this->createProsCons(MageWorkshop_DetailedReview_Model_Source_EntityType::PROS, $data['pros'], $storeIds, $doCreateProsCons)
                : array();

            $cons = isset($data['cons'])
                ? $this->createProsCons(MageWorkshop_DetailedReview_Model_Source_EntityType::CONS, $data['cons'], $storeIds, $doCreateProsCons)
                : array();

            $review->setPros($pros);
            $review->setCons($cons);
            $review->save();
            $this->_addUniqueIds($review);
            if ($data['rating']) {
                $optionData = explode('@', $data['rating']);

                foreach ($optionData as $option) {
                    $ratingData = explode(':',$option);
                    list($ratingCode, $optionValue) = $ratingData;
                    if ($ratingId = $this->createRating($ratingCode, $storeIds, $profileId, $doCreateRating)) {
                        $optionId = $this->createOption($ratingId, $optionValue);
                        if ($optionValue != 0) {
                            Mage::getModel('rating/rating')
                                ->setRatingId($ratingId)
                                ->setReviewId($review->getId())
                                ->setCustomerId($customerId)
                                ->addOptionVote($optionId, $productId);
                        }
                    }
                }
                $review->aggregate();
            }
        } else {
            throw new MageWorkshop_ImportExportReview_MissingProductException(
                Mage::helper('mageworkshop_importexportreview')->__("Product with SKU: %s does not exist. Review won't be saved", $data['sku'])
            );
        }
    }

    /**
     * @param $ids
     * @param array $data
     * @return string
     */
    protected function _getValuesByIds($ids, Array $data)
    {
        $ids = explode(',', $ids);
        $dataArray = array_intersect_key($data, array_flip($ids));
        return implode('@', $dataArray);
    }

    /**
     * @param Mage_Review_Model_Resource_Review_Collection $reviewsCollection
     * @param bool $fullImagesPath
     * @return array
     */
    public function exportReviews(Mage_Review_Model_Resource_Review_Collection $reviewsCollection, $fullImagesPath = true)
    {
        $baseUrl        = Mage::getBaseUrl('media');
        $exportFields   = array();
        $prosData       = array();
        $consData       = array();
        $ratingData     = array();
        $reviewsData    = array();
        $customerData   = $this->_getCustomerDataObject(true);
        $productData    = $this->_getProductDataObject(true);
        $ratingVoteData = $this->_getRatingOptionVoteData($reviewsCollection->getAllIds());

        /** @var MageWorkshop_DetailedReview_Model_Mysql4_Review_Proscons_Collection $prosConsCollection */
        $prosConsCollection = Mage::getModel('detailedreview/review_proscons')->getCollection();
        foreach ($prosConsCollection as $prosCons) {
            if ($prosCons->getEntityType() == Mage_Detailedreview_Model_Source_EntityType::PROS) {
                $prosData[$prosCons->getId()] = $prosCons->getName();
            } else {
                $consData[$prosCons->getId()] = $prosCons->getName();
            }
        }

        /** @var Mage_Rating_Model_Resource_Rating_Collection $allRatingCollection */
        $allRatingCollection = Mage::getModel('rating/rating')->getCollection();
        foreach ($allRatingCollection as $rating) {
            $ratingData[$rating->getId()] = $rating->getRatingCode();
        }

        /** @var Mage_Review_Model_Review|MageWorkshop_DetailedReview_Model_Review $review */
        foreach($reviewsCollection as $review) {
            if ($productData->hasData($review->getEntityPkValue())) {
                $optionValue = '';
                if (isset($ratingVoteData[$review->getId()])) {
                    foreach ($ratingVoteData[$review->getId()] as $rating) {
                        $value     =  $rating->getValue();
                        $ratingId   = $rating->getRatingId();
                        $ratingCode = $ratingData[$ratingId];

                        if (!empty($optionValue) && $optionValue != '') {
                            $optionValue = $optionValue."@".$ratingCode.":".$value;
                        } else {
                            $optionValue = $ratingCode.":".$value;
                        }
                    }
                }

                $customerEmail = $customerData->hasData($review->getCustomerId()) ? $customerData->getData($review->getCustomerId()) : '';
                $pros          = $this->_getValuesByIds($review->getPros(), $prosData);
                $cons          = $this->_getValuesByIds($review->getCons(), $consData);
                $image         = $fullImagesPath ? $review->getImage() ? $baseUrl . $review->getImage() : '' : $review->getImage();

                $exportFields['created_at']     = $review->getCreatedAt();
                $exportFields['sku']            = $productData->getData($review->getEntityPkValue());
                $exportFields['status_id']      = $review->getStatusId();
                $exportFields['title']          = $review->getTitle();
                $exportFields['detail']         = $review->getDetail();
                $exportFields['nickname']       = $review->getNickname();
                $exportFields['rating']         = $optionValue;
                $exportFields['entity_id']      = $review->getId();
                $exportFields['pros']           = $pros;
                $exportFields['cons']           = $cons;
                $exportFields['good_detail']    = $review->getGoodDetail();
                $exportFields['no_good_detail'] = $review->getNoGoodDetail();
                $exportFields['response']       = $review->getResponse();
                $exportFields['recommend_to']   = $review->getRecommendTo();
                $exportFields['video']          = $review->getVideo();
                $exportFields['image']          = $image;
                $exportFields['remote_addr']    = $review->getRemoteAddr();
                $exportFields['customer_email'] = $customerEmail;
                $exportFields['unique_id']      = $review->getUniqueId();

                $reviewsData[] = $exportFields;
            }
        }

        return $reviewsData;
    }

    /**
     * @param array $data
     * @param array $storeIds
     * @return bool
     */
    protected function _reviewAlreadyExists(Array $data, Array $storeIds)
    {
        $uniqueIdsObject = $this->_getUniqueIdsObject($storeIds);
        $reviewId        = $this->generateUniqueId($data['title'], $data['detail'], $data['sku']);
        return $uniqueIdsObject->hasData($reviewId);
    }

    /**
     * @param bool $isExport
     * @param array $websiteIds
     * @return Varien_Object
     */
    protected function _getProductDataObject($isExport = false, $websiteIds = array())
    {
        if (!self::$_productsDataObject) {
            $productData = array();
            /** @var Mage_Catalog_Model_Resource_Product_Collection $productCollection */
            $productCollection = Mage::getModel('catalog/product')->getCollection();
            if ($websiteIds) {
                $productCollection->getSelect()
                    ->joinInner(
                        array('websites' => $productCollection->getTable('catalog/product_website')),
                        'e.entity_id = websites.product_id',
                        array('website_id')
                    )
                    ->where('websites.website_id IN (?)', $websiteIds)
                    ->group('e.entity_id');
            }
            $productCollection->getSelect()->reset(Zend_Db_Select::COLUMNS)->columns(array('entity_id', 'sku'));
            foreach ($productCollection as $product) {
                $productData[$product->getSku()] = $product->getId();
            }
            if ($isExport) {
                $productData = array_flip($productData);
            }

            $productDataObject         = new Varien_Object();
            $productDataObject->setData($productData);
            self::$_productsDataObject = $productDataObject;
        }

        return self::$_productsDataObject;
    }

    /**
     * @param bool $isExport
     * @param array $websiteIds
     * @return Varien_Object
     */
    protected function _getCustomerDataObject($isExport = false, $websiteIds = array())
    {
        if (!self::$_customersDataObject) {
            $customerData = array();
            /** @var Mage_Customer_Model_Resource_Customer_Collection $customerCollection */
            $customerCollection = Mage::getModel('customer/customer')->getCollection();
            if ($websiteIds) {
                $customerCollection->addFieldToFilter('website_id', array('in' => $websiteIds));
            }

            $customerCollection->getSelect()->reset(Zend_Db_Select::COLUMNS)->columns(array('entity_id', 'email'));
            foreach ($customerCollection as $customer) {
                if ($email = $customer->getEmail()) {
                    $customerData[$email] = $customer->getId();
                }
            }
            if ($isExport) {
                $customerData = array_flip($customerData);
            }

            $customerDataObject         = new Varien_Object();
            $customerDataObject->setData($customerData);
            self::$_customersDataObject = $customerDataObject;
        }

        return self::$_customersDataObject;
    }

    /**
     * @param $profileId
     * @return Varien_Object
     */
    protected function _getRatingDataObject($profileId)
    {
        if (!self::$_ratingData) {
            $ratingData        = array();
            $ratingMappingData = array();
            if ($profileId) {
                $ratingMappingData = array();
                /** @var MageWorkshop_ImportExportReview_Model_Resource_RatingMapping_Collection $ratingMappingCollection */
                //Getting rating mapping for this profile
                $ratingMappingCollection = Mage::getModel('mageworkshop_importexportreview/ratingMapping')->getCollection()
                    ->addFieldToFilter('profile_id', array('eq' => $profileId));
                $ratingMappingCollection->getSelect()->joinLeft(
                    array('rating' => $ratingMappingCollection->getTable('rating/rating')),
                    'main_table.rating_id = rating.rating_id',
                    array('rating_label' => 'rating.rating_code')
                );
                foreach ($ratingMappingCollection as $ratingMapping) {
                    $ratingMappingData[$ratingMapping->getMappingValue()] = $ratingMapping->getRatingId();
                    $ratingMappingData[$ratingMapping->getMappingValue()] = $ratingMapping->getRatingId();
                }
            }
            /** @var Mage_Rating_Model_Resource_Rating_Collection $ratingCollection */
            $ratingCollection = Mage::getModel('rating/rating')->getCollection();
            foreach ($ratingCollection as $rating) {
                // Checking if there are some Mapping for this Rating;
                $ratingId = isset($ratingMappingData[$rating->getRatingCode()])? $ratingMappingData[$rating->getRatingCode()]: $rating->getId();
                $ratingData[$rating->getRatingCode()] = $ratingId;
            }
            $ratingData        = array_merge($ratingData, $ratingMappingData);
            $ratingDataObject  = new Varien_Object();
            $ratingDataObject->setData($ratingData);
            self::$_ratingData = $ratingDataObject;
        }

        return self::$_ratingData;
    }

    /**
     * @return Varien_Object
     */
    protected function _getRatingOptionsDataObject()
    {
        if (!self::$_ratingOptionData) {
            $optionData = array();
            /** @var Mage_Catalog_Model_Resource_Product_Collection $productCollection */
            $optionCollection = Mage::getModel('rating/rating_option')->getCollection();
            foreach ($optionCollection as $option) {
                $optionData[$option->getRatingId()][$option->getValue()] = $option->getId();
            }

            $ratingOptionDataObject  = new Varien_Object();
            $ratingOptionDataObject->setData($optionData);
            self::$_ratingOptionData = $ratingOptionDataObject;
        }
        
        return self::$_ratingOptionData;
    }

    /**
     * @param $store
     * @return array
     */
    protected function _getStoresIds($store)
    {
        if (!self::$_storeIds) {
            $storeIds = array();
            if ($store == 0) {
                $stores = Mage::app()->getStores();
                /** @var Mage_Core_Model_Store $store */
                foreach ($stores as $store) {
                    $storeIds[] = $store->getId();
                }
            } else {
                $storeIds = array($store);
            }

            self::$_storeIds = $storeIds;
        }

        return self::$_storeIds;
    }

    /**
     * @param array $storeIds
     * @return bool
     */
    protected function _useForAllStores(Array $storeIds)
    {
        return (count($storeIds) == 1 && reset($storeIds) == 0);
    }

    /**
     * @param array $storeIds
     * @return array
     */
    protected function _getWebsiteIds(Array $storeIds)
    {
        if (!$this->_useForAllStores($storeIds)) {
            if (!self::$_websiteIds) {
                $websiteIds = array();
                foreach ($storeIds as $storeId) {
                    $websiteIds[] = Mage::app()->getStore($storeId)->getWebsiteId();
                }

                self::$_websiteIds = $websiteIds;
            }
        }

        return self::$_websiteIds;
    }

    /**
     * @return array
     */
    protected function _getProsConsData()
    {
        if (!self::$_prosConsData) {
            $prosConsData = array();
            /** @var Mage_Catalog_Model_Resource_Product_Collection $productCollection */
            $prosConsCollection = Mage::getModel('detailedreview/review_proscons')->getCollection();
            foreach ($prosConsCollection as $prosCons) {
                $prosConsData[$prosCons->getEntityType()][$prosCons->getName()] = $prosCons->getId();
            }
            self::$_prosConsData = $prosConsData;
        }

        return self::$_prosConsData;
    }

    /**
     * @param array $reviewIds
     * @return array
     */
    protected function _getRatingOptionVoteData(Array $reviewIds)
    {
        $ratingVoteData = array();
        /** @var Mage_Rating_Model_Resource_Rating_Option_Vote_Collection $ratingCollection */
        $ratingCollection = Mage::getModel('rating/rating_option_vote')->getCollection();
        $ratingCollection->addFieldToFilter('review_id', array('in' => $reviewIds));
        /** @var Mage_Rating_Model_Rating_Option_Vote $item */
        foreach ($ratingCollection as $item) {
            $ratingVoteData[$item->getData('review_id')][] = $item;
        }

        return $ratingVoteData;
    }

    /**
     * @param array $storeIds
     * @return Varien_Object
     */
    protected function _getUniqueIdsObject(Array $storeIds)
    {
        if (!self::$_uniqueIdsObject) {
            $uniqueIds = array();
            $reviewCollection = Mage::getModel('review/review')->getCollection()->addFieldToSelect('unique_id');
            if (!$this->_useForAllStores($storeIds)) {
                $reviewCollection->addStoreFilter($storeIds);
            }

            foreach ($reviewCollection as $review) {
                $uniqueIds[$review->getData('unique_id')] = true;
            }

            $uniqueIdsObject        = new Varien_Object();
            $uniqueIdsObject->setData($uniqueIds);
            self::$_uniqueIdsObject = $uniqueIdsObject;
        }

        return self::$_uniqueIdsObject;
    }

    /**
     * @param Mage_Review_Model_Review $review
     */
    protected function _addUniqueIds(Mage_Review_Model_Review $review)
    {
        self::$_uniqueIdsObject->setData($review->getData('unique_id'), true);
    }

    /**
     * @param $images
     * @param $useFullPath
     * @param null $maxWidth
     * @param null $maxHeight
     * @return null|string
     */
    protected function _prepareImage($images, $useFullPath, $maxWidth = null, $maxHeight = null )
    {
        $importedImages = array();
        if ($images == '') {
            $images = null;
        } else {
            if ($useFullPath) {
                foreach (explode(',', $images) as $image) {
                    $image = trim($image);
                    $url = $image;
                    $name = pathinfo($url, PATHINFO_BASENAME);
                    $name = uniqid() . stripslashes($name);
                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
                    $rawData = curl_exec($ch);
                    if (!$rawData) {
                        continue;
                    }
                    curl_close($ch);
                    $media = Mage::getBaseDir('media') . DS;
                    $dispersion = Varien_File_Uploader::getDispretionPath($name);
                    $path = 'detailedreview' . $dispersion;
                    $drFullPath = $path . DS . $name;
                    $fullPath = $media . $drFullPath;

                    if (!file_exists($media . $path)) {
                        mkdir($media . $path, 0777, true);
                    }
                    $fp = fopen($fullPath, 'w');
                    fwrite($fp, $rawData);
                    fclose($fp);
                    try {
                        $imageObj = new Varien_Image($fullPath);
                        if ($maxWidth && $maxHeight
                            && $imageObj->getOriginalWidth() > $maxWidth
                            && $imageObj->getOriginalHeight() > $maxHeight
                        ) {
                            $imageObj->constrainOnly(TRUE);
                            $imageObj->keepAspectRatio(TRUE);
                            $imageObj->keepFrame(FALSE);
                            $imageObj->resize($maxWidth, $maxHeight);
                            $imageObj->save($fullPath);
                        }
                        $importedImages[] = $drFullPath;
                    } catch (Exception $e) {
                        continue;
                    }
                }
            } else {
                $importedImages[] = trim(preg_replace( '/\s*,\s*/' , ',', $images));
            }
        }
        return implode(',', $importedImages);
    }

    /**
     * @param string $title
     * @param string $detail
     * @param string $sku
     * @return string
     */
    public function generateUniqueId($title, $detail, $sku)
    {
        return md5($title . $detail . $sku);
    }
}