<?php
/**
 * Konfigurasi Database InfinityFree
 * 
 * Salin file ini menjadi 'db_config.php' di folder 'htdocs/config/'
 * lalu sesuaikan dengan kredensial database MySQL dari Control Panel (vPanel) InfinityFree Anda.
 */

return [
    // Contoh host: sql100.infinityfree.com (lihat di vPanel -> MySQL Databases)
    'host'   => 'sqlXXX.infinityfree.com',
    'port'   => '3306',
    
    // Contoh nama database: if0_12345678_isi_lemarigue
    'dbname' => 'if0_xxxx_isi_lemarigue',
    
    // Contoh username: if0_12345678
    'user'   => 'if0_xxxx',
    
    // Password akun hosting InfinityFree Anda (bisa dilihat di Client Area -> Account Details)
    'pass'   => 'PASSWORD_ANDA',
    
    'driver' => 'mysql'
];
