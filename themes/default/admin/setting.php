<?php

use App\Core\View;
use App\Core\Csrf;
use App\Core\Config;
use App\Helpers\Form;
use App\Helpers\Setting;

View::extend('layouts/admin');
?>

<?php View::section('content'); ?>
<div class=" flex flex-col gap-6  ">
    <h1 class="text-2xl font-bold mb-4 text-gray-600"> General Settings</h1>
    <div class="bg-white p-6 rounded-lg shadow-md drop-shadow-lg">
        <p class="text-gray-600 mb-4">Here you can manage your general website settings.</p>
        <!-- Example Setting -->
        <div class="mb-4 flex flex-col gap-3">
            <div class="flex flex-col">
                <label for="site_name" class="block font-medium text-gray-700">Site Title</label>
                <input type="text" id="site_name" name="site_name"
                    value="<?php echo Setting::setting('site_name', 'My Application'); ?>"
                    class="mt-1 p-2 block  border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm w-1/2">
            </div>
            <div class="flex flex-col">
                <label for="tagline" class="block font-medium text-gray-700">Tagline</label>
                <input type="text" id="tagline" name="tagline"
                    value="<?php echo Setting::setting('tagline', 'Site Tagline'); ?>"
                    class="mt-1 p-2 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            <div class="flex justify-between w-full gap-2">
                <div class="flex flex-col flex-1">
                    <label for="site_fav" class="block font-medium text-gray-700">Site Favicon</label>
                    <input type="file" accept=".ico" id="site_fav" name="site_fav"
                        value="<?php echo Setting::setting('site_fav', 'Site Favicon'); ?>"
                        class="mt-1 p-2 block  border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                <div class="flex flex-col flex-1">
                    <label for="site_logo" class="block font-medium text-gray-700">Site Logo</label>
                    <input type="file" accept=".webp, .png" id="site_logo" name="site_logo"
                        value="<?php echo Setting::setting('site_logo', 'Site Logo'); ?>"
                        class="mt-1 p-2 block  border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                <input type="hidden" name="group" value="general">
            </div>
            <div class=" flex mt-2 ">
                <button type="submit" class="px-4 py-1 bg-blue-500 text-white rounded-md">Save Changes</button>
            </div>
        </div>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md drop-shadow-lg">
        <p class="text-gray-600 mb-4">Here you can manage your Contact Settings.</p>
        <!-- Example Setting -->
        <div class="mb-4 flex flex-col gap-3">
            <div class="flex justify-between w-full gap-2">
                <div class="flex flex-col flex-1">
                    <label for="site_phone" class="block font-medium text-gray-700">Phone Number</label>
                    <input type="text" id="site_phone" name="site_phone"
                        value="<?php echo Setting::setting('site_phone', 'Phone Number'); ?>"
                        class="mt-1 p-2 block  border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                <div class="flex flex-col flex-1">
                    <label for="site_email" class="block font-medium text-gray-700">Email</label>
                    <input type="email" id="site_email" name="site_email"
                        value="<?php echo Setting::setting('site_email', 'Email'); ?>"
                        class="mt-1 p-2 block  border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
            </div>

            <div class="flex flex-col">
                <label for="site_address" class="block font-medium text-gray-700">Address</label>
                <textarea id="site_address" name="site_address"
                    class="mt-1 p-2 block  border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm w-1/2"><?php echo Setting::setting('site_address', '123 Main St, City, Country'); ?></textarea>
            </div>
            <input type="hidden" name="group" value="contact">
            <div class=" flex mt-2 ">
                <button type="submit" class="px-4 py-1 bg-blue-500 text-white rounded-md">Save Changes</button>
            </div>
        </div>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md drop-shadow-lg">
        <p class="text-gray-600 mb-4">Here you can manage your social media.</p>
        <!-- Example Setting -->
        <div class="mb-4 flex flex-col gap-3">
            <div class="flex justify-between w-full gap-2 h-full">
                <div class="flex flex-col flex-1">
                    <label for="social_title" class="block font-medium text-gray-700">Social Media Title</label>
                    <input type="text" id="social_title" name="social_title"
                        value="<?php echo Setting::setting('social_title', 'Social Media Title'); ?>"
                        class="mt-1 p-2 block  border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                <div class="flex flex-col flex-1">
                    <label for="social_link" class="block font-medium text-gray-700">Link</label>
                    <input type="text" id="social_link" name="social_link"
                        value="<?php echo Setting::setting('social_link', 'Social Link'); ?>"
                        class="mt-1 p-2 block  border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                <div class="flex flex-col flex-1">
                    <label for="site_logo" class="block font-medium text-gray-700">Logo</label>
                    <input type="file" id="site_logo" name="site_logo"
                        class="mt-1 p-1 block  border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                <div class="flex flex-col justify-end">
                    <button type="button" class="px-4 py-1 mb-1  bg-blue-500 text-white rounded-md">Add</button>
                </div>
            </div>
            <input type="hidden" name="group" value="contact">
        </div>
    </div>
</div>


<?php View::endSection(); ?>



<?php View::section('inject-js') ?>
<!-- External Dependencies (Vanilla JS compliant) -->

<?php View::endSection() ?>