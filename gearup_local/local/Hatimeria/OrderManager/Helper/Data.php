<?php

/**
 * Helper
 */

class Hatimeria_OrderManager_Helper_Data extends Mage_Core_Helper_Abstract
{
    const FIRST_SHIPPING = 1;
    const FIRST_PERIOD_WITH_EVEN_ID = 2;
    const TWO_PERIODS_ID_BACK = 2;
    const PERIOD_NOT_FOUND = '';

    const SUNDAY    = 0;
    const MONDAY    = 1;
    const TUESDAY   = 2;
    const WEDNESDAY = 3;
    const THURSDAY  = 4;
    const FRIDAY    = 5;
    const SATURDAY  = 6;

    protected $timeCreatedAt;
    protected $dayCreatedAt;

    public function getConfig($name, $group = 'general')
    {
        return Mage::getStoreConfig(sprintf('hordermanager/%s/%s', $group, $name));
    }

    /**
     * @param null $date
     * @return DateTime|string
     */
    public function getDayOfWeekNumber($date = null)
    {
        $orderDayOfWeek = new DateTime($date);
        $orderDayOfWeek = $orderDayOfWeek->format('w');

        return $orderDayOfWeek;
    }

    /**
     * @param null $time
     * @return DateTime|mixed|string
     */
    public function getCurrentTime($time = null)
    {
        $orderTime = $this->getConfig('time', 'testDate');

        if (!$orderTime) {
            $orderTime = new DateTime($time);
            $orderTime->setTimezone($this->getTimeZone());
            $orderTime = $orderTime->format('H:i:s');
        }

        return $orderTime;
    }

    /**
     * @param null $date
     * @return DateTime|mixed|string
     */
    public function getCurrentDate($date = null)
    {
        $orderDay = $this->getConfig('date', 'testDate');

        if (!$orderDay) {
            $orderDay = new DateTime($date);
            $orderDay->setTimezone($this->getTimeZone());
            $orderDay = $orderDay->format('Y-m-d');
        }

        return $orderDay;
    }

    /**
     * @return DateTimeZone
     */
    public function getTimeZone()
    {
        $timeZone = Mage::getStoreConfig('general/locale/timezone');
        return new DateTimeZone($timeZone);
    }

    public function getEstimatedShippingDate($periodEdges)
    {
        $nameOfShippingDay = $this->getNameOfDay($periodEdges['shipping_day']);

        $shippingDay = new DateTime($periodEdges['date_to']);

        $shippingDay->modify('next ' . $nameOfShippingDay);
        $shippingDay = $shippingDay->format('Y-m-d');

        return $shippingDay;
    }

