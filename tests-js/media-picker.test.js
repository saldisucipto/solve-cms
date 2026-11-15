import { mount } from '@vue/test-utils';
import { afterEach, describe, expect, it, vi } from 'vitest';
import MediaPicker from '@/components/admin/MediaPicker.vue';
import RichTextEditor from '@/components/admin/RichTextEditor.vue';

const ITEMS = [
    {
        id: 1,
        url: '/storage/a.png',
        thumbnail: '/storage/a-thumb.png',
        title: 'Alpha',
        alt_text: 'Alpha alt',
        mime_type: 'image/png',
        width: 800,
        height: 600,
    },
    {
        id: 2,
        url: '/storage/b.png',
        thumbnail: null,
        title: 'Beta',
        alt_text: null,
        mime_type: 'image/png',
        width: 400,
        height: 400,
    },
];

function stubFetch() {
    return vi.fn(async () => ({
        ok: true,
        json: async () => ({ items: ITEMS }),
    }));
}

afterEach(() => {
    vi.restoreAllMocks();
});

describe('media picker', () => {
    it('loads the library and emits the chosen image', async () => {
        global.fetch = stubFetch();

        const wrapper = mount(MediaPicker, {
            props: { modelValue: null, label: 'Choose image' },
        });

        await wrapper.find('button').trigger('click');
        await new Promise((resolve) => setTimeout(resolve, 0));
        await wrapper.vm.$nextTick();

        expect(global.fetch).toHaveBeenCalled();

        const tiles = wrapper
            .findAll('button[type="button"]')
            .filter((b) => b.find('img').exists());
        expect(tiles.length).toBe(2);

        await tiles[0].trigger('click');
        await wrapper.vm.$nextTick();

        expect(wrapper.emitted('update:modelValue')?.[0]).toEqual([1]);
        expect(wrapper.emitted('selected')?.[0][0].url).toBe('/storage/a.png');
    });

    it('renders a compact trigger for the editor toolbar', () => {
        const wrapper = mount(MediaPicker, {
            props: { modelValue: null, compact: true, label: 'Insert image' },
        });

        expect(wrapper.find('[title="Insert image"]').exists()).toBe(true);
        expect(wrapper.text()).not.toContain('No image selected');
    });
});

describe('rich text editor images', () => {
    it('offers an insert image control', () => {
        const wrapper = mount(RichTextEditor, {
            props: { modelValue: '<p>Hello</p>' },
        });

        expect(wrapper.find('[title="Insert image"]').exists()).toBe(true);
        expect(wrapper.find('[contenteditable="true"]').exists()).toBe(true);
    });

    it('renders existing content', () => {
        const wrapper = mount(RichTextEditor, {
            props: { modelValue: '<p>Existing body</p>' },
        });

        expect(wrapper.find('[contenteditable="true"]').html()).toContain(
            'Existing body',
        );
    });
});
