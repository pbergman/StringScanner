<?php
/**
 * @author    Philip Bergman <pbergman@live.nl>
 * @copyright Philip Bergman
 */
require_once dirname(__FILE__) . '/../../../vendor/autoload.php';

use PBergman\StringScanner\StringScanner;

class StringScannerTest extends \PHPUnit_Framework_TestCase
{

    /** @var null|StringScanner  */
    private $stringScanner = null;

    public function __construct()
    {
        $this->stringScanner = new StringScanner();
        $this->assertInstanceOf('PBergman\StringScanner\StringScanner', $this->stringScanner);

    }


    /**
     * Test Add and Scan methods
     */
    public function testSetAndScanString()
    {
        $this->stringScanner->setString('test string');
        $this->assertEquals("test",    $this->stringScanner->scan('/\w+/'), '::scan() '   . $this->stringScanner->inspect());
        $this->assertEquals(4,         $this->stringScanner->getPos(),      '::getPos() ' . $this->stringScanner->inspect());

        $this->stringScanner->setString('test string');
        $this->assertEquals(0,       $this->stringScanner->getPos(),  '::setString() should reset pos');
        $this->assertNull($this->stringScanner->matched(),            '::setString() should reset matched');

    }

    /**
     * check match methods
     *
     *  @depends testSetAndScanString
     *
     */
    public function testMatch()
    {

        $this->stringScanner->setString('test string');
        $this->assertEquals(4,    $this->stringScanner->match('/\w+/'), '::match() ' . $this->stringScanner->inspect());
        $this->assertEquals(4,    $this->stringScanner->match('/\w+/'), '::match() ' . $this->stringScanner->inspect());
        $this->assertNull($this->stringScanner->match('/\s+/'),         '::match() ' . $this->stringScanner->inspect());


        $this->stringScanner->setString('test string');
        $this->assertEquals(4,      $this->stringScanner->match('/\w+/'),    '::match(), should return 4 for "\w+" on string "test string"');
        $this->assertEquals('test', $this->stringScanner->matched(),         '::matched() returns last match value (test)');
        $this->assertEquals(4,      $this->stringScanner->matchedSize(),     '::matchedSize(), should return 4 for the last matched size');
        $this->assertTrue($this->stringScanner->isMatched(),                 '::isMatched() return true when we got a match');

        $this->assertNull($this->stringScanner->match('/\d+/'), '::match(), should return null because there are no numbers after "test" in "test string"');
        $this->assertFalse($this->stringScanner->isMatched(),   '::isMatched() return false because we do not have a match');
        $this->assertNull($this->stringScanner->matchedSize(),  '::matchedSize(), should return NULL for the last matched size');

        $this->assertEquals('test',   $this->stringScanner->scan('/\w+/'),   '::scan(), should return "test" for scan "\w+" on "test string"');
        $this->assertEquals(' ' ,     $this->stringScanner->scan('/\s+/'),   '::scan(), should return " " for "\s+" on (sub)string " string"');
        $this->assertEquals('test',   $this->stringScanner->preMatch(),      '::preMatch()  should return "test" because last match was " " ');
        $this->assertEquals('string', $this->stringScanner->postMatch(),     '::postMatch() should return "string" because last match was " " ');
    }

    /**
     * test (full) scan methods
     *
     * @depends testSetAndScanString
     *
     */
    public function testScan()
    {
        $this->stringScanner->setString('test string');

        $this->assertEquals("test",    $this->stringScanner->scan('/\w+/'), '::scan() ' . $this->stringScanner->inspect());
        $this->assertNull($this->stringScanner->scan('/\w+/'),              '::scan() ' . $this->stringScanner->inspect());
        $this->assertEquals(" ",       $this->stringScanner->scan('/\s+/'), '::scan() ' . $this->stringScanner->inspect());
        $this->assertEquals("string",  $this->stringScanner->scan('/\w+/'), '::scan() ' . $this->stringScanner->inspect());
        $this->assertNull($this->stringScanner->scan('/./'),                '::scan() ' . $this->stringScanner->inspect());


        $this->stringScanner->setString('Fri Dec 12 1975 14:39');

        $this->assertEquals("Fri Dec 1", $this->stringScanner->scanUntil('/1/'),   '::scanUntil() ' . $this->stringScanner->inspect());
        $this->assertEquals("Fri Dec ",  $this->stringScanner->preMatch(),         '::scanUntil() ' . $this->stringScanner->inspect());
        $this->assertNull($this->stringScanner->scanUntil('/XYZ/'),                '::scanUntil() ' . $this->stringScanner->inspect());


    }

