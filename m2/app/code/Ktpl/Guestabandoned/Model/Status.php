<?php

namespace Ktpl\Guestabandoned\Model;

class Status  {

    const STATUS_IN_PROGRESS = 1;
    const STATUS_CAPTURED = 2;
    const STATUS_CLOSED = 3;

    static public function getOptionArray() {
        return array(
            self::STATUS_IN_PROGRESS => __('In Progress'),
            self::STATUS_CAPTURED => __('Captured'),
            self::STATUS_CLOSED => __('Close')
        );
    }

}
