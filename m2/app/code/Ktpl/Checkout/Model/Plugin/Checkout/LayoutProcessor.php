<?php

namespace Ktpl\Checkout\Model\Plugin\Checkout;

class LayoutProcessor {

    protected $_storeManager;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $_storeManager
    ){
        $this->_storeManager = $_storeManager;
     }
    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     */
    public function afterProcess(\Magento\Checkout\Block\Checkout\LayoutProcessor $subject, array $jsLayout) {
        $customAttributeCode = 'dob';
        $customField = [
            'component' => 'Magento_Ui/js/form/element/abstract',
            'config' => [
// customScope is used to group elements within a single form (e.g. they can be validated separately)
                'customScope' => 'shippingAddress',
                'customEntry' => null,
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/date',
                'id' => 'date_of_birth'
            ],
            'options' => [
                'dateFormat'  => 'mm/dd/yy',
                'yearRange'=> '1900:c'
             ],
            'dataScope' => 'shippingAddress' . '.' . $customAttributeCode,
            'label' => 'Birth Date',
            'placeholder' => 'Birth Date*',
            'provider' => 'checkoutProvider',
            'sortOrder' => 250,
            'validation' => [
                'required-entry' => true,
                'validation-dob19' => true, 
                //'date_range_min' => 18
            ],           
            'filterBy' => null,
            'customEntry' => null,
            'visible' => true,
        ];

        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children'][$customAttributeCode] = $customField;

  $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['customer-email']['label'] = __('Email Address*');
  $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['customer-email']['label'] = __('Email Address*');
  
        if($this->_storeManager->getStore()->getStoreId() == "5"){

            if(array_key_exists('validation', $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['telephone']))
            {

                unset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['telephone']['validation']);

                $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['telephone']['label'] = __('Phone Number');
            }

            if(array_key_exists('validation', $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['dob']))
            {

                unset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['dob']['validation']);

                $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['dob']['label'] = __('Birth Date');

                $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['dob']['placeholder'] = __('Birth Date');
            }
        }
        if($this->_storeManager->getStore()->getStoreId() == "7"){

            if(array_key_exists('company', $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children'])) 
            {

                $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['company']['label'] = __('Company (optional)');

            }

            if(array_key_exists('validation', $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['street']['children']['0'])) {

                $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['street']['children']['0']['label'] = __('Apartment, Suite, etc.*');

            }
            
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['region_id']['label'] = __('region, state or province*');

            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['region_id']['sortOrder'] = "115";

            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['country_id']['sortOrder'] = "100";            
        }
            
        //print_r($jsLayout);exit;

        return $jsLayout;
    }

}
