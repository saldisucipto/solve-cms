<script setup>
import {
    AlignCenter,
    AlignLeft,
    AlignRight,
    Bold,
    Code,
    Heading2,
    Heading3,
    Italic,
    Link as LinkIcon,
    List,
    ListOrdered,
    Minus,
    Quote,
    Redo2,
    RemoveFormatting,
    Trash2,
    Underline,
    Undo2,
    WrapText,
} from '@lucide/vue';
import { nextTick, onMounted, ref, watch } from 'vue';
import MediaPicker from '@/components/admin/MediaPicker.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

const props = defineProps({
    placeholder: { type: String, default: 'Write your content…' },
});

const model = defineModel({ type: String, default: '' });

const editor = ref(null);
const isFocused = ref(false);
const selectedImage = ref(null);
const imageWidth = ref(50);
const imageUnit = ref('%');
const imageAlt = ref('');

const TOOLS = [
    { label: 'Undo', icon: Undo2, command: 'undo' },
    { label: 'Redo', icon: Redo2, command: 'redo' },
    {
        label: 'Heading 2',
        icon: Heading2,
        command: 'formatBlock',
        value: '<h2>',
    },
    {
        label: 'Heading 3',
        icon: Heading3,
        command: 'formatBlock',
        value: '<h3>',
    },
    { label: 'Bold', icon: Bold, command: 'bold' },
    { label: 'Italic', icon: Italic, command: 'italic' },
    { label: 'Underline', icon: Underline, command: 'underline' },
    { label: 'Bullet list', icon: List, command: 'insertUnorderedList' },
    { label: 'Numbered list', icon: ListOrdered, command: 'insertOrderedList' },
    {
        label: 'Quote',
        icon: Quote,
        command: 'formatBlock',
        value: '<blockquote>',
    },
    { label: 'Code block', icon: Code, command: 'formatBlock', value: '<pre>' },
    { label: 'Horizontal rule', icon: Minus, command: 'insertHorizontalRule' },
    {
        label: 'Clear formatting',
        icon: RemoveFormatting,
        command: 'removeFormat',
    },
];

const WIDTH_PRESETS = [25, 50, 75, 100];

onMounted(() => {
    if (editor.value) {
        editor.value.innerHTML = model.value ?? '';
    }
});

watch(
    () => model.value,
    (value) => {
        if (!editor.value || isFocused.value) {
            return;
        }

        const next = value ?? '';

        if (editor.value.innerHTML !== next) {
            editor.value.innerHTML = next;
            selectedImage.value = null;
        }
    },
);

function sync() {
    if (editor.value) {
        model.value = editor.value.innerHTML;
    }
}

async function run(tool) {
    editor.value?.focus();

    if (tool.command === 'formatBlock') {
        document.execCommand('formatBlock', false, tool.value);
    } else {
        document.execCommand(tool.command, false, null);
    }

    await nextTick();
    sync();
}

function addLink() {
    const url = window.prompt('Link URL');

    if (!url) {
        return;
    }

    editor.value?.focus();
    document.execCommand('createLink', false, url);
    sync();
}

/**
 * Insert an image chosen from the media library at the caret position, then
 * select it so it can be resized or wrapped straight away.
 */
