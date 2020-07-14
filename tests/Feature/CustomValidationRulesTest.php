<?php

namespace Tests\Feature;

use Tests\TestCase;

class CustomValidationRulesTest extends TestCase
{
    /**
     * @test validate rut, format: run-dv
     */
    public function testValidateRut()
    {
        $rules = [
            'field1' => 'rut'
        ];

        $data = [
            'field1' => '18765525-0',
        ];

        $v = $this->app['validator']->make($data, $rules);
        $this->assertTrue($v->passes());
    }

}
