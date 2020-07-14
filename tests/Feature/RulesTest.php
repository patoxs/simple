<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Support\Facades\Auth;

class RulesTest extends DatabaseTestCase
{

    /**
     * @test, instance a send form and evaluate the @@rule.
     */
    public function testFormRules()
    {
        //params
        $parameters = [
            '_token' => csrf_token(),
            'name' => 'Jhon Doe'
        ];

        //Send post
        $response = $this->call('POST', 'questions', $parameters);

        //rule to evaluate
        $rule = "@@name";

        //extract value from rule
        $nameData = preg_replace_callback('/@@(\w+)((->\w+|\[\w+\])*)/', function ($match) {
            return $match[1];
        }, $rule);

        //check if you can access the parameter sent
        $this->assertTrue(($this->app->request->{$nameData} == $parameters[$nameData]));
    }

    /**
     * @test, instance a factory user to then evaluate the @!rules stored in session.
     */
    public function testSessionRules()
    {
        //User Test
        $user = [
            'rut' => '18765525-0',
            'nombres' => 'test',
            'apellido_paterno' => 'test',
            'apellido_materno' => 'test',
            'email' => 'test@example.cl'
        ];

        //rules
        $rules = [
            '@!rut',
            '@!nombre',
            '@!nombres',
            '@!apellidos',
            '@!apellido_paterno',
            '@!apellido_materno',
            '@!email'
        ];

        //Fctory user
        $userFactory = factory(User::class)->create($user);

        //Login user
        Auth::login($userFactory);

        foreach ($rules as $rule) {

            //extract value from rule
            $nameData = preg_replace_callback('/@!(\w+)/', function ($match) {
                return $match[1];
            }, $rule);


            //this statement check if you can access the parameter Auth.
            if ($nameData == 'nombre') { //Custom

                $this->assertTrue((Auth::user()->nombres == $user['nombres']));

            } else if ($nameData == 'apellidos') { //Custom

                $lastNameFactory = Auth::user()->apellido_paterno . ' ' . Auth::user()->apellido_materno;
                $lastName = $user['apellido_paterno'] . ' ' . $user['apellido_materno'];
                $this->assertTrue(($lastName == $lastNameFactory));

            } else {

                $this->assertTrue((Auth::user()->{$nameData} == $user[$nameData]));

            }

        }

    }
}
