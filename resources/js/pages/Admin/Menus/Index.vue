<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import { ChevronRight, Pencil, Plus, Trash2, X } from '@lucide/vue';
import { ref } from 'vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input, InputError } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { dashboard } from '@/routes/admin';
import {
    index,
    store,
    update as updateMenu,
    destroy as destroyMenu,
} from '@/routes/admin/menus';
import {
    store as storeItem,
    update as updateItem,
    destroy as destroyItem,
} from '@/routes/admin/menus/items';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Navigation Menus', href: index() },
        ],
    },
});

const props = defineProps({
    menus: { type: Array, default: () => [] },
    linkables: {
        type: Object,
        default: () => ({ pages: [], posts: [], categories: [], tags: [] }),
    },
});

const activeMenu = ref(props.menus[0]?.id ?? null);
const editingMenu = ref(null);

const menuForm = useForm({ name: '', slug: '', location: '' });
const itemForm = useForm({
    label: '',
    type: 'custom',
    url: '',
    linkable_id: null,
    parent_id: null,
    target: '_self',
    order: 0,
});

const editingItem = ref(null);

const TYPE_OPTIONS = [
    { value: 'custom', label: 'Custom URL' },
    { value: 'page', label: 'Page' },
    { value: 'post', label: 'Post' },
    { value: 'category', label: 'Category' },
    { value: 'tag', label: 'Tag' },
];

function selectMenu(id) {
    activeMenu.value = id;
    editingItem.value = null;
    itemForm.reset();
}

function startMenuEdit(menu) {
    editingMenu.value = menu.id;
    menuForm.name = menu.name;
    menuForm.slug = menu.slug;
    menuForm.location = menu.location ?? '';
}

function resetMenuForm() {
    editingMenu.value = null;
    menuForm.reset();
}

function submitMenu() {
    if (editingMenu.value) {
        menuForm.put(updateMenu(editingMenu.value), {
            preserveScroll: true,
            onSuccess: () => resetMenuForm(),
        });
    } else {
        menuForm.post(store(), {
            preserveScroll: true,
            onSuccess: () => resetMenuForm(),
        });
    }
}

function deleteMenu(menu) {
    if (!confirm(`Delete menu "${menu.name}" and all its items?`)) return;
    router.delete(destroyMenu(menu.id), {
        preserveScroll: true,
        onSuccess: () => {
            if (activeMenu.value === menu.id) {
                activeMenu.value = null;
            }
        },
    });
}

function startItemEdit(item) {
    editingItem.value = item.id;
    itemForm.label = item.label;
    itemForm.type = item.type;
    itemForm.url = item.url ?? '';
    itemForm.linkable_id = item.linkable_id;
    itemForm.parent_id = item.parent_id ?? null;
    itemForm.target = item.target;
    itemForm.order = item.order;
}

function resetItemForm() {
    editingItem.value = null;
    itemForm.reset();
    itemForm.type = 'custom';
    itemForm.target = '_self';
    itemForm.order = 0;
}

function submitItem() {
    const menuId = activeMenu.value;

    if (editingItem.value) {
        itemForm.put(updateItem({ menu: menuId, item: editingItem.value }), {
            preserveScroll: true,
            onSuccess: () => resetItemForm(),
        });
    } else {
        itemForm.post(storeItem(menuId), {
            preserveScroll: true,
            onSuccess: () => resetItemForm(),
        });
    }
}

function deleteItem(item) {
    if (!confirm(`Remove "${item.label}"?`)) return;
    router.delete(destroyItem({ menu: activeMenu.value, item: item.id }), {
        preserveScroll: true,
    });
}

const LINKABLE_KEYS = {
    page: 'pages',
    post: 'posts',
    category: 'categories',
    tag: 'tags',
};

function linkableOptions(type) {
    return props.linkables[LINKABLE_KEYS[type]] ?? [];
}

const activeMenuData = () =>
    props.menus.find((m) => m.id === activeMenu.value) ?? null;
</script>

