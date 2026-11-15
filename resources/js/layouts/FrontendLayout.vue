<script setup>
import { computed, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Menu, Search, X } from '@lucide/vue';
import ChatWidget from '@/components/frontend/ChatWidget.vue';
import SeoHead from '@/components/frontend/SeoHead.vue';
import { Toaster } from '@/components/ui/sonner';
import { search as searchRoute } from '@/routes/frontend';

const page = usePage();
const mobileOpen = ref(false);
const query = ref(
    new URLSearchParams(page.url.split('?')[1] ?? '').get('q') ?? '',
);

const site = computed(() => page.props.site ?? {});
const menus = computed(() => page.props.menus ?? { primary: [], footer: [] });
const seo = computed(() => page.props.seo ?? null);
const themeCss = computed(() => page.props.themeCss ?? null);
const cspNonce = computed(() => page.props.cspNonce ?? '');
const colors = computed(() => site.value.colors ?? {});
const typography = computed(() => site.value.typography ?? {});
</script>

<template>
    <SeoHead :seo="seo" />
    <component
        :is="'style'"
        v-if="themeCss"
        :nonce="cspNonce"
        v-html="themeCss"
    />

    <div
        class="flex min-h-screen flex-col bg-[var(--fe-bg)] font-[var(--fe-body-font)] text-[var(--fe-text)]"
        :class="site.theme?.slug ? `fe-theme-${site.theme.slug}` : ''"
        :style="{
            '--fe-primary': colors.primary,
            '--fe-secondary': colors.secondary,
            '--fe-bg': colors.background,
            '--fe-text': colors.text,
            '--fe-link': colors.link,
            '--fe-body-font': typography.body,
            '--fe-heading-font': typography.heading,
            '--fe-container': `${site.containerWidth ?? 1152}px`,
            fontSize: `${typography.size ?? 16}px`,
            lineHeight: typography.lineHeight ?? 1.7,
        }"
    >
        <header class="border-b border-black/10">
            <div
                class="mx-auto px-6 py-4"
                :style="{ maxWidth: 'var(--fe-container)' }"
                :class="
                    site.headerLayout === 'centered'
                        ? 'flex flex-col items-center gap-3 text-center'
                        : 'flex items-center gap-4'
                "
            >
                <Link href="/" class="flex items-center gap-2">
                    <img
                        v-if="site.logo"
                        :src="site.logo"
                        :alt="site.name"
                        class="h-8 w-auto"
                    />
                    <span
                        class="text-lg font-semibold tracking-tight"
                        style="font-family: var(--fe-heading-font)"
                    >
                        {{ site.name }}
                    </span>
                </Link>

                <nav
                    class="flex items-center gap-5 text-sm"
                    :class="
                        site.headerLayout === 'centered'
                            ? ''
                            : 'ml-auto hidden md:flex'
                    "
                >
                    <a
                        v-for="item in menus.primary"
                        :key="item.label + item.url"
                        :href="item.url"
                        :target="item.target"
                        class="hover:text-[var(--fe-primary)]"
                    >
                        {{ item.label }}
                    </a>
                </nav>

                <form
                    v-if="site.showSearch !== false"
                    :action="searchRoute().url"
                    method="get"
                    class="hidden items-center md:flex"
                    :class="site.headerLayout === 'centered' ? '' : 'ml-4'"
                    role="search"
                >
                    <div class="relative">
                        <Search
                            class="pointer-events-none absolute top-1/2 left-2.5 size-4 -translate-y-1/2 text-black/40"
                        />
                        <input
                            v-model="query"
                            type="search"
                            name="q"
                            placeholder="Search…"
                            aria-label="Search"
                            class="w-40 rounded-md border border-black/15 bg-transparent py-1.5 pr-3 pl-8 text-sm focus:ring-2 focus:ring-[var(--fe-primary)] focus:outline-none"
                        />
                    </div>
                </form>

                <button
                    type="button"
                    class="md:hidden"
                    :class="site.headerLayout === 'centered' ? '' : 'ml-auto'"
                    aria-label="Toggle menu"
                    @click="mobileOpen = !mobileOpen"
                >
                    <X v-if="mobileOpen" class="size-5" />
                    <Menu v-else class="size-5" />
                </button>
            </div>

            <div v-if="mobileOpen" class="border-t border-black/10 md:hidden">
                <div
                    class="mx-auto flex flex-col gap-1 px-6 py-3"
                    :style="{ maxWidth: 'var(--fe-container)' }"
                >
                    <a
                        v-for="item in menus.primary"
                        :key="'m' + item.label"
                        :href="item.url"
                        class="rounded px-2 py-1.5 text-sm hover:bg-black/5"
                        @click="mobileOpen = false"
                    >
                        {{ item.label }}
                    </a>
                    <form
                        v-if="site.showSearch !== false"
                        :action="searchRoute().url"
                        method="get"
                        class="mt-2 flex gap-2"
                        role="search"
                    >
                        <input
                            v-model="query"
                            type="search"
                            name="q"
                            placeholder="Search…"
                            class="flex-1 rounded-md border border-black/15 bg-transparent px-3 py-1.5 text-sm"
                        />
                        <button
                            type="submit"
                            class="rounded-md bg-[var(--fe-primary)] px-3 py-1.5 text-sm text-white"
                        >
                            Go
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main class="flex-1">
            <div
                class="mx-auto px-6 py-10"
                :style="{ maxWidth: 'var(--fe-container)' }"
            >
                <slot />
            </div>
        </main>

        <footer class="border-t border-black/10">
            <div
                class="mx-auto gap-4 px-6 py-8 text-sm"
                :style="{ maxWidth: 'var(--fe-container)' }"
                :class="
                    site.footerLayout === 'columns'
                        ? 'grid sm:grid-cols-3'
                        : 'flex flex-col sm:flex-row sm:items-center sm:justify-between'
                "
            >
                <div class="text-black/60">
                    © {{ new Date().getFullYear() }} {{ site.name }}. All rights
                    reserved.
                </div>

                <nav v-if="menus.footer.length" class="flex flex-wrap gap-4">
                    <a
                        v-for="item in menus.footer"
                        :key="item.label + item.url"
                        :href="item.url"
                        :target="item.target"
                        class="text-black/60 hover:text-[var(--fe-primary)]"
                    >
                        {{ item.label }}
                    </a>
                </nav>

                <div class="text-black/40">
                    Powered by
                    <a
                        href="/admin"
                        class="underline hover:text-[var(--fe-primary)]"
                        >Solve CMS</a
                    >
                </div>
            </div>
        </footer>

        <ChatWidget />

        <Toaster />
    </div>
</template>
