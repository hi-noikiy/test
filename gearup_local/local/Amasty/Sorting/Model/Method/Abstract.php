<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */
class Amasty_Sorting_Model_Method_Abstract
{
    protected $_enabled = false;

    public function getCode()
    {
        return '';
    }
    
    public function getName()
    {
        return '';
    }
    
    public function apply($collection, $currDir)  
    {
        if (!$this->isEnabled()){
            return $this;
        }
        
        $table = $this->getIndexTable();
        $from = $collection->getSelect()->getPart('from');
        if ($table && Mage::getStoreConfig('amsorting/general/use_index')) {
            if (!isset($from['at_' . $this->getCode()])) {
                $collection->joinField(
                    $this->getCode(),      // alias
                    $table,                // table
                    $this->getCode(),      // field
                    'id=entity_id',        // bind
                    array('store_id' => Mage::app()->getStore()->getId()),
                    // conditions
                    'left'                 // join type
                );
            }
            $field = 'at_' . $this->getCode() . '.' . $this->getCode();
        }
        else {
            $select = $collection->getSelect();
            $col   = $select->getPart('columns');
            $col[] = array('', $this->getColumnSelect(), $this->getCode());
            $select->setPart('columns', $col);
            $field = $this->getCode();
        }
        $collection->getSelect()->order($field . ' ' . $currDir);
               
        return $this;
    }
    
    public function isEnabled()
    {
        if ($this->getEnabled())
            return true;

        $disabled = Mage::getStoreConfig('amsorting/general/disable_methods');
        return (false === in_array($this->getCode(), explode(',', $disabled)));
    } 

    public function getEnabled()
    {
        return $this->_enabled;
    }
    
    public function setEnabled($v)
    {
        $this->_enabled = $v;
        return $this;
    }

    public function getPeriodCondition($field, $settingKey)
    {
        $period = intVal(Mage::getStoreConfig('amsorting/' . $settingKey));
        if ($period) {
            $period = date('Y-m-d H:i:s', time() - $period * 24 * 3600);
            $period = " AND $field > '$period' ";
        } else {
            $period = '';
        }

        return $period;
    }
    
    public function getStoreCondition($field)
    {
        return " AND $field = " . Mage::app()->getStore()->getId();
    }
    
    public function getColumnSelect()
    {
        return '';         
    }

    public function getIndexTable()
    {
        return '';
    } 
     
    public function getIndexSelect() 
    {
        return '';
    }    
     
    public function reindex()
    {
        if (!$this->isEnabled()){
            return false;
        }
        
        $table = $this->getIndexTable();
        if (!$table){
            return false;
        }
        
        $db    = Mage::getSingleton('core/resource')->getConnection('core_write'); 
        $table = Mage::getSingleton('core/resource')->getTableName($table);
        
        $db->query("TRUNCATE TABLE $table ");
        $db->query("ALTER TABLE $table DISABLE KEYS");
        $db->query("INSERT INTO $table " . $this->getIndexSelect());
        $db->query("ALTER TABLE $table ENABLE KEYS");
        
        return true;        
    }    
}
