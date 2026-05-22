<x-app-layout>

<div class="min-h-screen bg-gray-100 py-10">

    <div class="max-w-7xl mx-auto px-4">

        <div class="bg-white rounded-3xl shadow-xl overflow-hidden">

            <!-- HEADER -->
            <div class="border-b bg-white px-8 py-6">

                <div class="flex flex-col gap-4">

                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">
                            Collaborative Editor
                        </h1>

                        <p class="text-sm text-gray-500 mt-1">
                            Realtime collaborative editing enabled
                        </p>
                    </div>

                    <!-- USERS -->
                    <div
                        id="online-users"
                        class="flex flex-wrap gap-2"
                    ></div>

                    <!-- LIVE -->
                    <div
                        id="live-status"
                        class="hidden bg-yellow-100 border border-yellow-300 text-yellow-800 px-4 py-3 rounded-2xl text-sm font-medium"
                    ></div>

                    <!-- CONFLICT -->
                    <div
                        id="conflict-status"
                        class="hidden bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded-2xl text-sm font-medium"
                    ></div>

                </div>

            </div>

            <!-- CONTENT -->
            <div class="grid grid-cols-1 lg:grid-cols-4">

                <!-- EDITOR -->
                <div class="lg:col-span-3 p-8 space-y-6">

                    <!-- TITLE -->
                    <input
                        type="text"
                        id="title"
                        value="{{ $document->title }}"
                        class="w-full border border-gray-300 rounded-2xl px-5 py-4 text-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >

                    <!-- TEXTAREA -->
                    <div class="relative">

                        <!-- CURSOR -->
                        <div
                            id="cursor-layer"
                            class="absolute inset-0 pointer-events-none z-20"
                        ></div>

                        <textarea
                            id="content"
                            class="w-full h-[600px] border border-gray-300 rounded-2xl p-5 resize-none leading-7 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >{{ $document->content }}</textarea>

                    </div>

                    <!-- STATUS -->
                    <div class="flex justify-between items-center">

                        <div class="text-sm text-gray-400">
                            Autosave enabled
                        </div>

                        <div
                            id="save-status"
                            class="text-sm text-gray-500 font-medium"
                        >
                            Synced
                        </div>

                    </div>

                </div>

                <!-- VERSION -->
                <div
                    id="version-history"
                    class="border-l bg-gray-50 p-6 h-[760px] overflow-y-auto"
                >

                    <h2 class="text-xl font-bold text-gray-800 mb-5">
                        Version History
                    </h2>

                    <div
                        id="version-container"
                        class="space-y-4"
                    >

                        @foreach(
                            $document->versions()
                                ->latest()
                                ->take(20)
                                ->get()
                            as $version
                        )

                            <div class="bg-white border rounded-2xl p-4 shadow-sm">

                                <div class="flex justify-between items-center mb-3">

                                    <p class="font-semibold text-gray-800">
                                        {{ $version->user->name ?? 'Unknown' }}
                                    </p>

                                    <p class="text-xs text-gray-500">
                                        {{ $version->created_at->diffForHumans() }}
                                    </p>

                                </div>

                                <div class="bg-gray-50 border rounded-xl p-3 text-sm whitespace-pre-wrap text-gray-700 max-h-40 overflow-hidden">
                                    {{ $version->content }}
                                </div>

                            </div>

                        @endforeach

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<script type="module">
    
const title =
    document.getElementById('title');
const content =
    document.getElementById('content');
const saveStatus =
    document.getElementById('save-status');
const onlineUsers =
    document.getElementById('online-users');
const cursorLayer =
    document.getElementById('cursor-layer');
const liveStatus =
    document.getElementById('live-status');
const conflictStatus =
    document.getElementById('conflict-status');
const versionContainer =
    document.getElementById('version-container');

// Variables

let users = [];
let autosaveTimeout;
let liveTimeout;
let conflictTimeout;
let versionReloadTimeout;
const conflictCooldowns = {};

