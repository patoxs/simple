<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VerifyLibrariesAvailableTest extends TestCase
{
    /**
     * A basic test check if the necessary libraries are installed. (php -m || php -me)
     *
     * @return void
     */
    public function testAvailableLibraries()
    {
        $libraries = [
            'xml',
            'soap',
            'mbstring',
            'mcrypt',
            'curl'
        ];

        foreach ($libraries as $library) {
            $this->assertTrue(extension_loaded($library), "{$library} library, not loaded in php.");
        }
    }
}
