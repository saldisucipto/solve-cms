import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, it, vi } from 'vitest';

const { page } = vi.hoisted(() => ({
    page: {
        url: '/admin',
        props: {
            name: 'Solve CMS',
            auth: {
                user: {
                    id: 1,
                    name: 'Solve Administrator',
                    email: 'admin@solvecms.test',
                    roles: ['Super Admin'],
                    is_super_admin: true,
                    permissions: [
                        'posts.view',
                        'pages.view',
                        'tags.manage',
                        'users.view',
                    ],
                },
            },
            notificationFeed: { unread: 0, items: [] },
        },
    },
}));

import { reactive } from 'vue';

vi.mock('@inertiajs/vue3', () => ({
    usePage: () => page,
    Link: {
        name: 'Link',
        props: ['href', 'method', 'as'],
        template: '<a><slot /></a>',
    },
    Head: { name: 'Head', template: '<div><slot /></div>' },
    router: {
        post: vi.fn(),
        get: vi.fn(),
        reload: vi.fn(),
        visit: vi.fn(),
        delete: vi.fn(),
        put: vi.fn(),
        patch: vi.fn(),
    },
    useForm: (initial = {}) =>
        reactive({
            ...initial,
            errors: {},
            processing: false,
            recentlySuccessful: false,
            post: vi.fn(),
            put: vi.fn(),
            patch: vi.fn(),
            delete: vi.fn(),
            reset: vi.fn(),
            clearErrors: vi.fn(),
        }),
}));

const paginator = (rows = []) => ({
    data: rows,
    links: [
        { url: null, label: '&laquo; Previous', active: false },
        { url: '/x', label: '1', active: true },
    ],
    current_page: 1,
    last_page: 1,
    from: rows.length ? 1 : 0,
    to: rows.length,
    total: rows.length,
});

