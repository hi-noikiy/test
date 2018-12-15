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
 * @package   mirasvit/extension_helpdesk
 * @version   1.5.4
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



class Mirasvit_Helpdesk_Model_Config
{
    const NOTIFICATION_TYPE_NEW_TICKET = 'new_ticket';
    const NOTIFICATION_TYPE_NEW_MESSAGE = 'new_message';
    const NOTIFICATION_TYPE_NEW_ASSIGN = 'reassign';

    const FOLLOWUPPERIOD_MINUTES = 'minutes';
    const FOLLOWUPPERIOD_HOURS = 'hours';
    const FOLLOWUPPERIOD_DAYS = 'days';
    const FOLLOWUPPERIOD_WEEKS = 'weeks';
    const FOLLOWUPPERIOD_MONTHS = 'months';
    const FOLLOWUPPERIOD_CUSTOM = 'custom';
    const PROTOCOL_POP3 = 'pop3';
    const PROTOCOL_IMAP = 'imap';
    const ENCRYPTION_NONE = 'none';
    const ENCRYPTION_SSL = 'ssl';
    const ENCRYPTION_TLS = 'tls';
    const ENCRYPTION_TLSNOVALID = 'tls/novalidate-cert';
    const SCOPE_HEADERS = 'headers';
    const SCOPE_SUBJECT = 'subject';
    const SCOPE_BODY = 'body';
    const FIELD_TYPE_TEXT = 'text';
    const FIELD_TYPE_TEXTAREA = 'textarea';
    const FIELD_TYPE_DATE = 'date';
    const FIELD_TYPE_CHECKBOX = 'checkbox';
    const FIELD_TYPE_SELECT = 'select';
    const RATE_3 = 3;
    const RATE_2 = 2;
    const RATE_1 = 1;
    const RULE_EVENT_NEW_TICKET = 'new_ticket';
    const RULE_EVENT_NEW_CUSTOMER_REPLY = 'new_customer_reply';
    const RULE_EVENT_NEW_STAFF_REPLY = 'new_staff_reply';
    const RULE_EVENT_NEW_THIRD_REPLY = 'new_third_reply';
    const RULE_EVENT_TICKET_ASSIGNED = 'ticket_assigned';
    const RULE_EVENT_TICKET_UPDATED = 'ticket_updated';
    const RULE_EVENT_CRON_EVERY_HOUR = 'cron_every_hour';
    const IS_ARCHIVE_TO_ARCHIVE = 1;
    const IS_ARCHIVE_FROM_ARCHIVE = 2;
    const TICKET_GRID_COLUMNS_CODE = 'code';
    const TICKET_GRID_COLUMNS_NAME = 'name';
    const TICKET_GRID_COLUMNS_CUSTOMER_NAME = 'customer_name';
    const TICKET_GRID_COLUMNS_LAST_REPLY_NAME = 'last_reply_name';
    const TICKET_GRID_COLUMNS_USER_ID = 'user_id';
    const TICKET_GRID_COLUMNS_DEPARTMENT_ID = 'department_id';
    const TICKET_GRID_COLUMNS_STORE_ID = 'store_id';
    const TICKET_GRID_COLUMNS_STATUS_ID = 'status_id';
    const TICKET_GRID_COLUMNS_PRIORITY_ID = 'priority_id';
    const TICKET_GRID_COLUMNS_REPLY_CNT = 'reply_cnt';
    const TICKET_GRID_COLUMNS_CREATED_AT = 'created_at';
    const TICKET_GRID_COLUMNS_UPDATED_AT = 'updated_at';
    const TICKET_GRID_COLUMNS_LAST_REPLY_AT = 'last_reply_at';
    const TICKET_GRID_COLUMNS_LAST_ACTIVITY = 'last_activity';
    const TICKET_GRID_COLUMNS_ACTION = 'action';
    const TICKET_GRID_COLUMNS_LAST_MESSAGE = 'last_message';
    const TICKET_GRID_COLUMNS_EMAIL = 'customer_email';
    const TICKET_GRID_COLUMNS_ORDER_NUMBER = 'order_number';
    const TICKET_GRID_COLUMNS_TAGS = 'tags';
    const SIGN_TICKET_BY_DEPARTMENT = 'department';
    const SIGN_TICKET_BY_USER = 'user';
    const ACCEPT_FOREIGN_TICKETS_DISABLE = 'disable';
    const ACCEPT_FOREIGN_TICKETS_AW = 'aw';
    const ACCEPT_FOREIGN_TICKETS_MW = 'mw';
    const ATTACHMENT_STORAGE_FS = 'fs';
    const ATTACHMENT_STORAGE_DB = 'db';

