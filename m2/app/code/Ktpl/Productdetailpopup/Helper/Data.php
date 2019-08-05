<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Ktpl\Productdetailpopup\Helper;

use Magento\Catalog\Model\Product;

/**
 * Class Data
 * Helper class for getting options
 * @api
 * @since 100.0.2
 */
class Data
{
    /**
     * Catalog Image Helper
     *
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;
    /**
     * @param \Magento\Catalog\Helper\Image $imageHelper
     */
    public function __construct(\Magento\Catalog\Helper\Image $imageHelper,\Magento\Eav\Model\Config $eavConfig)
    {
        $this->eavConfig = $eavConfig;
        $this->imageHelper = $imageHelper;
    }

    public function getOptions($currentProduct, $allowedProducts)
    {
        $options = [];
        $allowAttributes = $this->getAllowAttributes($currentProduct);
        
        foreach ($allowedProducts as $product) {

            $productId = $product->getId();
            foreach ($allowAttributes as $attribute) {
                $productAttribute = $attribute->getProductAttribute();
                $productAttributeId = $productAttribute->getId();
                $attributeValue = $product->getData($productAttribute->getAttributeCode());

                 //$attribute = $product->getResource()->getAttribute('color');
                $options[$productAttributeId][$attributeValue][] = $productId;
                $options['index'][$productId][$productAttributeId] = $attributeValue;
                $options['swatch'][$attributeValue] = $this->getOptiomSwatch($attributeValue);

            }
        }
        

        return $options;
    }
    public function getOptiomSwatch($attributeOptionId)
    {
        $swatch =  array();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $swatchHelper=$objectManager->get("Magento\Swatches\Helper\Media");
        $swatchCollection = $objectManager->create('Magento\Swatches\Model\ResourceModel\Swatch\Collection');

        $swatchCollection->addFieldtoFilter('option_id',$attributeOptionId);
        $item=$swatchCollection->getFirstItem();
        if($item->getType() == 2){
            $swatch['url'] = $swatchHelper->getSwatchAttributeImage('swatch_thumb', $item->getValue());
            //$swatchHelper->getSwatchAttributeImage('swatch_image', $item->getValue());
        }elseif($item->getType() == 1){
            $swatch['color'] = $item->getValue();
        }
               
     return $swatch;
    }

    /**
     * Get allowed attributes
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getAllowAttributes($product)
    {
        return $product->getTypeInstance()->getConfigurableAttributes($product);
    }
}
