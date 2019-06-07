<?php

/**
 * justselling Germany Ltd. EULA
 * http://www.justselling.de/
 * Read the license at http://www.justselling.de/lizenz
 *
 * Do not edit or add to this file, please refer to http://www.justselling.de for more information.
 *
 * @category    justselling
 * @package     justselling_
 * @copyright   Copyright (c) 2013 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
 * @author      Bodo Schulte
 */
class Gearup_Configurator_Model_Utils_TemplateCopy extends Justselling_Configurator_Model_Utils_TemplateCopy {

    public function __construct($objects = null) {
        $this->_templateObjects = $objects;
    }

    /**
     * Copies a template on the source of a template for the given ID. The template source may be a persisted one stored
     * in database or, depending on the constructor of this entity, a file-based, imported one.
     *
     * @param $id template ID, never null
     * @return int templateID the new generated template ID
     * @throws Exception on any internal exception
     */
    public function copy($id) {

        if (is_null($id))
            throw new Mage_Exception("Template ID to copy should never be null!");
        $newTemplateId = parent::copy($id);
        try {
            /* @var $resource Mage_Core_Model_Resource */
            $resource = Mage::getSingleton('core/resource');
            $table = $resource->getTableName('configurator_subsection');
            /* @var $db Magento_Db_Adapter_Pdo_Mysql */
            $db = $resource->getConnection('core_write');
            $db->exec("SET FOREIGN_KEY_CHECKS=0");
            $db->beginTransaction();
            if (!$newTemplateId)
                throw new Exception("Could not create new Template Entry!");
            $select = $db->select()
                    ->from(array("gco" => $table), array("id", "option_id", "gco.template_id", "sortorder", "subtitle"))
                    ->joinLeft(array('co' => Mage::getSingleton("core/resource")->getTableName('configurator/option')), 'co.id = gco.option_id', ['co.title'])
                    ->where('gco.template_id = ?', $id)
                    ->order(array("gco.id DESC"));

            if (!$newTemplateId)
                throw new Exception("Could not create new Template Entry!");
            $items = array_values($db->fetchAll($select));


            foreach ($items as $index):
                unset($index['id']);
                $index['template_id'] = $newTemplateId;
                $result = $db->select()
                                ->from(array("co" => Mage::getSingleton("core/resource")->getTableName('configurator/option')), array("id"))
                                ->where('template_id = ?', $newTemplateId)->where('title like ?', $index['title']);
                unset($index['title']);
                $index['option_id'] = $db->fetchRow($result)['id'];
                $affectedRows = $db->insertArray($table, array_keys($index), array($index));
                if (!$affectedRows) {
                    Js_Log::log("Insertion didn't affect any row: ", $this, Zend_Log::WARN);
                }
            endforeach;
            $db->commit();
        } catch (Exception $e) {
            Js_Log::logException($e, $this, 'Copy of sub sactions failed!');
            $db->rollback();
            $db->exec("SET FOREIGN_KEY_CHECKS=0");
            throw $e;
        }
    }

}