    const POSITION_LEFT = 'left';
    const POSITION_RIGHT = 'right';
    const STATUS_OPEN = 'open';
    const STATUS_CLOSED = 'closed';
    const FORMAT_PLAIN = 'TEXT/PLAIN';
    const FORMAT_HTML = 'TEXT/HTML';

    const CHANNEL_FEEDBACK_TAB = 'feedback_tab';
    const CHANNEL_CONTACT_FORM = 'contact_form';
    const CHANNEL_CUSTOMER_ACCOUNT = 'customer_account';
    const CHANNEL_EMAIL = 'email';
    const CHANNEL_BACKEND = 'backend';

    const MESSAGE_PUBLIC = 'public';
    const MESSAGE_INTERNAL = 'internal';
    const MESSAGE_PUBLIC_THIRD = 'public_third';
    const MESSAGE_INTERNAL_THIRD = 'internal_third';
    const MESSAGE_REMOVED = 'removed';
    const MESSAGE_RESTORED = 'restored';
    const MESSAGE_EDIT = 'edit';

    const CUSTOMER = 'customer';
    const USER = 'user';
    const THIRD = 'third';
    const RULE = 'rule';

    public function getDefaultPriority($store = null)
    {
        return Mage::getStoreConfig('helpdesk/general/default_priority', $store);
    }

    public function getDefaultStatus($store = null)
    {
        return Mage::getStoreConfig('helpdesk/general/default_status', $store);
    }

    public function getTicketGridColumns($store = null)
    {
        $value = Mage::getStoreConfig('helpdesk/general/ticket_grid_columns', $store);

        return explode(',', $value);
    }

    public function getGeneralSignTicketBy($store = null)
    {
        return Mage::getStoreConfig('helpdesk/general/sign_ticket_by', $store);
    }

    public function getGeneralArchivedStatusList($store = null)
    {
        $value = Mage::getStoreConfig('helpdesk/general/archived_status_list', $store);

        return explode(',', $value);
    }

    public function getGeneralContactUsIsActive($store = null)
    {
        return Mage::getStoreConfig('helpdesk/general/contact_us_is_active', $store);
    }

    public function getGeneralBccEmail($store = null)
    {
        $cc = Mage::getStoreConfig('helpdesk/general/bcc_email', $store);
        if ($cc) {
            $cc = explode(',', $cc);
            $cc = array_map('trim', $cc);

            return $cc;
        }

        return array();
    }

    public function getGeneralIsWysiwyg($store = null)
    {
        return Mage::getStoreConfig('helpdesk/general/is_wysiwyg', $store);
    }

    public function getGeneralIsDefaultCron($store = null)
    {
        return Mage::getStoreConfig('helpdesk/general/is_default_cron', $store);
    }

    public function getGeneralAcceptForeignTickets($store = null)
    {
        return Mage::getStoreConfig('helpdesk/general/accept_foreign_tickets', $store);
    }

    public function getGeneralAttachmentStorage($store = null)
    {
        return Mage::getStoreConfig('helpdesk/general/attachment_storage', $store);
    }

    public function getGeneralTicketAutosaveIsEnabled($store = null)
    {
        return (bool) (Mage::getStoreConfig('helpdesk/general/tickets_autosave_period', $store) > 0);
    }

    public function getGeneralTicketAutosavePeriod($store = null)
    {
        return (int) Mage::getStoreConfig('helpdesk/general/tickets_autosave_period', $store);
    }

