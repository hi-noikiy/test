<?php

class Justselling_Assetminify_Block_Page_Html_Head extends Mage_Page_Block_Html_Head {
    public function getCssJsHtml() {
        if (!Mage::getStoreConfigFlag('assetminify/settings/enabled')) {
            $html = parent::getCssJsHtml();
        } else {
            $webroot = "/";

            $lines = array();

            $baseJs = Mage::getBaseUrl('js');
            $baseJsFast = Mage::getBaseUrl('skin') . 'assetminify/';
            Mage::getConfig()->getVarDir('minifycache');
            $html = '';
            //$html = "<!--".BP."-->\n";
            $script = '<script type="text/javascript" src="%s" %s></script>';
            $stylesheet = '<link type="text/css" rel="stylesheet" href="%s" %s />';
            $alternate = '<link rel="alternate" type="%s" href="%s" %s />';

            foreach ($this->_data['items'] as $item) {
                if (!is_null($item['cond']) && !$this->getData($item['cond'])) {
                    continue;
                }
                $if = !empty($item['if']) ? $item['if'] : '';

                /* Check, if we have the new magento1.9 if syntax in layout.xml */
                $result = preg_match("/\.*[(if)+(.*)]\.*/", $if, $parts);
                if ($result == 1) {
                    $if = $parts[2];
                }

                switch ($item['type']) {
                    case 'js':
                        if (strpos($item['name'], 'packaging.js') !== false) {
                            $item['name'] = $baseJs . $item['name'];
                            $lines[$if]['script_direct'][] = $item;
                        } else {
                            $lines[$if]['script']['global'][] = "/" . $webroot . "js/" . $item['name'];
                        }
                        break;

                    case 'script_direct':
                        $lines[$if]['script_direct'][] = $item;
                        break;

                    case 'css_direct':
                        $lines[$if]['css_direct'][] = $item;
                        break;

                    case 'js_css':
                        $lines[$if]['other'][] = sprintf($stylesheet, $baseJs . $item['name'], $item['params']);
                        break;

                    case 'skin_js':
                        $chunks = explode('/skin', $this->getSkinUrl($item['name']), 2);
                        $lines[$if]['script']['skin'][] = "/" . $webroot . "skin" . $chunks[1];
                        break;

                    case 'skin_css':
                        if ($item['params'] == 'media="all"') {
                            $chunks = explode('/skin', $this->getSkinUrl($item['name']), 2);
                            $lines[$if]['stylesheet'][] = "/" . $webroot . "skin" . $chunks[1];
                        } elseif ($item['params'] == 'media="print"') {
                            $chunks = explode('/skin', $this->getSkinUrl($item['name']), 2);
                            $lines[$if]['stylesheet_print'][] = "/" . $webroot . "skin" . $chunks[1];
                        } else {
                            $lines[$if]['other'][] = sprintf(
                                $stylesheet, $this->getSkinUrl($item['name']), $item['params']
                            );
                        }
                        break;

                    case 'rss':
                        $lines[$if]['other'][] = sprintf(
                            $alternate, 'application/rss+xml' /*'text/xml' for IE?*/, $item['name'], $item['params']
                        );
                        break;

                    case 'link_rel':
                        $lines[$if]['other'][] = sprintf('<link %s href="%s" />', $item['params'], $item['name']);
                        break;

                    case 'ext_js':
                    default:
                        $lines[$if]['other'][] = sprintf(
                            '<script type="text/javascript" src="%s"></script>', $item['name']
                        );
                        break;

                }
            }

            foreach ($lines as $if => $items) {
                if (!empty($if)) {
                    $html .= '<!--[if ' . $if . ']>' . "\n";
                }
                if (!empty($items['stylesheet'])) {
                    $cssBuild = Mage::getModel('assetminify/buildSpeedster', array($items['stylesheet'], BP));
                    foreach (
                        $this->getChunkedItems($items['stylesheet'], $baseJsFast . $cssBuild->getLastModified()) as $item
                    ) {
                        $html .= sprintf($stylesheet, $item, 'media="all"') . "\n";
                    }
                }
                if (!empty($items['script']['global']) || !empty($items['script']['skin'])) {
                    if (!empty($items['script']['global']) && !empty($items['script']['skin'])) {
                        $mergedScriptItems = array_merge($items['script']['global'], $items['script']['skin']);
                    } elseif (!empty($items['script']['global']) && empty($items['script']['skin'])) {
                        $mergedScriptItems = $items['script']['global'];
                    } else {
                        $mergedScriptItems = $items['script']['skin'];
                    }
                    $jsBuild = Mage::getModel('assetminify/buildSpeedster', array($mergedScriptItems, BP));
                    $chunkedItems = $this->getChunkedItems($mergedScriptItems, $baseJsFast . $jsBuild->getLastModified());
                    foreach ($chunkedItems as $item) {
                        $html .= sprintf($script, $item, '') . "\n";
                    }
                }
                if (!empty($items['css_direct'])) {
                    foreach ($items['css_direct'] as $item) {
                        $html .= sprintf($stylesheet, $item['name']) . "\n";
                    }
                }
                if (!empty($items['script_direct'])) {
                    foreach ($items['script_direct'] as $item) {
                        $html .= sprintf($script, $item['name'], '') . "\n";
                    }
                }
                if (!empty($items['stylesheet_print'])) {
                    $cssBuild = Mage::getModel('assetminify/buildSpeedster', array($items['stylesheet_print'], BP));
                    foreach (
                        $this->getChunkedItems($items['stylesheet_print'], $baseJsFast . $cssBuild->getLastModified()) as
                        $item
                    ) {
                        $html .= sprintf($stylesheet, $item, 'media="print"') . "\n";
                    }
                }
                if (!empty($items['other'])) {
                    $html .= join("\n", $items['other']) . "\n";
                }
                if (!empty($if)) {
                    $html .= '<![endif]-->' . "\n";
                }
            }
        }

        if (Mage::getStoreConfigFlag('assetminify/settings/jquerymigrate')) {
            $script = sprintf('<script type="text/javascript" src="%s%s"></script>', Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS), "justselling/assetminify/jquery-migrate-1.2.1.min.js");
            $html .= $script;
        }

        if (Mage::getStoreConfigFlag('assetminify/settings/bootstrap')) {
            $script = sprintf('<script type="text/javascript" src="%s%s"></script>', Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS), "justselling/assetminify/bootstrap.js");
            $html .= $script;
        }

        return $html;
    }

    public function getChunkedItems($items, $prefix = '', $maxLen = 500) {
        if (!Mage::getStoreConfigFlag('assetminify/settings/enabled')) {
            return parent::getChunkedItems($items, $prefix, 450);
        }

        $chunks = array();
        $chunk = $prefix;

        foreach ($items as $item) {
            if (strlen($chunk . ',' . $item) > $maxLen) {
                $chunks[] = $chunk;
                $chunk = $prefix;
            }
            if ($chunk === $prefix) {
                $chunk .= substr($item, 1);
            } else {
                $chunk .= ',' . substr($item, 1);
            }
        }

        $chunks[] = $chunk;
        return $chunks;
    }
}
