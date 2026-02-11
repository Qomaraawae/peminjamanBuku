<?php
// ===================================
// ROLE HELPER - Fungsi bantuan role
// ===================================

// Fungsi untuk cek apakah user bisa melakukan operasi tertentu
function can_create() {
    return is_admin();
}

function can_edit() {
    return is_admin();
}

function can_delete() {
    return is_admin();
}

function can_view() {
    return is_logged_in(); // Semua user yang login bisa lihat
}

function can_borrow() {
    return is_logged_in(); // Semua user bisa meminjam
}

// Fungsi untuk menampilkan tombol berdasarkan role
function show_action_buttons($type = 'all') {
    if (!is_admin()) {
        return false;
    }
    return true;
}

// Fungsi untuk memvalidasi action sebelum eksekusi
function validate_action($action) {
    switch($action) {
        case 'create':
        case 'add':
        case 'tambah':
            if (!can_create()) {
                $_SESSION['error'] = 'Anda tidak memiliki izin untuk menambah data. Hanya admin yang diizinkan.';
                return false;
            }
            break;
            
        case 'edit':
        case 'update':
            if (!can_edit()) {
                $_SESSION['error'] = 'Anda tidak memiliki izin untuk mengubah data. Hanya admin yang diizinkan.';
                return false;
            }
            break;
            
        case 'delete':
        case 'hapus':
            if (!can_delete()) {
                $_SESSION['error'] = 'Anda tidak memiliki izin untuk menghapus data. Hanya admin yang diizinkan.';
                return false;
            }
            break;
    }
    return true;
}
?>