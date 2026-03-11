<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\View;

class ComponentController extends Controller
{
    public function index()
    {
        View::render('admin/components', [
            'title' => 'Admin Components'
        ]);
    }

    public function upload()
    {
        if (!empty($_FILES['upload'])) {
            $file = $_FILES['upload'];
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = time() . '_' . uniqid() . '.' . $ext;

            $targetDir = BASE_PATH . '/public/uploads/editor/';
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            if (move_uploaded_file($file['tmp_name'], $targetDir . $filename)) {
                header('Content-Type: application/json');
                echo json_encode([
                    'url' => '/uploads/editor/' . $filename
                ]);
                exit;
            }
        }

        header('Content-Type: application/json');
        echo json_encode([
            'error' => [
                'message' => 'Upload failed.'
            ]
        ]);
        exit;
    }
}
