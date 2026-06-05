<?php
/**
 * config/kuesioner_soal.php
 * Definisi pertanyaan kuesioner desa.
 *
 * Key 'sub' = sub-section header (pengelompokan dalam tabel yang sama).
 * Tipe field:
 *   text     = input teks
 *   number   = input angka (akan dijumlahkan di rekap kecamatan)
 *   select   = dropdown
 *   textarea = teks panjang
 *   date     = tanggal
 */

return [

  // ─────────────────────────────────────────────────────────
  'I. IDENTITAS DESA' => [
    ['kode'=>'nama_desa',         'label'=>'Nama Desa',                         'tipe'=>'text'],
    ['kode'=>'kode_desa',         'label'=>'Kode Desa (BPS)',                   'tipe'=>'text'],
    ['kode'=>'nama_kecamatan',    'label'=>'Nama Kecamatan',                    'tipe'=>'text'],
    ['kode'=>'nama_kabupaten',    'label'=>'Nama Kabupaten',                    'tipe'=>'text'],
    ['kode'=>'nama_provinsi',     'label'=>'Nama Provinsi',                     'tipe'=>'text'],
    ['kode'=>'tahun_pembentukan', 'label'=>'Tahun Pembentukan Desa',            'tipe'=>'number'],
    ['kode'=>'luas_wilayah',      'label'=>'Luas Wilayah (Ha)',                 'tipe'=>'number'],
    ['kode'=>'topografi',         'label'=>'Topografi / Bentang Alam',          'tipe'=>'select',
      'opsi'=>['Dataran Rendah','Dataran Tinggi','Perbukitan','Pegunungan','Tepi Pantai','Lembah','Campuran']],
    ['kode'=>'ketinggian',        'label'=>'Ketinggian dari Permukaan Laut (mdpl)', 'tipe'=>'number'],
    ['kode'=>'curah_hujan',       'label'=>'Curah Hujan Rata-rata (mm/thn)',    'tipe'=>'text'],
    // Batas wilayah
    ['kode'=>'batas_utara',   'label'=>'Batas Wilayah — Utara',   'tipe'=>'text', 'sub'=>'Batas Wilayah'],
    ['kode'=>'batas_selatan', 'label'=>'Batas Wilayah — Selatan', 'tipe'=>'text'],
    ['kode'=>'batas_timur',   'label'=>'Batas Wilayah — Timur',   'tipe'=>'text'],
    ['kode'=>'batas_barat',   'label'=>'Batas Wilayah — Barat',   'tipe'=>'text'],
    // Jarak
    ['kode'=>'jarak_kecamatan', 'label'=>'Jarak ke Ibukota Kecamatan (km)', 'tipe'=>'number', 'sub'=>'Jarak & Kontak'],
    ['kode'=>'jarak_kabupaten', 'label'=>'Jarak ke Ibukota Kabupaten (km)', 'tipe'=>'number'],
    ['kode'=>'no_telp',         'label'=>'Nomor Telepon Kantor Desa',       'tipe'=>'text'],
    ['kode'=>'email_desa',      'label'=>'Alamat Email Desa',               'tipe'=>'text'],
    ['kode'=>'website_desa',    'label'=>'Website Desa',                    'tipe'=>'text'],
  ],

  // ─────────────────────────────────────────────────────────
  'II. PEMERINTAHAN DESA' => [
    // Kepala Desa
    ['kode'=>'nama_kades',       'label'=>'Nama Kepala Desa',                  'tipe'=>'text',   'sub'=>'Kepala Desa'],
    ['kode'=>'nik_kades',        'label'=>'NIK Kepala Desa',                   'tipe'=>'text'],
    ['kode'=>'pendidikan_kades', 'label'=>'Pendidikan Terakhir Kepala Desa',   'tipe'=>'select',
      'opsi'=>['SD/Sederajat','SMP/Sederajat','SMA/Sederajat','Diploma','Sarjana (S1)','Pascasarjana']],
    ['kode'=>'jabatan_mulai',    'label'=>'Masa Jabatan — Mulai (Tahun)',      'tipe'=>'number'],
    ['kode'=>'jabatan_selesai',  'label'=>'Masa Jabatan — Selesai (Tahun)',    'tipe'=>'number'],
    // Struktur pemerintahan
    ['kode'=>'jml_perangkat', 'label'=>'Jumlah Perangkat Desa (orang)', 'tipe'=>'number', 'sub'=>'Struktur Pemerintahan'],
    ['kode'=>'jml_bpd',       'label'=>'Jumlah Anggota BPD (orang)',    'tipe'=>'number'],
    ['kode'=>'jml_dusun',     'label'=>'Jumlah Dusun',                  'tipe'=>'number'],
    ['kode'=>'jml_rw',        'label'=>'Jumlah RW',                     'tipe'=>'number'],
    ['kode'=>'jml_rt',        'label'=>'Jumlah RT',                     'tipe'=>'number'],
  ],

  // ─────────────────────────────────────────────────────────
  'III. KEPENDUDUKAN' => [
    // Jumlah penduduk
    ['kode'=>'pddk_laki',      'label'=>'Jumlah Penduduk Laki-laki (jiwa)', 'tipe'=>'number', 'sub'=>'Jumlah Penduduk'],
    ['kode'=>'pddk_perempuan', 'label'=>'Jumlah Penduduk Perempuan (jiwa)', 'tipe'=>'number'],
    ['kode'=>'pddk_total',     'label'=>'Jumlah Penduduk Total (jiwa)',     'tipe'=>'number'],
    ['kode'=>'jml_kk',         'label'=>'Jumlah Kepala Keluarga (KK)',      'tipe'=>'number'],
    ['kode'=>'jml_kk_miskin',  'label'=>'Jumlah KK Miskin / Tidak Mampu',  'tipe'=>'number'],
    // Kelompok usia
    ['kode'=>'jml_bayi',   'label'=>'Penduduk Usia 0–5 Tahun (Bayi)',    'tipe'=>'number', 'sub'=>'Kelompok Usia'],
    ['kode'=>'jml_anak',   'label'=>'Penduduk Usia 6–12 Tahun (Anak)',   'tipe'=>'number'],
    ['kode'=>'jml_remaja', 'label'=>'Penduduk Usia 13–17 Tahun (Remaja)','tipe'=>'number'],
    ['kode'=>'jml_dewasa', 'label'=>'Penduduk Usia 18–59 Tahun (Dewasa)','tipe'=>'number'],
    ['kode'=>'jml_lansia', 'label'=>'Penduduk Usia 60+ Tahun (Lansia)',  'tipe'=>'number'],
    // Lainnya
    ['kode'=>'jml_disabilitas', 'label'=>'Jumlah Penyandang Disabilitas', 'tipe'=>'number', 'sub'=>'Kategori Khusus'],
    ['kode'=>'wni',             'label'=>'WNI',                            'tipe'=>'number'],
    ['kode'=>'wna',             'label'=>'WNA',                            'tipe'=>'number'],
  ],

  // ─────────────────────────────────────────────────────────
  'IV. PENDIDIKAN' => [
    // Tingkat pendidikan warga
    ['kode'=>'pddk_tdk_sekolah', 'label'=>'Tidak / Belum Sekolah (orang)',    'tipe'=>'number', 'sub'=>'Tingkat Pendidikan Warga'],
    ['kode'=>'pddk_paud_tk',     'label'=>'Sedang PAUD / TK (orang)',          'tipe'=>'number'],
    ['kode'=>'pddk_sd',          'label'=>'Tamat SD / Sederajat (orang)',      'tipe'=>'number'],
    ['kode'=>'pddk_smp',         'label'=>'Tamat SMP / Sederajat (orang)',     'tipe'=>'number'],
    ['kode'=>'pddk_sma',         'label'=>'Tamat SMA / Sederajat (orang)',     'tipe'=>'number'],
    ['kode'=>'pddk_diploma',     'label'=>'Tamat Diploma (orang)',             'tipe'=>'number'],
    ['kode'=>'pddk_sarjana',     'label'=>'Tamat Sarjana S1 (orang)',          'tipe'=>'number'],
    ['kode'=>'pddk_pasca',       'label'=>'Tamat Pascasarjana S2/S3 (orang)', 'tipe'=>'number'],
    // Fasilitas pendidikan
    ['kode'=>'fas_paud',        'label'=>'Jumlah PAUD / TK (unit)',           'tipe'=>'number', 'sub'=>'Fasilitas Pendidikan'],
    ['kode'=>'fas_sd',          'label'=>'Jumlah SD / MI (unit)',             'tipe'=>'number'],
    ['kode'=>'fas_smp',         'label'=>'Jumlah SMP / MTs (unit)',           'tipe'=>'number'],
    ['kode'=>'fas_sma',         'label'=>'Jumlah SMA / SMK / MA (unit)',      'tipe'=>'number'],
    ['kode'=>'fas_pesantren',   'label'=>'Jumlah Pondok Pesantren (unit)',    'tipe'=>'number'],
    ['kode'=>'fas_perpustakaan','label'=>'Jumlah Perpustakaan Desa (unit)',   'tipe'=>'number'],
  ],

  // ─────────────────────────────────────────────────────────
  'V. KESEHATAN' => [
    // Fasilitas kesehatan
    ['kode'=>'fas_puskesmas', 'label'=>'Jumlah Puskesmas (unit)',           'tipe'=>'number', 'sub'=>'Fasilitas Kesehatan'],
    ['kode'=>'fas_pustu',     'label'=>'Jumlah Puskesmas Pembantu (unit)',  'tipe'=>'number'],
    ['kode'=>'fas_posyandu',  'label'=>'Jumlah Posyandu (unit)',            'tipe'=>'number'],
    ['kode'=>'fas_polindes',  'label'=>'Jumlah Polindes / Poskesdes (unit)','tipe'=>'number'],
    ['kode'=>'fas_klinik',    'label'=>'Jumlah Klinik Swasta (unit)',       'tipe'=>'number'],
    // Tenaga kesehatan
    ['kode'=>'jml_dokter', 'label'=>'Jumlah Dokter (orang)',         'tipe'=>'number', 'sub'=>'Tenaga Kesehatan'],
    ['kode'=>'jml_bidan',  'label'=>'Jumlah Bidan (orang)',          'tipe'=>'number'],
    ['kode'=>'jml_perawat','label'=>'Jumlah Perawat (orang)',        'tipe'=>'number'],
    ['kode'=>'jml_kader',  'label'=>'Jumlah Kader Posyandu (orang)','tipe'=>'number'],
    // Kondisi kesehatan
    ['kode'=>'jml_stunting',  'label'=>'Jumlah Kasus Stunting (jiwa)',    'tipe'=>'number', 'sub'=>'Kondisi Kesehatan'],
    ['kode'=>'jml_gizi_buruk','label'=>'Jumlah Kasus Gizi Buruk (jiwa)', 'tipe'=>'number'],
    ['kode'=>'air_bersih',    'label'=>'Sumber Air Bersih Utama',         'tipe'=>'select',
      'opsi'=>['PDAM','Sumur Bor','Mata Air','Sungai','Sumur Gali','Hujan','Lainnya']],
    ['kode'=>'sanitasi',      'label'=>'Kepemilikan Jamban Keluarga',     'tipe'=>'select',
      'opsi'=>['Hampir Semua (>80%)','Sebagian (50–80%)','Sedikit (<50%)','Tidak Ada']],
  ],

  // ─────────────────────────────────────────────────────────
  'VI. EKONOMI' => [
    // Mata pencaharian
    ['kode'=>'mata_pencaharian','label'=>'Mata Pencaharian Utama Penduduk', 'tipe'=>'text',   'sub'=>'Mata Pencaharian'],
    ['kode'=>'jml_petani',     'label'=>'Jumlah Petani (orang)',            'tipe'=>'number'],
    ['kode'=>'jml_peternak',   'label'=>'Jumlah Peternak (orang)',          'tipe'=>'number'],
    ['kode'=>'jml_nelayan',    'label'=>'Jumlah Nelayan (orang)',           'tipe'=>'number'],
    ['kode'=>'jml_pedagang',   'label'=>'Jumlah Pedagang / Wirausaha (orang)','tipe'=>'number'],
    ['kode'=>'jml_pns',        'label'=>'Jumlah PNS / TNI / Polri (orang)', 'tipe'=>'number'],
    ['kode'=>'jml_buruh',      'label'=>'Jumlah Buruh / Karyawan (orang)',  'tipe'=>'number'],
    // Usaha & keuangan
    ['kode'=>'jml_umkm',  'label'=>'Jumlah UMKM yang Terdaftar',      'tipe'=>'number', 'sub'=>'Usaha & Keuangan Desa'],
    ['kode'=>'jml_bumdes','label'=>'Jumlah BUMDes',                   'tipe'=>'number'],
    ['kode'=>'apbdes',    'label'=>'Total APBDes Tahun Ini (Rp)',      'tipe'=>'number'],
    ['kode'=>'dana_desa', 'label'=>'Dana Desa yang Diterima (Rp)',     'tipe'=>'number'],
    ['kode'=>'add',       'label'=>'Alokasi Dana Desa / ADD (Rp)',     'tipe'=>'number'],
    ['kode'=>'pad',       'label'=>'Pendapatan Asli Desa / PAD (Rp)', 'tipe'=>'number'],
    ['kode'=>'aset_desa', 'label'=>'Aset / Kekayaan Desa',            'tipe'=>'textarea'],
  ],

  // ─────────────────────────────────────────────────────────
  'VII. INFRASTRUKTUR' => [
    // Jalan
    ['kode'=>'jalan_aspal', 'label'=>'Panjang Jalan Aspal (km)',          'tipe'=>'number', 'sub'=>'Kondisi Jalan'],
    ['kode'=>'jalan_cor',   'label'=>'Panjang Jalan Cor Beton (km)',      'tipe'=>'number'],
    ['kode'=>'jalan_tanah', 'label'=>'Panjang Jalan Tanah / Kerikil (km)','tipe'=>'number'],
    // Utilitas
    ['kode'=>'listrik',   'label'=>'Jaringan Listrik PLN',            'tipe'=>'select', 'sub'=>'Utilitas',
      'opsi'=>['Sudah Ada (seluruh desa)','Sudah Ada (sebagian)','Belum Ada']],
    ['kode'=>'internet',  'label'=>'Jaringan Internet / WiFi',        'tipe'=>'select',
      'opsi'=>['Sudah Ada (luas)','Sudah Ada (terbatas)','Belum Ada']],
    ['kode'=>'sinyal_hp', 'label'=>'Kualitas Sinyal Telepon Seluler', 'tipe'=>'select',
      'opsi'=>['Kuat (4G/5G)','Sedang (3G)','Lemah (2G)','Tidak Ada Sinyal']],
    ['kode'=>'irigasi',   'label'=>'Kondisi Irigasi Pertanian',       'tipe'=>'select',
      'opsi'=>['Baik (teknis)','Sedang (semi teknis)','Buruk / Sederhana','Tidak Ada']],
    // Fasilitas umum
    ['kode'=>'fas_masjid',   'label'=>'Jumlah Masjid (unit)',                    'tipe'=>'number', 'sub'=>'Fasilitas Umum & Ibadah'],
    ['kode'=>'fas_mushola',  'label'=>'Jumlah Mushola / Langgar (unit)',         'tipe'=>'number'],
    ['kode'=>'fas_gereja',   'label'=>'Jumlah Gereja (unit)',                    'tipe'=>'number'],
    ['kode'=>'fas_balai',    'label'=>'Jumlah Balai Desa / Kantor Desa (unit)',  'tipe'=>'number'],
    ['kode'=>'fas_lapangan', 'label'=>'Jumlah Lapangan Olahraga (unit)',         'tipe'=>'number'],
    ['kode'=>'fas_pasar',    'label'=>'Jumlah Pasar Desa (unit)',                'tipe'=>'number'],
    ['kode'=>'fas_tpa',      'label'=>'Jumlah Tempat Pembuangan Sampah (unit)',  'tipe'=>'number'],
  ],

  // ─────────────────────────────────────────────────────────
  'VIII. SOSIAL BUDAYA & POTENSI DESA' => [
    // Potensi
    ['kode'=>'potensi_alam',    'label'=>'Potensi Sumber Daya Alam',         'tipe'=>'textarea', 'sub'=>'Potensi Desa'],
    ['kode'=>'potensi_wisata',  'label'=>'Potensi Wisata Desa',              'tipe'=>'textarea'],
    ['kode'=>'potensi_budaya',  'label'=>'Potensi Budaya / Tradisi / Adat',  'tipe'=>'textarea'],
    ['kode'=>'produk_unggulan', 'label'=>'Produk Unggulan / Komoditas Lokal','tipe'=>'textarea'],
    // Kelembagaan
    ['kode'=>'jml_karang_taruna','label'=>'Jumlah Anggota Karang Taruna', 'tipe'=>'number', 'sub'=>'Kelembagaan Sosial'],
    ['kode'=>'jml_pkk',          'label'=>'Jumlah Anggota PKK',           'tipe'=>'number'],
    ['kode'=>'jml_lembaga_adat', 'label'=>'Jumlah Lembaga Adat',          'tipe'=>'number'],
    // Catatan
    ['kode'=>'permasalahan', 'label'=>'Permasalahan Utama Desa',         'tipe'=>'textarea', 'sub'=>'Catatan & Prioritas'],
    ['kode'=>'harapan',      'label'=>'Harapan / Prioritas Pembangunan', 'tipe'=>'textarea'],
    ['kode'=>'catatan',      'label'=>'Catatan Tambahan',                'tipe'=>'textarea'],
  ],

];
