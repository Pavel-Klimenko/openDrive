<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\GlobalFunctions\Helper;


/**
 * @backupGlobals enabled
 */
class HelperTest extends TestCase
{
    private $serverDocumentRoot;

    protected function setUp(): void
    {
        $this->serverDocumentRoot = '/var/www/openDrive/public';
    }

    public function testPercentOfTotal()
    {
        $percent = Helper::getPercentOfTotal(2, 10);
        $this->assertIsNumeric($percent);
        $this->assertEquals(20, $percent);
    }

    public function testDirectorySize()
    {
        $directorySize = Helper::getDirectorySize($this->serverDocumentRoot.'/test/testingSize/');
        $this->assertEquals(53, $directorySize);
    }
}