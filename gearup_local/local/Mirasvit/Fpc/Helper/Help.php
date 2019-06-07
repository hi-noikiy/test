<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/extension_fpc
 * @version   1.0.87
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



class Mirasvit_Fpc_Helper_Help extends Mirasvit_MstCore_Helper_Help
{
    protected $_help = array(
        'system' => array(
            'general_enabled' => 'Enables full page cache. You can enable/disable full page cache for each store view.',
            'general_lifetime' => 'Cache lifetime (in seconds). Determines the time after which the page cache will be invalid. A new page cache will be created the next time a visitor visits the page.',
            'general_flush_cache_schedule' => 'Specifies how often cron must clear (flush) cache. Leave empty for disable this feature.<xmp></xmp> <b>Can be a cron expression only.</b> Example:<br/>0 1 * * *<br/>0 0 */3 * *',
            'general_max_cache_size' => 'Maximum full page cache size in megabytes. If the limit is reached, extension will clear cache. If REDIS installed FPC will not use the limit (REDIS will flush cache automatically if not enough RAM).',
            'general_max_cache_number' => 'Maximum number of cache files. If the limit is reached, extension will clear cache. If REDIS installed FPC will not use the limit (REDIS will flush cache automatically if not enough RAM).',
            'general_gzcompress_level' => 'Compress the cache. Use only for filecache. Flush Fpc cache after changing.',
            'general_cache_tags_level' => 'In most situation recommended to use "Minimal set of tags with custom prefix". "Default" - refresh cache if visible product changed. "Minimal set of tags" and "Minimal set of tags with custom prefix" - create minimal set of tags and use observer to flush cache. "Don\'t use tags - don\'t create product and category tags. Flush Fpc cache after changing.',

            'cache_rules_max_depth' => 'Determines the number of layered navigation filters, or parameters, that can be applied in order for a page to be cached.',
            'cache_rules_cacheable_actions' => 'List of cacheable actions.',
            'cache_rules_allowed_pages' => 'List of allowed pages (regular expression). In cache will be only allowed pages, other pages will be ignored by FPC. <xmp></xmp><b>Can be a regular expression only.</b> Example:<br/>/books\/a-tale-of-two-cities.html/<br/>/books/',
            'cache_rules_ignored_pages' => 'List of not allowed for caching pages (regular expression).<xmp></xmp><b>Can be a regular expression only.</b> Example:<br/>/books\/a-tale-of-two-cities.html/<br/>/books/',
            'cache_rules_user_agent_segmentation' => 'Determines the cache by user agent  (regular expression)',
            'cache_rules_ignored_url_params' => 'Ignore GET parameters when creating a cache. Parameters like "price", "limit", "order", "p" (except p=1), "dir" etc. can\'t be applied, because FPC will return incorrect content.',
            'cache_rules_mobile_detect' => 'Determines the cache by device type',
            'cache_rules_is_crawl_mobile_urls' => 'If enabled will crawl mobile urls',

            'extended_settings_clean_old_cache' => 'Clear old cache (expired cache) by cron. Sometimes if Magento cron job has not enough permissions for this operation, it will flush all cache. If you see such situation, you need to run Magento cron job from user with more permissions or set \'No\' for this configuration.',
            'extended_settings_update_headers' => 'If disabled add only FPC cache Id in headers and don\'t update other headers from cache. For most part of stores should be enabled.',
            'extended_settings_update_cart_method' => 'Update cart method. In most situation should be "Default". If you have any problem with cart set "Whole page" and refresh all cache.',
            'extended_settings_home_page_add_all_tags' => 'If enabled will add maximum tags for home page. Sometimes it is need for blocks update.',
            'extended_settings_use_session_for_specified_block' => 'If disabled ignore in_session parameter in cache.xml file and don\'t use session for such blocks.',
            'extended_settings_use_same_cache_for_all_groups' => 'If enabled create the same cache for all customer groups. If you set "Use the same cache for all groups" "Crawler for logged user" have to be disabled. <xmp></xmp> <b>Can be enabled only if you use the same price and other data for all customer groups.</b>',
            'extended_settings_update_stock_method' => 'In most situation should be "Default". Default - update stock using event "cataloginventory_stock_item_save_commit_after". Default (check stock in frontend) - check stock in frontend. Update during reindex (Cache Level should be Default) - update stock after product save and reindex.',
            'extended_settings_delete_ignored_url_params' => 'Delete ignored url params for layered navigation. In most situation should be "No"',
            'extended_settings_html_name_pattern' => 'Use different patterns for block excluding. Recommended use "Default".',

            'debug_info' => 'Show green block with FPC info in frontend.',
            'debug_flush_cache_button' => 'Add button in green block in frontend. "Flush current page cache" - flush cache only for current page. "Flush depending tags cache" - flush cache by tags for current page and depending pages.',
            'debug_hints' => 'Show debug hints.',
            'debug_log' => 'Create log file ( fpc_debug.log ) with FPC actions.',
            'debug_flush_log' => 'Create log file ( fpc_debug_flush_cache.log ) with FPC flush cache actions.',
            'debug_allowed_ip' => 'Comma separated IP addresses',
        ),
    );
}
