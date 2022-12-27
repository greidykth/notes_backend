<?php

namespace Tests;

use App\Models\Note;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class NoteTest extends TestCase
{
    use DatabaseTransactions;

    public function test_should_list_notes()
    {
        Note::factory()->count(10)->create();

        $response = $this->get('/notes');
        $response->seeStatusCode(200)->seeJsonStructure([
            'data' => ['*' =>
            [
                'id',
                'status',
                'title',
                'tags',
                'description',
                'created_at',
                'updated_at',
            ]
        ]]);
    }

    public function test_should_return_a_note()
    {
        $note = Note::factory()->count(1)->create()->toArray();

        $response = $this->get('/notes/'.$note[0]['id']);
        $response->seeStatusCode(200)->seeJsonStructure([
            'data' => [[
                    'id',
                    'status',
                    'title',
                    'description',
                    "tags",
                    'created_at',
                    'updated_at',
                ]]
            ]);
    }

    public function test_should_create_note()
    {
        $response = $this->post('/notes', [
            'title' => "Note 1",
            'description' => "Prueba 1",
            'status' => "ACTIVE",
            'tags' =>  [1,2],
        ]);

        $response->seeJsonContains(["title" => "Note 1"])->seeStatusCode(201)
        ->seeJsonStructure(["data" => ["title", "description", "status"]])
        ->seeInDatabase('notes', [
            'title' => "Note 1",
            'description' => "Prueba 1",
        ]);

        //Test with wrong data
        $wrongMaterial = $this->post('/notes', [
            'title' => "Note 1",
            'description' => "Prueba 1",
            'tags' =>  [1],
        ]);
        $wrongMaterial->seeStatusCode(422)->seeJsonStructure(["error" => [
            "status"], "code"])
            ->seeJsonContains(["The status field is required."]);
    }

    public function test_should_update_note()
    {
        $material = Note::factory()->count(1)->create()->toArray();

        $response = $this->put('/notes/'.$material[0]['id'], [
            'title' => "Note 1",
            'description' => "Update Description",
            'status' => "ACTIVE",
            'tags' =>  [1],
        ]);

        $response->seeJsonContains(["title" => "Note 1"])->seeStatusCode(200)
        ->seeJsonStructure(["data" => ["title", "description", "status"]])
        ->seeInDatabase('notes', [
            'title' => "Note 1",
            'description' => "Update Description",
            'status' => "ACTIVE",
        ]);
    }

    public function test_should_delete_a_note()
    {
        $material = Note::factory()->count(1)->create()->toArray();

        $response = $this->delete('/notes/'.$material[0]['id']);
        $response->seeStatusCode(200)->seeJsonStructure([
            'data' => [
                    'id',
                    'status',
                    'title',
                    'description',
                    'created_at',
                    'updated_at',
                ]
            ])->seeJsonContains(["status" => "CANCELED"]);
    }
}
