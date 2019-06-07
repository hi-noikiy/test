<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <info@mage-review.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_DetailedReview
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (https://mage-review.com/)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <info@mage-review.com>
 */
class MageWorkshop_DetailedReview_Helper_Validate extends Mage_Core_Helper_Abstract
{
    const VALIDATION_TYPE_FRONTEND = 'frontend';
    const VALIDATION_TYPE_BACKEND  = 'backend';
    const VALIDATION_RULES_PATH  = 'detailedreview/validation_options/';

    protected $_validationRules = array ();
    protected $_validationRestrictions = array();

    public function __construct()
    {
        $this->_validationRestrictions = array(
                "nickname_min" => Mage::getStoreConfig(self::VALIDATION_RULES_PATH . 'nickname_min'),
                "nickname_max" => Mage::getStoreConfig(self::VALIDATION_RULES_PATH . 'nickname_max'),
                "title_min" => Mage::getStoreConfig(self::VALIDATION_RULES_PATH . 'title_min'),
                "title_max" => Mage::getStoreConfig(self::VALIDATION_RULES_PATH . 'title_max'),
                "detail_min" => Mage::getStoreConfig(self::VALIDATION_RULES_PATH . 'detail_min'),
                "detail_max" => Mage::getStoreConfig(self::VALIDATION_RULES_PATH . 'detail_max'),
                "user_pros_max" => Mage::getStoreConfig(self::VALIDATION_RULES_PATH . 'user_pros_max'),
                "good_detail_max" => Mage::getStoreConfig(self::VALIDATION_RULES_PATH . 'good_detail_max'),
                "user_cons_max" => Mage::getStoreConfig(self::VALIDATION_RULES_PATH . 'user_cons_max'),
                "no_good_detail_max" => Mage::getStoreConfig(self::VALIDATION_RULES_PATH . 'no_good_detail_max'),
                "location_max" => Mage::getStoreConfig(self::VALIDATION_RULES_PATH . 'location_max'),
        );
        $this->_validationRules = array (
            'nickname' => array(
                self::VALIDATION_TYPE_FRONTEND => 'required-entry validate-length minimum-length-' . $this->_validationRestrictions['nickname_min'] . ' maximum-length-' . $this->_validationRestrictions['nickname_max'],
                self::VALIDATION_TYPE_BACKEND  => array(
                    'NotEmpty'     => true,
                    'StringLength' => array('min' => $this->_validationRestrictions['nickname_min'], 'max' => $this->_validationRestrictions['nickname_max'])
                ),
                'required' => true,
                'label' => 'Nickname',
                'error' => $this->__('Nickname must be min %d and max %d characters', $this->_validationRestrictions['nickname_min'],  $this->_validationRestrictions['nickname_max'])
            ),
            'title'    => array(
                self::VALIDATION_TYPE_FRONTEND => 'required-entry validate-length minimum-length-' . $this->_validationRestrictions['title_min'] . ' maximum-length-' . $this->_validationRestrictions['title_max'] . ' not-url',
                self::VALIDATION_TYPE_BACKEND  => array(
                    'NotEmpty'     => true,
                    'StringLength' => array('min' => $this->_validationRestrictions['title_min'], 'max' => $this->_validationRestrictions['title_max'], 'encoding' => 'UTF-8')
                ),
                'required' => true,
                'label' => 'Review Title',
                'error' => $this->__('Title must be min %d and max %d characters', $this->_validationRestrictions['title_min'], $this->_validationRestrictions['title_max'] )
            ),
            'detail'   => array(
                self::VALIDATION_TYPE_FRONTEND => 'required-entry validate-length minimum-length-' . $this->_validationRestrictions['detail_min'] . ' maximum-length-' . $this->_validationRestrictions['detail_max'],
                self::VALIDATION_TYPE_BACKEND  => array(
                    'NotEmpty'     => true,
                    'StringLength' => array('min' => $this->_validationRestrictions['detail_min'], 'max' => $this->_validationRestrictions['detail_max'], 'encoding' => 'UTF-8')
                ),
                'required' => true,
                'label' =>'Overall Review',
                'error' => $this->__('Review must be min %d and max %d characters', $this->_validationRestrictions['detail_min'], $this->_validationRestrictions['detail_max'])
            ),
            'user_pros' => array(
                self::VALIDATION_TYPE_FRONTEND => 'validate-length maximum-length-' . $this->_validationRestrictions['user_pros_max'] . ' not-url pros-cons',
                self::VALIDATION_TYPE_BACKEND  => array(
                    'StringLength' => array('max' => $this->_validationRestrictions['user_pros_max'], 'encoding' => 'UTF-8')
                ),
                'label' => 'Pros',
                'error' => $this->__('Pros have max %d characters', $this->_validationRestrictions['user_pros_max'])
            ),
            'good_detail' => array(
                self::VALIDATION_TYPE_FRONTEND => 'validate-length maximum-length-' . $this->_validationRestrictions['good_detail_max'] . ' not-url',
                self::VALIDATION_TYPE_BACKEND  => array(
                    'StringLength' => array('max' => $this->_validationRestrictions['good_detail_max'], 'encoding' => 'UTF-8')
                ),
                'label' => 'what do you like about this item?',
                'error' => $this->__('Must be max %d characters', $this->_validationRestrictions['good_detail_max'])
            ),
            'user_cons' => array(
                self::VALIDATION_TYPE_FRONTEND => 'validate-length maximum-length-' . $this->_validationRestrictions['user_cons_max'] . ' not-url pros-cons',
                self::VALIDATION_TYPE_BACKEND  => array(
                    'StringLength' => array('max' => $this->_validationRestrictions['user_cons_max'], 'encoding' => 'UTF-8')
                ),
                'label' => 'Cons',
                'error' => $this->__('Cons have max %d characters', $this->_validationRestrictions['user_cons_max'])
            ),
            'no_good_detail' => array(
                self::VALIDATION_TYPE_FRONTEND => 'validate-length maximum-length-' . $this->_validationRestrictions['no_good_detail_max'] . ' not-url',
                self::VALIDATION_TYPE_BACKEND  => array(
                    'StringLength' => array('max' => $this->_validationRestrictions['no_good_detail_max'], 'encoding' => 'UTF-8')
                ),
                'label' => 'what do you dislike about this item?',
                'error' => $this->__('Must be max %d characters', $this->_validationRestrictions['no_good_detail_max'])
            ),
            'video' => array(
                self::VALIDATION_TYPE_FRONTEND => 'validate-youtube-url',
                self::VALIDATION_TYPE_BACKEND  => array(
                    'Regex' => array('pattern' => '/^(?:https?:\/\/)?(?:(?:www|m)\.)?(?:youtu\.be\/|youtube(?:-nocookie)?\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?$/')
                ),
                'label' => 'video',
                'error' => $this->__('Wrong to link video')
            ),
            'location' => array(
                self::VALIDATION_TYPE_FRONTEND => 'validate-length maximum-length-' . $this->_validationRestrictions['location_max'] . ' not-url',
                self::VALIDATION_TYPE_BACKEND  => array(
                    'StringLength' => array('max' => $this->_validationRestrictions['location_max'], 'encoding' => 'UTF-8')
                ),
                'label' => 'Location',
                'error' => $this->__('Location has only letters, numbers and whitespace')
            ),
            'age' => array(
                self::VALIDATION_TYPE_FRONTEND => 'validate-digits',
                self::VALIDATION_TYPE_BACKEND  => array(
                    'Int' => true
                ),
                'label' => 'age',
                'error' => $this->__('Age must be integer')
            ),
            'height' => array(
                self::VALIDATION_TYPE_FRONTEND => 'validate-number',
                self::VALIDATION_TYPE_BACKEND  => array(
                    'Float' => true
                ),
                'allow_empty' => true,
                'label' => 'height',
                'error' => $this->__('Height must be number')
            )
        );


        if (!Mage::getSingleton('customer/session')->isLoggedIn() && Mage::getStoreConfig('detailedreview/settings_customer/email_field')) {
            $this->_validationRules['customer_email'] = array(
                self::VALIDATION_TYPE_FRONTEND => 'required-entry validate-email',
                self::VALIDATION_TYPE_BACKEND  => array(
                    'NotEmpty'     => true,
                    'EmailAddress' => array(
                        'allow' => Zend_Validate_Hostname::ALLOW_URI
                    ),
                ),
                'required' => true,
                'label' => 'Email',
                'error' => $this->__('Please enter a valid email address. For example johndoe@domain.com . ')
            );
        }
    }

    public function getFieldsToValidate()
    {
        return array_keys($this->_validationRules);
    }

    public function isRequired($field)
    {
        return isset($this->_validationRules[$field]['required']) ? $this->_validationRules[$field]['required'] : false;
    }

    public function getLabel($field)
    {
        return isset($this->_validationRules[$field]['label']) ? $this->_validationRules[$field]['label'] : '';
    }

    public function getFieldError($field)
    {
        return isset($this->_validationRules[$field]['error']) ? $this->_validationRules[$field]['error'] : 'Unable to post the review . ';
    }

    public function getValidationRules($field = null, $area = self::VALIDATION_TYPE_FRONTEND)
    {
        return $this->_validationRules[$field][$area];
    }
}