async function insertImage(item) {
    if (!item?.url) {
        return;
    }

    const alt = (item.alt_text ?? item.title ?? '').replace(/"/g, '&quot;');
    const html = `<img src="${item.url}" alt="${alt}" loading="lazy" style="max-width: 100%;" />`;

    editor.value?.focus();
    document.execCommand('insertHTML', false, html);

    await nextTick();

    const images = editor.value?.querySelectorAll('img') ?? [];

    if (images.length > 0) {
        selectImage(images[images.length - 1]);
    }

    sync();
}

function onEditorClick(event) {
    if (event.target?.tagName === 'IMG') {
        selectImage(event.target);

        return;
    }

    clearSelection();
}

function selectImage(element) {
    clearSelection();

    selectedImage.value = element;
    element.dataset.selected = 'true';

    imageAlt.value = element.getAttribute('alt') ?? '';

    const style = element.style;
    const width = style.width || '';

    if (width.endsWith('%')) {
        imageUnit.value = '%';
        imageWidth.value = Number.parseInt(width, 10) || 50;
    } else if (width.endsWith('px')) {
        imageUnit.value = 'px';
        imageWidth.value = Number.parseInt(width, 10) || 320;
    } else {
        imageUnit.value = '%';
        imageWidth.value = 100;
    }
}

function clearSelection() {
    if (selectedImage.value) {
        delete selectedImage.value.dataset.selected;
    }

    selectedImage.value = null;
}

function applyWidth() {
    const element = selectedImage.value;

    if (!element) {
        return;
    }

    const value = Number(imageWidth.value) || 0;
    const clamped = Math.min(100, Math.max(5, value));

    imageWidth.value = clamped;

    element.style.width =
        imageUnit.value === '%' ? `${clamped}%` : `${clamped}px`;
    element.style.height = 'auto';
    element.style.maxWidth = '100%';

    sync();
}

function applyPreset(preset) {
    imageUnit.value = '%';
    imageWidth.value = preset;
    applyWidth();
}

/**
 * `float` makes the surrounding text flow beside the image; centre and full
 * width clear the float.
 */
function applyAlignment(alignment) {
    const element = selectedImage.value;

    if (!element) {
        return;
    }

    element.style.float = '';
    element.style.display = '';
    element.style.margin = '';

    if (alignment === 'left') {
        element.style.float = 'left';
        element.style.margin = '0 1rem 1rem 0';
    } else if (alignment === 'right') {
        element.style.float = 'right';
        element.style.margin = '0 0 1rem 1rem';
    } else if (alignment === 'center') {
        element.style.display = 'block';
        element.style.margin = '1rem auto';
    } else if (alignment === 'full') {
        element.style.display = 'block';
        element.style.width = '100%';
        element.style.margin = '1rem 0';
        imageWidth.value = 100;
        imageUnit.value = '%';
    }

    sync();
}

function applyAlt() {
    const element = selectedImage.value;

    if (!element) {
        return;
    }

    element.setAttribute('alt', imageAlt.value);
    sync();
}

function removeImage() {
    const element = selectedImage.value;

    if (!element) {
        return;
    }

    element.remove();
    selectedImage.value = null;
    sync();
}

function onPaste(event) {
    event.preventDefault();

    const text = event.clipboardData?.getData('text/plain') ?? '';
    document.execCommand('insertText', false, text);
    sync();
}

function onBlur() {
    isFocused.value = false;
    sync();
}
</script>

<template>
    <div
        class="rounded-md border border-input shadow-sm focus-within:ring-1 focus-within:ring-ring"
    >
        <div class="flex flex-wrap items-center gap-0.5 border-b p-1">
            <Button
                v-for="tool in TOOLS"
                :key="tool.label"
                type="button"
                variant="ghost"
                size="icon"
                class="size-7"
                :title="tool.label"
                :aria-label="tool.label"
                @mousedown.prevent="run(tool)"
            >
                <component :is="tool.icon" class="size-3.5" />
            </Button>

            <Button
                type="button"
                variant="ghost"
                size="icon"
                class="size-7"
                title="Insert link"
                aria-label="Insert link"
                @mousedown.prevent="addLink"
            >
                <LinkIcon class="size-3.5" />
            </Button>

            <MediaPicker compact label="Insert image" @selected="insertImage" />
        </div>

        <!-- Image controls: size, wrapping, alt text -->
        <div
            v-if="selectedImage"
            class="flex flex-wrap items-end gap-3 border-b bg-muted/40 px-2 py-2 text-xs"
        >
            <div class="flex items-center gap-1">
                <span class="text-muted-foreground">Size</span>
                <Button
                    v-for="preset in WIDTH_PRESETS"
                    :key="preset"
                    type="button"
                    size="sm"
                    variant="ghost"
                    class="h-7 px-2 text-xs"
                    @click="applyPreset(preset)"
                >
                    {{ preset }}%
                </Button>
            </div>

            <div class="flex items-center gap-1">
                <Input
                    v-model="imageWidth"
                    type="number"
                    min="5"
                    max="2000"
                    class="h-7 w-16 text-xs"
                    aria-label="Image width"
                />
                <select
                    v-model="imageUnit"
                    class="h-7 rounded-md border border-input bg-transparent px-1 text-xs"
                    aria-label="Width unit"
                >
                    <option value="%">%</option>
                    <option value="px">px</option>
                </select>
                <Button
                    type="button"
                    size="sm"
                    variant="secondary"
                    class="h-7 px-2 text-xs"
                    @click="applyWidth"
                >
                    Apply
                </Button>
            </div>

            <div class="flex items-center gap-1">
                <span class="text-muted-foreground">Wrap</span>
                <Button
                    type="button"
                    size="icon"
                    variant="ghost"
                    class="size-7"
                    title="Image left, text wraps right"
                    aria-label="Float left"
                    @click="applyAlignment('left')"
                >
                    <AlignLeft class="size-3.5" />
                </Button>
                <Button
                    type="button"
                    size="icon"
                    variant="ghost"
                    class="size-7"
                    title="Centered, no wrapping"
                    aria-label="Center"
                    @click="applyAlignment('center')"
                >
                    <AlignCenter class="size-3.5" />
                </Button>
                <Button
                    type="button"
                    size="icon"
                    variant="ghost"
                    class="size-7"
                    title="Image right, text wraps left"
                    aria-label="Float right"
                    @click="applyAlignment('right')"
                >
                    <AlignRight class="size-3.5" />
                </Button>
                <Button
                    type="button"
                    size="icon"
                    variant="ghost"
                    class="size-7"
                    title="Full width"
                    aria-label="Full width"
                    @click="applyAlignment('full')"
                >
                    <WrapText class="size-3.5" />
                </Button>
            </div>

            <div class="flex items-center gap-1">
                <span class="text-muted-foreground">Alt</span>
                <Input
                    v-model="imageAlt"
                    class="h-7 w-40 text-xs"
                    placeholder="Describe the image"
                    aria-label="Image alt text"
                    @keydown.enter.prevent="applyAlt"
                    @blur="applyAlt"
                />
            </div>

            <Button
                type="button"
                size="sm"
                variant="ghost"
                class="ml-auto h-7 gap-1 px-2 text-xs text-destructive hover:text-destructive"
                @click="removeImage"
            >
                <Trash2 class="size-3.5" />
                Remove
            </Button>
        </div>

        <div
            ref="editor"
            class="min-h-[320px] px-3 py-2 text-sm leading-relaxed outline-none [&_blockquote]:border-l-2 [&_blockquote]:pl-3 [&_blockquote]:text-muted-foreground [&_h2]:mt-3 [&_h2]:text-lg [&_h2]:font-semibold [&_h3]:mt-2 [&_h3]:font-semibold [&_img]:h-auto [&_img]:cursor-pointer [&_img]:rounded [&_ol]:list-decimal [&_ol]:pl-5 [&_pre]:overflow-x-auto [&_pre]:rounded [&_pre]:bg-muted [&_pre]:p-2 [&_ul]:list-disc [&_ul]:pl-5"
            contenteditable="true"
            role="textbox"
            aria-multiline="true"
            :aria-label="placeholder"
            :data-placeholder="placeholder"
            @input="sync"
            @focus="isFocused = true"
            @blur="onBlur"
            @paste="onPaste"
            @click="onEditorClick"
        />
    </div>
</template>

<style scoped>
[contenteditable][data-placeholder]:empty::before {
    content: attr(data-placeholder);
    color: var(--color-muted-foreground, #94a3b8);
    pointer-events: none;
}

[contenteditable] :deep(img[data-selected='true']) {
    outline: 2px solid var(--color-primary, #2563eb);
    outline-offset: 2px;
}
</style>
