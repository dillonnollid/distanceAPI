<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DistanceControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_example()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    /** @test */
    public function calculates_distance_between_two_coordinates()
    {
        //Simulate a POST request to the API endpoint
        $response = $this->postJson('/api/calculate-distance', ['latitudeFrom' => 53.801140,'longitudeFrom' => -9.522290,'latitudeTo' => 53.854160,'longitudeTo' => -9.301740]);

        //Assert that the request was successful (HTTP status code 200)
        $response->assertStatus(200);

        //Assert that the response contains the 'distance' key
        $response->assertJsonStructure(['distance']);

    }
}
