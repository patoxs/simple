<?php

namespace Tests\Feature;

use Tests\TestCase;
use DB;
use Mockery;

class DatabaseTestCase extends TestCase
{
    /**
     * @inheritdoc
     */
    public function setUp()
    {
        parent::setUp();
        DB::beginTransaction();
    }

    /**
     * @inheritdoc
     */
    public function tearDown()
    {
        DB::rollBack();
        DB::disconnect();
        Mockery::close();
        parent::tearDown();
    }

}
