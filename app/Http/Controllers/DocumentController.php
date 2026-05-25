<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use App\Models\DocumentVersion;
use App\Events\DocumentUpdated;
use App\Events\DocumentListUpdated;

class DocumentController extends Controller
{
    public function index()
    {
        $documents = Document::with([
            'versions' => function ($query) {
                $query->latest();
            },
            'versions.user'
        ])
        ->latest()
        ->get();

        return view(
            'documents.index',
            compact('documents')
        );
    }

    public function create()
    {
        return view(
            'documents.create'
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255'
        ]);

        $document = Document::create([

            'title' =>
                $request->title,

            'content' =>
                '',

            'user_id' =>
                auth()->id()

        ]);

        DocumentVersion::create([

            'document_id' =>
                $document->id,

            'user_id' =>
                auth()->id(),

            'title' =>
                $document->title,

            'content' =>
                $document->content

        ]);


        $document->load([
            'versions.user'
        ]);


        broadcast(
            new DocumentListUpdated(
                $document->id,
                'created'
            )
        )->toOthers();


        return redirect('/documents');
    }


    public function show(Document $document)
    {
        $document->load([
            'versions.user'
        ]);

        return view(
            'documents.show',
            compact('document')
        );
    }


    public function edit(Document $document)
    {
        $document->load([
            'versions.user'
        ]);

        return view(
            'documents.edit',
            compact('document')
        );
    }

    public function update(
        Request $request,
        Document $document
    )
    {


        $request->validate([

            'title' =>
                'nullable|string|max:255',

            'content' =>
                'nullable|string'

        ]);

        $newTitle =
            $request->title
            ?? $document->title;

        $newContent =
            $request->content
            ?? $document->content;
        $titleChanged =
            trim($document->title)
            !== trim($newTitle);

        $contentChanged =
            trim($document->content)
            !== trim($newContent);
        if (
            !$titleChanged &&
            !$contentChanged
        ) {
            return response()->json([

                'success' => true,

                'message' =>
                    'No changes detected',

                'document' =>
                    $document

            ]);
        }

        $document->update([

            'title' =>
                $newTitle,

            'content' =>
                $newContent

        ]);

        DocumentVersion::create([

            'document_id' =>
                $document->id,

            'user_id' =>
                auth()->id(),

            'title' =>
                $newTitle,

            'content' =>
                $newContent

        ]);


        $document->refresh();

        $document->load([
            'versions.user'
        ]);


        broadcast(
            new DocumentUpdated(
                $document,
                auth()->user()
            )
        )->toOthers();

        broadcast(
            new DocumentListUpdated(
                $document->id,
                'updated'
            )
        )->toOthers();


        return response()->json([

            'success' => true,

            'document' => $document

        ]);
    }

    public function restore(
        Document $document,
        DocumentVersion $version
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
                $document->content

        ]);

        $document->update([

            'title' =>
                $version->title,

            'content' =>
                $version->content

        ]);


        DocumentVersion::create([

            'document_id' =>
                $document->id,

            'user_id' =>
                auth()->id(),

            'title' =>
                $version->title,

            'content' =>
                $version->content

        ]);

        $document->refresh();

        $document->load([
            'versions.user'
        ]);

        broadcast(
            new DocumentUpdated(
                $document,
                auth()->user()
            )
        )->toOthers();

        broadcast(
            new DocumentListUpdated(
                $document->id,
                'updated'
            )
        )->toOthers();


        return back();
    }

    public function destroy(Document $document)
    {
        $documentId = $document->id;
        $document->versions()->delete();

        $document->delete();

        broadcast(
            new DocumentListUpdated(
                $documentId,
                'deleted'
            )
        )->toOthers();


        return back();
    }
}