<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['group' => 'general', 'key' => 'site_name', 'value' => 'Solve CMS', 'type' => 'string'],
            ['group' => 'general', 'key' => 'site_tagline', 'value' => 'Your Content. Your Website. Your Rules.', 'type' => 'string'],
            ['group' => 'general', 'key' => 'site_description', 'value' => 'Solve CMS is a modern, SEO-first content management system.', 'type' => 'text'],
            ['group' => 'general', 'key' => 'site_url', 'value' => config('app.url'), 'type' => 'string'],
            ['group' => 'general', 'key' => 'logo_path', 'value' => null, 'type' => 'string'],
            ['group' => 'general', 'key' => 'dashboard_logo_path', 'value' => null, 'type' => 'string'],
            ['group' => 'general', 'key' => 'favicon_path', 'value' => null, 'type' => 'string'],
            ['group' => 'general', 'key' => 'posts_per_page', 'value' => '10', 'type' => 'integer'],
            ['group' => 'general', 'key' => 'timezone', 'value' => config('app.timezone'), 'type' => 'string'],

            ['group' => 'seo', 'key' => 'seo_title_separator', 'value' => '|', 'type' => 'string'],
            ['group' => 'seo', 'key' => 'seo_default_robots', 'value' => 'index,follow', 'type' => 'string'],
            ['group' => 'seo', 'key' => 'seo_sitemap_enabled', 'value' => '1', 'type' => 'boolean'],
            ['group' => 'seo', 'key' => 'seo_organization_name', 'value' => 'Solve CMS', 'type' => 'string'],
            ['group' => 'seo', 'key' => 'seo_twitter_handle', 'value' => null, 'type' => 'string'],
            ['group' => 'seo', 'key' => 'seo_google_verification', 'value' => null, 'type' => 'string'],

            ['group' => 'appearance', 'key' => 'theme', 'value' => 'solve-default', 'type' => 'string'],
            ['group' => 'appearance', 'key' => 'primary_color', 'value' => '#2563eb', 'type' => 'string'],
            ['group' => 'appearance', 'key' => 'secondary_color', 'value' => '#0f172a', 'type' => 'string'],
            ['group' => 'appearance', 'key' => 'background_color', 'value' => '#ffffff', 'type' => 'string'],
            ['group' => 'appearance', 'key' => 'text_color', 'value' => '#0f172a', 'type' => 'string'],
            ['group' => 'appearance', 'key' => 'link_color', 'value' => '#2563eb', 'type' => 'string'],
            ['group' => 'appearance', 'key' => 'font_family', 'value' => 'Instrument Sans', 'type' => 'string'],
            ['group' => 'appearance', 'key' => 'container_width', 'value' => '1152', 'type' => 'integer'],
            ['group' => 'appearance', 'key' => 'blog_layout', 'value' => 'grid', 'type' => 'string'],
            ['group' => 'appearance', 'key' => 'sidebar_enabled', 'value' => '1', 'type' => 'boolean'],

            ['group' => 'chat', 'key' => 'chat_enabled', 'value' => '1', 'type' => 'boolean'],
            ['group' => 'chat', 'key' => 'chat_welcome_message', 'value' => 'Hi! How can we help you today?', 'type' => 'text'],
            ['group' => 'chat', 'key' => 'chat_offline_message', 'value' => 'We are away right now. Leave a message and we will reply by email.', 'type' => 'text'],
            ['group' => 'chat', 'key' => 'chat_require_email', 'value' => '0', 'type' => 'boolean'],
        ];

        foreach ($settings as $setting) {
            SiteSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting,
            );
        }
    }
}