    public function getContactFormIsActive($store = null)
    {
        return Mage::getStoreConfig('helpdesk/contact_form/is_active', $store);
    }

    public function getContactFormDefaultDepartment($store = null)
    {
        return Mage::getStoreConfig('helpdesk/contact_form/default_department', $store);
    }

    public function getColor($store = null)
    {
        return Mage::getStoreConfig('helpdesk/contact_form/color', $store);
    }

    public function getTitle($store = null)
    {
        return Mage::getStoreConfig('helpdesk/contact_form/title', $store);
    }

    public function getPosition($store = null)
    {
        return Mage::getStoreConfig('helpdesk/contact_form/position', $store);
    }

    public function getFormTitle($store = null)
    {
        return Mage::getStoreConfig('helpdesk/contact_form/form_title', $store);
    }

    public function getSubjectTitle($store = null)
    {
        return Mage::getStoreConfig('helpdesk/contact_form/subject_title', $store);
    }

    public function getSubjectPlaceholder($store = null)
    {
        return Mage::getStoreConfig('helpdesk/contact_form/subject_placeholder', $store);
    }

    public function getDescriptionTitle($store = null)
    {
        return Mage::getStoreConfig('helpdesk/contact_form/description_title', $store);
    }

    public function getDescriptionPlaceholder($store = null)
    {
        return Mage::getStoreConfig('helpdesk/contact_form/description_placeholder', $store);
    }

    public function getContactFormIsActiveAttachment($store = null)
    {
        return Mage::getStoreConfig('helpdesk/contact_form/is_active_attachment', $store);
    }

    public function getContactFormIsAllowPriority($store = null)
    {
        return Mage::getStoreConfig('helpdesk/contact_form/is_allow_priority', $store);
    }

    public function getContactFormIsAllowDepartment($store = null)
    {
        return Mage::getStoreConfig('helpdesk/contact_form/is_allow_department', $store);
    }

    public function getContactFormIsActiveKb($store = null)
    {
        return Mage::getStoreConfig('helpdesk/contact_form/is_active_kb', $store);
    }

    public function getNotificationIsShowCode($store = null)
    {
        return Mage::getStoreConfig('helpdesk/notification/is_show_code', $store);
    }

    public function getNotificationHistoryRecordsNumber($store = null)
    {
        return Mage::getStoreConfig('helpdesk/notification/history_records_number', $store);
    }

    public function getNotificationNewTicketTemplate($store = null)
    {
        return Mage::getStoreConfig('helpdesk/notification/new_ticket_template', $store);
    }

    public function getNotificationStaffNewTicketTemplate($store = null)
    {
        return Mage::getStoreConfig('helpdesk/notification/staff_new_ticket_template', $store);
    }

    public function getNotificationNewMessageTemplate($store = null)
    {
        return Mage::getStoreConfig('helpdesk/notification/new_message_template', $store);
    }

    public function getNotificationStaffNewMessageTemplate($store = null)
    {
        return Mage::getStoreConfig('helpdesk/notification/staff_new_message_template', $store);
    }

    public function getNotificationThirdNewMessageTemplate($store = null)
    {
        return Mage::getStoreConfig('helpdesk/notification/third_new_message_template', $store);
    }

    public function getNotificationReminderTemplate($store = null)
    {
        return Mage::getStoreConfig('helpdesk/notification/reminder_template', $store);
    }

    public function getNotificationRuleTemplate($store = null)
    {
        return Mage::getStoreConfig('helpdesk/notification/rule_template', $store);
    }

    public function getNotificationStaffNewSatisfactionTemplate($store = null)
    {
        return Mage::getStoreConfig('helpdesk/notification/staff_new_satisfaction_template', $store);
    }

    public function getSatisfactionIsActive($store = null)
    {
        return Mage::getStoreConfig('helpdesk/satisfaction/is_active', $store);
    }

    public function getSatisfactionIsShowResultsInTicket($store = null)
    {
        return Mage::getStoreConfig('helpdesk/satisfaction/is_show_results_in_ticket', $store);
    }

