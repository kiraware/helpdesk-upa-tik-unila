<?php

namespace App\Enums;

enum UserEntity: string
{
    case SUPER_USER = 'superuser';
    case MAHASISWA = 'mahasiswa';
    case DOSEN = 'dosen';
    case TENDIK = 'tendik';
    case KARYAWAN = 'karyawan';
    case TAMU = 'tamu';
    case LAINNYA = 'lainnya';
}
