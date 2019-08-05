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
namespace Magestore\Affiliateplus\Model;

/**
 * Model banner
 */
class Banner extends AbtractModel
{
    const BANNER_IMAGE = '1';

    const BANNER_FLASH = '2';

    const BANNER_TEXT = '3';

    /**
     * @var
     */
    const BASE_MEDIA_PATH = 'affiliateplus/banner';

    /**
     * @var null
     */
    protected $_storeViewId = null;

    protected $_eventPrefix = 'affiliateplus_banner';

    protected $_eventObject = 'affiliateplus_banner';

    /**
     * Model construct that should be used for object initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magestore\Affiliateplus\Model\ResourceModel\Banner');
    }
    /**
     * Get available statuses.
     *
     * @return void
     */
    public static function getAvailableTypes()
    {
        return [
                self::BANNER_IMAGE => __('Image'),
                self::BANNER_FLASH => __('Flash'),
                self::BANNER_TEXT => __('Text')
        ];
    }

    /**
     * @return int
     */
    public function getStoreViewId()
    {
        return $this->_storeViewId;
    }

    /**
     * @param $storeViewId
     * @return $this
     */
    public function setStoreViewId($storeViewId)
    {
        $this->_storeViewId = $storeViewId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStoreAttributes()
    {
        $storeAttribute = new \Magento\Framework\DataObject(
            [
                'store_attribute'	=> array(
                    'title',
                    //'width',
                    //'height',
                    'status'
                )
            ]
        );

        $this->_eventManager->dispatch($this->_eventPrefix.'_get_store_attributes',array(
            $this->_eventObject	=> $this,
            'attributes'		=> $storeAttribute,
        ));

        return $storeAttribute->getStoreAttribute();
    }

    /**
     * @param int $id
     * @param null $field
     * @return $this
     */
    public function load($id, $field=null)
    {
        parent::load($id,$field);

        $this->_eventManager->dispatch($this->_eventPrefix.'_load_store_value_before', $this->_getEventData());

        if ($this->getStoreViewId())
            $this->loadStoreValue();

        $this->_eventManager->dispatch($this->_eventPrefix.'_load_store_value_after', $this->_getEventData());

        return $this;
    }

    /**
     * @param null $storeId
     * @return $this
     */
    public function loadStoreValue($storeId = null)
    {
        if (!$storeId)
            $storeId = $this->getStoreViewId();

        if (!$storeId)
            return $this;

        $storeValues = $this->_objectManager->create('Magestore\Affiliateplus\Model\ResourceModel\Banner\Value\Collection')
                ->addFieldToFilter('banner_id',$this->getId())
                ->addFieldToFilter('store_id',$storeId);

        foreach ($storeValues as $value){
            $this->setData($value->getAttributeCode().'_in_store',true);
            $this->setData($value->getAttributeCode(),$value->getValue());
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function beforeSave(){
        $defaultBanner = $this->_objectManager->create('\Magestore\Affiliateplus\Model\Banner')->load($this->getId());
        if ($storeId = $this->getStoreViewId()){
            $storeAttributes = $this->getStoreAttributes();
            $data = $this->getData();
            foreach ($storeAttributes as $attribute){
                if (isset($data['use_default']) && isset($data['use_default'][$attribute]) && $data['use_default'][$attribute]){
                    $this->setData($attribute.'_in_store',false);
                }else{
                    $this->setData($attribute.'_in_store',true);
                    $this->setData($attribute.'_value',$this->getData($attribute));
                }
                $this->setData($attribute,$defaultBanner->getData($attribute));
            }
        }
        return parent::beforeSave();
    }

    /**
     * @return $this
     */
    public function afterSave(){
        if ($storeId = $this->getStoreViewId()){
            $storeAttributes = $this->getStoreAttributes();
            foreach ($storeAttributes as $attribute){
                $attributeValue = $this->_objectManager->create('Magestore\Affiliateplus\Model\Banner\Value')
                        ->loadAttributeValue($this->getId(),$storeId,$attribute);
                if ($this->getData($attribute.'_in_store')){
                    try{
                        $attributeValue->setValue($this->getData($attribute.'_value'))->save();
                    }catch(\Exception $e){

                    }
                }elseif($attributeValue && $attributeValue->getId()){
                    try{
                        $attributeValue->delete();
                    }catch(\Exception $e){

                    }
                }
            }
        }
        return parent::afterSave();
    }
}
