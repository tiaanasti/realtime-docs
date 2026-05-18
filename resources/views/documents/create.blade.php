<x-app-layout>

<div class="min-h-screen bg-gray-100 py-10">

    <div class="max-w-3xl mx-auto px-4">

        <div class="bg-white rounded-2xl shadow p-8">

            <h1 class="text-3xl font-bold text-gray-800">
                Create Document
            </h1>

            <p class="text-gray-500 mt-2 mb-8">
                Start a new collaborative document
            </p>

            <form method="POST" action="/documents">

                @csrf

                <input
                    type="text"
                    name="title"
                    placeholder="Document title..."
                    class="w-full border-gray-300 rounded-xl shadow-sm"
                >

                <div class="mt-6 flex justify-end">

                    <button
                        type="submit"
                        class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl"
                    >
                        Create
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

</x-app-layout>