<?php
/**
 * Created by PhpStorm.
 * User: alex.boyce
 * Date: 10/26/18
 * Time: 11:18 AM
 */

namespace AE\SalesforceRestSdk\Psr7;

use Psr\Http\Message\StreamInterface;

/**
 * CSV reader/writer wrapping a PSR-7 stream.
 *
 * It does not implement StreamInterface: read() returns a parsed row and getContents() a row generator,
 * which psr/http-message 2.0's string return types do not allow.
 */
class CsvStream
{
    private StreamInterface $stream;

    public function __construct(StreamInterface $stream)
    {
        $this->stream = $stream;
    }

    public function __toString(): string
    {
        return $this->stream->__toString();
    }

    public function close(): void
    {
        $this->stream->close();
    }

    public function detach()
    {
        return $this->stream->detach();
    }

    public function getSize(): ?int
    {
        return $this->stream->getSize();
    }

    public function tell(): int
    {
        return $this->stream->tell();
    }

    public function eof(): bool
    {
        return $this->stream->eof();
    }

    public function isSeekable(): bool
    {
        return $this->stream->isSeekable();
    }

    public function seek(int $offset, int $whence = SEEK_SET): void
    {
        $this->stream->seek($offset, $whence);
    }

    public function rewind(): void
    {
        $this->stream->rewind();
    }

    public function isWritable(): bool
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
    public function write($string, string $delimiter = ',', string $enclosure = '"', string $escapeChar = "\\"): int
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

    public function isReadable(): bool
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

    public function getMetadata(?string $key = null)
    {
        return $this->stream->getMetadata($key);
    }
}
