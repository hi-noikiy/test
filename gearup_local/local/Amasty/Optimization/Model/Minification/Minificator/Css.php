<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Optimization
 */


class Amasty_Optimization_Model_Minification_Minificator_Css extends Amasty_Optimization_Model_Minification_Minificator
{
    public function isDeferred()
    {
        return false;
    }

    public function getCode()
    {
        return 'css';
    }

    protected function minifyFile($path)
    {
        $contents = file_get_contents($path);

        $contents = $this->applyRules($contents, array(
            // shorthand hex color codes
            array('/(?<![\'"])#([0-9a-z])\\1([0-9a-z])\\2([0-9a-z])\\3(?![\'"])/i', '#$1$2$3'),
            // strip comments
            array('/\/\*(.*?)\*\//is', ''),
            // semicolon/space before closing bracket > replace by bracket
            array('/;?\s*}/', '}'),
            // bracket, colon, semicolon or comma proceeded or followed by whitespace > remove space
            array('/\s*([\{:;,])\s*/', '$1'),
            // disable union selector when parts on different lines
            array('/([^{},])[\n]+([^{},])/', '$1 $2'),
            // proceeding/trailing whitespace > remove
            array('/^\s*|\s*$/m', ''),
            // newlines > remove
            array('/\n/', ''),
        ));

        $designPackage = Mage::getSingleton('core/design_package');
        $result = $designPackage->beforeMergeCss($this->getOriginalPath($path), $contents);

        file_put_contents($path, $result, LOCK_EX);
    }

    public function applyRules($contents, $rules)
    {
        foreach ($rules as $rule)
            $contents = preg_replace($rule[0], $rule[1], $contents);

        return $contents;
    }
}
