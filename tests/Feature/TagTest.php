<?php

namespace Tests;

use App\Models\Tag;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class TagTest extends TestCase
{
    use DatabaseTransactions;
    
    public function test_should_list_tags()
    {
        Tag::factory()->count(10)->create();

        $response = $this->get('/tags');
        $response->seeStatusCode(200)->seeJsonStructure([
            'data' => ['*' =>
            [
                'id',
                'status',
                'name',
                'created_at',
                'updated_at',
            ]
        ]]);
    }

    public function test_should_create_tag()
    {
        $response = $this->post('/tags', [
            'name' => 'Tag 1',
            'status' => 'ACTIVE',
        
        ]);

        $response->seeJsonContains(['name' => 'Tag 1'])
                ->seeStatusCode(201)
                ->seeJsonStructure(["data" => ["id", "name", "status", "created_at", "updated_at"]])
                ->seeInDatabase('tags', [
                    'name' => "Tag 1",
                    'status' => "ACTIVE",
                ]);

        //Test with wrong data
        $wrongTag = $this->post('/tags', [
            'name' => "",
            'status' => "ACTIVE",
        ]);
        $wrongTag->seeStatusCode(422)->seeJsonStructure(["error" => [
            "name"], "code"])
            ->seeJsonContains(["The name field is required."]);
    }
}
