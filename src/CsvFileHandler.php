<?php

namespace CsvFileHandler;

class CsvFileHandler
{
    /**
     * @var string
     */
    protected $filename;

    /**
     * @var array
     */
    protected $records = [];

    /**
     * @var bool
     */
    protected $headerRow;

    /**
     * @var string
     */
    protected $delimiter = ',';

    /**
     * @var string
     */
    protected $enclosure = '"';

    /**
     * @var string
     */
    protected $escape = '\\';

    /**
     * @var resource|null
     */
    protected $filePointer = null;

    /**
     * @var int
     */
    protected $recordIndex = 0;

    /**
     * @var array
     */
    protected $headers = [];

    /**
     * Specify file for loading. Configuration settings optional
     * @param $filename
     * @param boolean $headerRow
     * @param string $delimiter
     * @param string $enclosure
     * @param string $escape
     * @param bool $autoParse
     * @throws \Exception
     */
    public function __construct(
        $filename,
        $headerRow = true,
        $delimiter = ',',
        $enclosure = '"',
        $escape = '\\',
        $autoParse = true
    ) {
        if (!file_exists($filename)) {
            throw new \Exception($filename . " not found");
        }

        $this->setHeaderRow($headerRow);

        if ($delimiter != $this->getDelimiter()) {
            $this->setDelimiter($delimiter);
        }

        if ($enclosure != $this->getEnclosure()) {
            $this->setEnclosure($enclosure);
        }

        if ($escape != $this->getEscape()) {
            $this->setEscape($escape);
        }

        $this->setFilename($filename);

        $this->loadFilePointer();
        $this->processHeaders();

        if ($autoParse) {
            $this->parseFile();
        }
    }

    /**
     * @return boolean
     */
    public function getHeaderRow()
    {
        return $this->headerRow;
    }

    /**
     * @param boolean $headerRow
     */
    public function setHeaderRow($headerRow)
    {
        $this->headerRow = $headerRow;
    }

    /**
     * Get field delimiter
     * @return string
     */
    public function getDelimiter()
    {
        return $this->delimiter;
    }

    /**
     * Set field delimiter value (One character only)
     * @param string $delimiter
     * @throws \Exception
     */
    public function setDelimiter($delimiter)
    {
        if (1 != strlen($delimiter)) {
            throw new \Exception("One character only for delimiter");
        }

        $this->delimiter = $delimiter;
    }

    /**
     * Get enclosure character
     * @return string
     */
    public function getEnclosure()
    {
        return $this->enclosure;
    }

    /**
     * Set field enclosure value (One character only)
     * @param string $enclosure
     * @throws \Exception
     */
    public function setEnclosure($enclosure)
    {
        if (1 != strlen($enclosure)) {
            throw new \Exception("One character only for enclosure");
        }

        $this->enclosure = $enclosure;
    }

    /**
     * Get escape character
     * @return string
     */
    public function getEscape()
    {
        return $this->escape;
    }

    /**
     * Set escape character (One character only)
     * @param string $escape
     * @throws \Exception
     */
    public function setEscape($escape)
    {
        if (1 != strlen($escape)) {
            throw new \Exception("One character only for escape");
        }
        $this->escape = $escape;
    }

    /**
     * Get filename as specified in constructor
     * @return String
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Set filename for parsing
     * @param String $filename
     */
    protected function setFilename($filename)
    {
        $this->filename = $filename;
    }

    /**
     * Get array of RecordObject representing lines in the file
     * @return Array
     */
    public function getRecords()
    {
        return $this->records;
    }

    /**
     * Add RecordObject to array representative of each line in the file
     * @param RecordObject $record
     */
    public function addRecord(RecordObject $record)
    {
        $this->records[] = $record;
    }

    private function loadFilePointer()
    {
        $fp = fopen($this->getFilename(), 'r');
        $this->setFilePointer($fp);
    }

    /**
     * @return null
     */
    private function getFilePointer()
    {
        return $this->filePointer;
    }

    /**
     * @param $filePointer
     */
    public function setFilePointer($filePointer)
    {
        $this->filePointer = $filePointer;
    }

    public function incrementRecordIndex()
    {
        $this->recordIndex += 1;
    }

    /**
     * @return int
     */
    public function getRecordIndex()
    {
        return $this->recordIndex;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;
    }

    private function processHeaders()
    {
        $headers = null;

        if ($this->getHeaderRow()) {
            $headers = fgetcsv($this->getFilePointer(), null, $this->getDelimiter(), $this->getEnclosure(),
                $this->getEscape());
        }

        $this->setHeaders($headers);
    }

    /**
     * Load file into array and convert each line to RecordObject
     */
    public function parseFile()
    {
        while (($values = fgetcsv($this->getFilePointer(), null, $this->getDelimiter(), $this->getEnclosure(),
                $this->getEscape())) !== false) {
            if (is_null($values)) {
                continue;
            }

            $record = new RecordObject($this->getHeaders(), $values);
            $this->addRecord($record);
        }

        fclose($this->getFilePointer());
    }

    public function readRecord()
    {
        if (feof($this->getFilePointer())) {
            return false;
        }

        $values = fgetcsv($this->getFilePointer(), null, $this->getDelimiter(), $this->getEnclosure(),
            $this->getEscape());

        $this->incrementRecordIndex();

        if (empty($values)) {
            return null;
        }

        return new RecordObject($this->getHeaders(), $values);
    }

    public function closeFile()
    {
        fclose($this->getFilePointer());
    }

    /**
     * @param $header
     * @return bool
     */
    public function hasHeader($header)
    {
        $result = array_filter($this->getHeaders(),
            function ($header_column) use (&$header) {
                return $header_column === $header;
            }
        );

        return count($result) > 0;
    }
}