    /**
     * @param null $productIsSpecial
     * @return DateTime|string
     */
    public function getDayOfShipping($orderCreatedAt, $productIsSpecial = null)
    {
        $explodedCreatedAt = explode(' ', $orderCreatedAt);
        
        /* Remove this block -  and uncomment 2 line below to back */
        if(!empty($orderCreatedAt)) {
            $this->timeCreatedAt = $explodedCreatedAt[1];
            $this->dayCreatedAt = $explodedCreatedAt[0];
        }
        /* Remove this block -  and uncomment 2 line below to back */
        
        // $this->timeCreatedAt = $explodedCreatedAt[1];
        // $this->dayCreatedAt = $explodedCreatedAt[0];

        $currentDayNumber = $this->getDayOfWeekNumber($this->dayCreatedAt);
        $currentTime = $this->getCurrentTime($this->timeCreatedAt);
        $edgeDayOfPeriodNumber = $this->getConfig('day', 'general');

        $startHourOfFirstPeriod = $this->getConfig('beginTime', 'firstPeriod');
        $startDayOfFirstPeriodNumber = $this->getConfig('beginDay', 'firstPeriod');
        $endHourOfFirstPeriod = $this->getConfig('endTime', 'firstPeriod');
        $endDayOfFirstPeriodNumber = $this->getConfig('endDay', 'firstPeriod');

        $startHourOfSecondPeriod = $this->getConfig('beginTime', 'secondPeriod');
        $startDayOfSecondPeriodNumber = $this->getConfig('beginDay', 'secondPeriod');
        $endHourOfSecondPeriod = $this->getConfig('endTime', 'secondPeriod');
        $endDayOfSecondPeriodNumber = $this->getConfig('endDay', 'secondPeriod');

        $result = '';

        if (1 == $productIsSpecial) {
            $result = $this->getSameDay();
        } elseif ($currentDayNumber == $edgeDayOfPeriodNumber) {
            if ($currentTime < $endHourOfFirstPeriod) {
                $result = $this->checkDayOfShipping(self::FIRST_SHIPPING);
            } else {
                $result = $this->checkDayOfShipping();
            }
        } elseif ($currentDayNumber == $startDayOfFirstPeriodNumber || $currentDayNumber == $endDayOfSecondPeriodNumber) {
            if ($currentTime < $startHourOfFirstPeriod || $currentTime < $endHourOfSecondPeriod) {
                $result = $this->checkDayOfShipping();
            } else {
                $result = $this->checkDayOfShipping(self::FIRST_SHIPPING);
            }
        } elseif ($currentDayNumber == $startDayOfSecondPeriodNumber || $currentDayNumber == $endDayOfFirstPeriodNumber) {
            if ($currentTime < $startHourOfSecondPeriod || $currentTime < $endHourOfFirstPeriod) {
                $result = $this->checkDayOfShipping(self::FIRST_SHIPPING);
            } else {
                $result = $this->checkDayOfShipping();
            }
        } elseif ($currentDayNumber < $endDayOfFirstPeriodNumber && $currentDayNumber > $startDayOfFirstPeriodNumber) {
            $result = $this->checkDayOfShipping(self::FIRST_SHIPPING);
        } elseif ($currentDayNumber < $endDayOfSecondPeriodNumber && $currentDayNumber > $startDayOfSecondPeriodNumber) {
            $result = $this->checkDayOfShipping();
        } elseif ($currentDayNumber < $edgeDayOfPeriodNumber) {
            $periodType = $this->getPeriodEdgesFromModelByDate($this->dayCreatedAt);
            if ($periodType == 2) {
                $result = $this->checkDayOfShipping();
            } elseif ($periodType == 1) {
                $result = $this->checkDayOfShipping(self::FIRST_SHIPPING);
            } elseif ($periodType == 3) {
                $result = self::PERIOD_NOT_FOUND;
            }
        }  else {
            $result = $this->checkDayOfShipping();
        }

        return $result;
    }

    /**
     * @param null $dayFlag
     * @return DateTime|string
     */
    public function checkDayOfShipping($dayFlag = null)
    {
        $currentDay = $this->dayCreatedAt;

        if (isset($dayFlag)) {
            $numberOfShippingDay = $this->getConfig('day', 'firstShipping');
        } else {
            $numberOfShippingDay = $this->getConfig('day', 'secondShipping');
        }

        $nameOfShippingDay = $this->getNameOfDay($numberOfShippingDay);

        $shippingDay = new DateTime($currentDay);

        $shippingDay->modify('next ' . $nameOfShippingDay);
        $shippingDay = $shippingDay->format('l d.m.Y');

        return $shippingDay;
    }

    /**
     * @return string
     */
    public function getSameDay()
    {
        return 'Same Day';
    }

    /**
     * @param $numberOfDay
     * @return mixed
     */
    public function getNameOfDay($numberOfDay)
    {
        $daysOfWeek = array(
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday'
        );

        return $daysOfWeek[$numberOfDay];
    }

    /**
     * prepare string with hour to set time of begin and end of period
     * @param $hour
     * @return array
     */
    public function explodeHour($hour)
    {
        $array = explode(':', $hour);

        return array('hours' => intval($array[0]), 'minutes' => intval($array[1]), 'seconds' => intval($array[2]));
    }

    /**
     * @param $orderDay
     * @param $orderDayOfWeekNumber
     * @param $startDayOfPeriodNumber
     * @param $endDayOfPeriodNumber
     * @return array
     */
    public function modifyDateEdges($orderDay, $orderDayOfWeekNumber, $startDayOfPeriodNumber, $endDayOfPeriodNumber)
    {
        $startDay = $this->getNameOfEdgeDayOfPeriod($startDayOfPeriodNumber);
        $endDay = $this->getNameOfEdgeDayOfPeriod($endDayOfPeriodNumber);

        if ($orderDayOfWeekNumber == $startDayOfPeriodNumber) {
            $dateFrom = new DateTime($orderDay);
            $dateTo = new DateTime($orderDay);
            $dateTo->modify('next ' . $endDay);
        } elseif ($orderDayOfWeekNumber == $endDayOfPeriodNumber) {
            $dateTo = new DateTime($orderDay);
            $dateFrom = new DateTime($orderDay);
            $dateFrom->modify('last ' . $startDay);
        } else {
            $orderDay = new DateTime($orderDay);
            $dateFrom = clone($orderDay);
            $dateFrom->modify('last ' . $startDay);

            $dateTo = clone($orderDay);
            $dateTo->modify('next ' . $endDay);
        }

        return array('date_from' => $dateFrom, 'date_to' => $dateTo);
    }