// User List

function renderUsers(activeUsers)
{
    onlineUsers.innerHTML = '';
    activeUsers.forEach(user => {
        onlineUsers.innerHTML += `
            <div
                class="
                    px-3
                    py-1.5
                    bg-green-100
                    text-green-700
                    rounded-full
                    text-sm
                "
            >
                ${user.name}
            </div>
        `;

    });
}

// Status Messages
function showLive(message)
{
    clearTimeout(liveTimeout);
    liveStatus.innerText = message;
    liveStatus.classList.remove('hidden');
    liveTimeout = setTimeout(() => {
        liveStatus.classList.add('hidden');

    }, 2000);
}

function showConflict(message, userId)
{
    const now = Date.now();
    const cooldown = 300000;
    if (
        conflictCooldowns[userId]
        &&
        now - conflictCooldowns[userId] < cooldown
    )
    {
        return;
    }

    conflictCooldowns[userId] = now;
    clearTimeout(conflictTimeout);
    conflictStatus.innerText = message;
    conflictStatus.classList.remove('hidden');
    conflictTimeout = setTimeout(() => {

        conflictStatus.classList.add('hidden');

    }, 3500);
}

// Autosave

async function autosave()
{
    try {

        saveStatus.innerText =
            'Syncing...';

        await fetch(
            '/documents/{{ $document->id }}',
            {
                method: 'PUT',
                headers: {

                    'Content-Type':
                        'application/json',
                    'Accept':
                        'application/json',
                    'X-CSRF-TOKEN':
                        document
                            .querySelector(
                                'meta[name="csrf-token"]'
                            )
                            .getAttribute(
                                'content'
                            )
                },

                body: JSON.stringify({

                    title:
                        title.value,
                    content:
                        content.value

                })
            }
        );

        saveStatus.innerText =
            'Synced';

    }
    catch {
        saveStatus.innerText =
            'Error';

    }
}

function triggerAutosave()
{
    clearTimeout(autosaveTimeout);

    autosaveTimeout =
        setTimeout(
            autosave,
            800
        );
}

// Version History

function reloadVersionHistory()
{
    clearTimeout(versionReloadTimeout);

    versionReloadTimeout = setTimeout(() => {

        fetch(window.location.href)
            .then(response => response.text())
            .then(html => {

                const parser =
                    new DOMParser();

                const doc =
                    parser.parseFromString(
                        html,
                        'text/html'
                    );

                const newVersion =
                    doc.getElementById(
                        'version-container'
                    );

                if (newVersion)
                {
                    versionContainer.innerHTML =
                        newVersion.innerHTML;
                }

            });

    }, 500);
}

// Cursor Position

function getCursorCoordinates(
    textarea,
    position
)
{
    const div =
        document.createElement('div');
    const style =
        window.getComputedStyle(textarea);
    div.style.position =
        'absolute';
    div.style.visibility =
        'hidden';
    div.style.whiteSpace =
        'pre-wrap';
    div.style.wordWrap =
        'break-word';
    div.style.font =
        style.font;
    div.style.padding =
        style.padding;
    div.style.lineHeight =
        style.lineHeight;
    div.style.width =
        textarea.clientWidth + 'px';
    div.style.border =
        style.border;
    div.textContent =
        textarea.value.substring(
            0,
            position
        );

    const span =
        document.createElement('span');
    span.textContent = '|';
    div.appendChild(span);
    document.body.appendChild(div);
    const coords = {

        top:
            span.offsetTop
            - textarea.scrollTop,

        left:
            span.offsetLeft
            - textarea.scrollLeft
    };
    document.body.removeChild(div);
    return coords;
}

// Cursor

