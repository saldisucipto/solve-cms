<script setup>
import { CheckCircle2, AlertTriangle, XCircle, Loader2 } from '@lucide/vue';
import { computed, onMounted, ref, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { requestJson } from '@/lib/http';
import { analyze as analyzeRoute } from '@/routes/admin/seo';

const props = defineProps({
    title: { type: String, default: '' },
    seoTitle: { type: String, default: '' },
    description: { type: String, default: '' },
    focusKeyword: { type: String, default: '' },
    slug: { type: String, default: '' },
    content: { type: String, default: '' },
    canonicalUrl: { type: String, default: '' },
    featuredImageAlt: { type: String, default: '' },
    siteUrl: { type: String, default: '' },
    ignoreSeoId: { type: Number, default: null },
});

const result = ref(null);
const loading = ref(false);
const errors = ref([]);

const snippetTitle = computed(
    () => props.seoTitle || props.title || 'Untitled',
);
const snippetSlug = computed(() => (props.slug || '').replace(/^-+|-+$/g, ''));
const breadcrumb = computed(() => {
    const base = (props.siteUrl || '')
        .replace(/^https?:\/\//, '')
        .replace(/\/$/, '');
    return `${base} › ${snippetSlug.value}`;
});

const STATUS_ICON = {
    good: CheckCircle2,
    warning: AlertTriangle,
    bad: XCircle,
};

const STATUS_CLASS = {
    good: 'text-green-600 dark:text-green-500',
    warning: 'text-amber-600 dark:text-amber-500',
    bad: 'text-destructive',
};

let timer = null;

async function run() {
    loading.value = true;
    errors.value = [];

    try {
        result.value = await requestJson(analyzeRoute.url(), {
            method: 'POST',
            body: JSON.stringify({
                title: props.title,
                seo_title: props.seoTitle,
                meta_description: props.description,
                focus_keyword: props.focusKeyword,
                slug: props.slug,
                content: props.content,
                canonical_url: props.canonicalUrl,
                featured_image_alt: props.featuredImageAlt,
                ignore_seo_id: props.ignoreSeoId,
            }),
        });
    } catch (error) {
        errors.value = error.messages?.() ?? ['Could not run the analysis.'];
    } finally {
        loading.value = false;
    }
}

function schedule() {
    if (timer) {
        clearTimeout(timer);
    }

    timer = setTimeout(run, 700);
}

watch(
    () => [
        props.title,
        props.seoTitle,
        props.description,
        props.focusKeyword,
        props.slug,
        props.content,
        props.canonicalUrl,
    ],
    schedule,
);

onMounted(run);
</script>

<template>
    <div class="flex flex-col gap-4">
        <!-- Google search preview -->
        <Card>
            <CardHeader>
                <CardTitle class="text-sm font-medium"
                    >Search result preview</CardTitle
                >
            </CardHeader>
            <CardContent>
                <div class="rounded-md border bg-white p-3 dark:bg-slate-950">
                    <p class="text-xs text-slate-600 dark:text-slate-400">
                        {{ breadcrumb }}
                    </p>
                    <p
                        class="mt-0.5 line-clamp-1 text-base text-blue-700 dark:text-blue-400"
                    >
                        {{ snippetTitle }}
                    </p>
                    <p
                        class="mt-0.5 line-clamp-2 text-sm text-slate-600 dark:text-slate-400"
                    >
                        {{
                            description ||
                            'No meta description set — search engines will choose their own snippet.'
                        }}
                    </p>
                </div>
                <div class="mt-2 flex gap-4 text-xs text-muted-foreground">
                    <span>Title: {{ snippetTitle.length }} chars</span>
                    <span>Description: {{ description.length }} chars</span>
                </div>
            </CardContent>
        </Card>

        <!-- Analysis -->
        <Card>
            <CardHeader class="flex flex-row items-center justify-between">
                <CardTitle class="text-sm font-medium">SEO checks</CardTitle>
                <div class="flex items-center gap-2">
                    <Loader2
                        v-if="loading"
                        class="size-3.5 animate-spin text-muted-foreground"
                    />
                    <template v-if="result">
                        <Badge variant="secondary"
                            >{{ result.summary.good }} good</Badge
                        >
                        <Badge v-if="result.summary.warning" variant="outline">
                            {{ result.summary.warning }} warnings
                        </Badge>
                        <Badge v-if="result.summary.bad" variant="destructive">
                            {{ result.summary.bad }} issues
                        </Badge>
                    </template>
                </div>
            </CardHeader>
            <CardContent>
                <ul
                    v-if="errors.length"
                    class="flex flex-col gap-1 text-sm text-destructive"
                >
                    <li v-for="(message, index) in errors" :key="index">
                        {{ message }}
                    </li>
                </ul>

                <ul v-else-if="result" class="flex flex-col gap-2.5">
                    <li
                        v-for="check in result.checks"
                        :key="check.id"
                        class="flex gap-2 text-sm"
                    >
                        <component
                            :is="STATUS_ICON[check.status]"
                            class="mt-0.5 size-4 shrink-0"
                            :class="STATUS_CLASS[check.status]"
                        />
                        <div>
                            <span class="font-medium">{{ check.label }}:</span>
                            <span class="text-muted-foreground">
                                {{ check.message }}</span
                            >
                        </div>
                    </li>
                </ul>

                <p v-else class="text-sm text-muted-foreground">
                    Running checks…
                </p>

                <div
                    v-if="result"
                    class="mt-4 flex flex-wrap gap-3 border-t pt-3 text-xs text-muted-foreground"
                >
                    <span>{{ result.stats.words }} words</span>
                    <span>{{ result.stats.headings }} subheadings</span>
                    <span
                        >{{ result.stats.internal_links }} internal links</span
                    >
                    <span
                        >{{ result.stats.external_links }} external links</span
                    >
                    <span>{{ result.stats.images }} images</span>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