    /**
     * testing scan methods
     *
     * @depends testMatch
     */
    public function testCheck()
    {
        $this->stringScanner->setString('Fri Dec 12 1975 14:39');
        $this->assertEquals("Fri", $this->stringScanner->check('/Fri/'),  '::check() '     . $this->stringScanner->inspect());
        $this->assertEquals(0,     $this->stringScanner->getPos(),        '::pos() '       . $this->stringScanner->inspect());
        $this->assertEquals("Fri", $this->stringScanner->matched(),       '::matched() '   . $this->stringScanner->inspect());
        $this->assertNull($this->stringScanner->check('/12/'),            '::check() '     . $this->stringScanner->inspect());
        $this->assertNull($this->stringScanner->matched(),                '::matched() '   . $this->stringScanner->inspect());


        $this->stringScanner->setString('Fri Dec 12 1975 14:39');

        $this->assertEquals("Fri Dec 12",  $this->stringScanner->checkUntil('/12/'),  '::checkUntil() ' . $this->stringScanner->inspect());
        $this->assertEquals(0 ,            $this->stringScanner->getPos(),            '::getPos() '     . $this->stringScanner->inspect());
        $this->assertEquals(12,            $this->stringScanner->matched(),           '::matched() '   . $this->stringScanner->inspect());
    }

    /**
     * Testing some look ahead methods
     *
     *  @depends testScan
     */
    public function testLookAhead()
    {
        $this->stringScanner->setString('test string');

        $this->assertEquals(3,       $this->stringScanner->exists('/s/'),  '::exists() ' . $this->stringScanner->inspect());
        $this->assertEquals("test",  $this->stringScanner->scan('/test/'), '::scan() '   . $this->stringScanner->inspect());
        $this->assertEquals(2,       $this->stringScanner->exists('/s/'),  '::exists() ' . $this->stringScanner->inspect());
        $this->assertNull($this->stringScanner->exists('/e/'),             '::exists() ' . $this->stringScanner->inspect());



        $this->stringScanner->reset();
        $this->assertEquals("test st", $this->stringScanner->peek(7), '::peek() ' . $this->stringScanner->inspect());
        $this->assertEquals("test st", $this->stringScanner->peek(7), '::peek() ' . $this->stringScanner->inspect());



    }

    /**
     * testing skip methods
     *
     * @depends testSetAndScanString
     */
    public function testSkip()
    {
        $this->stringScanner->setString('test string');

        $this->assertEquals(4,     $this->stringScanner->skip('/\w+/'), '::skip() ' . $this->stringScanner->inspect());
        $this->assertNull($this->stringScanner->skip('/\w+/'),          '::skip() ' . $this->stringScanner->inspect());
        $this->assertEquals(1,     $this->stringScanner->skip('/\s+/'), '::skip() ' . $this->stringScanner->inspect());
        $this->assertEquals(6,     $this->stringScanner->skip('/\w+/'), '::skip() ' . $this->stringScanner->inspect());
        $this->assertNull($this->stringScanner->skip('/./'),            '::skip() ' . $this->stringScanner->inspect());


        $this->stringScanner->setString('Fri Dec 12 1975 14:39');
        $this->assertEquals(10,    $this->stringScanner->skipUntil('/12/'),'::skipUntil() ' . $this->stringScanner->inspect());
    }

    /**
     * get character test
     *
     * @depends testSetAndScanString
     *
     */
    public function testAdvancingScanPointer()
    {

        $this->stringScanner->setString('ab');
        $this->assertEquals("a",  $this->stringScanner->getCh(), '::getCh() ' . $this->stringScanner->inspect());
        $this->assertEquals("b",  $this->stringScanner->getCh(), '::getCh() ' . $this->stringScanner->inspect());
        $this->assertNull($this->stringScanner->getCh(),         '::getCh() ' . $this->stringScanner->inspect());
    }

