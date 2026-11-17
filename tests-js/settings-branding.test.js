import { mount } from '@vue/test-utils';
import { describe, expect, it, vi } from 'vitest';

vi.mock('@inertiajs/vue3', () => ({
    Head: { name: 'Head', template: '<div><slot /></div>' },
    Link: { name: 'Link', props: ['href'], template: '<a><slot /></a>' },
    router: {
        post: vi.fn(),
        get: vi.fn(),
        reload: vi.fn(),
        visit: vi.fn(),
        delete: vi.fn(),
        put: vi.fn(),
        patch: vi.fn(),
    },
    useForm: (initial = {}) => ({
        ...initial,
        errors: {},
        processing: false,
        put: vi.fn(),
        post: vi.fn(),
    }),
}));

import SettingsIndex from '@/pages/Admin/Settings/Index.vue';

function mountSettings() {
    return mount(SettingsIndex, {
        props: {
            settings: {
                site_name: 'Solve CMS',
                site_tagline: 'Tagline',
                site_description: 'Description',
                site_url: 'http://localhost:8000',
                posts_per_page: 10,
                timezone: 'UTC',
                logo_path: null,
                dashboard_logo_path: null,
                favicon_path: null,
            },
            timezones: ['UTC'],
        },
        global: { stubs: { Head: true } },
    });
}

describe('settings branding uploads', () => {
    for (const [label, type] of [
        ['Upload', 'logo'],
        ['Upload favicon', 'favicon'],
    ]) {
        it(`opens the hidden file input for the ${type} picker`, async () => {
            const wrapper = mountSettings();

            const button = wrapper
                .findAll('button')
                .find((b) => b.text().includes(label));

            expect(button).toBeTruthy();

            const input = wrapper.find(`input[type="file"][data-type="${type}"]`);

            expect(input.exists()).toBe(true);

            const click = vi.spyOn(input.element, 'click');

            await button.trigger('click');

            expect(click).toHaveBeenCalled();

            wrapper.unmount();
        });
    }
});
