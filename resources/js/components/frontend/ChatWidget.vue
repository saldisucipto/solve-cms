<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from 'vue';
import { MessageCircle, Send, X } from '@lucide/vue';

const open = ref(false);
const loading = ref(true);
const enabled = ref(true);
const requiresEmail = ref(false);
const welcomeMessage = ref('');
const conversation = ref(null);
const messages = ref([]);
const visitorToken = ref(null);
const name = ref('');
const email = ref('');
const body = ref('');
const sending = ref(false);
const error = ref('');
const feed = ref(null);

const STORAGE_NAME = 'solve.chat.name';
const STORAGE_EMAIL = 'solve.chat.email';

let pollTimer = null;
let echoChannel = null;

const hasConversation = computed(() => conversation.value !== null);

function csrf() {
    return (
        document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content') ?? ''
    );
}

async function request(url, options = {}) {
    const response = await fetch(url, {
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrf(),
            'X-Requested-With': 'XMLHttpRequest',
        },
        ...options,
    });

    if (!response.ok) {
        throw new Error(`Request failed: ${response.status}`);
    }

    return response.json();
}

async function load() {
    try {
        const data = await request('/chat', { method: 'GET' });
        enabled.value = data.enabled !== false;

        if (!enabled.value) {
            return;
        }

        welcomeMessage.value = data.welcome_message ?? '';
        requiresEmail.value = Boolean(data.requires_email);
        visitorToken.value = data.token ?? null;
        conversation.value = data.conversation ?? null;

        if (Array.isArray(data.messages)) {
            messages.value = data.messages;
        }

        if (conversation.value) {
            subscribe();
        }
    } catch {
        enabled.value = false;
    } finally {
        loading.value = false;
    }
}

async function startConversation() {
    error.value = '';

    if (!name.value.trim()) {
        error.value = 'Please enter your name.';

        return;
    }

    try {
        const data = await request('/chat/start', {
            method: 'POST',
            body: JSON.stringify({
                name: name.value.trim(),
                email: email.value.trim() || null,
            }),
        });

        conversation.value = data.conversation;
        messages.value = data.messages ?? [];

        try {
            localStorage.setItem(STORAGE_NAME, name.value.trim());
            localStorage.setItem(STORAGE_EMAIL, email.value.trim());
        } catch {
            // storage may be unavailable
        }

        subscribe();
        scrollToBottom();
    } catch {
        error.value = 'Could not start the chat. Please try again.';
    }
}

async function sendMessage() {
    const text = body.value.trim();

    if (text === '' || sending.value) {
        return;
    }

    sending.value = true;
    error.value = '';

    try {
        const data = await request('/chat/messages', {
            method: 'POST',
            body: JSON.stringify({ body: text }),
        });

        if (data.message) {
            messages.value.push(data.message);
        }

        body.value = '';
        scrollToBottom();
    } catch {
        error.value = 'Message failed to send.';
    } finally {
        sending.value = false;
    }
}

function subscribe() {
    if (echoChannel || !visitorToken.value) {
        return;
    }

    if (!window.Echo || typeof window.Echo.private !== 'function') {
        return;
    }

    echoChannel = window.Echo.private(`chat.visitor.${visitorToken.value}`);
    echoChannel.listen('.chat.message', (payload) => {
        if (payload.message) {
            appendMessage(payload.message);
        }
    });
}

function appendMessage(message) {
    if (messages.value.some((existing) => existing.id === message.id)) {
        return;
    }

    messages.value.push(message);
    scrollToBottom();
}

async function poll() {
    if (!open.value || !conversation.value) {
        return;
    }

    try {
        const data = await request('/chat', { method: 'GET' });

        if (Array.isArray(data.messages)) {
            messages.value = data.messages;
        }

        if (data.conversation) {
            conversation.value = data.conversation;
        }
    } catch {
        // ignore polling errors
    }
}

function toggle() {
    open.value = !open.value;

    if (open.value) {
        scrollToBottom();

        if (conversation.value) {
            request('/chat/read', { method: 'POST' }).catch(() => {});
        }
    }
}

function scrollToBottom() {
    nextTick(() => {
        feed.value?.scrollTo({
            top: feed.value.scrollHeight,
            behavior: 'smooth',
        });
    });
}

