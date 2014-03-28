<?php
/**
 * @author    Philip Bergman <pbergman@live.nl>
 * @copyright Philip Bergman
 */
require_once '../../../vendor/autoload.php';

use StringScanner\StringScanner;

class StringScannerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * testing methods:
     *  match
     *  matched
     *  matchedSize
     *  isMatched
     *  preMatch
     *  postMatch
     *  scan
     */
    public function testMatch()
    {
        $string = new StringScanner('test string');

        $this->assertEquals(4,      $string->match('/\w+/'),    '::match(), should return 4 for "\w+" on string "test string"');
        $this->assertEquals('test', $string->matched(),         '::matched() returns last match value (test)');
        $this->assertEquals(4,      $string->matchedSize(),     '::matchedSize(), should return 4 for the last matched size');
        $this->assertTrue($string->isMatched(),                 '::isMatched() return true when we got a match');

        $this->assertEquals(null,   $string->match('/\d+/'),    '::match(), should return null because there are no numbers after "test" in "test string"');
        $this->assertFalse($string->isMatched(),                '::isMatched() return false because we do not have a match');
        $this->assertEquals(null,   $string->matchedSize(),     '::matchedSize(), should return NULL for the last matched size');

        $this->assertEquals('test',   $string->scan('/\w+/'),   '::scan(), should return "test" for scan "\w+" on "test string"');
        $this->assertEquals(' ' ,     $string->scan('/\s+/'),   '::scan(), should return " " for "\s+" on (sub)string " string"');
        $this->assertEquals('test',   $string->preMatch(),      '::preMatch()  should return "test" because last match was " " ');
        $this->assertEquals('string', $string->postMatch(),     '::postMatch() should return "string" because last match was " " ');

    }

    /**
     * testing methods:
     *  bol
     *  eos
     *  rest
     *  pos
     */
    public function testFindingWhereWeAre()
    {

        $string = new StringScanner("test\ntest\n");
        $this->assertTrue($string->bol(),               '::bol() ' . $string->inspect());
        $string->scan('/te/');
        $this->assertFalse($string->bol(),              '::bol() ' . $string->inspect());
        $string->scan('/st\n/');
        $this->assertTrue($string->bol(),               '::bol() ' . $string->inspect());
        $string->terminate();
        $this->assertTrue($string->bol(),               '::bol() ' . $string->inspect());


        $string = new StringScanner("test string");

        $this->assertFalse($string->eos(),              '::eos() ' . $string->inspect());
        $string->scan('/test/');
        $this->assertFalse($string->eos(),              '::eos() ' . $string->inspect());
        $string->terminate();
        $this->assertTrue($string->eos(),               '::eos() ' .  $string->inspect());


        $string->reset();
        $string->scan('/test/');
        $this->assertEquals(' string',  $string->rest(),     '::rest(), rest of string should be " string" after scan of "test"');
        $this->assertEquals(7,          $string->restSize(), '::rest(), size should be 7 from length of string " string"');


        $string->reset();
        $this->assertEquals(0,   $string->getPos(),   '::pos() ' . $string->inspect());
        $string->scanUntil('/str/');
        $this->assertEquals(8,   $string->getPos(),   '::pos() ' . $string->inspect());
        $string->terminate();
        $this->assertEquals(11,  $string->getPos(),   '::pos() ' . $string->inspect());

        $string->reset();
        $string->setPos(7);
        $this->assertEquals("ring",  $string->rest(),  '::pos() ' . $string->inspect());


    }
}