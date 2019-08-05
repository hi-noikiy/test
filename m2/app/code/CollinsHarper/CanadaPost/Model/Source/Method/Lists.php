<?php
/**
 * Copyright © 2015 Collins Harper. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CollinsHarper\CanadaPost\Model\Source\Method;

/**
 * Source model for Collins Harper shipping methods
 */
class Lists extends \CollinsHarper\CanadaPost\Model\Source\AbstractSource
{

    /**
     *
     * @var array
     */
    protected  $dataList =  array(
        //canada
        'DOM.EP' => 'Expedited Parcel',
        'DOM.LIB' => 'Library Books',
        'DOM.PC' => 'Priority',
        'DOM.RP' => 'Regular Parcel',
        'DOM.XP' => 'Xpresspost',
        'DOM.XP.CERT' => 'Xpresspost Certified',
        //usa
        'USA.EP' => 'Expedited Parcel USA',
        'USA.PW.ENV' => 'Priority Worldwide Envelope USA',
        'USA.PW.PAK' => 'Priority Worldwide pak USA',
        'USA.PW.PARCEL' => 'Priority Worldwide Parcel USA',
        'USA.SP.AIR' => 'Small Packet USA Air',
        'USA.TP' => 'Tracked Packet - USA',
        'USA.TP.LVM' => 'Tracked Packet ?? USA LVM',
        'USA.XP' => 'Xpresspost USA',
        //international
        'INT.IP.AIR' => 'International Parcel Air',
        'INT.IP.SURF' => 'International Parcel Surface',
        'INT.PW.ENV' => 'Priority Worldwide Envelope Int\'l',
        'INT.PW.PAK' => 'Priority Worldwide pak Int\'l',
        'INT.PW.PARCEL' => 'Priority Worldwide parcel Int\'l',
        'INT.SP.AIR' => 'Small Packet International Air',
        'INT.SP.SURF' => 'Small Packet International Surface',
        'INT.TP' => 'Tracked Packet International',
        'INT.XP' => 'Xpresspost International',

    );

    /**
     * 
     * @return array
     */
    public function getList()
    {
       return $this->dataList;
    }
    /**
     * Returns array to be used in multiselect on back-end
     *
     * @return array
     */
    public function toOptionArray()
    {

        $options = [];

        foreach ($this->dataList as $k => $code) {
            $options[] = ['value' => $k, 'label' => ucwords($code)];
        }

        return $options;
    }
}
