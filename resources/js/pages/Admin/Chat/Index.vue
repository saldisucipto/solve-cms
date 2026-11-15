<script setup>
import {
    computed,
    nextTick,
    onBeforeUnmount,
    onMounted,
    ref,
    watch,
} from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { CornerUpLeft, Send, UserPlus } from '@lucide/vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { dashboard } from '@/routes/admin';
import {
    index,
    reply,
    assign,
    status,
    messages as messagesRoute,
} from '@/routes/admin/chat';
import { statusVariant } from '@/lib/format';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Live Chat', href: index() },
        ],
    },
});

const props = defineProps({
    conversations: { type: Array, default: () => [] },
    selected: { type: Object, default: null },
    agents: { type: Array, default: () => [] },
    counts: { type: Object, default: () => ({}) },
    filters: { type: Object, default: () => ({}) },
    can: { type: Object, default: () => ({}) },
});

const page = usePage();
const search = ref(props.filters.search ?? '');
const activeStatus = ref(props.filters.status ?? '');
const thread = ref(props.selected?.messages ?? []);
const replyBody = ref('');
const sending = ref(false);
const feed = ref(null);

let pollTimer = null;
let echoChannel = null;

const selectedId = computed(() => props.selected?.id ?? null);

function applyFilters(statusValue = activeStatus.value) {
    router.get(
        index(),
        { status: statusValue, search: search.value },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

function openConversation(conversation) {
    router.get(
        index(),
        { conversation: conversation.id },
        { preserveScroll: true },
    );
}

function sendReply() {
    if (!props.selected || replyBody.value.trim() === '') {
        return;
    }

    sending.value = true;

    router.post(
        reply(props.selected.id),
        { body: replyBody.value.trim() },
        {
            preserveScroll: true,
            onSuccess: () => {
                replyBody.value = '';
                refreshThread();
            },
            onFinish: () => {
                sending.value = false;
            },
        },
    );
}

function updateStatus(conversation, value) {
    router.post(
        status(conversation.id),
        { status: value },
        { preserveScroll: true },
    );
}

function assignTo(conversation, userId) {
    router.post(
        assign(conversation.id),
        { user_id: userId || null },
        { preserveScroll: true },
    );
}

async function refreshThread() {
    if (!props.selected) {
        return;
    }

    const last = thread.value.at(-1)?.id ?? 0;

    try {
        const response = await fetch(
            `${messagesRoute(props.selected.id).url}?after=${last}`,
            {
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            },
        );

        if (!response.ok) {
            return;
        }

        const data = await response.json();

        if (Array.isArray(data.messages) && data.messages.length) {
            data.messages.forEach((message) => {
                if (
                    !thread.value.some((existing) => existing.id === message.id)
                ) {
                    thread.value.push(message);
                }
            });

            scrollToBottom();
        }
    } catch {
        // ignore transient errors
    }
}

function subscribe() {
    if (
        !selectedId.value ||
        !window.Echo ||
        typeof window.Echo.private !== 'function'
    ) {
        return;
    }

    echoChannel = window.Echo.private(`chat.conversation.${selectedId.value}`);
    echoChannel.listen('.chat.message', () => refreshThread());
}

function unsubscribe() {
    if (echoChannel && window.Echo && selectedId.value) {
        window.Echo.leave(`chat.conversation.${selectedId.value}`);
        echoChannel = null;
    }
}

function scrollToBottom() {
    nextTick(() => {
        feed.value?.scrollTo({ top: feed.value.scrollHeight });
    });
}

watch(
    () => props.selected?.id,
    () => {
        thread.value = props.selected?.messages ?? [];
        unsubscribe();
        subscribe();
        scrollToBottom();
    },
);

onMounted(() => {
    subscribe();
    scrollToBottom();
    pollTimer = window.setInterval(refreshThread, 6000);
});

onBeforeUnmount(() => {
    if (pollTimer) {
        window.clearInterval(pollTimer);
    }

    unsubscribe();
});
</script>

<template>
    <Head title="Live Chat" />

    <div class="flex flex-col gap-6 p-6">
        <PageHeader
            title="Live Chat"
            description="Conversations with visitors. Replies reach them instantly when Reverb is running."
        />

        <div class="flex flex-wrap gap-2">
            <Button
                v-for="(count, key) in counts"
                :key="key"
                size="sm"
                :variant="
                    activeStatus === (key === 'all' ? '' : key)
                        ? 'default'
                        : 'outline'
                "
                @click="
                    activeStatus = key === 'all' ? '' : key;
                    applyFilters(activeStatus);
                "
            >
                {{ key }}
                <Badge variant="secondary" class="ml-1.5">{{ count }}</Badge>
            </Button>
        </div>

        <div class="grid gap-6 lg:grid-cols-[320px_minmax(0,1fr)]">
            <!-- Conversation list -->
            <Card class="h-fit">
                <CardHeader class="pb-3">
                    <CardTitle class="text-sm font-medium"
                        >Conversations</CardTitle
                    >
                    <Input
                        v-model="search"
                        placeholder="Search visitors…"
                        class="mt-2"
                        @keydown.enter="applyFilters()"
                    />
                </CardHeader>
                <CardContent
                    class="flex max-h-[32rem] flex-col gap-1 overflow-y-auto p-2"
                >
                    <button
                        v-for="conversation in conversations"
                        :key="conversation.id"
                        type="button"
                        class="flex flex-col gap-1 rounded-md border p-2.5 text-left text-sm hover:bg-muted"
                        :class="
                            selectedId === conversation.id
                                ? 'border-primary bg-muted'
                                : 'border-transparent'
                        "
                        @click="openConversation(conversation)"
                    >
                        <span class="flex items-center justify-between gap-2">
                            <span class="font-medium">{{
                                conversation.visitor_name
                            }}</span>
                            <Badge
                                v-if="conversation.unread"
                                variant="destructive"
                                class="text-[10px]"
                            >
                                {{ conversation.unread }}
                            </Badge>
                        </span>
                        <span
                            class="flex items-center gap-2 text-[11px] text-muted-foreground"
                        >
                            <span
                                class="inline-block size-1.5 rounded-full"
                                :class="
                                    conversation.online
                                        ? 'bg-green-500'
                                        : 'bg-slate-300'
                                "
                            />
                            {{ conversation.last_message_at }}
                        </span>
                        <span class="flex items-center gap-1.5">
                            <Badge
                                :variant="statusVariant(conversation.status)"
                                class="text-[10px]"
                            >
                                {{ conversation.status }}
                            </Badge>
                            <span
                                v-if="conversation.agent"
                                class="text-[10px] text-muted-foreground"
                            >
                                {{ conversation.agent }}
                            </span>
                        </span>
                    </button>

                    <p
                        v-if="!conversations.length"
                        class="p-4 text-center text-xs text-muted-foreground"
                    >
                        No conversations yet.
                    </p>
                </CardContent>
            </Card>

            <!-- Thread -->
            <Card v-if="selected" class="flex flex-col">
                <CardHeader
                    class="flex flex-row items-center justify-between gap-3 border-b"
                >
                    <div>
                        <CardTitle class="text-sm font-medium">{{
                            selected.visitor_name
                        }}</CardTitle>
                        <p class="text-xs text-muted-foreground">
                            {{ selected.visitor_email || 'no email' }}
                            <span v-if="selected.visitor_ip">
                                · {{ selected.visitor_ip }}</span
                            >
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <Badge :variant="statusVariant(selected.status)">{{
                            selected.status
                        }}</Badge>
                        <Button
                            v-if="can.reply && selected.status !== 'closed'"
                            size="sm"
                            variant="outline"
                            @click="updateStatus(selected, 'closed')"
                        >
                            Close
                        </Button>
                        <Button
                            v-else-if="can.reply"
                            size="sm"
                            variant="outline"
                            @click="updateStatus(selected, 'open')"
                        >
                            Reopen
                        </Button>
                    </div>
                </CardHeader>

                <CardContent class="flex flex-col gap-3 p-0">
                    <div
                        v-if="can.assign"
                        class="flex items-center gap-2 border-b px-4 py-2"
                    >
                        <UserPlus class="size-4 text-muted-foreground" />
                        <select
                            class="h-8 rounded-md border border-input bg-transparent px-2 text-sm"
                            :value="selected.assigned_to ?? ''"
                            @change="
                                assignTo(
                                    selected,
                                    $event.target.value
                                        ? Number($event.target.value)
                                        : null,
                                )
                            "
                        >
                            <option value="">Unassigned</option>
                            <option
                                v-for="agent in agents"
                                :key="agent.id"
                                :value="agent.id"
                            >
                                {{ agent.name
                                }}{{ agent.online ? ' (online)' : '' }}
                            </option>
                        </select>
                    </div>

                    <div
                        ref="feed"
                        class="flex max-h-[26rem] min-h-[16rem] flex-col gap-2 overflow-y-auto px-4 py-3 text-sm"
                    >
                        <div
                            v-for="message in thread"
                            :key="message.id"
                            class="flex flex-col"
                            :class="
                                message.sender_type === 'agent'
                                    ? 'items-end'
                                    : 'items-start'
                            "
                        >
                            <div
                                class="max-w-[80%] rounded-lg px-3 py-1.5"
                                :class="
                                    message.sender_type === 'agent'
                                        ? 'bg-primary text-primary-foreground'
                                        : 'bg-muted'
                                "
                            >
                                {{ message.body }}
                            </div>
                            <span
                                class="mt-0.5 text-[10px] text-muted-foreground"
                            >
                                {{ message.author }} ·
                                {{ message.created_human }}
                            </span>
                        </div>

                        <p
                            v-if="!thread.length"
                            class="py-8 text-center text-xs text-muted-foreground"
                        >
                            No messages yet.
                        </p>
                    </div>

                    <form
                        v-if="can.reply"
                        class="flex items-center gap-2 border-t px-4 py-3"
                        @submit.prevent="sendReply"
                    >
                        <Input
                            v-model="replyBody"
                            placeholder="Type your reply…"
                            :disabled="sending || selected.status === 'closed'"
                        />
                        <Button
                            type="submit"
                            :disabled="sending || selected.status === 'closed'"
                        >
                            <Send class="size-4" />
                        </Button>
                    </form>
                    <p
                        v-else
                        class="border-t px-4 py-3 text-xs text-muted-foreground"
                    >
                        You have read-only access to conversations.
                    </p>
                </CardContent>
            </Card>

            <Card v-else class="flex items-center justify-center py-20">
                <div class="text-center text-sm text-muted-foreground">
                    <CornerUpLeft class="mx-auto mb-2 size-5" />
                    Select a conversation to read and reply.
                </div>
            </Card>
        </div>
    </div>
</template>
