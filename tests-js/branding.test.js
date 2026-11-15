import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, it, vi } from 'vitest';

const { page } = vi.hoisted(() => ({
    page: { url: '/admin', props: {} },
}));

vi.mock('@inertiajs/vue3', () => ({
    usePage: () => page,
    Link: { name: 'Link', props: ['href'], template: '<a><slot /></a>' },
    Head: { name: 'Head', template: '<div><slot /></div>' },
    router: { post: vi.fn(), get: vi.fn(), reload: vi.fn(), visit: vi.fn() },
    useForm: () => ({
        errors: {},
        processing: false,
        put: vi.fn(),
        post: vi.fn(),
    }),
}));

import AppLogo from '@/components/AppLogo.vue';
import FrontendLayout from '@/layouts/FrontendLayout.vue';

describe('dashboard branding', () => {
    beforeEach(() => {
        page.props = {
            name: 'Solve CMS',
            branding: { name: 'Solve CMS', logo: null },
        };
    });

    it('falls back to the built-in mark when no dashboard logo is set', () => {
        const wrapper = mount(AppLogo);

        expect(wrapper.find('img').exists()).toBe(false);
        expect(wrapper.text()).toContain('Solve CMS');
    });

    it('shows the uploaded dashboard logo', () => {
        page.props.branding = {
            name: 'Solve CMS',
            logo: '/storage/media/dash.png',
        };

        const wrapper = mount(AppLogo);
        const image = wrapper.find('img');

        expect(image.exists()).toBe(true);
        expect(image.attributes('src')).toBe('/storage/media/dash.png');
        expect(image.attributes('alt')).toBe('Solve CMS');
    });
});

describe('public branding', () => {
    it('uses the public logo, not the dashboard one', () => {
        page.props = {
            name: 'Solve CMS',
            branding: { name: 'Solve CMS', logo: '/storage/media/dash.png' },
            cspNonce: 'x',
            themeCss: null,
            site: {
                name: 'Solve CMS',
                logo: '/storage/media/public.png',
                colors: {},
                typography: {},
                showSearch: false,
                headerLayout: 'classic',
                footerLayout: 'simple',
                containerWidth: 1152,
            },
            menus: { primary: [], footer: [] },
            seo: null,
        };

        const wrapper = mount(FrontendLayout, {
            global: {
                stubs: {
                    ChatWidget: true,
                    Toaster: true,
                    SeoHead: true,
                },
            },
        });

        expect(wrapper.find('header img').attributes('src')).toBe(
            '/storage/media/public.png',
        );
    });
});