onMounted(() => {
    try {
        name.value = localStorage.getItem(STORAGE_NAME) ?? '';
        email.value = localStorage.getItem(STORAGE_EMAIL) ?? '';
    } catch {
        // ignore
    }

    load();
    pollTimer = window.setInterval(poll, 5000);
});

onBeforeUnmount(() => {
    if (pollTimer) {
        window.clearInterval(pollTimer);
    }

    if (echoChannel && window.Echo) {
        window.Echo.leave(`chat.visitor.${visitorToken.value}`);
    }
});
</script>

<template>
    <div
        v-if="enabled && !loading"
        class="fixed right-5 bottom-5 z-50 flex flex-col items-end gap-3"
    >
        <div
            v-if="open"
            class="flex h-[27rem] w-[21rem] flex-col overflow-hidden rounded-xl border border-black/10 bg-white shadow-2xl"
        >
            <div
                class="flex items-center justify-between bg-[var(--fe-primary)] px-3 py-2 text-white"
            >
                <span class="text-sm font-semibold">Live chat</span>
                <button
                    type="button"
                    aria-label="Close chat"
                    @click="open = false"
                >
                    <X class="size-4" />
                </button>
            </div>

            <!-- Start form -->
            <form
                v-if="!hasConversation"
                class="flex flex-1 flex-col gap-3 p-4 text-sm"
                @submit.prevent="startConversation"
            >
                <p class="text-black/70">{{ welcomeMessage }}</p>

                <label class="flex flex-col gap-1">
                    <span class="text-xs font-medium">Your name</span>
                    <input
                        v-model="name"
                        type="text"
                        required
                        class="rounded-md border border-black/15 px-2 py-1.5"
                    />
                </label>

                <label class="flex flex-col gap-1">
                    <span class="text-xs font-medium">
                        Email
                        <span class="text-black/40">{{
                            requiresEmail ? '' : '(optional)'
                        }}</span>
                    </span>
                    <input
                        v-model="email"
                        type="email"
                        :required="requiresEmail"
                        class="rounded-md border border-black/15 px-2 py-1.5"
                    />
                </label>

                <p v-if="error" class="text-xs text-red-600">{{ error }}</p>

                <button
                    type="submit"
                    class="mt-auto rounded-md bg-[var(--fe-primary)] px-3 py-2 text-sm font-medium text-white"
                >
                    Start chat
                </button>
            </form>

            <!-- Thread -->
            <template v-else>
                <div
                    ref="feed"
                    class="flex-1 space-y-2 overflow-y-auto p-3 text-sm"
                >
                    <div
                        v-for="message in messages"
                        :key="message.id"
                        class="flex flex-col"
                        :class="
                            message.sender_type === 'visitor'
                                ? 'items-end'
                                : 'items-start'
                        "
                    >
                        <div
                            class="max-w-[85%] rounded-lg px-2.5 py-1.5"
                            :class="
                                message.sender_type === 'visitor'
                                    ? 'bg-[var(--fe-primary)] text-white'
                                    : 'bg-black/5 text-black/80'
                            "
                        >
                            <!-- eslint-disable-next-line vue/no-v-html -->
                            <span v-html="message.body" />
                        </div>
                        <span class="mt-0.5 text-[10px] text-black/40">
                            {{ message.author }} · {{ message.created_human }}
                        </span>
                    </div>

                    <p
                        v-if="!messages.length"
                        class="text-center text-xs text-black/40"
                    >
                        Send a message to start the conversation.
                    </p>
                </div>

                <form
                    class="flex items-center gap-2 border-t border-black/10 p-2"
                    @submit.prevent="sendMessage"
                >
                    <input
                        v-model="body"
                        type="text"
                        maxlength="2000"
                        placeholder="Type a message…"
                        class="flex-1 rounded-md border border-black/15 px-2 py-1.5 text-sm"
                    />
                    <button
                        type="submit"
                        :disabled="sending"
                        class="rounded-md bg-[var(--fe-primary)] p-2 text-white disabled:opacity-50"
                        aria-label="Send message"
                    >
                        <Send class="size-4" />
                    </button>
                </form>
            </template>
        </div>

        <button
            type="button"
            class="flex size-14 items-center justify-center rounded-full bg-[var(--fe-primary)] text-white shadow-lg"
            :aria-label="open ? 'Close chat' : 'Open chat'"
            @click="toggle"
        >
            <X v-if="open" class="size-6" />
            <MessageCircle v-else class="size-6" />
        </button>
    </div>
</template>
