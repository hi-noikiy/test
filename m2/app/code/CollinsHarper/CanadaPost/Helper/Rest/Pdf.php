<?php
/**
 * Copyright © 2015 Collins Harper. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CollinsHarper\CanadaPost\Helper\Rest;

/**
 * Measure Unit helper
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Pdf extends Request
{

    /**
     * 
     * @param string $url
     * @param string $media_type
     * @param string $filename
     * @param int $return_transfer
     * @return bool|string
     */
    public function load($url, $media_type, $filename='labels', $return_transfer = 1)
    {
        $headers = array(
            'Accept: ' . $media_type
        );

        if ($return_transfer) {

            header('Content-type: '.$media_type);

            header('Content-Disposition: attachment; filename="'.$filename.'-'.date('Y-m-d--H-i-s').'.pdf"');

            $this->send($url, '', 1, $headers);

            return true;

        } else {

            return $this->send($url, '', 1, $headers);

        }

    }


    // TODO identify datatype of $pdf
    /**
     * 
     * @param \Zend_Pdf $pdf
     * @param string $pdfString
     */
    public function addPage($pdf, $pdfString)
    {

        // TODO needs tobe rebuilt as mage 2
        $extractor = new \Zend_Pdf_Resource_Extractor();

        $temp_pdf = \Zend_Pdf::parse($pdfString);

        $page = $extractor->clonePage($temp_pdf->pages[0]);

        $pdf->pages[] = $page;

    }



}