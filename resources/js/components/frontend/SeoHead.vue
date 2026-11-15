<script setup>
import { onMounted, watchEffect } from 'vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    seo: { type: Object, default: null },
});

const nonce = usePage().props.cspNonce ?? '';

function upsertMeta(attribute, key, value) {
    if (!value) {
        document.head.querySelector(`meta[${attribute}="${key}"]`)?.remove();

        return;
    }

    let element = document.head.querySelector(`meta[${attribute}="${key}"]`);

    if (!element) {
        element = document.createElement('meta');
        element.setAttribute(attribute, key);
        document.head.appendChild(element);
    }

    element.setAttribute('content', value);
}

function upsertLink(rel, href) {
    let element = document.head.querySelector(`link[rel="${rel}"]`);

    if (!element) {
        element = document.createElement('link');
        element.setAttribute('rel', rel);
        document.head.appendChild(element);
    }

    element.setAttribute('href', href);
}

function replaceSchemas(schemas) {
    document.head
        .querySelectorAll('script[type="application/ld+json"]')
        .forEach((node) => node.remove());

    schemas.forEach((schema) => {
        const script = document.createElement('script');
        script.type = 'application/ld+json';
        script.nonce = nonce;
        script.textContent = JSON.stringify(schema);
        document.head.appendChild(script);
    });
}

function apply(seo) {
    if (!seo) {
        return;
    }

    document.title = seo.title;

    upsertMeta('name', 'description', seo.description ?? '');
    upsertMeta('name', 'robots', seo.robots);
    upsertLink('canonical', seo.canonical);

    upsertMeta('property', 'og:type', seo.og.type);
    upsertMeta('property', 'og:title', seo.og.title);
    upsertMeta('property', 'og:description', seo.og.description ?? '');
    upsertMeta('property', 'og:url', seo.og.url);
    upsertMeta('property', 'og:site_name', seo.og.site_name);
    upsertMeta('property', 'og:image', seo.og.image ?? '');

    upsertMeta('name', 'twitter:card', 'summary_large_image');
    upsertMeta('name', 'twitter:title', seo.og.title);
    upsertMeta('name', 'twitter:description', seo.og.description ?? '');
    upsertMeta('name', 'twitter:image', seo.og.image ?? '');

    if (seo.twitterHandle) {
        upsertMeta('name', 'twitter:site', seo.twitterHandle);
    }

    replaceSchemas(seo.schemas ?? []);
}

onMounted(() => apply(props.seo));

watchEffect(() => {
    if (typeof document !== 'undefined') {
        apply(props.seo);
    }
});
</script>

<template>
    <span class="hidden" aria-hidden="true" />
</template>
