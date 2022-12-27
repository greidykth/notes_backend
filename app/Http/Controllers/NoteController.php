<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Tag;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class NoteController extends Controller
{
    use ApiResponser;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $notes = new Note();

        if ($request->has("searchText") && $request->searchText != "") {
            $notes = $notes->where(function ($whereAnidado) use ($request) {
                $whereAnidado->orWhere("title", "like", "%" . $request->searchText . "%")
                    ->orWhere("description", "like", "%" . $request->searchText . "%")
                    ->orWhereHas("tags", function ($tag) use ($request) {
                        $tag->where("tags.name", "like", "%" . $request->searchText . "%");
                    });
            });
        }

        $notes = $notes->where('status', 'ACTIVE')->with(["tags" => function ($tag) {
            $tag->where("note_tag.status", "ACTIVE");
        }])->get();

        return $this->successResponse($notes, Response::HTTP_OK);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Note  $note
     * @return \Illuminate\Http\Response
     */
    public function show($note)
    {
        $note = Note::where('id', $note)->where('status', 'ACTIVE')->with(["tags" => function ($tag) {
            $tag->where("note_tag.status", "ACTIVE");
        }])->first();
        
        return $this->successResponse($note, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = [
            'title' => 'required',
            'description' => 'required',
            'tags' => 'required',
        ];

        $this->validate($request, $rules);

        $note = new Note();
        $note->title = $request->title;
        $note->status = "ACTIVE";
        $note->description = $request->description;
        $note->save();

        if ($request->has("tags")) {
            $tags = $request->tags;
            
            foreach ($tags as $tag) {
                $existedTag = Tag::where('status', 'ACTIVE')->where('id', $tag)->first();
                if ($existedTag) {
                    $note->tags()->attach($tag, ["status" => "ACTIVE"]);
                } else {
                    return $this->errorResponse("Tag with id {$tag} not found", 404);
                }
            }
        }

        return $this->successResponse($note, Response::HTTP_CREATED);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Models\Note  $note
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $note)
    {
        $rules = [
            'title' => 'required',
            'description' => 'required',
            'tags' => 'required',
        ];

        $this->validate($request, $rules);
        
        $note = Note::findOrFail($note);
        $note->title = $request->title;
        $note->status = "ACTIVE";
        $note->description = $request->description;
        $note->save();

        if ($request->has("tags")) {
            $tags = $request->tags;
            $note->tags()->update(['note_tag.status' => 'CANCELED']);

            foreach ($tags as $tagUpdate) {
                $existedTag = Tag::where('status', 'ACTIVE')->where('id', $tagUpdate)->first();
                if ($existedTag) {
                    $tagRelationFind = $note->tags()->where("tag_id", $tagUpdate)->first();
                    if ($tagRelationFind) {
                        $note->tags()->updateExistingPivot($tagUpdate, ["status" => 'ACTIVE']);
                    }else {
                        $note->tags()->attach($tagUpdate, ["status" => "ACTIVE"]);
                    }
                } else {
                    return $this->errorResponse("Tag with id {$tagUpdate} not found", 404);
                }
            }
        }

        return $this->successResponse($note, Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Note  $note
     * @return \Illuminate\Http\Response
     */
    public function destroy($note)
    {
        $note = Note::findOrFail($note);
        $note->status = 'CANCELED';
        $note->save();
        
        $note->tags()->update(['note_tag.status' => 'CANCELED']);
        
        return $this->successResponse($note, Response::HTTP_OK);
    }
}
