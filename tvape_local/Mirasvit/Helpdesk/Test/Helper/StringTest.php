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



class Mirasvit_Helpdesk_Test_Model_StringTest extends EcomDev_PHPUnit_Test_Case
{
    /** @var  Mirasvit_Helpdesk_Helper_String $helper */
    protected $helper;
    protected function setUp()
    {
        parent::setUp();

        $this->helper = Mage::helper('helpdesk/string');
    }

    /**
     * @test
     */
    public function generateTicketCode()
    {
        $result = $this->helper->generateTicketCode();
        $this->assertEquals(13, strlen($result));
    }

    /**
     * @test
     * @dataProvider convertToHtmlProvider
     *
     * @param string $input
     * @param string $expected
     */
    public function convertToHtml($input, $expected)
    {
        $result = $this->helper->convertToHtml($input);
        $this->assertEquals($expected, $result);
    }

    public function convertToHtmlProvider()
    {
        return array(
            array(
                ' aaaa@bbbb.com ',
                '&nbsp;<a href="mailto:aaaa@bbbb.com">aaaa@bbbb.com</a>&nbsp;',
                ),
            array(
                'https://www.evernote.com/shard/s405/sh/bfc9423b-9051-49ef-a3ea-37293055b1be/17c7264f76e1a8a08db7c964641abe52/deep/0/ftp.officerock.com---dev@officerock.com@ftp.officerock.com---FileZilla.png',
                '<a href="https://www.evernote.com/shard/s405/sh/bfc9423b-9051-49ef-a3ea-37293055b1be/17c7264f76e1a8a08db7c964641abe52/deep/0/ftp.officerock.com---dev@officerock.com@ftp.officerock.com---FileZilla.png">https://www.evernote.com/shard/s405/sh/bfc9423b-9051-49ef-a3ea-37293055b1be/17c7264f76e1a8a08db7c964641abe52/deep/0/ftp.officerock.com---dev@officerock.com@ftp.officerock.com---FileZilla.png</a>',
                ),
            array(
                '/var/www/vhosts/espace-camera.com/httpdocs/Observer.php',
                '/var/www/vhosts/espace-camera.com/httpdocs/Observer.php',
                ),
            array(
                'http://store.com/?aaaa=1&bbbb=2',
                '<a href="http://store.com/?aaaa=1&bbbb=2">http://store.com/?aaaa=1&bbbb=2</a>',
                ),
            array(
                ' www.espace-camera.com/httpdocs/',
                '<a href="http://www.espace-camera.com/httpdocs/">www.espace-camera.com/httpdocs/</a>',
                ),
            array(
                'http://espace-camera.com/httpdocs/',
                '<a href="http://espace-camera.com/httpdocs/">http://espace-camera.com/httpdocs/</a>',
                ),
            array(
                'https://espace-camera.com/httpdocs/',
                '<a href="https://espace-camera.com/httpdocs/">https://espace-camera.com/httpdocs/</a>',
                ),
        );
    }

    /**
     * @test
     * @dataProvider ebayEmailProvider
     *
     * @param string $text
     * @param string $expected
     *
     * @return string
     */
    public function parseEbayCodeFromMessage($text, $expected)
    {
        $result = $this->helper->parseEbayCodeFromMessage($text);
        $this->assertEquals($expected, $result);
    }