const PAGES = [
    [
        'Admin/Dashboard',
        () => import('@/pages/Admin/Dashboard.vue'),
        () => ({
            stats: [
                {
                    label: 'Posts',
                    value: 1,
                    detail: '1 published',
                    href: '/admin/posts',
                    icon: 'file-text',
                },
            ],
            recentPosts: [],
            pendingComments: [],
            contentByStatus: { published: 1, draft: 0 },
            publishingActivity: [
                { date: '2026-11-14', label: 'Nov 14', count: 1 },
            ],
        }),
    ],
    [
        'Admin/Posts/Index',
        () => import('@/pages/Admin/Posts/Index.vue'),
        () => ({
            posts: paginator(),
            filters: {},
            can: { create: true },
        }),
    ],
    [
        'Admin/Posts/Form',
        () => import('@/pages/Admin/Posts/Form.vue'),
        () => ({
            post: null,
            options: {
                categories: [{ id: 1, name: 'Engineering' }],
                tags: [{ id: 1, name: 'laravel' }],
                media: [],
            },
        }),
    ],
    [
        'Admin/Pages/Index',
        () => import('@/pages/Admin/Pages/Index.vue'),
        () => ({
            pages: paginator(),
            filters: {},
            can: { create: true },
        }),
    ],
    [
        'Admin/Pages/Form',
        () => import('@/pages/Admin/Pages/Form.vue'),
        () => ({
            page: null,
            options: {
                parents: [{ id: 1, title: 'About' }],
                templates: [{ value: 'default', label: 'Default' }],
                media: [],
            },
        }),
    ],
    [
        'Admin/Categories/Index',
        () => import('@/pages/Admin/Categories/Index.vue'),
        () => ({
            categories: [
                {
                    id: 1,
                    name: 'News',
                    slug: 'news',
                    description: null,
                    order: 0,
                    parent_id: null,
                    parent: null,
                    posts_count: 0,
                },
            ],
            parents: [{ id: 1, name: 'News' }],
        }),
    ],
    [
        'Admin/Tags/Index',
        () => import('@/pages/Admin/Tags/Index.vue'),
        () => ({
            tags: paginator([
                {
                    id: 1,
                    name: 'laravel',
                    slug: 'laravel',
                    description: null,
                    posts_count: 1,
                },
            ]),
        }),
    ],
    [
        'Admin/Comments/Index',
        () => import('@/pages/Admin/Comments/Index.vue'),
        () => ({
            comments: paginator(),
            counts: { all: 0, pending: 0, approved: 0, spam: 0, rejected: 0 },
            filters: {},
        }),
    ],
    [
        'Admin/Media/Index',
        () => import('@/pages/Admin/Media/Index.vue'),
        () => ({
            media: paginator(),
            filters: {},
            can: { upload: true, delete: true },
        }),
    ],
    [
        'Admin/Menus/Index',
        () => import('@/pages/Admin/Menus/Index.vue'),
        () => ({
            menus: [
                {
                    id: 1,
                    name: 'Primary',
                    slug: 'primary',
                    location: 'primary',
                    items: [],
                },
            ],
            linkables: { pages: [], posts: [], categories: [], tags: [] },
        }),
    ],
    [
        'Admin/Users/Index',
        () => import('@/pages/Admin/Users/Index.vue'),
        () => ({
            users: paginator(),
            roles: [{ id: 1, label: 'Super Admin' }],
            filters: {},
            can: { manage: true },
        }),
    ],
    [
        'Admin/Themes/Index',
        () => import('@/pages/Admin/Themes/Index.vue'),
        () => ({
            themes: [
                {
                    slug: 'solve-default',
                    name: 'Solve Default',
                    version: '1.0.0',
                    author: 'Solve CMS',
                    description: null,
                    is_active: true,
                    has_stylesheet: true,
                    templates: { blog: ['grid'] },
                    has_draft: false,
                },
            ],
            can: { manage: true, customize: true },
            previewUrl: '/?theme-preview=1',
        }),
    ],
    [
        'Admin/Customizer/Index',
        () => import('@/pages/Admin/Customizer/Index.vue'),
        () => ({
            theme: {
                slug: 'solve-default',
                name: 'Solve Default',
                templates: {
                    header: ['classic'],
                    footer: ['simple'],
                    blog: ['grid'],
                    homepage: ['featured'],
                },
            },
            settings: {},
            defaults: {},
            hasDraft: false,
            general: { site_name: 'Solve CMS' },
            images: [],
            previewUrl: '/?theme-preview=1',
        }),
    ],
    [
        'Admin/Seo/Overview',
        () => import('@/pages/Admin/Seo/Overview.vue'),
        () => ({
            stats: {
                content: 0,
                missing_title: 0,
                missing_description: 0,
                missing_keyword: 0,
                redirects: 0,
                active_redirects: 0,
            },
            urls: { sitemap: '/sitemap.xml', robots: '/robots.txt' },
            sitemapEnabled: true,
            recentRedirects: [],
        }),
    ],
    [
        'Admin/Seo/Settings',
        () => import('@/pages/Admin/Seo/Settings.vue'),
        () => ({
            settings: {},
            can: { manage: true },
        }),
    ],
    [
        'Admin/Seo/Analysis',
        () => import('@/pages/Admin/Seo/Analysis.vue'),
        () => ({
            items: [],
            filters: { type: 'all', issue: '' },
            issueTypes: { missing_title: 'Missing SEO title' },
        }),
    ],
    [
        'Admin/Seo/Sitemap',
        () => import('@/pages/Admin/Seo/Sitemap.vue'),
        () => ({
            enabled: true,
            url: 'http://localhost:8000/sitemap.xml',
            robotsUrl: 'http://localhost:8000/robots.txt',
            counts: { home: 1, page: 2, post: 3, category: 3, tag: 5 },
            total: 14,
            entries: [
                {
                    loc: 'http://localhost:8000/',
                    lastmod: '2026-11-15T00:00:00+00:00',
                    changefreq: 'daily',
                    priority: '1.0',
                    type: 'home',
                },
                {
                    loc: 'http://localhost:8000/about',
                    lastmod: null,
                    changefreq: 'monthly',
                    priority: '0.8',
                    type: 'page',
                },
            ],
            truncated: false,
        }),
    ],
    [
        'Admin/Seo/Redirects',
        () => import('@/pages/Admin/Seo/Redirects.vue'),
        () => ({
            redirects: paginator(),
            filters: {},
            statusCodes: [301, 302],
        }),
    ],
    [
        'Admin/Chat/Index',
        () => import('@/pages/Admin/Chat/Index.vue'),
        () => ({
            conversations: [
                {
                    id: 1,
                    token: 't',
                    visitor_name: 'Visitor',
                    visitor_email: null,
                    status: 'open',
                    agent: null,
                    assigned_to: null,
                    unread: 1,
                    last_message_at: 'now',
                    online: true,
                },
            ],
            selected: {
                id: 1,
                token: 't',
                visitor_name: 'Visitor',
                visitor_email: null,
                visitor_ip: '127.0.0.1',
                status: 'open',
                assigned_to: null,
                agent: null,
                messages: [
                    {
                        id: 1,
                        sender_type: 'visitor',
                        body: 'Hello',
                        author: 'Visitor',
                        created_human: 'now',
                    },
                ],
            },
            agents: [{ id: 1, name: 'Agent', online: true }],
            counts: {
                all: 1,
                open: 1,
                pending: 0,
                assigned: 0,
                closed: 0,
                unread: 1,
            },
            filters: {},
            can: { reply: true, assign: true },
        }),
    ],
    [
        'Admin/Settings/Index',
        () => import('@/pages/Admin/Settings/Index.vue'),
        () => ({
            settings: {
                site_name: 'Solve CMS',
                site_tagline: 'Tagline',
                site_description: 'Description',
                site_url: 'http://localhost:8000',
                posts_per_page: 10,
                timezone: 'UTC',
                logo_path: null,
                favicon_path: null,
            },
            timezones: ['UTC', 'Asia/Jakarta'],
        }),
    ],
    [
        'Admin/Activity/Index',
        () => import('@/pages/Admin/Activity/Index.vue'),
        () => ({
            logs: paginator([
                {
                    id: 1,
                    event: 'settings.updated',
                    description: 'Updated website settings',
                    user: 'Solve Administrator',
                    subject_type: null,
                    subject_id: null,
                    ip_address: '127.0.0.1',
                    created_at: '2026-11-15T00:00:00+00:00',
                    created_human: 'just now',
                },
            ]),
            events: ['settings.updated'],
            users: [{ id: 1, name: 'Solve Administrator' }],
            filters: { search: '', event: '', user: null },
        }),
    ],
    [
        'Admin/Notifications/Index',
        () => import('@/pages/Admin/Notifications/Index.vue'),
        () => ({
            notifications: paginator([
                {
                    id: 'a',
                    title: 'New comment',
                    body: 'x',
                    url: '/admin/comments',
                    type: 'comment',
                    read: false,
                    created_at: 'now',
                },
            ]),
            unread: 1,
        }),
    ],
];

describe('admin pages render', () => {
    beforeEach(() => {
        document.body.innerHTML = '';
    });

    for (const [name, load, props] of PAGES) {
        it('renders ' + String(name), async () => {
            const component = (await load()).default;
            const wrapper = mount(component, {
                props: props(),
                global: { stubs: { Head: true } },
            });

            expect(wrapper.html().length).toBeGreaterThan(0);
            wrapper.unmount();
        });
    }
});
