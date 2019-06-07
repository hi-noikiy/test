<?php

class MageWorkshop_ImportExportReview_Model_Convert_Adapter_Import extends Mage_Catalog_Model_Convert_Adapter_Product
{
    /**
     * @var Varien_Object
     */
    protected static $_ratingData;

    public function saveRow(Array $data)
    {
        /** @var MageWorkshop_ImportExportReview_Helper_ImportExport $helper */
        $helper           = Mage::helper('mageworkshop_importexportreview/importExport');
        $doCreateRating   = $this->getBatchParams('doCreateRatings');
        $doCreateProsCons = $this->getBatchParams('doCreateProsCons');
        $useFullPath      = $this->getBatchParams('full_images_path');
        $profileId        = $this->getBatchParams('profile_id');
        $store            = $this->getBatchParams('store');
        $maxWidth         = $this->getBatchParams('max_width');
        $maxHeight        = $this->getBatchParams('max_height');
        
        if ($this->getBatchParams('yotpo')) {
            $data = $this->_mapFieldsForYotpo($data);
        }
        $helper->saveRow($data, $useFullPath, $store, $profileId, $doCreateRating, $doCreateProsCons, $maxWidth, $maxHeight);
    }

    /**
     * @param array $data
     * @return array
     */
    protected function _mapFieldsForYotpo(Array $data)
    {
        $map = array(
            'review_title'      => 'title',
            'review_content'    => 'detail',
            'review_score'      => 'rating',
            'date'              => 'created_at',
            'product_image_url' => 'image',
            'display_name'      => 'nickname',
            'email'             => 'customer_email'
        );
        
        $additionalFields = array(
            'response'     => null,
            'recommend_to' => null,
            'video'        => null
        );

        foreach ($map as $yotpoField => $drField) {
            switch ($yotpoField) {
                case 'date':
                    $data[$drField] = isset($data[$yotpoField]) ? date('Y-m-d H:i:s', strtotime($data[$yotpoField])) : date('Y-m-d H:i:s');
                    break;
                case 'review_score':
                    $data[$drField] = $this->_getRatingValue($data[$yotpoField]);
                    break;
                default:
                    $data[$drField] = isset($data[$yotpoField]) ? $data[$yotpoField] : null;
            }
        }

        $data = array_merge($data, $additionalFields);

        return $data;
    }

    protected function _getRatingValue($score)
    {
        $ratingData = $this->_getRatingData();
        return implode(":$score@", $ratingData) . ":$score";
    }

    /**
     * @return Varien_Object
     */
    protected function _getRatingData()
    {
        if (!self::$_ratingData) {
            /** @var Mage_Rating_Model_Resource_Rating_Collection $ratingCollection */
            $ratingCollection = Mage::getModel('rating/rating')->getCollection();
            $ratingData       = array();
            foreach ($ratingCollection as $rating) {
                $ratingData[] =  $rating->getRatingCode();
            }
            self::$_ratingData = $ratingData;
        }

        return self::$_ratingData;
    }
}




























