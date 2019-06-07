<?php

class Gearup_Activity_Model_Cron
{
    public function checkActivity() {
        Mage::helper('gearup_activity')->checkExpireDate();
    }

}
