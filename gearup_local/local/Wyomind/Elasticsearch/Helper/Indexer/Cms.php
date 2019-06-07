<?php

/**     
 * The technical support is guaranteed for all modules proposed by Wyomind.
 * The below code is obfuscated in order to protect the module's copyright as well as the integrity of the license and of the source code.
 * The support cannot apply if modifications have been made to the original source code (https://www.wyomind.com/terms-and-conditions.html).
 * Nonetheless, Wyomind remains available to answer any question you might have and find the solutions adapted to your needs.
 * Feel free to contact our technical team from your Wyomind account in My account > My tickets. 
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
  class Wyomind_Elasticsearch_Helper_Indexer_Cms extends Wyomind_Elasticsearch_Helper_Indexer_Abstract {public $xba=null;public $xfa=null;public $x2d=null;  protected $_blockClass = 'Wyomind_Elasticsearch_Block_Autocomplete_Cms';  private $x1d7 = null; public $error = "\105\154\x61s\164\151\x63\x73e\x61\162c\150 \103M\x53 Ind\145\170 \72\40\x49\156\x76\141lid \114\x69\143e\x6e\x73\x65\41"; public function __construct() {$x78e = "h\145\154\160\x65\162";$x9e6 = "\x61\x70\x70";$x832 = "\x67etM\x6f\x64el";$x8d5 = "\147\x65\164\x53\x74\157r\145C\157\156\x66\151\x67";$x968 = "get\x52\x65\163\157\165\162\143\x65\115\157\144e\154";$x99f = "\x64\x69\163p\141t\x63\x68\x45\x76en\x74"; $this->_construct(); } public function _construct() {$x78e = "\x68e\x6c\x70\145\x72";$x9e6 = "\141pp";$x832 = "g\145\164\x4d\x6fd\145l";$x8d5 = "\x67etS\164\x6f\162e\103on\x66\151\x67";$x968 = "\147\x65\x74\122e\163ou\x72c\x65\115o\x64\145\154";$x99f = "\x64\151\x73p\141t\x63\x68Eve\156t"; $this->x1d7 = Mage::helper("\x6c\151\143\145\156s\145\x6da\x6e\x61\147\145r\x2fd\x61ta"); $this->x1d7->constructor($this, func_get_args()); }  public function export($xaa = array()) {$x74 = $this->xfa->x1be->{$this->xba->x1be->{$this->xfa->x1be->x719}};$x65 = $this->x2d->x1be->{$this->xba->x1be->{$this->x2d->x1be->x721}};$x9e = $this->xba->x1be->x728;$xa5 = $this->xfa->x19b->{$this->xba->x19b->x335};$xe5 = $this->x2d->x1be->x73f;$x78e = "h\145\154\160\x65\x72";$x9e6 = "\141\160p";$x832 = "ge\164M\x6f\144\145\154";$x8d5 = "\147et\123\x74\157re\x43\157nfi\147";$x968 = "g\x65\x74\x52\145\x73our\143e\x4d\157\x64\145l";$x99f = "\144\x69s\160\141\x74c\x68\x45\x76en\x74"; try { ${$this->xfa->x1be->{$this->xfa->x1be->{$this->x2d->x1be->{$this->xfa->x1be->x5f3}}}} = $this; ${$this->xfa->x19b->{$this->xfa->x19b->{$this->x2d->x19b->x208}}} = "M\x61\147\x65"; ${$this->xba->x1be->{$this->xfa->x1be->x5ff}} = "he\154\160\145\162"; ${$this->xfa->x1be->{$this->x2d->x1be->{$this->xfa->x1be->{$this->x2d->x1be->x613}}}} = "th\162o\x77Ex\143\145p\164ion"; ${$this->xfa->x1be->{$this->x2d->x1be->x61a}} = $x74($x65()); ${$this->xfa->x1be->{$this->xfa->x1be->{$this->xfa->x1be->x5ef}}}->{$this->xfa->x1be->x5bf}->{$this->xfa->x19b->x393}(${$this->x2d->x1be->x5e6}, ${$this->xfa->x19b->{$this->xba->x19b->x226}}); if (${$this->x2d->x1be->x5e6}->{$this->xfa->x19b->x3a3}(${$this->xba->x1be->x615}) != $x74(${$this->xfa->x1be->{$this->x2d->x1be->x61a}})) { ${$this->xfa->x19b->{$this->xfa->x19b->{$this->x2d->x19b->x208}}}::${$this->xba->x19b->{$this->xba->x19b->{$this->xfa->x19b->x21c}}}(${$this->xfa->x19b->{$this->xfa->x19b->{$this->x2d->x19b->x208}}}::${$this->xfa->x19b->{$this->x2d->x19b->{$this->xfa->x19b->x210}}}("e\x6ca\163\x74i\x63\163\145\x61\162\x63h")->{$this->xfa->x19b->x3b6}(${$this->xfa->x1be->{$this->xfa->x1be->{$this->x2d->x1be->{$this->xba->x1be->{$this->x2d->x1be->x5f4}}}}}->{$this->xba->x1be->{$this->xfa->x1be->x5ce}})); } ${$this->xba->x19b->{$this->xba->x19b->{$this->xfa->x19b->x230}}} = array(); foreach (Mage::$x9e6()->{$this->xfa->x19b->x3cf}() as ${$this->x2d->x1be->{$this->xba->x1be->{$this->xfa->x1be->{$this->xba->x1be->x631}}}}) {  if (!${$this->x2d->x1be->{$this->xba->x1be->{$this->xfa->x1be->{$this->xba->x1be->x631}}}}->{$this->x2d->x19b->x3dc}()) { continue; } ${$this->x2d->x19b->{$this->xfa->x19b->x244}} = (int) ${$this->x2d->x1be->{$this->xba->x1be->x62e}}->{$this->x2d->x19b->x3ee}(); if (isset(${$this->x2d->x1be->{$this->x2d->x1be->x5d7}}['store_id'])) { if (!$x9e(${$this->x2d->x1be->{$this->x2d->x1be->{$this->x2d->x1be->{$this->xfa->x1be->{$this->xba->x1be->x5e1}}}}}['store_id'])) { ${$this->x2d->x19b->x1ec}['store_id'] = array(${$this->x2d->x1be->{$this->x2d->x1be->{$this->x2d->x1be->{$this->x2d->x1be->x5e0}}}}['store_id']); } if (!$xa5(${$this->xfa->x1be->{$this->xfa->x1be->x63d}}, ${$this->x2d->x1be->{$this->x2d->x1be->{$this->xba->x1be->x5dc}}}['store_id'])) { continue; } } $this->{$this->xba->x19b->x404}(' > Exporting CMS pages of store %s', ${$this->x2d->x1be->{$this->xba->x1be->x62e}}->{$this->xba->x19b->x411}()); ${$this->xba->x19b->x22c}[${$this->xfa->x1be->{$this->xfa->x1be->{$this->xba->x1be->{$this->xba->x1be->{$this->xba->x1be->x64a}}}}}] = array();  ${$this->xfa->x19b->x248} = $this->{$this->xfa->x19b->x41f}('cms', ${$this->xfa->x1be->x62a}); ${$this->xfa->x1be->{$this->xfa->x1be->{$this->x2d->x1be->x657}}} = Mage::$x832('cms/page')->{$this->xba->x19b->x434}() ->{$this->x2d->x19b->x447}(${$this->xba->x19b->{$this->xba->x19b->x238}}->{$this->x2d->x19b->x3ee}()) ->{$this->xba->x19b->x463}(${$this->xfa->x19b->{$this->xfa->x19b->x24d}}); if (${$this->x2d->x19b->{$this->x2d->x19b->x25c}} = $this->{$this->xba->x19b->{$this->xfa->x19b->{$this->xfa->x19b->x2ed}}}(${$this->x2d->x1be->{$this->xba->x1be->{$this->xfa->x1be->{$this->x2d->x1be->{$this->x2d->x1be->x634}}}}})) { ${$this->xfa->x19b->{$this->xfa->x19b->x254}}->{$this->xba->x19b->x479}('page_id', array('nin' => ${$this->x2d->x1be->{$this->xba->x1be->x65e}})); } ${$this->xba->x1be->x653}->{$this->xba->x19b->x479}('is_active', array('eq' => 1)); foreach (${$this->xba->x1be->x653} as ${$this->xba->x19b->{$this->xba->x19b->{$this->x2d->x19b->{$this->xba->x19b->x26e}}}}) { ${$this->x2d->x1be->x61e}[${$this->xfa->x1be->{$this->xfa->x1be->x63d}}][${$this->xba->x19b->{$this->xba->x19b->{$this->x2d->x19b->{$this->xba->x19b->x26e}}}}->{$this->x2d->x19b->x3ee}()] = $xe5( array('id' => ${$this->xfa->x1be->x662}->{$this->x2d->x19b->x3ee}()), ${$this->xba->x1be->{$this->x2d->x1be->{$this->xba->x1be->{$this->xfa->x1be->x66e}}}}->{$this->x2d->x19b->x49d}(${$this->xfa->x19b->x248}) ); } $this->{$this->xba->x19b->x404}(' > CMS pages exported'); } return ${$this->xba->x19b->{$this->xba->x19b->{$this->xfa->x19b->x230}}}; } catch (Exception $e) { throw $e; } }  public function getExcludedPageIds($x10b = null) {$x106 = $this->xba->x19b->x343;$x78e = "he\x6cpe\162";$x9e6 = "\141\x70\160";$x832 = "\147\x65\x74\x4d\157\x64\x65l";$x8d5 = "\x67\145tS\x74\x6f\162\145\103o\156f\151\147";$x968 = "\147\145\164\x52\x65\x73\157\165rceM\x6f\x64e\154";$x99f = "\x64\x69sp\x61\x74\x63\150\105\166\145\x6e\164"; return $x106(',', Mage::$x8d5('elasticsearch/cms/excluded_pages', ${$this->xba->x1be->x677})); }  public function getStoreIndexProperties($x172 = null) {$x124 = $this->xba->x1be->{$this->xfa->x1be->x75a};$x15b = $this->x2d->x1be->{$this->x2d->x1be->{$this->x2d->x1be->x76f}};$x182 = $this->xfa->x19b->{$this->xfa->x19b->x373};$x78e = "\x68e\154\160\145r";$x9e6 = "\x61\160\160";$x832 = "g\145tMod\x65l";$x8d5 = "g\x65tS\164\x6f\162e\103o\156\x66\151g";$x968 = "\147\145\x74R\x65so\165\162\143e\115\157\144\145\154";$x99f = "\x64\x69\163\x70a\x74\143\150Ev\x65\156t"; ${$this->x2d->x1be->{$this->xba->x1be->{$this->xfa->x1be->{$this->x2d->x1be->x685}}}} = Mage::$x9e6()->{$this->xba->x19b->x4cd}(${$this->xba->x19b->{$this->xfa->x19b->x285}}); ${$this->xba->x19b->x28a} = 'elasticsearch_cms_index_properties_' . ${$this->xba->x19b->{$this->xba->x19b->{$this->x2d->x19b->x287}}}->{$this->x2d->x19b->x3ee}(); if (Mage::$x9e6()->{$this->x2d->x19b->x4f5}('config')) { ${$this->xba->x19b->{$this->x2d->x19b->x299}} = Mage::$x9e6()->{$this->xba->x19b->x505}(${$this->xfa->x1be->{$this->xfa->x1be->{$this->x2d->x1be->{$this->x2d->x1be->{$this->xba->x1be->x696}}}}}); if (${$this->xba->x19b->{$this->x2d->x19b->x299}}) { return $x124(${$this->xba->x19b->{$this->x2d->x19b->x299}}); } } ${$this->xba->x1be->{$this->x2d->x1be->x6a5}} = $this->{$this->xba->x19b->x510}(${$this->x2d->x1be->{$this->xba->x1be->{$this->xfa->x1be->x680}}}); ${$this->x2d->x1be->{$this->xba->x1be->x69e}} = array(); ${$this->x2d->x1be->x6ab} = Mage::$x968('cms/page'); ${$this->xba->x19b->{$this->xfa->x19b->{$this->xfa->x19b->{$this->xfa->x19b->{$this->x2d->x19b->x2bf}}}}} = ${$this->x2d->x19b->{$this->x2d->x19b->{$this->xba->x19b->x2af}}}->{$this->xba->x19b->x52b}()->{$this->xba->x19b->x539}(${$this->xba->x1be->{$this->xfa->x1be->{$this->x2d->x1be->{$this->xfa->x1be->x6b2}}}}->{$this->xfa->x19b->x543}()); foreach ($this->{$this->xfa->x19b->x41f}('cms', ${$this->xfa->x19b->x280}) as ${$this->x2d->x1be->x6be}) { if (isset(${$this->xba->x19b->{$this->xfa->x19b->{$this->xba->x19b->x2b7}}}[${$this->x2d->x19b->{$this->xfa->x19b->x2c5}}])) { ${$this->x2d->x1be->x69b}[${$this->xba->x1be->{$this->x2d->x1be->x6c1}}] = array( 'type' => 'string', 'analyzer' => 'std', 'include_in_all' => true, 'boost' => 1,  'fields' => array( 'std' => array( 'type' => 'string', 'analyzer' => 'std', ) ), ); if (${$this->xba->x19b->{$this->xfa->x19b->{$this->xfa->x19b->{$this->xfa->x19b->x2bc}}}}[${$this->xba->x1be->{$this->x2d->x1be->x6c1}}]['DATA_TYPE'] == Varien_Db_Ddl_Table::TYPE_VARCHAR) { ${$this->x2d->x1be->{$this->xba->x1be->x69e}}[${$this->xba->x1be->{$this->x2d->x1be->x6c1}}]['fields'] = $x15b(${$this->xfa->x19b->x295}[${$this->x2d->x1be->x6be}]['fields'], array( 'prefix' => array( 'type' => 'string', 'analyzer' => 'text_prefix', 'search_analyzer' => 'std', ), 'suffix' => array( 'type' => 'string', 'analyzer' => 'text_suffix', 'search_analyzer' => 'std', ), )); } if (isset(${$this->x2d->x1be->x6a3}['analysis']['analyzer']['language'])) { ${$this->xba->x19b->{$this->x2d->x19b->x299}}[${$this->x2d->x19b->x2c0}]['analyzer'] = 'language'; } } } ${$this->xba->x19b->{$this->x2d->x19b->x299}} = new Varien_Object(${$this->xfa->x19b->x295}); Mage::$x99f('wyomind_elasticsearch_index_properties', array( 'indexer' => $this, 'store' => ${$this->xba->x19b->{$this->xba->x19b->{$this->x2d->x19b->{$this->xfa->x19b->x288}}}}, 'properties' => ${$this->xba->x19b->{$this->xfa->x19b->{$this->xfa->x19b->x29d}}}, )); ${$this->xba->x19b->{$this->xfa->x19b->{$this->xfa->x19b->x29d}}} = ${$this->x2d->x1be->x69b}->{$this->xfa->x19b->x3a3}(); if (Mage::$x9e6()->{$this->x2d->x19b->x4f5}('config')) { ${$this->xba->x19b->{$this->xba->x19b->x2cd}} = $this->{$this->xba->x19b->x59c}(); Mage::$x9e6()->{$this->xfa->x19b->x5af}($x182(${$this->x2d->x1be->{$this->xba->x1be->x69e}}), ${$this->xba->x19b->x28a}, array('config'), ${$this->xba->x19b->x2c9}); } return ${$this->xba->x19b->{$this->xfa->x19b->{$this->xfa->x19b->x29d}}}; } } 