<template>
    <Head title="Navigation Menus" />

    <div class="flex flex-col gap-6 p-6">
        <PageHeader
            title="Navigation Menus"
            description="Build the site menus used by the theme."
        />

        <div class="grid gap-6 lg:grid-cols-4">
            <!-- Menu list + create -->
            <div class="flex flex-col gap-4">
                <Card>
                    <CardHeader>
                        <CardTitle class="text-sm font-medium">Menus</CardTitle>
                    </CardHeader>
                    <CardContent class="flex flex-col gap-1">
                        <button
                            v-for="menu in menus"
                            :key="menu.id"
                            class="flex items-center justify-between rounded-md px-2 py-1.5 text-left text-sm hover:bg-muted"
                            :class="
                                activeMenu === menu.id
                                    ? 'bg-muted font-medium'
                                    : ''
                            "
                            @click="selectMenu(menu.id)"
                        >
                            <span class="flex items-center gap-1">
                                <ChevronRight
                                    class="size-3.5"
                                    :class="
                                        activeMenu === menu.id
                                            ? ''
                                            : 'opacity-0'
                                    "
                                />
                                {{ menu.name }}
                            </span>
                            <Badge
                                v-if="menu.location"
                                variant="outline"
                                class="text-[10px]"
                            >
                                {{ menu.location }}
                            </Badge>
                        </button>
                        <p
                            v-if="!menus.length"
                            class="py-2 text-xs text-muted-foreground"
                        >
                            No menus yet.
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="text-sm font-medium">
                            {{ editingMenu ? 'Edit Menu' : 'New Menu' }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form
                            class="flex flex-col gap-3"
                            @submit.prevent="submitMenu"
                        >
                            <div class="flex flex-col gap-1">
                                <Label>Name</Label>
                                <Input
                                    v-model="menuForm.name"
                                    placeholder="Primary Navigation"
                                />
                                <InputError :message="menuForm.errors.name" />
                            </div>
                            <div class="flex flex-col gap-1">
                                <Label>Slug</Label>
                                <Input
                                    v-model="menuForm.slug"
                                    placeholder="primary"
                                />
                            </div>
                            <div class="flex flex-col gap-1">
                                <Label>Location</Label>
                                <Input
                                    v-model="menuForm.location"
                                    placeholder="primary / footer"
                                />
                                <InputError
                                    :message="menuForm.errors.location"
                                />
                            </div>
                            <div class="flex gap-2">
                                <Button
                                    type="submit"
                                    size="sm"
                                    :disabled="menuForm.processing"
                                    class="flex-1"
                                >
                                    <Plus v-if="!editingMenu" class="size-4" />
                                    <Pencil v-else class="size-4" />
                                    {{ editingMenu ? 'Update' : 'Create' }}
                                </Button>
                                <Button
                                    v-if="editingMenu"
                                    type="button"
                                    size="sm"
                                    variant="ghost"
                                    @click="resetMenuForm"
                                >
                                    <X class="size-4" />
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>

            <!-- Active menu items -->
            <div class="flex flex-col gap-4 lg:col-span-3">
                <Card v-if="activeMenuData()">
                    <CardHeader
                        class="flex flex-row items-center justify-between"
                    >
                        <CardTitle class="text-sm font-medium">
                            {{ activeMenuData().name }} — Items
                        </CardTitle>
                        <div class="flex gap-2">
                            <Button
                                size="sm"
                                variant="ghost"
                                @click="startMenuEdit(activeMenuData())"
                            >
                                <Pencil class="size-3.5" /> Rename
                            </Button>
                            <Button
                                size="sm"
                                variant="ghost"
                                class="text-destructive hover:text-destructive"
                                @click="deleteMenu(activeMenuData())"
                            >
                                <Trash2 class="size-3.5" /> Delete
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <ul class="flex flex-col gap-1">
                            <li
                                v-for="item in activeMenuData().items"
                                :key="item.id"
                            >
                                <div
                                    class="flex items-center justify-between rounded-md border px-3 py-2 text-sm"
                                >
                                    <span>
                                        {{ item.label }}
                                        <span
                                            class="ml-2 text-xs text-muted-foreground"
                                        >
                                            {{
                                                item.type === 'custom'
                                                    ? item.url
                                                    : item.type
                                            }}
                                        </span>
                                    </span>
                                    <div class="flex gap-1">
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            @click="startItemEdit(item)"
                                        >
                                            <Pencil class="size-3.5" />
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="text-destructive hover:text-destructive"
                                            @click="deleteItem(item)"
                                        >
                                            <Trash2 class="size-3.5" />
                                        </Button>
                                    </div>
                                </div>
                                <ul
                                    v-if="item.children.length"
                                    class="mt-1 ml-6 flex flex-col gap-1 border-l pl-3"
                                >
                                    <li
                                        v-for="child in item.children"
                                        :key="child.id"
                                        class="flex items-center justify-between rounded-md border px-3 py-1.5 text-sm"
                                    >
                                        <span>{{ child.label }}</span>
                                        <div class="flex gap-1">
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                @click="startItemEdit(child)"
                                            >
                                                <Pencil class="size-3" />
                                            </Button>
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                class="text-destructive hover:text-destructive"
                                                @click="deleteItem(child)"
                                            >
                                                <Trash2 class="size-3" />
                                            </Button>
                                        </div>
                                    </li>
                                </ul>
                            </li>
                            <li
                                v-if="!activeMenuData().items.length"
                                class="text-xs text-muted-foreground"
                            >
                                No items yet. Add one below.
                            </li>
                        </ul>
                    </CardContent>
                </Card>

                <Card v-if="activeMenuData()">
                    <CardHeader>
                        <CardTitle class="text-sm font-medium">
                            {{ editingItem ? 'Edit Item' : 'Add Item' }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form
                            class="grid gap-3 sm:grid-cols-2"
                            @submit.prevent="submitItem"
                        >
                            <div class="flex flex-col gap-1">
                                <Label>Label</Label>
                                <Input
                                    v-model="itemForm.label"
                                    placeholder="Home"
                                />
                                <InputError :message="itemForm.errors.label" />
                            </div>

                            <div class="flex flex-col gap-1">
                                <Label>Type</Label>
                                <Select v-model="itemForm.type">
                                    <SelectTrigger
                                        ><SelectValue
                                    /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="opt in TYPE_OPTIONS"
                                            :key="opt.value"
                                            :value="opt.value"
                                        >
                                            {{ opt.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div
                                v-if="itemForm.type === 'custom'"
                                class="flex flex-col gap-1 sm:col-span-2"
                            >
                                <Label>URL</Label>
                                <Input
                                    v-model="itemForm.url"
                                    placeholder="/about or https://…"
                                />
                                <InputError :message="itemForm.errors.url" />
                            </div>

                            <div
                                v-else
                                class="flex flex-col gap-1 sm:col-span-2"
                            >
                                <Label>Linked {{ itemForm.type }}</Label>
                                <Select v-model="itemForm.linkable_id">
                                    <SelectTrigger
                                        ><SelectValue placeholder="Select…"
                                    /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="opt in linkableOptions(
                                                itemForm.type,
                                            )"
                                            :key="opt.id"
                                            :value="opt.id"
                                        >
                                            {{ opt.title ?? opt.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError
                                    :message="itemForm.errors.linkable_id"
                                />
                            </div>

                            <div class="flex flex-col gap-1">
                                <Label>Target</Label>
                                <Select v-model="itemForm.target">
                                    <SelectTrigger
                                        ><SelectValue
                                    /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="_self"
                                            >Same tab</SelectItem
                                        >
                                        <SelectItem value="_blank"
                                            >New tab</SelectItem
                                        >
                                    </SelectContent>
                                </Select>
                            </div>

                            <div class="flex flex-col gap-1">
                                <Label>Order</Label>
                                <Input
                                    v-model="itemForm.order"
                                    type="number"
                                    min="0"
                                />
                            </div>

                            <div class="flex gap-2 sm:col-span-2">
                                <Button
                                    type="submit"
                                    :disabled="itemForm.processing"
                                >
                                    <Plus v-if="!editingItem" class="size-4" />
                                    <Pencil v-else class="size-4" />
                                    {{
                                        editingItem ? 'Update Item' : 'Add Item'
                                    }}
                                </Button>
                                <Button
                                    v-if="editingItem"
                                    type="button"
                                    variant="ghost"
                                    @click="resetItemForm"
                                >
                                    Cancel
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>

                <Card v-else>
                    <CardContent
                        class="py-10 text-center text-sm text-muted-foreground"
                    >
                        Select a menu to manage its items.
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>
