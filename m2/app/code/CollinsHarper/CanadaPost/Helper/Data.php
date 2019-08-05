<?php
/**
 * Copyright © 2015 Collins Harper. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CollinsHarper\CanadaPost\Helper;


use \CollinsHarper\Core\Helper\Measure;
use \CollinsHarper\CanadaPost\Model\Source\Date\Formats;
/**
 * Measure Unit helper
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends AbstractHelp
{

    /**
     * 
     * @param array $items
     * @param array $qtys
     * @return array
     */
    public function getBoxForItems($items, $qtys = array())
    {
        $box = array();

        try {

            if ($this->_moduleManager->isOutputEnabled('CollinsHarper_ShippingBox')) {

                $box = $this->objectFactory->setClass('CollinsHarper\ShippingBox\Helper\Data')->create()->selectBoxForItems($items, $qtys);

            }

        } catch (Exception $e) {
            $this->_chLogged->notice(__METHOD__ . " Exception in getting boxes form Collinsharper.com " . $e->getMessage());
            //Mage::logException($e);
            $box = [];
        }



        if(!count($box)) {

            $box[0]['box']['l'] = round($this->getDefaultLength(), 1);

            $box[0]['box']['w'] = round($this->getDefaultWidth(), 1);

            $box[0]['box']['h'] = round($this->getDefaultHeight(), 1);

            $box[0]['box']['weight'] = 0;

            $formatItems = array();
            $k = 0;

            foreach ($items as $i => $item) {

                $product = $this->getProduct($item);


                $qty = (!empty($qtys[$item->getId()])) ? $qtys[$item->getId()] : $item->getQty();

                // TODO we are doing this in multiple places.
                for ($j = 0; $j < $qty; $j++) {
                    $itemWeight = $this->getConvertedWeight($product->getWeight());
                    $formatItems[$k] = array(
                        'id' => $item->getId(),
                        'l' => $this->getProductLength($product),
                        'w' => $this->getProductWidth($product),
                        'h' => $this->getProductHeight($product),
                        'weight' => $itemWeight,
                    );

                    $box[0]['box']['weight']  += $itemWeight;


                    if (empty($formatItems[$k]['l'])) {
                        $formatItems[$k]['l'] = $this->getDefaultLength() ? $this->getDefaultLength() : self::DEFAULT_SIZE;
                    }

                    if (empty($formatItems[$k]['w'])) {
                        $formatItems[$k]['w'] = $this->getDefaultWidth() ? $this->getDefaultWidth() : self::DEFAULT_SIZE;
                    }

                    if (empty($formatItems[$k]['h'])) {
                        $formatItems[$k]['h'] = $this->getDefaultHeight() ? $this->getDefaultHeight() : self::DEFAULT_SIZE;
                    }

                    $k++;
                }
            }

            $box[0]['items'] = $formatItems;
        }

        $this->_chLogged->info(__METHOD__ . __LINE__);
        $this->_chLogged->info(__METHOD__ . " boxed " . print_r($box, 1));


        return $box;

    }

    /**
     * 
     * @param \Magento\Catalog\Model\Product $product
     * @return float
     */
    public function getProductLength($product)
    {
        $dim = $this->getConvertedDimension($product->getData($this->getConfigValue(Measure::XML_PATH_PRODUCT_SHIPPING_LENGTH)));
        return $dim ? $dim : $this->getDefaultLength();
    }

    /**
     * 
     * @param \Magento\Catalog\Model\Product $product
     * @return float
     */
    public function getProductWidth($product)
    {
       $dim = $this->getConvertedDimension($product->getData($this->getConfigValue(Measure::XML_PATH_PRODUCT_SHIPPING_WIDTH)));
        return $dim ? $dim : $this->getDefaultWidth();
    }

    /**
     * 
     * @param \Magento\Catalog\Model\Product $product
     * @return float
     */
    public function getProductHeight($product)
    {
        $dim = $this->getConvertedDimension($product->getData($this->getConfigValue(Measure::XML_PATH_PRODUCT_SHIPPING_HEIGHT)));
        return $dim ? $dim : $this->getDefaultHeight();
    }

    /**
     * 
     * @param string $date
     * @param boolean $format
     * @return string
     */
    public function formatDate($date, $format = false)
    {
        if(!$format) {
            $format = $this->getConfigValue(self::XML_PATH_DATE_FORMAT);
        }
        // TODO constants for these for the list and this ficntioun
        $date = strtotime($date);
        switch ($format) {
            case Formats::FULL:
                return date(Formats::FULL_FORMAT, $date);
            case Formats::LONG:
                return date(Formats::LONG_FORMAT, $date);
            case Formats::MEDIUM:
                return date(Formats::MEDIUM_FORMAT, $date);
            case Formats::SHORT:
                return date(Formats::SHORT_FORMAT, $date);
        }

        return $date;
    }


}