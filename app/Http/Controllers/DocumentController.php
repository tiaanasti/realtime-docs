<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Document;
use App\Models\DocumentVersion;

use App\Events\DocumentUpdated;

class DocumentController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | DOCUMENT LIST
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $documents =
            Document::latest()->get();

        return view(
            'documents.index',
            compact('documents')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE PAGE
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        return view(
            'documents.create'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STORE NEW DOCUMENT
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $request->validate([

            'title' => 'required'

        ]);

        $document = Document::create([

            'title' =>
                $request->title,

            'content' =>
                '',

            'user_id' =>
                auth()->id()

        ]);

        /*
        |--------------------------------------------------------------------------
        | CREATE INITIAL VERSION
        |--------------------------------------------------------------------------
        */

        DocumentVersion::create([

            'document_id' =>
                $document->id,

            'user_id' =>
                auth()->id(),

            'title' =>
                $document->title,

            'content' =>
                $document->content,

        ]);

        return redirect(
            '/documents'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW DOCUMENT
    |--------------------------------------------------------------------------
    */

    public function show(Document $document)
    {
        return view(
            'documents.show',
            compact('document')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT DOCUMENT
    |--------------------------------------------------------------------------
    */

    public function edit(Document $document)
    {
        return view(
            'documents.edit',
            compact('document')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE DOCUMENT
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Document $document
    )
    {
        /*
        |--------------------------------------------------------------------------
        | VALIDATION
        |--------------------------------------------------------------------------
        */

        $request->validate([

            'title' =>
                'nullable|string',

            'content' =>
                'nullable|string'

        ]);

        /*
        |--------------------------------------------------------------------------
        | NEW DATA
        |--------------------------------------------------------------------------
        */

        $newTitle =
            $request->title ?? '';

        $newContent =
            $request->content ?? '';

        /*
        |--------------------------------------------------------------------------
        | DETECT CHANGES
        |--------------------------------------------------------------------------
        */

        $contentChanged =
            trim($document->content)
            !== trim($newContent);

        $titleChanged =
            trim($document->title)
            !== trim($newTitle);

        /*
        |--------------------------------------------------------------------------
        | GET LAST VERSION
        |--------------------------------------------------------------------------
        */

        $lastVersion =
            DocumentVersion::where(
                'document_id',
                $document->id
            )
            ->latest()
            ->first();

        /*
        |--------------------------------------------------------------------------
        | VERSION LOGIC
        |--------------------------------------------------------------------------
        */

        $shouldCreateVersion = false;

        if (!$lastVersion)
        {
            $shouldCreateVersion = true;
        }
        else
        {
            $secondsPassed =
                now()->diffInSeconds(
                    $lastVersion->created_at
                );

            /*
            |--------------------------------------------------------------------------
            | CREATE SNAPSHOT EVERY 5 MINUTES
            |--------------------------------------------------------------------------
            */

            if (
                $secondsPassed >= 300 &&
                (
                    $contentChanged ||
                    $titleChanged
                )
            )
            {
                $shouldCreateVersion = true;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | UPDATE DOCUMENT FIRST
        |--------------------------------------------------------------------------
        */

        $document->update([

            'title' =>
                $newTitle,

            'content' =>
                $newContent,

        ]);

        /*
        |--------------------------------------------------------------------------
        | REFRESH MODEL
        |--------------------------------------------------------------------------
        */

        $document->refresh();

        /*
        |--------------------------------------------------------------------------
        | SAVE UPDATED VERSION
        |--------------------------------------------------------------------------
        */

        if (
            $shouldCreateVersion ||
            $contentChanged ||
            $titleChanged
        )
        {
            DocumentVersion::create([

                'document_id' =>
                    $document->id,

                'user_id' =>
                    auth()->id(),

                'title' =>
                    $document->title,

                'content' =>
                    $document->content,

            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | BROADCAST REALTIME
        |--------------------------------------------------------------------------
        */

        broadcast(

            new DocumentUpdated(
                $document,
                auth()->user()
            )

        )->toOthers();

        /*
        |--------------------------------------------------------------------------
        | RESPONSE
        |--------------------------------------------------------------------------
        */

        return response()->json([

            'success' => true,

            'document' => $document

        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | RESTORE VERSION
    |--------------------------------------------------------------------------
    */

    public function restore(
        Document $document,
        DocumentVersion $version
    )
    {
        /*
        |--------------------------------------------------------------------------
        | SAVE CURRENT VERSION
        |--------------------------------------------------------------------------
        */

        DocumentVersion::create([

            'document_id' =>
                $document->id,

            'user_id' =>
                auth()->id(),

            'title' =>
                $document->title,

            'content' =>
                $document->content,

        ]);

        /*
        |--------------------------------------------------------------------------
        | RESTORE DATA
        |--------------------------------------------------------------------------
        */

        $document->update([

            'title' =>
                $version->title,

            'content' =>
                $version->content,

        ]);

        /*
        |--------------------------------------------------------------------------
        | REFRESH
        |--------------------------------------------------------------------------
        */

        $document->refresh();

        /*
        |--------------------------------------------------------------------------
        | BROADCAST RESTORE
        |--------------------------------------------------------------------------
        */

        broadcast(

            new DocumentUpdated(
                $document,
                auth()->user()
            )

        )->toOthers();

        /*
        |--------------------------------------------------------------------------
        | REDIRECT
        |--------------------------------------------------------------------------
        */

        return back();
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE DOCUMENT
    |--------------------------------------------------------------------------
    */

    public function destroy(Document $document)
    {
        $document->delete();

        return back();
    }
}