<?php
/**
 * Below code is here because wkhtmltopdf by Mike Haertl uses namespaces
 */
include Mage::getBaseDir() . '/lib/mikehaertl/wkhtmltopdf/src/Pdf.php';
include Mage::getBaseDir() . '/lib/mikehaertl/wkhtmltopdf/src/Command.php';

use mikehaertl\wkhtmltopdf\src\Pdf as Pdf;
use mikehaertl\wkhtmltopdf\src\Command as Command;

spl_autoload_register(function($class) {
    if (false !== strpos($class, 'Pdf\\') || false !== strpos($class, 'Command\\')) {
        $class = trim($class, '\\');
        $classFile = str_replace(' ', DIRECTORY_SEPARATOR, ucwords(str_replace('\\', ' ', $class)));
        $classFile .= '.php';
        @include $classFile;
    }
});

/**
 * Class Hatimeria_WkHtmlToPdf_Model_Pdf
 */
class Hatimeria_WkHtmlToPdf_Model_Pdf extends Pdf
{
    /**
     * Return pdf object from WkHtmlToPdf
     *
     * @param $invoice
     * @return Pdf
     */
    public function createInvoicePdf($invoice)
    {
        $pdf = new Pdf(sprintf('%s', Mage::getUrl('hwkhtmltopdf/invoice/printInvoice')) , array('invoice_id' => $invoice->getId()));
        return $pdf;
    }
} 