    public function ebayEmailProvider()
    {
        return array(
            array(
                'eBay ayuda a proteger tu privacidad y seguridad en Internet cuando utilizas
nuestras herramientas de mensajería. Si no ha habido ninguna transacción
reciente entre ti y el usuario con el que contactas, nuestras herramientas
pueden hacer anónimas vuestras direcciones de correo electrónico.

-----------------------------------------------------------------
Identificador de referencia del correo electrónico:
[#a04-l0ngukzifb#]_[#4935869565dc4af08ec6504994e37133#]
-----------------------------------------------------------------

No borres este número. Puede que te lo pida el servicio de Atención al
cliente de eBay.', '[#a04-l0ngukzifb#]_[#4935869565dc4af08ec6504994e37133#]',
            ),
        );
    }

    /**
     * @test
     * @dataProvider subjectProvider
     *
     * @param string $input
     * @param string $expected
     */
    public function getTicketCodeFromSubject($input, $asseptForeignTickets, $expected)
    {
        $result = $this->helper->getTicketCodeFromSubject($input, $asseptForeignTickets);
        $this->assertEquals($expected, $result);
    }

    public function subjectProvider()
    {
        return array(
            array('[#ION-465-43972] Bug', Mirasvit_Helpdesk_Model_Config::ACCEPT_FOREIGN_TICKETS_DISABLE, 'ION-465-43972'),
            array('Re: [#ION-465-43972] Bug', Mirasvit_Helpdesk_Model_Config::ACCEPT_FOREIGN_TICKETS_DISABLE, 'ION-465-43972'),
            array('Re:Re:[#ION-465-43972]Bug', Mirasvit_Helpdesk_Model_Config::ACCEPT_FOREIGN_TICKETS_DISABLE, 'ION-465-43972'),
            array('Re:Re:[ION-465-43972] Bug', Mirasvit_Helpdesk_Model_Config::ACCEPT_FOREIGN_TICKETS_DISABLE, false),
            array('Re:Re:#ION-465-43972 Bug', Mirasvit_Helpdesk_Model_Config::ACCEPT_FOREIGN_TICKETS_DISABLE,  'ION-465-43972'),
            #aw tickets
            array('Re:Re:[AWR-52708] Bug', Mirasvit_Helpdesk_Model_Config::ACCEPT_FOREIGN_TICKETS_AW, false),
            array('Re:Re:#AWR-52708 Bug', Mirasvit_Helpdesk_Model_Config::ACCEPT_FOREIGN_TICKETS_AW,  'AWR-52708'),
            array('Re: [#CER-84876] New Ticket created: test', Mirasvit_Helpdesk_Model_Config::ACCEPT_FOREIGN_TICKETS_AW,  'CER-84876'),
            array('Re: [#HNR-12188] Ticket replied: Re: Your points at The Green Nursery are about to expire', Mirasvit_Helpdesk_Model_Config::ACCEPT_FOREIGN_TICKETS_AW,  'HNR-12188'),

            #wm tickets
            #Re: Ticket #1000090 - test
            array('Re:Re:Ticket #1000090 - test', Mirasvit_Helpdesk_Model_Config::ACCEPT_FOREIGN_TICKETS_MW, 'Ticket #1000090'),
            array('Re: Ticket #1000090 - test', Mirasvit_Helpdesk_Model_Config::ACCEPT_FOREIGN_TICKETS_MW, 'Ticket #1000090'),
            array('Re: Ticket #1000090111 sss- test', Mirasvit_Helpdesk_Model_Config::ACCEPT_FOREIGN_TICKETS_MW, 'Ticket #1000090111'),
            array('pipe flashing Pittsburg fold 0812', Mirasvit_Helpdesk_Model_Config::ACCEPT_FOREIGN_TICKETS_MW, false),

        );
    }

    /**
     * @test
     * @dataProvider body2parseProvider
     *
     * @param string $input
     * @param string $expected
     */
    public function getTicketCodeFromBody($input, $expected)
    {
        $result = $this->helper->getTicketCodeFromBody($input);
        $this->assertEquals($expected, $result);
    }

    public function body2parseProvider()
    {
        return array(
            array('Message-Id:--#AAA-123-45678--', 'AAA-123-45678'),
            array('Message-Id:--#abcedasdfwerwefasdfasdfsadf--', 'abcedasdfwerwefasdfasdfsadf'),
            array('asdfnsjdf askhudfbia sub Ticket Message-Id:--#AAA-123-45678-- 5%32423sfsd', 'AAA-123-45678'),
            array('#AAA-123-45678', false),
            array('dsfa #AAA-123-45678 asdf', false),
        );
    }

    protected function getFixt($code)
    {
        return file_get_contents(dirname(__FILE__)."/StringTest/fixtures/$code");
    }

    /**
     * @test
     * @dataProvider bodyProvider
     *
     * @param string $expected
     * @param string $format
     * @param string $input
     */
    public function parseBodyTest($expected, $format, $input)
    {
        $result = $this->helper->parseBody($this->getFixt($input), $format);
        // echo $result;die;
        $this->assertEquals($expected, $result);
    }

    public function bodyProvider()
    {
        return array(
array(
'So lösen Sie den Geschenkgutschein ein', Mirasvit_Helpdesk_Model_Config::FORMAT_HTML, 'email3.html',
),
array(
'H1 HEADER

H2 HEADER

H3 HEADER

p block
http://link.com
http://link.com
www.x.com

div block
italic
bold', Mirasvit_Helpdesk_Model_Config::FORMAT_HTML, 'email2.html',
),
array(
'line 1
line2

line3

line4', Mirasvit_Helpdesk_Model_Config::FORMAT_HTML, 'email1.html',
),
        );
    }

    /**
     * @test
     * @dataProvider timeProvider
     *
     * @param string $timeExample
     */
    public function removeTimeTest($timeExample)
    {
        $input = "aaaaaa\nbbbbbb\n$timeExample";
        $expected = "aaaaaa\nbbbbbb";

        $result = $this->helper->removeTime($input);
        // echo $result;
        $this->assertEquals($expected, $result);
    }

    public function timeProvider()
    {
        return array(
            array('2014-03-26 19:47 GMT+02:00 Sales <support2@mirasvit.com.ua>:'),
            array('2014-03-25 0:00 GMT+02:00 COPPERLAB Customer Support <support..m>:'),
            array('On Mon, Mar 24, 2014 at 10:58 PM, Sales wrote:'),
            array('2014-03-24 19:22 GMT+02:00 Sales :'),
            array('On Dec 8, 2014, at 9:24 AM, Mirasvit Support <a8v1oq0kggnvsinmg6dv@mirasvit.com> wrote:'),
            array('2014-12-12 9:52 GMT-03:00 Mirasvit Support <a8v1oq0kggnvsinmg6dv@mirasvit.com>:'),
            array('2014-12-05 11:31 GMT-03:00 Mirasvit Support <a8v1oq0kggnvsinmg6dv@mirasvit.com>:'),
            array('El 11-12-2014, a las 12:22 p.m., Mirasvit Support <a8v1oq0kggnvsinmg6dv@mirasvit.com> escribió:'),
            array('Thu, 22 Sep 2016 11:52:31 +0000:'),
            array('9/22/16 18:20 (GMT+01:00):'),
            array('viernes, 23 de septiembre de 2016 10:48:'),
            array('Date: Thu, 22 Sep 2016 11:52:31 +0000:'),
            array('Enviado el: viernes, 23 de septiembre de 2016 10:48:'),
            array('Date: 9/22/16 18:20 (GMT+01:00):'),
            array('El 23/9/16, Zococity.es - Ventas <info@zococity.com> escribió:'),
            array('Em 16/04/2016 8:43 da manhã, "Zococity - Ventas" <a8v1oq0kggnvsinmg6dv@mirasvit.com>:'),
            array("2014-12-05 11:31 GMT-03:00 Mirasvit Support \n<a8v1oq0kggnvsinmg6dv@mirasvit.com>:"),
        );
    }
}
