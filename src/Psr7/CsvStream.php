<?php
/**
 * Created by PhpStorm.
 * User: alex.boyce
 * Date: 10/26/18
 * Time: 11:18 AM
 */

namespace AE\SalesforceRestSdk\Psr7;

use Psr\Http\Message\StreamInterface;

class CsvStream implements StreamInterface
{
    private $stream;

    public function __construct(StreamInterface $stream)
    {
        $this->stream = $stream;
    }

    public function __toString()
    {
        return $this->stream->__toString();
    }

    public function close()
    {
        $this->stream->close();
    }

    public function detach()
    {
        return $this->stream->detach();
    }

    public function getSize()
    {
        return $this->stream->getSize();
    }

    public function tell()
    {
        return $this->stream->tell();
    }

    public function eof()
    {
        return $this->stream->eof();
    }

    public function isSeekable()
    {
        return $this->stream->isSeekable();
    }

    public function seek($offset, $whence = SEEK_SET)
    {
        return $this->stream->seek($offset, $whence);
    }

    public function rewind()
    {
        $this->stream->rewind();
    }

    public function isWritable()
    {
        return $this->stream->isWritable();
    }

    /**
     * @param string|array $string
     * @param string $delimiter
     * @param string $enclosure
     * @param string $escapeChar
     *
     * @return int
     */
    public function write($string, $delimiter = ',', $enclosure = '"', $escapeChar = "\\")
    {
        if (!$this->isWritable()) {
            throw new \RuntimeException("The stream is not writable.");
        }

        if (is_array($string)) {
            $arr = array_values($string);
            $res = tmpfile();

            if (!$res) {
                throw new \RuntimeException("The stream is not writable.");
            }

            fputcsv($res, $arr, $delimiter, $enclosure, $escapeChar);
            rewind($res);
            $string = stream_get_contents($res);
            fclose($res);
        }

        return $this->stream->write($string);
    }

    public function isReadable()
    {
        return $this->stream->isReadable();
    }

    /**
     * Similar to \GuzzleHttp\Psr7\readline, but with some helper flags
     *
     * @param StreamInterface $stream
     * @param int|null $maxLength
     * @param string $enclosure
     * @param string $escape
     *
     * @return string
     */
    public static function readline(
        StreamInterface $stream,
        ?int $maxLength = null,
        string $enclosure = '"',
        string $escape = "\\"
    ) {
        $buffer  = '';
        $size    = 0;
        $encFlag = false;
        $escFlag = false;

        if ($stream instanceof CsvStream) {
            throw new \InvalidArgumentException("Cannot use CsvStream::readline on a CsvStream.");
        }

        while (!$stream->eof()) {
            if (null == ($byte = $stream->read(1))) {
                return $buffer;
            }

            $buffer .= $byte;

            if ($byte === $enclosure) {
                $encFlag = !$encFlag;
            }

            if ((!$encFlag && !$escFlag && $byte === "\n") || ++$size === $maxLength - 1) {
                break;
            }

            $escFlag = $byte === $escape && !$escFlag;
        }

        $buffer = rtrim($buffer, "\n");

        if (strlen($buffer) === 0) {
            return false;
        }

        return $buffer;
    }

    public function read($length = 0, string $delimiter = ",", string $enclosure = '"', string $escape = "\\")
    {
        if ($length < 0) {
            throw new \RuntimeException("The length cannot be a negative number,");
        }

        $line = self::readline($this->stream, $length > 0 ? $length : null, $enclosure, $escape);

        if (!$line) {
            return false;
        }

        return str_getcsv($line, $delimiter, $enclosure, $escape);
    }

    /**
     * @param bool $hasHeaders
     * @param string $delimiter
     * @param string $enclosure
     * @param string $escape
     *
     * Call to get the next row as a keyed array with header values (if $hasHeader == true), if there are no more rows,
     * the function will return null
     *
     * @return \Generator|array|boolean
     */
    public function getContents(
        bool $hasHeaders = true,
        string $delimiter = ",",
        string $enclosure = '"',
        string $escape = "\\"
    ) {
        $headers = [];

        if ($hasHeaders) {
            $headers = $this->read(0, $delimiter, $enclosure, $escape);
        }

        while (false !== ($row = $this->read(0, $delimiter, $enclosure, $escape))
            && (count($row) > 0
                && (count($row) > 1 || null !== $row[0]))
        ) {
            yield $hasHeaders ? array_combine($headers, $row) : $row;
        }
    }

    public function getMetadata($key = null)
    {
        return $this->stream->getMetadata($key);
    }
}
