<?php

class TM_ProLabels_Block_Category extends TM_ProLabels_Block_Content_Abstract
{

    public function _construct()
    {
        parent::_construct();
        if (!$this->getTemplate()) {
            $this->setTemplate('tm/prolabels/content/labels.phtml');
        }
        $this->mode = 'category';
    }

}
