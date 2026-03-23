<?php

namespace App\Controllers\Admin\Master;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Core\Validate;
use App\Helpers\Debug;

class MasterController extends Controller
{
    public function store_master_customer()
    {
        // Validasi input
        $validasi = new Validate();
        $data = Request::all();

        $isValid = $validasi->check($data, [
            'nama' => 'required|min:3',
            'email' => 'required|email',
            'npwp' => 'required',
            'customer_person' => 'required',
            'coa' => 'required',
        ]);
        $input_errors = $validasi->errors();
        // Debug::dd($input_errors);
        // Jika ada error, redirect kembali dengan error
        if (!empty($input_errors)) {
            $_SESSION['input_errors'] = $input_errors;
            Session::set('_old', $data);
            header('Location: /admin/master/customer');
            exit;
        }

        // Simpan data ke database (contoh menggunakan PDO)
        // Pastikan untuk mengganti dengan logika penyimpanan yang sesuai dengan aplikasi Anda
        /*
        $stmt = $pdo->prepare("INSERT INTO customers (name, email, npwp, customer_person, coa) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$_POST['name'], $_POST['email'], $_POST['npwp'], $_POST['customer_person'], $_POST['coa']]);
        */

        // Redirect kembali dengan pesan sukses
        $_SESSION['success'] = 'Customer berhasil disimpan.';
        header('Location: /admin/master/customer');
        exit;
    }
}
