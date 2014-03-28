<?php
/**
 * @author    Philip Bergman <pbergman@live.nl>
 * @copyright Philip Bergman
 */
namespace StringScanner;

/**
 * a port from ruby StringScanner
 *
 * @url http://docs.ruby-lang.org/en/2.1.0/StringScanner.html
 * @url https://github.com/rubysl/rubysl-strscan/blob/2.0/lib/rubysl/strscan/strscan.rb
 *
 * Class StringScanner
 */
class StringScanner
{
    /** @var string  */
    private $string;
    /** @var null|array  */
    private $matched  = null;
    /** @var int  */
    private $pos = 0;
    /** @var int  */
    private $prevPos = 0;

    /**
     * constructor
     *
     * @param string $string
     */
    public function __construct($string = '')
    {
        $this->string = $string;
    }

    /**
     * This returns the value that scan would return,
     * without advancing the scan pointer.
     *
     * The match register is affected, though.
     *
     *  it “checks” to see whether a scan will return a value.
     *
     * @param   string  $pattern
     *
     * @return  int|null
     */
    public function check($pattern)
    {
        return $this->process($pattern, false, true);
    }

    /**
     * This returns the value that scan_until would return,
     * without advancing the scan pointer.
     *
     * The match register is affected, though.
     *
     *  it “checks” to see whether a scanUntil will return a value.
     *
     * @param   string  $pattern
     *
     * @return  int|null
     */
    public function checkUntil($pattern)
    {
        return $this->process($pattern, false, true, false);
    }

    /**
     * Set the scan pointer to the end of the string and clear matching data
     */
    public function terminate()
    {
        $this->matched = null;
        $this->pos     = $this->strlen($this->string);
    }

    /**
     * Appends str to the string being scanned.
     * This method does not affect scan pointer.
     *
     * @param string $string
     */
    public function concat($string)
    {
        $this->string .= $string;
    }

    /**
     * Returns true if the scan pointer is at the end of the string.
     *
     * @return bool
     */
    public function eos()
    {
        return ($this->strlen($this->string) <= $this->pos);
    }

    /**
     * Returns true iff the scan pointer is at the beginning of the line.
     *
     * @return bool
     */
    public function bol()
    {
        return ($this->pos == 0 || ord($this->string[$this->pos - 1]) == 10 );
    }

    /**
     * Looks ahead to see if the pattern exists anywhere in the string,
     * without advancing the scan pointer. This predicates whether a
     * scan_until will return a value.
     *
     * @param   string  $pattern
     *
     * @return  int|null
     */
    public function exists($pattern)
    {
        return $this->process($pattern, false, false, false);
    }

    /**
     * Scans one byte and returns it.
     *
     * This method is not multibyte character sensitive.
     *
     * See also: getch.
     *
     * @return string
     */
    public function getByte()
    {
        $return = null;

        if ($this->pos < $this->strlen($this->string)) {
            $return = pack("C*", unpack("C*", $this->substr($this->string, $this->pos, 1))[1]);
            $this->matched = array(
                0 => $return,
                1 => 1,
            );
            $this->pos++;
        }

        return $return;
    }

    /**
     * Scans one character and returns it.
     *
     * This method is multibyte character sensitive.
     *
     * @return int|null
     */
    public function getCh()
    {
        return $this->scan('/./su');
    }

    /**
     * Set the scan pointer to the previous position.
     * Only one previous position is remembered, and
     * it changes with each scanning operation.
     *
     * @throws \Exception
     */
    public function unscan()
    {
        if (empty($this->matched)) {
            throw new \Exception("ScanError: unscan failed: previous match record not exist");
        }

        $this->pos     = $this->prevPos;
        $this->prevPos = 0;
        $this->matched = null;
    }

    /**
     *
     * Extracts a string corresponding to string[pos,len],
     * without advancing the scan pointer.
     *
     * @param   int $len
     *
     * @return  string
     *
     * @throws  \RangeException
     */
    public function peek($len)
    {
        if ($len > 0 && $len > ($this->strlen($this->string) - $this->pos)) {
            throw new \RangeException("offset outside of possible range");
        }

        return $this->substr($this->string, $this->pos, $len);
    }

    /**
     * Returns the string being scanned.
     *
     * @return string
     */
    public function getString()
    {
        return $this->string;
    }