    /**
     * Testing reset and terminate methods
     *
     * @depends testSetAndScanString
     */
    public function testTerminateAndReset()
    {
        $this->stringScanner->setString('test string');
        $this->stringScanner->terminate();
        $this->assertEquals(11, $this->stringScanner->getPos(), '::terminate() ' . $this->stringScanner->inspect());

        $this->stringScanner->reset();
        $this->assertEquals(0, $this->stringScanner->getPos(), '::reset() ' . $this->stringScanner->inspect());

    }

    /**
     * test some string methods
     *
     * @depends testScan
     * @expectedException \Exception
     */
    public function testString()
    {

        $this->stringScanner->setString('Fri Dec 12 1975 14:39');
        $this->stringScanner->scan('/Fri /');
        $this->stringScanner->concat(' +1000 GMT');
        $this->assertEquals("Fri Dec 12 1975 14:39 +1000 GMT", $this->stringScanner->getString(), '::concat() ' . $this->stringScanner->inspect());
        $this->assertEquals("Dec", $this->stringScanner->scan('/Dec/'), '::concat() ' . $this->stringScanner->inspect());


        $this->stringScanner->setString('test string');
        $this->assertEquals("test", $this->stringScanner->scan('/\w+/'), '::unscan() ' . $this->stringScanner->inspect());
        $this->stringScanner->unscan();
        $this->assertEquals("te",   $this->stringScanner->scan('/../'),  '::unscan() ' . $this->stringScanner->inspect());
        $this->assertNull($this->stringScanner->scan('/\d/'),            '::unscan() ' . $this->stringScanner->inspect());
        $this->stringScanner->unscan();

    }

    /**
     * some positions method
     *
     * @depends testTerminateAndReset
     */
    public function testPosition()
    {

        $this->stringScanner->setString("test\ntest\n");
        $this->assertTrue($this->stringScanner->bol(), '::bol() ' . $this->stringScanner->inspect());
        $this->stringScanner->scan('/te/');
        $this->assertFalse($this->stringScanner->bol(),'::bol() ' . $this->stringScanner->inspect());
        $this->stringScanner->scan('/st\n/');
        $this->assertTrue($this->stringScanner->bol(), '::bol() ' . $this->stringScanner->inspect());
        $this->stringScanner->terminate();
        $this->assertTrue($this->stringScanner->bol(), '::bol() ' . $this->stringScanner->inspect());


        $this->stringScanner->setString("test string");

        $this->assertFalse($this->stringScanner->eos(), '::eos() ' . $this->stringScanner->inspect());
        $this->stringScanner->scan('/test/');
        $this->assertFalse($this->stringScanner->eos(), '::eos() ' . $this->stringScanner->inspect());
        $this->stringScanner->terminate();
        $this->assertTrue($this->stringScanner->eos(),  '::eos() ' . $this->stringScanner->inspect());


        $this->stringScanner->reset();
        $this->stringScanner->scan('/test/');
        $this->assertEquals(' string',  $this->stringScanner->rest(),     '::rest(), rest of string should be " string" after scan of "test"');
        $this->assertEquals(7,          $this->stringScanner->restSize(), '::rest(), size should be 7 from length of string " string"');


        $this->stringScanner->reset();
        $this->assertEquals(0,   $this->stringScanner->getPos(),   '::getPos() ' . $this->stringScanner->inspect());
        $this->stringScanner->scanUntil('/str/');
        $this->assertEquals(8,   $this->stringScanner->getPos(),   '::getPos() ' . $this->stringScanner->inspect());
        $this->stringScanner->terminate();
        $this->assertEquals(11,  $this->stringScanner->getPos(),   '::getPos() ' . $this->stringScanner->inspect());
        $this->assertEquals(3,   $this->stringScanner->setPos(3),  '::getPos() ' . $this->stringScanner->inspect());


        $this->stringScanner->reset();
        $this->stringScanner->setPos(7);
        $this->assertEquals("ring",  $this->stringScanner->rest(), '::setPos() ' . $this->stringScanner->inspect());


    }
}