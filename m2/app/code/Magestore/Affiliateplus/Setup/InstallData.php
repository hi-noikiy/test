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
namespace Magestore\Affiliateplus\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var \Magento\Cms\Model\Page
     */
    protected $_pageModel;
    /**
     * schema table
     */
    const SCHEMA_ACCOUNT =       'magestore_affiliateplus_account';
    const SCHEMA_TRANSACTION =   'magestore_affiliateplus_transaction';
    const SCHEMA_PAYMENT =       'magestore_affiliateplus_payment';
    const SCHEMA_PAYMENT_PAYPAL ='magestore_affiliateplus_payment_paypal';
    const SCHEMA_REFERER =       'magestore_affiliateplus_referer';
    const SCHEMA_ACTION =        'magestore_affiliateplus_action';
    const SCHEMA_PAYMENT_VERIFY= 'magestore_affiliateplus_payment_verify';
    const SCHEMA_PAYMENY_HISTORY='magestore_affiliateplus_payment_history';
    const SCHEMA_PAYMENY_OFFLINE='magestore_affiliatepluspayment_offline';
    const SCHEMA_PAYMENY_BANK   ='magestore_affiliatepluspayment_bank';
    const SCHEMA_PAYMENY_MONERBOOKER   ='magestore_affiliatepluspayment_moneybooker';


    /**
     * InstallSchema constructor.
     * @param \Magento\Cms\Model\PageFactory $pageModel
     */
    public function __construct(
        \Magento\Cms\Model\PageFactory $pageModel
    )
    {
        $this->_pageModel = $pageModel;
    }

    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        /**
         * your code here
         */

        /**
         * add sample data for cms page
         */
        $content = "<p>Affiliate programs are common throughout the Internet and offer website owners an additional way to spread the word about their websites. Among others, our program is free to join, easy to sign up and requires no technical knowledge! As our affiliates, you will generate traffic and sales for our website and receive attractive commissions in return.</p><h3>How Does It Work?</h3><p>When you join our affiliate program, you will be supplied with a range of banners and text links to place wherever you like. When a user clicks on one of your links, they will be brought to our website and their activities will be tracked by our affiliate program. Once this user completes a purchase, you earn commission!</p><h3>Real-Time Statistics and Reports!</h3><p>Log in anytime to check your performance with data of sales, traffic generated and your commission balance.</p>";

        $cmsPage = [
            'title' 		=> __('Affiliate'),
            'identifier' 	=> 'affiliate-home',
            'content_heading' =>__('Welcome To Our Affiliate Network!'),
            'content' 		=> $content,
            'is_active' 	=> 1,
            'sort_order' 	=> 0,
            'stores' 		=> [0],
            'page_layout' => 'two_columns_left'
        ];

        $this->_pageModel->create()->setData($cmsPage)->save();
        $installer->getConnection()->resetDdlCache($installer->getTable(self::SCHEMA_TRANSACTION));

        /**
         * check if table exist
         *
         */
        $affiliateplusStatistic = $installer->getTable('magestore_affiliateplusstatistic');

        if ($installer->tableExists($affiliateplusStatistic)) {
            /**
             * add column account_email
             */
            if (!$installer->getConnection()->tableColumnExists(
                $affiliateplusStatistic,
                'account_email')
            ) {
                $installer->getConnection()->addColumn(
                    $affiliateplusStatistic,
                    'account_email',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        255,
                        'nullable' => true,
                        'comment' => 'Account Email'
                    ]
                );
            }

            /**
             * JOIN
             */
            $refererEmail = $installer->getConnection()->select()->reset()
                ->from(
                    [
                        'r' => $installer->getTable(self::SCHEMA_REFERER)],
                    ['referer_id']
                )
                ->joinInner(
                    [
                        'a' => $installer->getTable(self::SCHEMA_ACCOUNT)
                    ],
                    'r.account_id = a.account_id',
                    ['email']
                );
            $select = $installer->getConnection()->select()->reset()
                ->joinInner(
                    array(
                        'e' => new \Zend_Db_Expr("({$refererEmail->__toString()})")
                    ),
                    'e.referer_id = main_table.referer_id', null)
                ->columns(['account_email' => 'email']);

            $updateSql = $select->crossUpdateFromSelect(['main_table' => $affiliateplusStatistic]);
            $installer->getConnection()->query($updateSql);

            /**
             * Convert from old data to new data
             */
            $trafficSelect = $installer->getConnection()->select()->reset()
                ->from(
                    [
                        's' => $affiliateplusStatistic
                    ],
                    []
                )
                ->joinInner(
                    [
                        'r' => $installer->getTable(self::SCHEMA_REFERER)
                    ],
                    's.referer_id = r.referer_id',
                    []
                )->columns(
                    [
                        'account_id'    => 'r.account_id',
                        'account_email' => 's.account_email',
                        'ip_address'    => 's.ip_address',
                        'domain'        => 's.referer',
                        'referer'       => 's.referer',
                        'landing_page'  => 's.url_path',
                        'totals'        => 'COUNT(s.id)',
                        'created_date'  => 'DATE(s.visit_at)',
                        'updated_time'  => 's.visit_at',
                        'store_id'      => 's.store_id'
                    ]
                )->group(
                    [
                        's.referer', 's.url_path',
                        's.ip_address',
                        'DATE(s.visit_at)',
                        's.store_id'
                    ]
                );
            $updateSql = $trafficSelect->insertFromSelect($installer->getTable(self::SCHEMA_ACTION),
                [
                    'account_id',
                    'account_email',
                    'ip_address',
                    'domain',
                    'referer',
                    'landing_page',
                    'totals',
                    'created_date',
                    'updated_time',
                    'store_id'
                ],
                true
            );
            $installer->getConnection()->query($updateSql);

            /**
             * repair unique click data
             */
            $uniqueSelect = $installer->getConnection()->select()->reset()
                ->from(
                    [
                        'u' => $installer->getTable(self::SCHEMA_ACTION)
                    ],
                    ['action_id']
                )
                ->group(
                    [
                        'u.ip_address',
                        'u.account_id',
                        'u.domain'
                    ]
                );
            $select = $installer->getConnection()->select()->reset()
                ->joinInner(
                    [
                        'e' => new \Zend_Db_Expr("({$uniqueSelect->__toString()})")
                    ],
                    'e.action_id = main_table.action_id',
                    null
                )
                ->columns(
                    [
                        'is_unique' => 'ABS(1)'
                    ]
                );
            $updateSql = $select->crossUpdateFromSelect(
                [
                    'main_table' => $installer->getTable(self::SCHEMA_ACTION)
                ]
            );
            $installer->getConnection()->query($updateSql);

        }

        /**
         * transfer old data
         */

        if ($installer->tableExists($installer->getTable(self::SCHEMA_PAYMENT_VERIFY))) {
            /**
             * transfer email paypal verified to verify table
             */
            if ($installer->tableExists($installer->getTable(self::SCHEMA_PAYMENT_PAYPAL))) {
                $paypalSelect = $installer->getConnection()->select()->reset()
                    ->from(
                        [
                            'p' => $installer->getTable(self::SCHEMA_PAYMENT)
                        ], []
                    )
                    ->where('p.status=3')
                    ->joinInner(
                        [
                            'pp' => $installer->getTable(self::SCHEMA_PAYMENT_PAYPAL)
                        ],
                        'p.payment_id = pp.payment_id',
                        []
                    )->columns(
                        [
                            'account_id' => 'p.account_id',
                            'payment_method' => 'p.payment_method',
                            'paypal_email' => 'pp.email',
                            'verified' => 'ABS(1)'
                        ]
                    )->group(
                        [
                            'p.account_id',
                            'p.payment_method',
                            'pp.email'
                        ]
                    );

                $paypalSql = $paypalSelect->insertFromSelect($installer->getTable(self::SCHEMA_PAYMENT_VERIFY),
                    [
                        'account_id',
                        'payment_method',
                        'field',
                        'verified'
                    ],
                    true
                );
                $installer->getConnection()->query($paypalSql);
            }

            /**
             * transfer email moneybooker verified to verify table
             */
            $affiliatepluspaymentMoneybooker = $installer->getTable(self::SCHEMA_PAYMENY_MONERBOOKER);

            if ($installer->tableExists($affiliatepluspaymentMoneybooker)) {
                $moneybookerSelect = $installer->getConnection()->select()->reset()
                    ->from(
                        [
                            'p' => $installer->getTable(self::SCHEMA_PAYMENT)
                        ],
                        []
                    )
                    ->where('p.status=3')
                    ->joinInner(
                        [
                            'mb' => $affiliatepluspaymentMoneybooker
                        ],
                        'p.payment_id = mb.payment_id',
                        []
                    )->columns(
                        [
                            'account_id' => 'p.account_id',
                            'payment_method' => 'p.payment_method',
                            'paypal_email' => 'mb.email',
                            'verified' => 'ABS(1)'
                        ]
                    )->group(
                        [
                            'p.account_id',
                            'p.payment_method',
                            'mb.email'
                        ]
                    );
                $moneybookerSql = $moneybookerSelect->insertFromSelect($installer->getTable(self::SCHEMA_PAYMENT_VERIFY),
                    [
                        'account_id',
                        'payment_method',
                        'field',
                        'verified'
                    ],
                    true
                );
                $installer->getConnection()->query($moneybookerSql);
            }
            /**
             * transfer offline address verified to verify table
             */
            $affiliatepluspaymentOffline = $installer->getTable(self::SCHEMA_PAYMENY_OFFLINE);

            if ($installer->tableExists($affiliatepluspaymentOffline)) {
                $offlineSelect = $installer->getConnection()->select()->reset()
                    ->from(
                        [
                            'p' => $installer->getTable(self::SCHEMA_PAYMENT)
                        ],
                        []
                    )
                    ->where('p.status=3')
                    ->joinInner(
                        [
                            'ol' => $affiliatepluspaymentOffline
                        ],
                        'p.payment_id = ol.payment_id',
                        []
                    )->columns(
                        [
                            'account_id' => 'p.account_id',
                            'payment_method' => 'p.payment_method',
                            'address_id' => 'ol.address_id',
                            'verified' => 'ABS(1)'
                        ]
                    )->group(
                        [
                            'p.account_id',
                            'p.payment_method',
                            'ol.address_id'
                        ]
                    );
                $offlineSql = $offlineSelect->insertFromSelect($installer->getTable(self::SCHEMA_PAYMENT_VERIFY),
                    [
                        'account_id',
                        'payment_method',
                        'field',
                        'verified'
                    ],
                    true
                );
                $installer->getConnection()->query($offlineSql);
            }
            /**
             * transfer bank account verified to verify table
             */
            $affiliatepluspaymentBank=$installer->getTable(self::SCHEMA_PAYMENY_BANK);

            if ($installer->tableExists($affiliatepluspaymentBank)) {
                $bankSelect = $installer->getConnection()->select()->reset()
                    ->from(
                        [
                            'p' => $installer->getTable(self::SCHEMA_PAYMENT)
                        ],
                        []
                    )
                    ->where('p.status=3')
                    ->joinInner(
                        array(
                            'ba' => $affiliatepluspaymentBank
                        ),
                        'p.payment_id = ba.payment_id',
                        []
                    )->columns(
                        [
                            'account_id'    => 'p.account_id',
                            'payment_method'    => 'p.payment_method',
                            'bankaccount_id' => 'ba.bankaccount_id',
                            'verified'	=> 'ABS(1)'
                        ]
                    )->group(
                        [
                            'p.account_id',
                            'p.payment_method',
                            'ba.bankaccount_id'
                        ]
                    );
                $bankSql = $bankSelect->insertFromSelect($installer->getTable(self::SCHEMA_PAYMENT_VERIFY),
                    [
                        'account_id',
                        'payment_method',
                        'field',
                        'verified'
                    ],
                    true
                );
                $installer->getConnection()->query($bankSql);
            }
        }
        /**
         * @specify
         */
        $paymentSelect = $installer->getConnection()->select()->reset()
            ->from(
                [
                    'p' => $installer->getTable(self::SCHEMA_PAYMENT)
                ],
                [
                    'payment_id'    => 'payment_id',
                    'status'        => 'ABS(1)',
                    'created_time'  => 'request_time',
                    'description'   => "LTRIM('Create Withdrawal')"
                ]
            );
        $updateSql = $paymentSelect->insertFromSelect(
            $installer->getTable(self::SCHEMA_PAYMENY_HISTORY),
            [
                'payment_id',
                'status',
                'created_time',
                'description'
            ],
            true
        );
        $installer->getConnection()->query($updateSql);


        $installer->endSetup();
    }
}
