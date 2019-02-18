<?php

use CsvFileHandler\CsvFileHandler;

class HeadersTest extends \PHPUnit\Framework\TestCase
{
    private const SMALL_TEST_EXISTING_HEADERS = [
        'id',
        'first_name',
        'last_name',
        'email',
        'gender',
        'ip_address'
    ];

    /**
     * @var CsvFileHandler
     */
    private $csvFileHandler;

    public function setUp()
    {
        $this->csvFileHandler = new CsvFileHandler(__DIR__ . DIRECTORY_SEPARATOR .
            'resources' . DIRECTORY_SEPARATOR . 'small_test.csv');
    }

    public function testHasHeadersSuccessWithSixHeaders() : void
    {
        foreach (self::SMALL_TEST_EXISTING_HEADERS as $header) {
            $this->assertTrue($this->csvFileHandler->hasHeader($header));
        }
    }

    public function testHasHeadersFailureWithOneHeader() : void
    {
        $this->assertFalse($this->csvFileHandler->hasHeader('non_existent'));
    }
}