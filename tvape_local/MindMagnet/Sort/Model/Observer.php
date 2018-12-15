<?php
/**
 * MindMagnet Products Sort
 * Model Class
 *
 * Copyright (C) 2015-2016 MindMagnet <contact@blugento.com>
 * LICENSE: GNU General Public License for more details <http://opensource.org/licenses/gpl-license.php>
 *
 * @package MindMagnet_Sort
 * @author Simona Trifan <simona.plesuvu@mindmagnetsoftware.com>
 * @link http://www.blugento.com
 */

class MindMagnet_Sort_Model_Observer
{
    public function prepareAttributeForm($observer)
    {
        $event = $observer->getEvent();

        $form = $event->getForm();

        $fieldset = $form->getElement('base_fieldset');

        $fieldset->addField('mmsort_image', 'image', array(
            'name'     => 'mmsort_image',
            'label'    => Mage::helper('catalog')->__('Image'),
            'title'    => Mage::helper('catalog')->__('Image')
        ));
    }
}