    public function getSatisfactionIsSendResultsOwner($store = null)
    {
        return Mage::getStoreConfig('helpdesk/satisfaction/is_send_results_owner', $store);
    }

    public function getSatisfactionResultsEmail($store = null)
    {
        $result = trim(Mage::getStoreConfig('helpdesk/satisfaction/results_email', $store));
        if ($result) {
            return explode(',', $result);
        }
    }

    public function getSatisfactionIsShowInTicketFront($store = null)
    {
        return Mage::getStoreConfig('helpdesk/satisfaction/is_show_in_ticket_front', $store);
    }

    public function getFrontendIsActive($store = null)
    {
        return Mage::getStoreConfig('helpdesk/frontend/is_active', $store);
    }

    public function getFrontendIsAllowPriority($store = null)
    {
        return Mage::getStoreConfig('helpdesk/frontend/is_allow_priority', $store);
    }

    public function getFrontendIsAllowDepartment($store = null)
    {
        return Mage::getStoreConfig('helpdesk/frontend/is_allow_department', $store);
    }

    public function getFrontendIsAllowOrder($store = null)
    {
        return Mage::getStoreConfig('helpdesk/frontend/is_allow_order', $store);
    }

    public function getDeveloperIsActive($store = null)
    {
        return Mage::getStoreConfig('helpdesk/developer/is_active', $store);
    }

    public function getDeveloperSandboxEmail($store = null)
    {
        return Mage::getStoreConfig('helpdesk/developer/sandbox_email', $store);
    }

    public function getDesktopNotificationIsActive($store = null)
    {
        return (bool) (Mage::getStoreConfig('helpdesk/desktop_notification/check_period', $store) > 0);
    }

    public function getDesktopNotificationCheckPeriod($store = null)
    {
        return Mage::getStoreConfig('helpdesk/desktop_notification/check_period', $store);
    }

    /**
     * @param null $store
     *
     * @return int[]
     */
    public function getDesktopNotificationAboutTicketUserIds($store = null)
    {
        return explode(',',
            Mage::getStoreConfig('helpdesk/desktop_notification/is_notification_about_ticket_user_ids', $store));
    }

    /**
     * @param int $store
     *
     * @return bool
     */
    public function getDesktopNotificationAboutMessage($store = null)
    {
        return Mage::getStoreConfig('helpdesk/desktop_notification/is_notification_allow_message', $store);
    }

    /**
     * @param int $store
     *
     * @return bool
     */
    public function getDesktopNotificationAboutAssign($store = null)
    {
        return Mage::getStoreConfig('helpdesk/desktop_notification/is_notification_allow_assign', $store);
    }

    /**
     * @param int $store
     * @return bool
     */
    public function getDesktopNotificationIsIndicatorShown($store = null)
    {
        return Mage::getStoreConfig('helpdesk/desktop_notification/is_indicator_shown', $store);
    }

    public function isActiveRma()
    {
        if (Mage::helper('mstcore')->isModuleInstalled('Mirasvit_Rma')) {
            $config = Mage::getSingleton('rma/config');
            if (method_exists($config, 'isActiveHelpdesk') && $config->isActiveHelpdesk()) {
                return true;
            }
        }
    }

    public function getSolvedStatuses($store = null)
    {
        $statues = Mage::getStoreConfig('helpdesk/report/solved_status', $store);
        $statues = array_filter(explode(',', $statues));
        $statues[] = 0;

        return $statues;
    }

    /**
     * @param Mage_Core_Model_Store|int $store
     *
     * @return array
     */
    public function getGeneralFileAllowedExtensions($store = null)
    {
        if (!$extensions = Mage::getStoreConfig('helpdesk/general/file_allowed_extensions', $store)) {
            return array();
        }
        $extensions = explode(',', $extensions);
        $extensions = array_map('trim', $extensions);
        return $extensions;
    }
    /**
     * @param Mage_Core_Model_Store|int $store
     *
     * @return string
     */
    public function getGeneralFileSizeLimit($store = null)
    {
        return Mage::getStoreConfig('helpdesk/general/file_size_limit', $store);
    }
}
