<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use App\Models\DocumentVersion;
use App\Events\DocumentUpdated;
use App\Events\DocumentListUpdated;

class DocumentController extends Controller
{
// List documents
    public function index()
    {
        $documents = Document::with([
            'versions.user'
        ])->latest()->get();

        return view(
            'documents.index',
            compact('documents')
        );
    }

// Create document page
    public function create()
    {
        return view(
            'documents.create'
        );
    }

    // Store new document
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

// Save initial version
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

// Realtime document list
        broadcast(

            new DocumentListUpdated(
                $document->id,
                'created'
            )

        )->toOthers();

        return redirect(
            '/documents'
        );
    }

// Show document details
    public function show(Document $document)
    {
        return view(
            'documents.show',
            compact('document')
        );
    }

// Edit document page
    public function edit(Document $document)
    {
        return view(
            'documents.edit',
            compact('document')
        );
    }

   
// Update document
    public function update(
        Request $request,
        Document $document
    )
    {

    //    Validation
        $request->validate([

            'title' =>
                'nullable|string',

            'content' =>
                'nullable|string'

        ]);

    //   New data
        $newTitle =
            $request->title ?? $document->title;

        $newContent =
            $request->content ?? $document->content;

    //   Detect changes
        $contentChanged =
            trim($document->content)
            !== trim($newContent);

        $titleChanged =
            trim($document->title)
            !== trim($newTitle);

      
// Version logic
        $shouldCreateVersion = false;

        if (
            $contentChanged ||
            $titleChanged
        )
        {
            $shouldCreateVersion = true;
        }

// Update document
        $document->update([

            'title' =>
                $newTitle,
            'content' =>
                $newContent,

        ]);

// Refresh model
        $document->refresh();

// Save version
        if ($shouldCreateVersion)
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

    //    Realtime document editor

        broadcast(

            new DocumentUpdated(
                $document,
                auth()->user()
            )

        )->toOthers();

        // Realtime document list

        broadcast(

            new DocumentListUpdated(
                $document->id,
                'updated'
            )

        )->toOthers();

    //    Response
        return response()->json([

            'success' => true,
            'document' => $document

        ]);
    }

  
// Restore document version
    public function restore(
        Document $document,
        DocumentVersion $version
    )
    {
        // Save current version
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

       
// Restore version
        $document->update([

            'title' =>
                $version->title,

            'content' =>
                $version->content,

        ]);

       
// Refresh model
        $document->refresh();

    //    Realtime document editor
        broadcast(

            new DocumentUpdated(
                $document,
                auth()->user()
            )

        )->toOthers();

    //    Realtime document list
        broadcast(

            new DocumentListUpdated(
                $document->id,
                'updated'
            )

        )->toOthers();

        return back();
    }

// Delete document
    public function destroy(Document $document)
    {
        $documentId = $document->id;

        $document->delete();

    //    Realtime delete
        broadcast(

            new DocumentListUpdated(
                $documentId,
                'deleted'
            )

        )->toOthers();

        return back();
    }
}