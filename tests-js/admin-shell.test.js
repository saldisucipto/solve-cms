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
                        'categories.manage',
                        'tags.manage',
                        'comments.moderate',
                        'media.view',
                        'menus.manage',
                        'themes.view',
                        'appearance.customize',
                        'seo.view',
                        'seo.redirects',
                        'chat.view',
                        'users.view',
                        'notifications.view',
                    ],
                },
            },
            notificationFeed: null,
        },
    },
}));

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
    },
    useForm: () => ({
        data: () => ({}),
        errors: {},
        processing: false,
        post: vi.fn(),
        put: vi.fn(),
        reset: vi.fn(),
    }),
}));

import AdminLayout from '@/layouts/AdminLayout.vue';
import Dashboard from '@/pages/Admin/Dashboard.vue';
import PagesIndex from '@/pages/Admin/Pages/Index.vue';

const breadcrumbs = [{ title: 'Dashboard', href: '/admin' }];

const stats = [
    {
        label: 'Posts',
        value: 4,
        detail: '3 published',
        href: '/admin/posts',
        icon: 'file-text',
    },
    {
        label: 'Pages',
        value: 2,
        detail: '2 published',
        href: '/admin/pages',
        icon: 'layers',
    },
    {
        label: 'Media',
        value: 0,
        detail: '0 B',
        href: '/admin/media',
        icon: 'image',
    },
    {
        label: 'Comments',
        value: 2,
        detail: '0 awaiting review',
        href: '/admin/comments',
        icon: 'message-square',
    },
    {
        label: 'Categories',
        value: 3,
        detail: '5 tags',
        href: '/admin/categories',
        icon: 'folder-tree',
    },
    {
        label: 'Users',
        value: 3,
        detail: '3 active',
        href: '/admin/users',
        icon: 'users',
    },
];

function dashboardProps() {
    return {
        stats,
        recentPosts: [],
        pendingComments: [],
        contentByStatus: { published: 3, draft: 1 },
        publishingActivity: [{ date: '2026-11-14', label: 'Nov 14', count: 1 }],
    };
}

function pagesIndexProps() {
    return {
        pages: { data: [], links: [], last_page: 1, from: 0, to: 0, total: 0 },
        filters: {},
        can: { create: true },
    };
}

describe('admin shell', () => {
    beforeEach(() => {
        document.body.innerHTML = '';
    });

    it('renders the dashboard inside the admin layout', () => {
        const wrapper = mount(AdminLayout, {
            props: { breadcrumbs },
            slots: { default: { render: () => null } },
        });

        expect(wrapper.find('aside').exists()).toBe(true);
    });

    it('mounts the dashboard page without crashing', () => {
        const wrapper = mount(Dashboard, {
            props: dashboardProps(),
            global: { stubs: { Head: true } },
        });

        expect(wrapper.text()).toContain('Posts');
    });

    it('mounts the pages index without crashing', () => {
        const wrapper = mount(PagesIndex, {
            props: pagesIndexProps(),
            global: { stubs: { Head: true } },
        });

        expect(wrapper.text().length).toBeGreaterThan(0);
    });

    it('swaps pages inside the layout (client-side navigation)', async () => {
        const Harness = {
            components: { AdminLayout, Dashboard, PagesIndex },
            props: ['current'],
            data() {
                return {
                    dashboard: dashboardProps(),
                    pages: pagesIndexProps(),
                };
            },
            template: `
                <AdminLayout :breadcrumbs="breadcrumbs">
                    <Dashboard v-if="current === 'dashboard'" v-bind="dashboard" />
                    <PagesIndex v-else v-bind="pages" />
                </AdminLayout>
            `,
            computed: {
                breadcrumbs() {
                    return [{ title: 'Dashboard', href: '/admin' }];
                },
            },
        };

        const wrapper = mount(Harness, { props: { current: 'dashboard' } });

        await wrapper.setProps({ current: 'pages' });
        await wrapper.vm.$nextTick();

        expect(wrapper.text().length).toBeGreaterThan(0);
    });
});
