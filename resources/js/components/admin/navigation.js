import {
    Activity,
    Bell,
    ExternalLink,
    FolderTree,
    Image,
    LayoutDashboard,
    Layers,
    Menu,
    MessageSquare,
    MessagesSquare,
    Palette,
    Search,
    Settings,
    ShieldCheck,
    Tags,
    Users,
} from '@lucide/vue';
import { dashboard } from '@/routes/admin';
import { index as posts } from '@/routes/admin/posts';
import { index as pages } from '@/routes/admin/pages';
import { index as categories } from '@/routes/admin/categories';
import { index as tags } from '@/routes/admin/tags';
import { index as comments } from '@/routes/admin/comments';
import { index as menus } from '@/routes/admin/menus';
import { index as media } from '@/routes/admin/media';
import { index as users } from '@/routes/admin/users';
import { index as themes } from '@/routes/admin/themes';
import { index as customizer } from '@/routes/admin/customizer';
import { index as chat } from '@/routes/admin/chat';
import { index as notifications } from '@/routes/admin/notifications';
import { index as siteSettings } from '@/routes/admin/settings';
import { index as activityLogs } from '@/routes/admin/activity';
import {
    index as seoOverview,
    settings as seoSettings,
    analysis as seoAnalysis,
} from '@/routes/admin/seo';
import { index as seoRedirects } from '@/routes/admin/seo/redirects';
import { sitemap as seoSitemap } from '@/routes/admin/seo';

export const navigationGroups = [
    {
        label: 'Overview',
        items: [
            {
                title: 'Dashboard',
                href: dashboard(),
                icon: LayoutDashboard,
                permission: null,
            },
        ],
    },
    {
        label: 'Content',
        items: [
            {
                title: 'Posts',
                href: posts(),
                icon: Layers,
                permission: 'posts.view',
            },
            {
                title: 'Pages',
                href: pages(),
                icon: Layers,
                permission: 'pages.view',
            },
            {
                title: 'Categories',
                href: categories(),
                icon: FolderTree,
                permission: 'categories.manage',
            },
            {
                title: 'Tags',
                href: tags(),
                icon: Tags,
                permission: 'tags.manage',
            },
            {
                title: 'Comments',
                href: comments(),
                icon: MessageSquare,
                permission: 'comments.moderate',
            },
            {
                title: 'Media Library',
                href: media(),
                icon: Image,
                permission: 'media.view',
            },
            {
                title: 'Navigation Menus',
                href: menus(),
                icon: Menu,
                permission: 'menus.manage',
            },
        ],
    },
    {
        label: 'Website',
        items: [
            {
                title: 'Themes',
                href: themes(),
                icon: Palette,
                permission: 'themes.view',
            },
            {
                title: 'Theme Customizer',
                href: customizer(),
                icon: Settings,
                permission: 'appearance.customize',
            },
            {
                title: 'Website Settings',
                href: siteSettings(),
                icon: Settings,
                permission: 'settings.manage',
            },
        ],
    },
    {
        label: 'SEO',
        items: [
            {
                title: 'SEO Overview',
                href: seoOverview(),
                icon: Search,
                permission: 'seo.view',
            },
            {
                title: 'SEO Settings',
                href: seoSettings(),
                icon: Settings,
                permission: 'seo.view',
            },
            {
                title: 'Redirect Manager',
                href: seoRedirects(),
                icon: ShieldCheck,
                permission: 'seo.redirects',
            },
            {
                title: 'SEO Analysis',
                href: seoAnalysis(),
                icon: Search,
                permission: 'seo.view',
            },
            {
                title: 'Sitemap',
                href: seoSitemap(),
                icon: ExternalLink,
                permission: 'seo.view',
            },
        ],
    },
    {
        label: 'Communication',
        items: [
            {
                title: 'Live Chat',
                href: chat(),
                icon: MessagesSquare,
                permission: 'chat.view',
            },
        ],
    },
    {
        label: 'Administration',
        items: [
            {
                title: 'Users',
                href: users(),
                icon: Users,
                permission: 'users.view',
            },
            {
                title: 'Notifications',
                href: notifications(),
                icon: Bell,
                permission: 'notifications.view',
            },
            {
                title: 'Activity Logs',
                href: activityLogs(),
                icon: Activity,
                permission: 'activity.view',
            },
        ],
    },
];

export function canAccess(user, permission) {
    if (permission === null) {
        return true;
    }

    if (!user) {
        return false;
    }

    if (user.is_super_admin) {
        return true;
    }

    return (
        Array.isArray(user.permissions) && user.permissions.includes(permission)
    );
}

export function visibleNavigation(user) {
    return navigationGroups
        .map((group) => ({
            ...group,
            items: group.items.filter((item) =>
                canAccess(user, item.permission),
            ),
        }))
        .filter((group) => group.items.length > 0);
}
