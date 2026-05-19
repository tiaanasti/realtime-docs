<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use App\Models\DocumentVersion;
use App\Events\DocumentUpdated;

class DocumentController extends Controller
{
//    Document list

    public function index()
    {
        $documents =
            Document::latest()->get();

        return view(
            'documents.index',
            compact('documents')
        );
    }

    //    Create document

    public function create()
    {
        return view(
            'documents.create'
        );
    }

    //   Store document

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
// Store document version

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

    // Show document

    public function show(Document $document)
    {
        return view(
            'documents.show',
            compact('document')
        );
    }

//   Edit document

    public function edit(Document $document)
    {
        return view(
            'documents.edit',
            compact('document')
        );
    }

//   Update document

    public function update(
        Request $request,
        Document $document
    )
    {
        // Validation

        $request->validate([

            'title' =>
                'nullable|string',

            'content' =>
                'nullable|string'

        ]);

        // New data

        $newTitle =
            $request->title ?? '';

        $newContent =
            $request->content ?? '';

    //    Detect Chnages

        $contentChanged =
            trim($document->content)
            !== trim($newContent);

        $titleChanged =
            trim($document->title)
            !== trim($newTitle);

    //   Get last version
        $lastVersion =
            DocumentVersion::where(
                'document_id',
                $document->id
            )
            ->latest()
            ->first();

    //   Veraion logic

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

        //    Create snapshot every 5 minutes if there are changes
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

        // Update document

        $document->update([

            'title' =>
                $newTitle,

            'content' =>
                $newContent,

        ]);

    //  Refresh model
        $document->refresh();

        // Saved Update version

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

        // Broadcast realtime

        broadcast(

            new DocumentUpdated(
                $document,
                auth()->user()
            )

        )->toOthers();

    //   Response
        return response()->json([

            'success' => true,

            'document' => $document

        ]);
    }

//    Restore version

    public function restore(
        Document $document,
        DocumentVersion $version
    )
    {
    //    Save current version
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

        // Restore data

        $document->update([

            'title' =>
                $version->title,

            'content' =>
                $version->content,

        ]);

    //   Refresh model
        $document->refresh();
// Brpadcast realtime update

        broadcast(

            new DocumentUpdated(
                $document,
                auth()->user()
            )

        )->toOthers();

        // Redirect

        return back();
    }

// Delete document

    public function destroy(Document $document)
    {
        $document->delete();

        return back();
    }
}