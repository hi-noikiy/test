<?php
$installer = $this;
$installer->startSetup();

$installer->setCustomerAttributes(
    array(
        'oye_socialauth_gid' => array(
            'type' => 'text',
            'visible' => false,
            'required' => false,
            'user_defined' => false                
        ),
        'oye_socialauth_gtoken' => array(
            'type' => 'text',
            'visible' => false,
            'required' => false,
            'user_defined' => false                
        ),
        'oye_socialauth_fid' => array(
            'type' => 'text',
            'visible' => false,
            'required' => false,
            'user_defined' => false                
        ),
        'oye_socialauth_ftoken' => array(
            'type' => 'text',
            'visible' => false,
            'required' => false,
            'user_defined' => false                
        ),
        'oye_socialauth_tid' => array(
            'type' => 'text',
            'visible' => false,
            'required' => false,
            'user_defined' => false                
        ),
        'oye_socialauth_ttoken' => array(
            'type' => 'text',
            'visible' => false,
            'required' => false,
            'user_defined' => false                
        )
    )
);

$installer->installCustomerAttributes();

$installer->endSetup();