    /**
     * @param $oldDate
     * @param $newDayOfPeriodNumber
     * @param $periodId
     * @param null $dateFrom
     * @internal param $dayOfNewDateNumber
     * @internal param $oldDateFrom
     * @return string
     */
    public function modifyDate($oldDate, $newDayOfPeriodNumber, $periodId, $dateFrom = null)
    {
        $result = '';
        $oldDateNumber = $this->getDayOfWeekNumber($oldDate);
        $newDateDayName = $this->getNameOfEdgeDayOfPeriod($newDayOfPeriodNumber);

        if ($periodId > self::FIRST_PERIOD_WITH_EVEN_ID) {
            $period = Mage::getModel('hordermanager/period')->load($periodId - self::TWO_PERIODS_ID_BACK);
            if ($dateFrom) {
                $oldDate = $period->getDateFrom();
                $oldDate = new DateTime($oldDate);
                $newDate = $oldDate->modify('+ 7 days');
                $result = $newDate->format('Y-m-d');
            } else {
                $oldDate = $period->getDateTo();
                $oldDate = new DateTime($oldDate);
                $newDate = $oldDate->modify('+ 7 days');
                $result = $newDate->format('Y-m-d');
            }
        } else {
            if ($oldDateNumber == $newDayOfPeriodNumber) {
                $result = $oldDate;
            } elseif ($oldDateNumber < $newDayOfPeriodNumber) {
                $oldDate = new DateTime($oldDate);
                $newDate = $oldDate->modify($newDateDayName);
                $result = $newDate->format('Y-m-d');
            } else {
                $oldDate = new DateTime($oldDate);
                $newDate = $oldDate->modify('last ' . $newDateDayName);
                $result = $newDate->format('Y-m-d');
            }
        }

        return $result;
    }

    /**
     * @param $numberOfEdgeDay
     * @return mixed
     */
    public function getNameOfEdgeDayOfPeriod($numberOfEdgeDay)
    {
        $edgeDay = '';
        switch($numberOfEdgeDay) {
            case self::SUNDAY:
                $edgeDay = $this->getNameOfDay($numberOfEdgeDay);
                break;
            case self::MONDAY:
                $edgeDay = $this->getNameOfDay($numberOfEdgeDay);
                break;
            case self::TUESDAY:
                $edgeDay = $this->getNameOfDay($numberOfEdgeDay);
                break;
            case self::WEDNESDAY:
                $edgeDay = $this->getNameOfDay($numberOfEdgeDay);
                break;
            case self::THURSDAY:
                $edgeDay = $this->getNameOfDay($numberOfEdgeDay);
                break;
            case self::FRIDAY:
                $edgeDay = $this->getNameOfDay($numberOfEdgeDay);
                break;
            case self::SATURDAY:
                $edgeDay = $this->getNameOfDay($numberOfEdgeDay);
                break;
        }

        return $edgeDay;
    }

    /**
     * return edges of period by given id
     *
     * @param $currentDayDate
     * @return int|string
     */
    public function getPeriodEdgesFromModelByDate()
    {
        $periodsCollection = Mage::getModel('hordermanager/period')->getCollection();
        $periodId = 0;
        $result = '';

        foreach ($periodsCollection as $period) {
            $dateFrom = $period->getDateFrom();
            $dateTo = $period->getDateTo();

            if ($this->dayCreatedAt < $dateTo && $this->dayCreatedAt > $dateFrom) {
                $periodId = $period->getPeriodId();
            } elseif ($this->dayCreatedAt > $dateTo) {
                $result = 3;
            }
        }

        if (0 != $periodId) {
            if ($periodId % 2 == 0) {
                $result = 2;
            } else {
                $result = 1;
            }
        }

        return $result;
    }
}