<?php

namespace Ktpl\Checkout\Model\Plugin;

class AttributeMergerPlugin {

    public function afterMerge(\Magento\Checkout\Block\Checkout\AttributeMerger $subject, $result) {

        if (array_key_exists('firstname', $result)) {
            $result['firstname']['additionalClasses'] = 'field_custom';
        }

        if (array_key_exists('lastname', $result)) {
            $result['lastname']['additionalClasses'] = 'field_custom';
        }
        if (array_key_exists('city', $result)) {
            $result['city']['additionalClasses'] = 'field_custom';
        }
        if (array_key_exists('region_id', $result)) {
            $result['region_id']['additionalClasses'] = 'field_custom';
        }

        if (array_key_exists('postcode', $result)) {
            $result['postcode']['additionalClasses'] = 'field_custom zip-custom';
        }
        if (array_key_exists('country_id', $result)) {
            $result['country_id']['additionalClasses'] = 'field_custom country-custom';
        }
        if (array_key_exists('dob', $result)) {
            $result['dob']['additionalClasses'] = 'field_custom';
        }

        if (array_key_exists('street', $result)) {
            $result['street']['children'][0]['label'] = __('Street Address 1*');
            $result['street']['children'][1]['label'] =  __('Street Address 2');
           
        }

        if(array_key_exists('firstname', $result)){
            $result['firstname']['label'] = __('First Name*');
        }

        if(array_key_exists('lastname', $result)){
            $result['lastname']['label'] = __('Last Name*');
        }

        if(array_key_exists('city', $result)){
            $result['city']['label'] = __('City*');
        }

        if(array_key_exists('region_id', $result)){
            $result['region_id']['label'] = __('Please select a region, state or province*');
        }

        if(array_key_exists('postcode', $result)){
            $result['postcode']['label'] = __('Zip/Postal Code*');
        }

        if(array_key_exists('telephone', $result)){
            $result['telephone']['label'] = __('Phone Number*');
        }
     
        return $result;
    }

}
