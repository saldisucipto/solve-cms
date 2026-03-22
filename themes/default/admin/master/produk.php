<?php

use App\Core\View;
use App\Core\Csrf;
use App\Core\Config;

View::extend('layouts/admin');
?>

<?php View::section('content'); ?>
<div class="space-y-8 print:space-y-0">
    <!-- Header -->
    <div
        class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-4 print:hidden">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Master Produk</h1>
            <p class="text-sm text-slate-500 font-medium mt-1">Reusable components for your admin ecosystem.</p>
        </div>
        <div class="flex gap-2">
            <button onclick="$modal.open_modal()"
                class="px-4 py-2 bg-slate-800 text-white font-bold rounded-lg shadow-sm hover:bg-slate-700 transition-all text-xs flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add New Product
            </button>
        </div>
    </div>

    <!-- DataTable Section -->
    <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-200 print:hidden">
        <div class="flex items-center justify-between mb-8 border-b border-slate-100 pb-4">
            <h3 class="text-lg font-bold text-slate-800">Advanced DataTable</h3>
            <button onclick="printTable()"
                class="text-blue-600 font-bold text-xs hover:underline flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Export PDF/Print
            </button>
        </div>

        <div class="overflow-x-auto">
            <table id="myDataTable" class="w-full text-sm text-left">
                <thead>
                    <tr
                        class="text-slate-400 font-bold uppercase text-[10px] tracking-[0.15em] border-b border-slate-100">
                        <th class="px-4 py-4">ID</th>
                        <th class="px-4 py-4">User</th>
                        <th class="px-4 py-4">Role</th>
                        <th class="px-4 py-4">Status</th>
                        <th class="px-4 py-4">Date Joined</th>
                        <th class="px-4 py-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-4 py-4 font-bold text-slate-400">#<?= 1000 + $i ?></td>
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="h-8 w-8 rounded-lg bg-blue-100 text-blue-600 grid place-items-center font-bold text-[10px]">
                                        JD</div>
                                    <div>
                                        <p class="font-bold text-slate-800">John Doe <?= $i ?></p>
                                        <p class="text-[10px] text-slate-400 font-medium">john@example.com</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4 font-semibold text-slate-600">Administrator</td>
                            <td class="px-4 py-4">
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-600 text-[10px] font-black uppercase tracking-wide border border-emerald-100">Active</span>
                            </td>
                            <td class="px-4 py-4 text-slate-500 font-medium">March 12, 2024</td>
                            <td class="px-4 py-4 text-right">
                                <button
                                    class="h-8 w-8 rounded-lg hover:bg-white hover:shadow-sm border border-transparent hover:border-slate-100 text-slate-400 hover:text-blue-600 transition-all">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-auto" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Print Preview Section (Hidden by default, shown in print) -->
    <div id="print-area" class="hidden print:block bg-white p-10">
        <div class="flex justify-between items-start mb-10">
            <div>
                <h2 class="text-3xl font-black text-slate-900 mb-2"><?= Config::get('app.name') ?></h2>
                <p class="text-slate-500 font-bold uppercase tracking-widest text-xs">System Export Report</p>
            </div>
            <div class="text-right">
                <p class="text-sm font-bold text-slate-800">Report #EXP-2024-001</p>
                <p class="text-xs text-slate-400 font-medium mt-1">Generated: <?= date('F j, Y, g:i a') ?></p>
            </div>
        </div>

        <div id="print-content" class="border-t-2 border-slate-900 pt-8">
            <!-- Table content will be cloned here via JS -->
        </div>

        <div class="mt-20 pt-8 border-t border-slate-100 flex justify-between">
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Authorized By</p>
                <div class="mt-4 h-1 w-40 bg-slate-100"></div>
                <p class="mt-2 text-xs font-bold text-slate-800 italic">System Administrator</p>
            </div>
            <div class="text-right">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">System Stamp</p>
                <div
                    class="mt-4 h-16 w-16 bg-slate-50 rounded-full border-2 border-slate-100 ml-auto flex items-center justify-center font-black text-slate-200">
                    S</div>
            </div>
        </div>
    </div>

    <!-- Styles for DataTable -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <style>
        /* Custom DataTable Styling to match UI */
        .dataTables_wrapper .dataTables_length select {
            @apply bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-xs focus: outline-none focus:border-blue-500 font-bold text-slate-600 transition-all cursor-pointer mr-2;
        }

        .dataTables_wrapper .dataTables_filter input {
            @apply bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus: outline-none focus:border-blue-500 focus:bg-white transition-all font-medium text-slate-700 placeholder:text-slate-300 mb-6 min-w-[300px];
        }

        .dataTables_wrapper .dataTables_filter label {
            @apply text-xs font-bold text-slate-400 uppercase tracking-widest flex items-center gap-3;
        }

        .dataTables_wrapper .dataTables_length label {
            @apply text-xs font-bold text-slate-400 uppercase tracking-widest flex items-center;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            @apply bg-blue-600 border-blue-600 text-white !important rounded-lg font-bold text-xs shadow-sm;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            @apply border-transparent text-slate-500 font-bold text-xs !important hover: bg-slate-100 hover:text-blue-600 !important transition-all rounded-lg;
        }

        .dataTables_wrapper .dataTables_info {
            @apply text-xs font-bold text-slate-400 uppercase tracking-widest pt-4;
        }

        table.dataTable thead th {
            border-bottom: none !important;
        }

        table.dataTable.no-footer {
            border-bottom: none !important;
        }
    </style>

    <?php View::endSection(); ?>

    <?php View::section('modal') ?>
    <!-- Modal Form -->
    <div
        class="app-modal absolute hidden min-h-screen h-full top-0 z-[150] bg-black/50 backdrop-blur-sm flex items-start justify-start w-full mx-auto ">
        <div class="bg-white rounded-2xl shadow-lg w-full max-w-screen-md   mx-auto overflow-hidden mt-10">
            <div class="flex justify-between items-center px-6 py-4 border-b border-slate-100 ">
                <h3 class="modal-title text-lg font-bold text-slate-800">Modal Title</h3>
                <button onclick="window.$modal.close()"
                    class="modal-close text-slate-400 hover:text-slate-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="p-6 modal-body ">
                <!-- Dynamic content will be injected here -->
                <p class="text-sm text-slate-500">This is a modal. You can add forms or any content here.</p>
            </div>
        </div>
    </div>

    <?php View::endSection(); ?>

    <?php View::section('inject-js') ?>
    <!-- External Dependencies (Vanilla JS compliant) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

    <script>
        // Custom Upload Adapter for CKEditor
        class MyUploadAdapter {
            constructor(loader) {
                this.loader = loader;
            }

            upload() {
                return this.loader.file.then(file => new Promise((resolve, reject) => {
                    const data = new FormData();
                    data.append('upload', file);
                    data.append('_token', '<?= Csrf::token() ?>');

                    fetch('/admin/upload', {
                            method: 'POST',
                            body: data
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(`HTTP error! status: ${response.status}`);
                            }
                            return response.json();
                        })
                        .then(result => {
                            if (result.error) {
                                return reject(result.error.message);
                            }
                            resolve({
                                default: result.url
                            });
                        })
                        .catch(error => {
                            console.error('CKEditor Upload Error:', error);
                            reject('Upload failed: ' + error.message);
                        });
                }));
            }

            abort() {
                // Handle abort if needed
            }
        }

        function MyCustomUploadAdapterPlugin(editor) {
            editor.plugins.get('FileRepository').createUploadAdapter = (loader) => {
                return new MyUploadAdapter(loader);
            };
        }

        let editorInstance;

        document.addEventListener('DOMContentLoaded', function() {

            // 1. Initialize DataTable
            $('#myDataTable').DataTable({
                pageLength: 5,
                dom: '<"flex flex-col sm:flex-row justify-between items-center mb-6 gap-4"lf>rt<"flex flex-col sm:flex-row justify-between items-center mt-6 gap-4"ip>',
                language: {
                    search: "",
                    searchPlaceholder: "Search records...",
                    lengthMenu: "Show _MENU_ entries"
                }
            });

            // 2. Single Image Preview
            const singleUpload = document.getElementById('single-upload');
            const singlePreview = document.getElementById('single-preview');

            singleUpload?.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        singlePreview.innerHTML =
                            `<img src="${e.target.result}" class="h-full w-full object-cover">`;
                        singlePreview.classList.remove('border-dashed');
                        singlePreview.classList.add('border-solid', 'border-white', 'shadow-sm');
                    }
                    reader.readAsDataURL(file);
                }
            });

            // 3. Multiple Image Preview
            const multiUpload = document.getElementById('multi-upload');
            const multiContainer = document.getElementById('multi-preview-container');

            multiUpload?.addEventListener('change', function(e) {
                const files = e.target.files;
                Array.from(files).forEach(file => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const div = document.createElement('div');
                        div.className =
                            'aspect-square rounded-xl bg-slate-100 overflow-hidden relative group shadow-sm border border-slate-200';
                        div.innerHTML = `
                    <img src="${e.target.result}" class="h-full w-full object-cover">
                    <button type="button" class="absolute inset-0 bg-red-500/80 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white" onclick="this.parentElement.remove()">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                `;
                        multiContainer.insertBefore(div, multiContainer.lastElementChild);
                    }
                    reader.readAsDataURL(file);
                });
            });
        });

        // 4. Print Table Function
        function printTable() {
            const table = document.getElementById('myDataTable').cloneNode(true);
            // Remove last column (Action) from print
            const rows = table.querySelectorAll('tr');
            rows.forEach(row => {
                if (row.lastElementChild) row.lastElementChild.remove();
            });

            const printContent = document.getElementById('print-content');
            printContent.innerHTML = '';
            printContent.appendChild(table);

            window.print();
        }

        // 5. Get Editor Data
        function getEditorData() {
            if (editorInstance) {
                const data = editorInstance.getData();
                console.log("Editor Content:", data);
                alert("Editor data logged to console. Check your developer tools!");
            }
        }

        let modal = document.getElementById("app-modal");
    </script>
    <?php View::endSection() ?>