    /**
     * Changes the string being scanned to str and resets the scanner. Returns str.
     *
     * @param   string  $string
     * @return  mixed
     */
    public function setString($string)
    {
        $this->string = $string;

        return $this->string;
    }

    /**
     * Returns the byte position of the scan pointer.
     * In the ‘reset’ position, this value is zero.
     * In the ‘terminated’ position (i.e. the string
     * is exhausted), this value is the bytesize of
     * the string.
     *
     * In short, it’s a 0-based index into the string.
     *
     * @return int
     */
    public function getPos()
    {
        return $this->pos;
    }

    /**
     * will set pos
     *
     * @param   $pos
     *
     * @return  mixed
     */
    public function setPos($pos)
    {
        return $this->pos = $pos;
    }

    public function reset()
    {
        $this->pos     = 0;
        $this->prevPos = 0;
        $this->matched = null;
    }

    public function inspect()
    {
        if ($this->eos()) {
            $return = "#<StringScanner eos>";
        } else {
            if (($this->strlen($this->string) - $this->pos) > 5) {
                $rest = sprintf('%s...', $this->substr($this->string, $this->pos, 5));
            } else {
                $rest = $this->substr($this->string, $this->pos, $this->strlen($this->string) );
            }

            if ($this->pos > 0) {

                if ($this->pos > 5) {
                    $prev = sprintf('...%s', $this->substr($this->string, ($this->pos - 5), 5));
                } else {
                    $prev = $this->substr($this->string, 0, $this->pos );
                }

                $return = sprintf('#<StringScanner %s/%s "%s" @ "%s">', $this->pos, $this->strlen($this->string), $prev, $rest);

            } else {
                $return = sprintf('#<StringScanner %s/%s @ "%s">', $this->pos, $this->strlen($this->string), $rest);
            }
        }

        return $return;
    }

    /**
     * Returns the “rest” of the string (i.e. everything after the scan pointer).
     * If there is no more data (eos? = true), it returns ""
     *
     * @return string
     */
    public function rest()
    {
        if (false === $return = $this->substr($this->string, $this->pos)) {
            $return = '';
        }

        return $return;
    }

    /**
     * get size of rest()
     *
     * @return mixed
     */
    public function restSize()
    {
        return $this->strlen($this->rest());
    }

    /**
     * Tests whether the given pattern is matched from the current scan pointer.
     * Returns the length of the match, or null. The scan pointer is not advanced
     *
     * @param  string   $pattern
     * @return int|null
     */
    public function match($pattern)
    {
        return $this->process($pattern, false, false);

    }

    /**
     * Returns the last matched string.
     *
     * @param   int     $index
     * @return  null
     */
    public function matched($index = 0)
    {
        $return = null;

        if (!is_null($this->matched) && isset($this->matched[$index])) {
           $return =  $this->matched[$index][0];
        }

        return $return;
    }

    /**
     * Returns true if the last match was successful.
     *
     * @return bool
     */
    public function isMatched()
    {
        return !empty($this->matched);
    }

    /**
     * Returns the size of the most recent match (see matched),
     * or null if there was no recent match.
     *
     *
     * @return int|null
     */
    public function matchedSize()
    {
        $return = null;

        if (!empty($this->matched) && is_array($this->matched)) {
            $return =  $this->strlen($this->matched[0][0]);
        }

        return $return;
    }

    /**
     * Return the post-match (in the regular expression sense) of the last scan.
     */
    public function postMatch()
    {
        if (empty($this->matched) || false === $return = $this->substr($this->string, $this->strlen($this->matched[0][0]) + $this->matched[0][1])) {
            $return = '';
        }

        return $return;
    }

    /**
     * Return the pre-match (in the regular expression sense) of the last scan.
     *
     * @return string
     */
    public function preMatch()
    {
        if (empty($this->matched) || false === $return = $this->substr($this->string, 0, $this->pos - $this->strlen($this->matched[0][0]) )) {
            $return = '';
        }

        return $return;
    }

    /**
     * Tries to match with pattern at the current position. If there’s
     * a match, the scanner advances the “scan pointer” and returns the
     * matched string. Otherwise, the scanner returns null.
     *
     * @param   string  $pattern
     *
     * @return  int|null
     */
    public function scan($pattern)
    {
        return $this->process($pattern, true, true, true);
    }

    /**
     * Scans the string until the pattern is matched. Returns
     * the substring up to and including the end of the match,
     * advancing the scan pointer to that location.
     *
     * If there is no match, null is returned.
     *
     * @param  string   $pattern
     *
     * @return int|null
     */
    public function scanUntil($pattern)
    {
        return $this->process($pattern, true, true, false);
    }

