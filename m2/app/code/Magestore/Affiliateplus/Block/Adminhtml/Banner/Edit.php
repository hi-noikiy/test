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
namespace Magestore\Affiliateplus\Block\Adminhtml\Banner;

/**
 * Grid Grid
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_objectId = 'banner_id';
        $this->_blockGroup = 'Magestore_Affiliateplus';
        $this->_controller = 'adminhtml_banner';

        parent::_construct();

        $this->buttonList->update('save', 'label', __('Save banner'));
        $this->buttonList->update('delete', 'label', __('Delete'));

        $this->buttonList->add(
            'saveandcontinue',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form']],
                ],
            ],
            -100
        );

        $this->buttonList->add(
            'new-button',
            [
                'label' => __('Save and New'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => ['event' => 'saveAndNew', 'target' => '#edit_form'],
                    ],
                ],
            ],
            10
        );

        $this->_formScripts[] = '   
            function toggleEditor() {
                if (tinyMCE.getInstanceById(\'block_content\') == null) {
                    tinyMCE.execCommand(\'mceAddControl\', false, \'block_content\');
                } else {
                    tinyMCE.execCommand(\'mceRemoveControl\', false, \'block_content\');
                }
            }

          
                     
                        
                    require([
                            "jquery",
                            "underscore",
                            "mage/mage",
                            "mage/backend/tabs",
                            "domReady!"
                        ], function($) {
                       
                            var $form = $(\'#edit_form\');
                            $form.mage(\'form\', {
                                handlersData: {
                                    save: {},
                                    saveAndNew: {
                                        action: {
                                            args: {back: \'new\'}
                                        }
                                    },
                                }
                            });

                        });
                    
            function showFileField() {
                require([\'jquery\'], function($){
					var file = $(\'#banner_source_file\').parent().parent();
                    var width = $(\'#banner_width\').parent().parent();
                    var height = $(\'#banner_height\').parent().parent();
                    var view = $(\'#banner_banner_view\');

                    if($(\'#banner_type_id\').val() == 1 || $(\'#banner_type_id\').val() == 2){
                        $(\'#banner_source_file\').addClass(\'required-entry\');
                        $(\'#banner_width\').addClass(\'required-entry\');
                        $(\'#banner_height\').addClass(\'required-entry\');

                        if(view != null || view != undefined){
                            view.parent().parent().show();
                            $(\'#banner_source_file\').removeClass(\'required-entry\');
                        }

                        file.show();
                        width.show();
                        height.show();
                    }else{
                        if(view != null || view != undefined){
                            view.parent().parent().hide();
                        }

                        $(\'#banner_source_file\').removeClass(\'required-entry\');
                        $(\'#banner_width\').removeClass(\'required-entry\');
                        $(\'#banner_height\').removeClass(\'required-entry\');

                        file.hide();
                        width.hide();
                        height.hide();
                    }
				});
            }
            showFileField();
        ';
    }
}