function renderCursor(data)
{
    if (
        data.user_id ==
        {{ auth()->user()->id }}
    ) {
        return;
    }

    let cursor =
        document.getElementById(
            'cursor-' + data.user_id
        );

    if (!cursor)
    {
        cursor =
            document.createElement('div');
        cursor.id =
            'cursor-' + data.user_id;
        cursor.className =
            'absolute pointer-events-none z-50';
        cursor.innerHTML = `
            <div class="flex items-center gap-1">

                <div
                    class="
                        w-[2px]
                        h-6
                        bg-red-500
                        rounded
                    "
                ></div>

                <div
                    class="
                        bg-red-500
                        text-white
                        text-[10px]
                        px-2
                        py-[2px]
                        rounded
                        whitespace-nowrap
                    "
                >
                    ${data.name}
                </div>

            </div>
        `;

        cursorLayer.appendChild(cursor);
    }

    const coords =
        getCursorCoordinates(
            content,
            data.position
        );

    cursor.style.top =
        (coords.top + 6) + 'px';

    cursor.style.left =
        (coords.left + 6) + 'px';
}


// Connect to Echo channel
// Bersihkan listener lama
window.Echo.leave(
    'document.{{ $document->id }}'
);

const channel =
    window.Echo.join(
        'document.{{ $document->id }}'
    );
channel

    .here(usersOnline => {
        users = usersOnline;
        renderUsers(users);

    })

    .joining(user => {

        if (
            !users.find(
                u => u.id === user.id
            )
        ) {
            users.push(user);
        }

        renderUsers(users);
        showLive(
            `${user.name} joined`
        );

    })

    .leaving(user => {

        users =
            users.filter(
                u => u.id !== user.id
            );

        renderUsers(users);
        showLive(
            `${user.name} left`
        );

    })

// Realtime edi

    .listenForWhisper(
        'editing',
        e => {

            if (
                e.user_id ==
                {{ auth()->user()->id }}
            ) {
                return;
            }
            title.value =
                e.title;

            content.value =
                e.content;

            if (
                document.activeElement === content
                ||
                document.activeElement === title
            )
            {
                showConflict(
                    `${e.name} is editing the document`,
                    e.user_id
                );
            }

        }
    )

// Document updated

.listen(
    '.DocumentUpdated',
    e => {

        if (
            e.user_id ==
            {{ auth()->user()->id }}
        ) {
            return;
        }

        // Hindari overwrite berulang
        if (
            title.value !== e.document.title
        ) {
            title.value =
                e.document.title;
        }

       if (
    content.value !== e.document.content
) {

    // Simpan posisi cursor
    const start =
        content.selectionStart;

    const end =
        content.selectionEnd;

    content.value =
        e.document.content;

    // Restore cursor setelah render selesai
    setTimeout(() => {

        content.setSelectionRange(
            start,
            end
        );

    }, 0);
}

        // Reload version history
        reloadVersionHistory();

        showLive(
            `${e.user_name} synced changes`
        );

    }
)
// Cursor

    .listenForWhisper(
        'cursor-move',
        e => {

            renderCursor(e);
        }
    );
// Send

function sendRealtime()
{
    if (!channel) {
        return;
    }

    channel.whisper(
        'editing',
        {
            user_id:
                {{ auth()->user()->id }},

            name:
                "{{ auth()->user()->name }}",

            title:
                title.value,

            content:
                content.value
        }
    );
}

function sendCursor()
{
    channel.whisper(
        'cursor-move',
        {
            user_id:
                {{ auth()->user()->id }},
            name:
                "{{ auth()->user()->name }}",
            position:
                content.selectionStart

        }
    );
}

// Event
content.addEventListener(
    'input',
    () => {
        sendRealtime();
        sendCursor();
        triggerAutosave();

    }
);

title.addEventListener(
    'input',
    () => {

        sendRealtime()
        triggerAutosave();

    }
);

[
    'click',
    'keyup',
    'keydown',
    'scroll',
    'select'
].forEach(event => {

    content.addEventListener(
        event,
        sendCursor
    );

});

</script>

</x-app-layout>