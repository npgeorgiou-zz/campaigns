<?php

namespace Tests\Feature;

use App\Models\Campaign;
use Illuminate\Http\Response;
use Tests\TestCase;


class ExampleTest extends TestCase {
    function test_user_can_create_campaign() {
        $response = $this->post('api/campaigns/create', [
            'user_email' => 'foo@bar.baz',
            'name' => 'foo',
            'source' => 'bar',
            'channel' => 'baz',
            'target_url' => 'qux',
        ]);

        // Assert response
        $this->assertEquals(Response::HTTP_OK, $response->status());

        $content = json_decode($response->getContent());
        $this->assertIsArray($content->inputs);
        $this->assertEquals([
            (object)[
                'type' => 'name',
                'value' => 'foo'
            ], (object)[
                'type' => 'source',
                'value' => 'bar'
            ], (object)[
                'type' => 'channel',
                'value' => 'baz'
            ], (object)[
                'type' => 'target_url',
                'value' => 'qux'
            ],
        ], $content->inputs);

        // Assert DB
        $this->assertCount(1, Campaign::all());
    }

    function test_user_cant_create_campaign_when_missing_input() {
        $response = $this->post('api/campaigns/create', [
            'user_email' => 'foo@bar.baz',
            'source' => 'bar',
            'channel' => 'baz',
            'target_url' => 'qux',
        ]);

        // Assert response
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->status());

        // Assert DB
        $this->assertCount(0, Campaign::all());
    }

    function test_user_cant_create_campaign_witn_nonexisting_user() {
        $response = $this->post('api/campaigns/create', [
            'user_email' => 'imaginary@user.com',
            'source' => 'bar',
            'channel' => 'baz',
            'target_url' => 'qux',
        ]);

        // Assert response
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->status());

        // Assert DB
        $this->assertCount(0, Campaign::all());
    }

    function test_user_cant_create_campaign_witn_same_inputs_as_existing_campaign() {
        $inputs = [
            'name' => 'foo',
            'source' => 'bar',
            'channel' => 'baz',
            'target_url' => 'qux',
        ];

        $response = $this->post('api/campaigns/create', array_merge(['user_email' => 'foo@bar.baz'], $inputs));
        $this->assertEquals(Response::HTTP_OK, $response->status());
        $this->assertCount(1, Campaign::all());

        $response = $this->post('api/campaigns/create', array_merge(['user_email' => 'foo@bar.baz'], $inputs));
        $this->assertEquals(Response::HTTP_CONFLICT, $response->status());
        $this->assertCount(1, Campaign::all());
    }

    function test_user_can_search_campaign() {
        // Create 3 campaigns
        $this->post('api/campaigns/create', [
            'user_email' => 'foo@bar.baz',
            'name' => 'foo1',
            'source' => 'bar1',
            'channel' => 'baz1',
            'target_url' => 'qux1',
        ]);

        $this->post('api/campaigns/create', [
            'user_email' => 'foo@bar.baz',
            'name' => 'foo2',
            'source' => 'bar2',
            'channel' => 'baz2',
            'target_url' => 'qux2',
        ]);

         $this->post('api/campaigns/create', [
            'user_email' => 'foo@bar.baz',
            'name' => 'foo3',
            'source' => 'bar3',
            'channel' => 'baz3',
            'target_url' => 'qux3',
        ]);

        $response = $this->post('api/campaigns/get', [
            'page' => 2,
            'size' => 1
        ]);

        // Assert response
        $this->assertEquals(Response::HTTP_OK, $response->status());

        $content = json_decode($response->getContent());
        // Assert pagination works. We should have gotten the second campaign.
        $this->assertCount(1, $content);
        $this->assertTrue(str_contains($response->getContent(), 'foo2'));
    }
}
