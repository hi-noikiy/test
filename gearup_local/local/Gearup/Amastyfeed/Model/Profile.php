<?php

class Gearup_Amastyfeed_Model_Profile extends Amasty_Feed_Model_Profile
{
	/**
     * prepare attribute value for output
     *
     * @param string $value
     * @param string $code attribute code
     *
     * @return string
     */
    protected function _modifyAttribute($value, $code)
    {
        switch ($code) {
            case 'image':
            case 'small_image':
            case 'thumbnail':
                $mediaConfig = Mage::getSingleton('catalog/product_media_config');

                if ($this->getDefaultImage() && ($value == "no_selection" || !$value)) {
                    // if no image selected. Get default image URL
                    $value = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA)
                        . 'amfeed/images/' . $this->getId() . '.jpg';
                } else {
                    if ($value && $value != "no_selection") {
                        //$value = str_replace('https://', 'http://', $mediaConfig->getMediaUrl($value));
                        $value = str_replace('http://', 'https://', $mediaConfig->getMediaUrl($value));
                    } else {
                        $value = '';
                    }
                }
        }

        return $value;
    }
    /**
     * 
     * @param type $value
     * @param type $format
     */
    protected function _format(&$value, $format){
        switch ($format) {
            case 'as_is':
                break;
            case 'strip_tags':
                $value = strtr($value, array("\n" => '', "\r" => ''));
                $value = strip_tags($value);
                break;
            case 'html_escape':
                $value = htmlspecialchars($value);
                break;
            case 'date':
                if ($this->getFrmDate() && !empty($value)) {
                    $value = date($this->getFrmDate(), strtotime($value));
                }
                break;
            case 'price':
                if ($this->getFrmPrice() !== null && $this->getFrmPrice() !== '') {

                    $decPoint = $this->getFrmPriceDecPoint();
                    $thPoint  = $this->getFrmPriceThousandsSep();
                    $roundingPrice = intval($this->getPriceRounding());
                    if ($decPoint === null) {
                        $decPoint = '';
                    }
                    if ($thPoint === null) {
                        $thPoint = '';
                    }

                    if ($value > 0) {
                        $value = $value * $this->getCurrencyRate();
                        $code = $this->getCurrency();

                        if ($roundingPrice === 1){
                            if (in_array($code, array('AED','SAR'))) {
                                $value = ceil(ceil($value) + ceil($value)*5/100);
                            } elseif (in_array($code, array('OMR', 'KWD', 'BHD', 'QAR'))) {
                                $value = round($value, 1);
                            }
                        }
                        $value = number_format($value, intval($this->getFrmPrice()), $decPoint, $thPoint);
                    }
                }
                break;
            case 'lowercase':
                $value = function_exists("mb_strtolower") ?
                            mb_strtolower($value, "UTF-8") :
                            strtolower($value);
                break;
            case 'integer':
                $value = intval($value);
                break;
        }
    }
}