<?php
/**
 * Copyright © 2016 CollinsHarper. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace CollinsHarper\Moneris\Controller\Mycards;

use CollinsHarper\Moneris\Controller\AbstractMycards;

class Create extends AbstractMycards
{
    protected function _execute()
    {
        $this->_forward('edit');
    }
}
