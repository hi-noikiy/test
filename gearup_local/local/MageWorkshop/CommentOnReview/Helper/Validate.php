<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <info@mage-review.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_CommentOnReview
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (https://mage-review.com/)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <info@mage-review.com>
 */

/**
 * Class MageWorkshop_CommentOnReview_Helper_Validate
 */
class MageWorkshop_CommentOnReview_Helper_Validate extends Mage_Core_Helper_Abstract
{
    const VALIDATION_TYPE_FRONTEND = 'frontend';
    const VALIDATION_TYPE_BACKEND  = 'backend';
    const VALIDATION_RULES_PATH  = 'detailedreview/validation_options/';

    /** @var array _validationRules */
    protected $_validationRules = array ();
    protected $_validationRestrictions = array();
    
    public function __construct()
    {
        $minDetailLength = Mage::getStoreConfig(MageWorkshop_CommentOnReview_Helper_Data::COMMENT_XML_PATH_MIN_SYMBOLS);
        $maxDetailLength = Mage::getStoreConfig(MageWorkshop_CommentOnReview_Helper_Data::COMMENT_XML_PATH_MAX_SYMBOLS);

        $this->_validationRestrictions = array(
            "nickname_min" => Mage::getStoreConfig(self::VALIDATION_RULES_PATH . 'nickname_min'),
            "nickname_max" => Mage::getStoreConfig(self::VALIDATION_RULES_PATH . 'nickname_max'),
            "title_min" => Mage::getStoreConfig(self::VALIDATION_RULES_PATH . 'title_min'),
            "title_max" => Mage::getStoreConfig(self::VALIDATION_RULES_PATH . 'title_max')
        );

        $this->_validationRules = array (
            'nickname' => array(
                self::VALIDATION_TYPE_FRONTEND => 'required-entry validate-length minimum-length-' . $this->_validationRestrictions['nickname_min'] . ' maximum-length-' . $this->_validationRestrictions['nickname_max'],
                self::VALIDATION_TYPE_BACKEND  => array(
                    'NotEmpty'     => true,
                    'StringLength' => array('min' => $this->_validationRestrictions['nickname_min'], 'max' => $this->_validationRestrictions['nickname_max'], 'encoding' => 'UTF-8')
                ),
                'required' => true,
                'label' => 'Nickname',
                'error' => array(
                    'NotEmpty'     => $this->__('Nickname can not be empty'),
                    'StringLength' => $this->__('Nickname must be min %d and max %d characters', $this->_validationRestrictions['nickname_min'],  $this->_validationRestrictions['nickname_max'])
                )
            ),
            'detail'   => array(
                self::VALIDATION_TYPE_FRONTEND => 'required-entry validate-length minimum-length-' . $minDetailLength
                    . ' maximum-length-' . $maxDetailLength,
                self::VALIDATION_TYPE_BACKEND  => array(
                    'NotEmpty'     => true,
                    'StringLength' => array('min' => $minDetailLength, 'max' => $maxDetailLength, 'encoding' => 'UTF-8')
                ),
                'required' => true,
                'label' =>'Overall Review',
                'error' => array(
                    'NotEmpty'     => $this->__('Reply can not be empty'),
                    'StringLength' => $this->__(
                        'Reply must be min %s and max %s characters', $minDetailLength, $maxDetailLength
                    )
                )
            ),


        );
    }

    /**
     * Validate new reply data
     *
     * @param $newData
     * @return array|bool
     * @throws Exception
     * @throws Zend_Validate_Exception
     */
    public function validate($newData)
    {
        $errors = array();

        /** @var Mage_Customer_Helper_Data $helper */
        $helper = Mage::helper('customer');
        
        $newData = $this->_cutAuthorNameFromReplyDetail($newData);

        try {
            foreach ($this->getFieldsToValidate() as $field) {
                $rules = $this->getValidationRules($field, $this::VALIDATION_TYPE_BACKEND);
                $data = $newData[$field];

                if (empty($data) && !$this->isRequired($field)) {
                    continue;
                }

                foreach ($rules as $key => $value) {
                    $isValid = is_array($value)
                        ? Zend_Validate::is($data, $key, $value)
                        : Zend_Validate::is($data, $key);

                    if (!$isValid) {
                        $errors['messages'][] = $helper->__($this->getFieldError($field, $key));
                    }
                }
            }
        } catch (Exception $e) {
            Mage::logException($e);
            $errors['messages'][] = $this->__('An error occurred while saving your reply.');
        }

        return empty($errors) ? true : $errors;
    }

    /**
     * Get fields to validate
     *
     * @return array
     */
    public function getFieldsToValidate()
    {
        return array_keys($this->_validationRules);
    }

    /**
     * Check if field is required
     *
     * @param $field
     * @return bool
     */
    public function isRequired($field)
    {
        return isset($this->_validationRules[$field]['required']) ? $this->_validationRules[$field]['required'] : false;
    }

    /**
     * Get error by field
     *
     * @param $field string
     * @param $rule string
     * @return string
     */
    public function getFieldError($field, $rule)
    {
        return isset($this->_validationRules[$field]['error'][$rule])
            ? $this->_validationRules[$field]['error'][$rule]
            : $this->__('Unable to post the review.');
    }

    /**
     * Get validate rule
     *
     * @param null|string $field
     * @param string $area
     * @return mixed
     */
    public function getValidationRules($field = null, $area = self::VALIDATION_TYPE_FRONTEND)
    {
        return $this->_validationRules[$field][$area];
    }

    /**
     * Cut author name from reply detail
     * 
     * @param array $newData
     * @return array $newData
     */
    protected function _cutAuthorNameFromReplyDetail($newData)
    {
        $authorName = Mage::app()->getRequest()->getParam('authorName');
        
        $replyDetail = $newData['detail'];

        $pos = strpos($replyDetail, $authorName);
        
        $newData['detail'] = $pos !== false ? substr_replace($replyDetail, '', $pos, strlen($authorName)) : $replyDetail;
        
        return $newData;
    }
}