import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import RichTextEditor from '@/components/admin/RichTextEditor.vue';

async function mountEditor(html) {
    const wrapper = mount(RichTextEditor, { props: { modelValue: html } });
    await wrapper.vm.$nextTick();

    return wrapper;
}

function firstImage(wrapper) {
    return wrapper.find('[contenteditable="true"] img').element;
}

/** defineModel emits instead of mutating the prop, so read the latest emission. */
function modelValue(wrapper, fallback = '') {
    const emissions = wrapper.emitted('update:modelValue');

    return emissions?.length ? emissions[emissions.length - 1][0] : fallback;
}

describe('image size and wrapping controls', () => {
    it('shows the controls only after an image is selected', async () => {
        const wrapper = await mountEditor(
            '<p>Text</p><img src="/a.png" alt="A">',
        );

        expect(wrapper.text()).not.toContain('Wrap');

        await wrapper.find('[contenteditable="true"] img').trigger('click');

        expect(wrapper.text()).toContain('Wrap');
        expect(wrapper.text()).toContain('Size');
    });

    it('applies a percentage width preset', async () => {
        const wrapper = await mountEditor('<img src="/a.png" alt="A">');

        await wrapper.find('[contenteditable="true"] img').trigger('click');

        const preset = wrapper
            .findAll('button')
            .find((b) => b.text() === '50%');
        await preset.trigger('click');

        expect(firstImage(wrapper).style.width).toBe('50%');
        expect(modelValue(wrapper)).toContain('width: 50%');
    });

    it('wraps text by floating the image left', async () => {
        const wrapper = await mountEditor('<img src="/a.png" alt="A">');

        await wrapper.find('[contenteditable="true"] img').trigger('click');
        await wrapper
            .find('[title="Image left, text wraps right"]')
            .trigger('click');

        const image = firstImage(wrapper);

        expect(image.style.float).toBe('left');
        expect(image.style.margin).toBe('0px 1rem 1rem 0px');
        expect(modelValue(wrapper)).toContain('float: left');
    });

    it('wraps text by floating the image right', async () => {
        const wrapper = await mountEditor('<img src="/a.png" alt="A">');

        await wrapper.find('[contenteditable="true"] img').trigger('click');
        await wrapper
            .find('[title="Image right, text wraps left"]')
            .trigger('click');

        expect(firstImage(wrapper).style.float).toBe('right');
    });

    it('centres the image without wrapping', async () => {
        const wrapper = await mountEditor('<img src="/a.png" alt="A">');

        await wrapper.find('[contenteditable="true"] img').trigger('click');
        await wrapper.find('[title="Centered, no wrapping"]').trigger('click');

        const image = firstImage(wrapper);

        expect(image.style.display).toBe('block');
        expect(image.style.float).toBe('');
    });

    it('edits the alt text', async () => {
        const wrapper = await mountEditor('<img src="/a.png" alt="Old">');

        await wrapper.find('[contenteditable="true"] img').trigger('click');

        const alt = wrapper.find('input[aria-label="Image alt text"]');
        await alt.setValue('A descriptive caption');
        await alt.trigger('blur');

        expect(firstImage(wrapper).getAttribute('alt')).toBe(
            'A descriptive caption',
        );
        expect(modelValue(wrapper)).toContain('alt="A descriptive caption"');
    });

    it('removes the selected image', async () => {
        const wrapper = await mountEditor(
            '<p>Keep</p><img src="/a.png" alt="A">',
        );

        await wrapper.find('[contenteditable="true"] img').trigger('click');

        const remove = wrapper
            .findAll('button')
            .find((b) => b.text().includes('Remove'));
        await remove.trigger('click');

        expect(wrapper.find('[contenteditable="true"] img').exists()).toBe(
            false,
        );
        expect(modelValue(wrapper)).toContain('Keep');
        expect(wrapper.text()).not.toContain('Wrap');
    });
});
