<x-app-layout>

<div class="min-h-screen bg-gray-100 py-10">

    <div class="max-w-7xl mx-auto px-4">

        <!-- HEADER -->
        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 mb-10">

            <div>

                <h1 class="text-3xl font-bold text-gray-800">
                    Realtime Documents
                </h1>

                <p class="text-gray-500 mt-1">
                    Collaborative workspace
                </p>

            </div>

            <a
                href="/documents/create"
                class="px-5 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow-sm transition"
            >
                + Create
            </a>

        </div>

        <!-- STATS -->
        <div class="flex justify-center mb-12">

            <div class="bg-white rounded-2xl shadow p-8 hover:shadow-md transition w-full max-w-sm text-center">

                <p class="text-gray-500 text-sm">
                    Total Documents
                </p>

                <h2 class="text-4xl font-bold text-gray-800 mt-2">
                    {{ $documents->count() }}
                </h2>

            </div>

        </div>

        <!-- DOCUMENTS -->
        <div
            class="grid gap-8 justify-center"
            style="grid-template-columns: repeat(auto-fit, minmax(380px, 380px));"
        >

            @forelse($documents as $document)

                @php

                    $latestVersion =
                        $document->versions()
                            ->latest()
                            ->first();

                @endphp

                <div class="bg-white rounded-2xl shadow hover:shadow-xl transition p-6 flex flex-col justify-between min-h-[320px]">

                    <div>

                        <!-- TITLE -->
                        <div class="flex justify-between items-start gap-4">

                            <h2 class="text-xl font-semibold text-gray-800 leading-snug break-words">
                                {{ $document->title }}
                            </h2>

                            <span class="w-3 h-3 bg-green-500 rounded-full animate-pulse flex-shrink-0"></span>

                        </div>

                        <!-- UPDATED -->
                        <p class="text-sm text-gray-500 mt-2">
                            Updated {{ $document->updated_at->diffForHumans() }}
                        </p>

                        <!-- VERSION HISTORY -->
                        <div class="mt-5">

                            <div class="flex items-center justify-between mb-2">

                                <h3 class="text-sm font-semibold text-gray-700">
                                    Latest Version
                                </h3>

                                @if($latestVersion)

                                    <span class="text-xs text-gray-400">
                                        {{ $latestVersion->created_at->diffForHumans() }}
                                    </span>

                                @endif

                            </div>

                            @if($latestVersion)

                                <!-- USER -->
                                <div class="mb-3">

                                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-indigo-100 text-indigo-700 text-xs font-medium">
                                        {{ $latestVersion->user->name ?? 'Unknown User' }}
                                    </span>

                                </div>

                                <!-- CONTENT -->
                                <div class="bg-gray-50 border rounded-xl p-3">

                                    <p class="text-xs text-gray-500 mb-2">
                                        Latest Edited Text
                                    </p>

                                    <div class="text-sm text-gray-700 whitespace-pre-wrap leading-relaxed max-h-28 overflow-hidden">
                                        {{ $latestVersion->content }}
                                    </div>

                                </div>

                            @else

                                <div class="bg-gray-50 border rounded-xl p-4 text-sm text-gray-500">

                                    No version history yet

                                </div>

                            @endif

                        </div>

                    </div>

                    <!-- ACTIONS -->
                    <div class="mt-6 flex justify-between items-center">

                        <span class="text-xs text-gray-400">
                            #{{ $document->id }}
                        </span>

                        <div class="flex gap-2">

                            <!-- EDIT BUTTON -->
                            <a
                                href="/documents/{{ $document->id }}/edit"
                                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm transition"
                            >
                                Edit
                            </a>

                            <!-- DELETE BUTTON -->
                            <form
                                action="/documents/{{ $document->id }}"
                                method="POST"
                                onsubmit="return confirm('Delete this document?')"
                            >
                                @csrf
                                @method('DELETE')

                                <button
                                    type="submit"
                                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm transition"
                                >
                                    Delete
                                </button>

                            </form>

                        </div>

                    </div>

                </div>

            @empty

                <div class="bg-white rounded-2xl shadow p-10 text-center w-[380px]">

                    <h2 class="text-2xl font-bold text-gray-700">
                        No Documents
                    </h2>

                    <p class="text-gray-500 mt-2">
                        Create your first document
                    </p>

                </div>

            @endforelse

        </div>

    </div>

</div>

</x-app-layout>