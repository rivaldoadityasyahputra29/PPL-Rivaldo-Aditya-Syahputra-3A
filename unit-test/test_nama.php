<?php
class Validator {
    public static function ValidateName($name) {
        // Cek jika nama kosong
        if (empty(trim($name))) {
            return [
                'status' => false,
                'message' => 'Nama tidak boleh kosong.'
            ];
        }

        // Cek jika nama mengandung angka/karakter selain huruf dan spasi
        if (!preg_match("/^[a-zA-Z\s]+$/", $name)) {
            return [
                'status' => false,
                'message' => 'Dimana nama harus huruf (tidak boleh mengandung angka).'
            ];
        }

        return [
            'status' => true,
            'message' => 'Input nama valid.'
        ];
    }
}
?>