    /**
     * Tests whether the given pattern is matched from the current scan
     * pointer.Advances the scan pointer if advance_pointer_p is true.
     * Returns the matched string if return_string_p is true. The match
     * register is affected. “full” means “#scan with full parameters”.
     *
     * @param string    $pattern
     * @param bool      $advancePosition
     * @param bool      $getString
     *
     * @return int|null
     */
    public function scanFull($pattern, $advancePosition, $getString)
    {
        return $this->process($pattern, $advancePosition, $getString);
    }

    /**
     * does a full search on string and discarding position
     *
     * @param string    $pattern
     * @param bool      $advancePosition
     * @param bool      $getString
     *
     * @return int|null
     */
    public function searchFull($pattern, $advancePosition, $getString)
    {
        return $this->process($pattern, $advancePosition, $getString, false);
    }

    /**
     * Attempts to skip over the given pattern beginning with the scan pointer.
     * If it matches, the scan pointer is advanced to the end of the match,
     * and the length of the match is returned. Otherwise, nil is returned.
     *
     * It’s similar to scan, but without returning the matched string.
     *
     * @param   string  $pattern
     *
     * @return  int|null
     */
    public function skip($pattern)
    {
        return $this->process($pattern, true, false);
    }

    /**
     * Advances the scan pointer until pattern is matched and consumed.
     * Returns the number of bytes advanced, or nil if no match was found.
     *
     * Look ahead to match pattern, and advance the scan pointer to the end of the
     * match. Return the number of characters advanced, or nil if the match was unsuccessful.
     *
      * It’s similar to scanUntil, but without returning the intervening string.
     *
     * @param   string  $pattern
     *
     * @return  int|null
     */
    public function skipUntil($pattern)
    {
        return $this->process($pattern, true, false, false);
    }

    /**
     * internal string processor
     *
     * @param   string    $pattern
     * @param   bool      $advancePosition
     * @param   bool      $getString
     * @param   bool      $headOnly
     *
     * @throws  \Exception
     * @return  int|null
     */
    protected function process($pattern, $advancePosition = false, $getString = false, $headOnly = true)
    {
        $return = null;

        // Check for valid pattern
        if (@preg_match($pattern, null) === false) {
            throw new \Exception(sprintf('"%s" is not a valid PREG pattern', $pattern));
        }

        $this->matched = null;

        // Add A (PCRE_ANCHORED) so wee look from start
        if ($headOnly) {
            if($pattern[1] !=  '^' ) {
                $pattern .= 'A';
            }
        }

        preg_match($pattern, $this->string, $this->matched, PREG_OFFSET_CAPTURE, $this->pos);

        if ($this->isMatched() === true) {

            $toMove   = ($this->matched[0][1] - $this->pos) + $this->strlen($this->matched[0][0]);

            $this->prevPos = $this->pos;

            if ($advancePosition === true){
                $this->pos += $toMove;
            }

            if ($headOnly) {
                $matchedString = $this->matched[0][0];
            } else {
                $preMatch      = $this->substr($this->string, $this->prevPos, $this->matched[0][1]);
                $matchedString = $preMatch . $this->matched[0][0];
            }

            $return = ($getString === true) ? $matchedString : $this->strlen($this->matched[0][0]);

        }

        return $return;
    }


    /**
     * will try return a mb_strlen if
     * possible else return normal strlen
     *
     * @param   string  $string
     * @return  int|null
     */
    protected function strlen($string)
    {
        $return = null;

        if (function_exists('mb_strlen')) {

            if (false !== $encoding = mb_detect_encoding($string)) {
                $return = @mb_strlen($string, $encoding);
            }

        }

        if (is_null($string)) {
            $return = @strlen($string);
        }

        return $return;
    }

    /**
     * get substring of given string
     *
     * @param string    $string
     * @param int       $start
     * @param null|int  $length
     *
     * @return string
     */
    protected function substr($string, $start, $length = null)
    {
        $return = null;

        if (function_exists('mb_substr')) {

            if (false !== $encoding = mb_detect_encoding($string)) {
                $return = @mb_substr($string, $start, $length, $encoding);
            }

        }

        if (is_null($return)) {
            $return = @substr($string, $start, $length);
        }

        return $return;
    }
}
