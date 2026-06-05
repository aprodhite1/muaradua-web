-- ============================================================
-- SEED DATA: Desa 2-8 (Gunung Batu, Batu Belang, dll)
-- Jalankan di phpMyAdmin setelah muaradua_db.sql
-- ============================================================
USE muaradua_db;

-- ============================================================
-- PENDUDUK: Desa 5-8 (desa 2-4 sudah ada di muaradua_db.sql)
-- ============================================================
INSERT INTO penduduk (desa_id, rt, rw, laki_laki, perempuan) VALUES
-- Sukarami (5)
(5, 'RT 01', 'RW 01', 148, 142),
(5, 'RT 02', 'RW 01', 135, 128),
(5, 'RT 03', 'RW 02', 162, 155),
(5, 'RT 04', 'RW 02', 140, 138),
(5, 'RT 05', 'RW 02', 125, 120),
-- Sinar Harapan (6)
(6, 'RT 01', 'RW 01', 175, 168),
(6, 'RT 02', 'RW 01', 158, 152),
(6, 'RT 03', 'RW 02', 190, 182),
(6, 'RT 04', 'RW 02', 162, 155),
(6, 'RT 05', 'RW 03', 148, 145),
(6, 'RT 06', 'RW 03', 130, 125),
-- Pasar Muaradua (7)
(7, 'RT 01', 'RW 01', 285, 275),
(7, 'RT 02', 'RW 01', 268, 260),
(7, 'RT 03', 'RW 02', 310, 298),
(7, 'RT 04', 'RW 02', 245, 238),
(7, 'RT 05', 'RW 03', 228, 220),
(7, 'RT 06', 'RW 03', 265, 255),
(7, 'RT 07', 'RW 04', 198, 190),
(7, 'RT 08', 'RW 04', 215, 208),
(7, 'RT 09', 'RW 05', 188, 180),
-- Padang Bindu (8)
(8, 'RT 01', 'RW 01', 128, 122),
(8, 'RT 02', 'RW 01', 115, 110),
(8, 'RT 03', 'RW 02', 138, 130),
(8, 'RT 04', 'RW 02', 118, 112),
(8, 'RT 05', 'RW 02', 105, 100);

-- ============================================================
-- PENDIDIKAN: Semua Desa
-- ============================================================
INSERT INTO pendidikan (desa_id, tingkat, laki_laki, perempuan, urutan) VALUES
-- Gunung Batu (2)
(2,'Tidak/Belum Sekolah',98,112,1),(2,'Belum Tamat SD',158,145,2),
(2,'Tamat SD/Sederajat',282,265,3),(2,'SMP/Sederajat',315,298,4),
(2,'SMA/SMK/Sederajat',248,235,5),(2,'Diploma/Sarjana',112,105,6),
-- Batu Belang (3)
(3,'Tidak/Belum Sekolah',82,95,1),(3,'Belum Tamat SD',132,122,2),
(3,'Tamat SD/Sederajat',238,225,3),(3,'SMP/Sederajat',268,252,4),
(3,'SMA/SMK/Sederajat',205,195,5),(3,'Diploma/Sarjana',88,82,6),
-- Tanjung Jaya (4)
(4,'Tidak/Belum Sekolah',115,128,1),(4,'Belum Tamat SD',185,172,2),
(4,'Tamat SD/Sederajat',328,312,3),(4,'SMP/Sederajat',368,350,4),
(4,'SMA/SMK/Sederajat',288,275,5),(4,'Diploma/Sarjana',135,125,6),
-- Sukarami (5)
(5,'Tidak/Belum Sekolah',72,85,1),(5,'Belum Tamat SD',118,108,2),
(5,'Tamat SD/Sederajat',205,195,3),(5,'SMP/Sederajat',232,218,4),
(5,'SMA/SMK/Sederajat',178,168,5),(5,'Diploma/Sarjana',75,68,6),
-- Sinar Harapan (6)
(6,'Tidak/Belum Sekolah',88,102,1),(6,'Belum Tamat SD',145,135,2),
(6,'Tamat SD/Sederajat',262,248,3),(6,'SMP/Sederajat',295,280,4),
(6,'SMA/SMK/Sederajat',228,218,5),(6,'Diploma/Sarjana',98,92,6),
-- Pasar Muaradua (7)
(7,'Tidak/Belum Sekolah',168,185,1),(7,'Belum Tamat SD',285,265,2),
(7,'Tamat SD/Sederajat',518,492,3),(7,'SMP/Sederajat',582,558,4),
(7,'SMA/SMK/Sederajat',462,445,5),(7,'Diploma/Sarjana',245,228,6),
-- Padang Bindu (8)
(8,'Tidak/Belum Sekolah',65,78,1),(8,'Belum Tamat SD',108,98,2),
(8,'Tamat SD/Sederajat',188,178,3),(8,'SMP/Sederajat',212,200,4),
(8,'SMA/SMK/Sederajat',158,148,5),(8,'Diploma/Sarjana',62,58,6);

-- ============================================================
-- EKONOMI: Semua Desa
-- ============================================================
INSERT INTO ekonomi (desa_id, mata_pencaharian, jumlah, persentase, emoji) VALUES
-- Gunung Batu (2)
(2,'Petani / Buruh Tani',385,35.8,'🌾'),(2,'Pedagang / Wiraswasta',165,15.3,'🏪'),
(2,'PNS / TNI / Polri',92,8.5,'🏢'),(2,'Buruh Bangunan',148,13.7,'🔧'),
(2,'Jasa Transportasi',68,6.3,'🚗'),(2,'Lainnya/Tidak Bekerja',322,20.4,'📦'),
-- Batu Belang (3)
(3,'Petani / Buruh Tani',322,35.2,'🌾'),(3,'Pedagang / Wiraswasta',138,15.1,'🏪'),
(3,'PNS / TNI / Polri',78,8.5,'🏢'),(3,'Buruh Bangunan',125,13.7,'🔧'),
(3,'Jasa Transportasi',55,6.0,'🚗'),(3,'Lainnya/Tidak Bekerja',232,21.5,'📦'),
-- Tanjung Jaya (4)
(4,'Petani / Buruh Tani',448,35.5,'🌾'),(4,'Pedagang / Wiraswasta',192,15.2,'🏪'),
(4,'PNS / TNI / Polri',108,8.6,'🏢'),(4,'Buruh Bangunan',168,13.3,'🔧'),
(4,'Jasa Transportasi',82,6.5,'🚗'),(4,'Lainnya/Tidak Bekerja',262,20.9,'📦'),
-- Sukarami (5)
(5,'Petani / Buruh Tani',278,35.0,'🌾'),(5,'Pedagang / Wiraswasta',118,14.9,'🏪'),
(5,'PNS / TNI / Polri',65,8.2,'🏢'),(5,'Buruh Bangunan',105,13.2,'🔧'),
(5,'Jasa Transportasi',48,6.0,'🚗'),(5,'Lainnya/Tidak Bekerja',186,22.7,'📦'),
-- Sinar Harapan (6)
(6,'Petani / Buruh Tani',362,35.3,'🌾'),(6,'Pedagang / Wiraswasta',155,15.1,'🏪'),
(6,'PNS / TNI / Polri',88,8.6,'🏢'),(6,'Buruh Bangunan',138,13.5,'🔧'),
(6,'Jasa Transportasi',65,6.3,'🚗'),(6,'Lainnya/Tidak Bekerja',222,21.2,'📦'),
-- Pasar Muaradua (7)
(7,'Petani / Buruh Tani',485,19.8,'🌾'),(7,'Pedagang / Wiraswasta',748,30.5,'🏪'),
(7,'PNS / TNI / Polri',325,13.3,'🏢'),(7,'Buruh Bangunan',295,12.0,'🔧'),
(7,'Jasa Transportasi',218,8.9,'🚗'),(7,'Lainnya/Tidak Bekerja',569,15.5,'📦'),
-- Padang Bindu (8)
(8,'Petani / Buruh Tani',255,35.1,'🌾'),(8,'Pedagang / Wiraswasta',108,14.9,'🏪'),
(8,'PNS / TNI / Polri',58,8.0,'🏢'),(8,'Buruh Bangunan',95,13.1,'🔧'),
(8,'Jasa Transportasi',42,5.8,'🚗'),(8,'Lainnya/Tidak Bekerja',168,23.1,'📦');

-- ============================================================
-- KESEHATAN: Semua Desa
-- ============================================================
INSERT INTO kesehatan (desa_id, fasilitas, jumlah, keterangan) VALUES
-- Gunung Batu (2)
(2,'Posyandu','2 Unit','Aktif setiap bulan'),(2,'Polindes','1 Unit','Bidan desa bertugas'),
(2,'Bidan Praktek Mandiri','1 Orang','Aktif praktek'),
-- Batu Belang (3)
(3,'Posyandu','2 Unit','Aktif setiap bulan'),(3,'Polindes','1 Unit','Bidan desa bertugas'),
-- Tanjung Jaya (4)
(4,'Posyandu','3 Unit','Aktif setiap bulan'),(4,'Polindes','1 Unit','Bidan desa bertugas'),
(4,'Puskesmas Pembantu','1 Unit','Jarak 1 km dari kantor desa'),
-- Sukarami (5)
(5,'Posyandu','2 Unit','Aktif setiap bulan'),(5,'Polindes','1 Unit','Bidan desa bertugas'),
-- Sinar Harapan (6)
(6,'Posyandu','2 Unit','Aktif setiap bulan'),(6,'Polindes','1 Unit','Bidan desa bertugas'),
(6,'Bidan Praktek Mandiri','1 Orang','Aktif praktek'),
-- Pasar Muaradua (7)
(7,'Puskesmas','1 Unit','Melayani seluruh kecamatan'),(7,'Posyandu','4 Unit','Aktif setiap bulan'),
(7,'Apotek','3 Unit','Buka setiap hari'),(7,'Bidan Praktek Mandiri','3 Orang','Aktif praktek'),
(7,'Klinik Pratama','2 Unit','Buka 24 jam'),
-- Padang Bindu (8)
(8,'Posyandu','2 Unit','Aktif setiap bulan'),(8,'Polindes','1 Unit','Bidan desa bertugas');

-- ============================================================
-- INFRASTRUKTUR: Semua Desa
-- ============================================================
INSERT INTO infrastruktur (desa_id, jenis, panjang_jumlah, kondisi) VALUES
-- Gunung Batu (2)
(2,'Jalan Aspal','2,8 km','Baik'),(2,'Jalan Makadam','3,2 km','Sedang'),
(2,'Jalan Tanah','2,5 km','Perlu Perbaikan'),(2,'Jembatan Beton','2 unit','Baik'),
(2,'Masjid/Musala','4 unit','Baik'),(2,'Sekolah Dasar','1 unit','Baik'),(2,'PAUD/TK','1 unit','Baik'),
-- Batu Belang (3)
(3,'Jalan Aspal','2,2 km','Baik'),(3,'Jalan Makadam','2,8 km','Sedang'),
(3,'Jalan Tanah','2,0 km','Perlu Perbaikan'),(3,'Jembatan Beton','1 unit','Baik'),
(3,'Masjid/Musala','3 unit','Baik'),(3,'Sekolah Dasar','1 unit','Baik'),
-- Tanjung Jaya (4)
(4,'Jalan Aspal','3,5 km','Baik'),(4,'Jalan Makadam','3,0 km','Baik'),
(4,'Jalan Tanah','1,8 km','Sedang'),(4,'Jembatan Beton','2 unit','Baik'),
(4,'Irigasi Desa','4,2 km','Baik'),(4,'Masjid/Musala','5 unit','Baik'),
(4,'Sekolah Dasar','2 unit','Baik'),(4,'SMP','1 unit','Baik'),
-- Sukarami (5)
(5,'Jalan Aspal','1,8 km','Baik'),(5,'Jalan Makadam','2,5 km','Sedang'),
(5,'Jalan Tanah','2,2 km','Perlu Perbaikan'),(5,'Masjid/Musala','3 unit','Baik'),
(5,'Sekolah Dasar','1 unit','Baik'),
-- Sinar Harapan (6)
(6,'Jalan Aspal','2,5 km','Baik'),(6,'Jalan Makadam','2,8 km','Sedang'),
(6,'Jalan Tanah','2,0 km','Perlu Perbaikan'),(6,'Jembatan Beton','2 unit','Baik'),
(6,'Masjid/Musala','4 unit','Baik'),(6,'Sekolah Dasar','1 unit','Baik'),
-- Pasar Muaradua (7)
(7,'Jalan Aspal','3,8 km','Baik'),(7,'Jalan Makadam','0,8 km','Baik'),
(7,'Pasar Tradisional','1 unit','Baik'),(7,'Jembatan Beton','3 unit','Baik'),
(7,'Masjid/Musala','8 unit','Baik'),(7,'Sekolah Dasar','3 unit','Baik'),
(7,'SMP','2 unit','Baik'),(7,'SMA','1 unit','Baik'),(7,'Kantor Camat','1 unit','Baik'),
-- Padang Bindu (8)
(8,'Jalan Aspal','1,5 km','Baik'),(8,'Jalan Makadam','2,2 km','Sedang'),
(8,'Jalan Tanah','3,5 km','Perlu Perbaikan'),(8,'Masjid/Musala','3 unit','Baik'),
(8,'Sekolah Dasar','1 unit','Baik');

-- ============================================================
-- PERTANIAN: Semua Desa
-- ============================================================
INSERT INTO pertanian (desa_id, komoditas, luas_tanam, produksi_tahun, emoji) VALUES
-- Gunung Batu (2)
(2,'Kopi','32,5 Ha','28 Ton','☕'),(2,'Karet','25,8 Ha','32 Ton','🌿'),
(2,'Padi Ladang','18,2 Ha','95 Ton','🌾'),(2,'Sayuran','8,5 Ha','62 Ton','🥬'),
-- Batu Belang (3)
(3,'Padi Sawah','38,2 Ha','225 Ton','🌾'),(3,'Karet','22,5 Ha','28 Ton','🌿'),
(3,'Jagung','10,5 Ha','62 Ton','🌽'),(3,'Sayuran','4,2 Ha','38 Ton','🥬'),
-- Tanjung Jaya (4)
(4,'Padi Sawah','62,8 Ha','368 Ton','🌾'),(4,'Karet','18,5 Ha','24 Ton','🌿'),
(4,'Jagung','15,2 Ha','88 Ton','🌽'),(4,'Ikan Kolam','5,8 Ha','45 Ton','🐟'),
-- Sukarami (5)
(5,'Padi Sawah','52,5 Ha','308 Ton','🌾'),(5,'Jagung','18,8 Ha','110 Ton','🌽'),
(5,'Karet','15,2 Ha','20 Ton','🌿'),(5,'Sayuran','6,5 Ha','55 Ton','🥬'),
-- Sinar Harapan (6)
(6,'Padi Sawah','45,2 Ha','265 Ton','🌾'),(6,'Kopi','28,5 Ha','24 Ton','☕'),
(6,'Karet','20,8 Ha','26 Ton','🌿'),(6,'Sayuran','5,2 Ha','45 Ton','🥬'),
-- Pasar Muaradua (7)
(7,'Padi Sawah','15,2 Ha','88 Ton','🌾'),(7,'Sayuran','8,8 Ha','82 Ton','🥬'),
(7,'Buah-buahan','6,5 Ha','58 Ton','🍎'),
-- Padang Bindu (8)
(8,'Karet','42,8 Ha','55 Ton','🌿'),(8,'Kopi','22,5 Ha','18 Ton','☕'),
(8,'Padi Ladang','15,8 Ha','82 Ton','🌾'),(8,'Kayu Hutan','35,0 Ha','22 Ton','🪵');

-- ============================================================
-- SOSIAL BUDAYA: Semua Desa
-- ============================================================
INSERT INTO sosial_budaya (desa_id, kategori, nama, jumlah, keterangan) VALUES
-- Gunung Batu (2)
(2,'Agama','Islam','2.155 jiwa','98,8%'),(2,'Agama','Lainnya','25 jiwa','1,2%'),
(2,'Organisasi','PKK Desa','98 orang','Aktif'),(2,'Organisasi','Karang Taruna','65 orang','Aktif'),
(2,'Organisasi','Kelompok Tani','3 kelompok','Aktif'),
-- Batu Belang (3)
(3,'Agama','Islam','1.825 jiwa','98,6%'),(3,'Agama','Lainnya','25 jiwa','1,4%'),
(3,'Organisasi','PKK Desa','82 orang','Aktif'),(3,'Organisasi','Karang Taruna','55 orang','Aktif'),
(3,'Organisasi','Kelompok Tani','3 kelompok','Aktif'),
-- Tanjung Jaya (4)
(4,'Agama','Islam','2.528 jiwa','98,8%'),(4,'Agama','Lainnya','32 jiwa','1,2%'),
(4,'Organisasi','PKK Desa','118 orang','Aktif'),(4,'Organisasi','Karang Taruna','78 orang','Aktif'),
(4,'Organisasi','Kelompok Tani','4 kelompok','Aktif'),(4,'Organisasi','Kelompok Tani Irigasi','2 kelompok','Aktif'),
-- Sukarami (5)
(5,'Agama','Islam','1.598 jiwa','98,6%'),(5,'Agama','Lainnya','22 jiwa','1,4%'),
(5,'Organisasi','PKK Desa','72 orang','Aktif'),(5,'Organisasi','Karang Taruna','48 orang','Aktif'),
(5,'Organisasi','Kelompok Tani','3 kelompok','Aktif'),
-- Sinar Harapan (6)
(6,'Agama','Islam','2.062 jiwa','98,7%'),(6,'Agama','Lainnya','28 jiwa','1,3%'),
(6,'Organisasi','PKK Desa','95 orang','Aktif'),(6,'Organisasi','Karang Taruna','62 orang','Aktif'),
(6,'Organisasi','Kelompok Tani','3 kelompok','Aktif'),
-- Pasar Muaradua (7)
(7,'Agama','Islam','4.058 jiwa','98,5%'),(7,'Agama','Kristen','45 jiwa','1,1%'),
(7,'Agama','Lainnya','17 jiwa','0,4%'),
(7,'Organisasi','PKK Desa','185 orang','Aktif'),(7,'Organisasi','Karang Taruna','128 orang','Aktif'),
(7,'Organisasi','Kelompok Tani','5 kelompok','Aktif'),(7,'Organisasi','Koperasi Desa','385 anggota','Aktif'),
(7,'Organisasi','BUMDES','1 unit','Aktif'),
-- Padang Bindu (8)
(8,'Agama','Islam','1.458 jiwa','98,5%'),(8,'Agama','Lainnya','22 jiwa','1,5%'),
(8,'Organisasi','PKK Desa','65 orang','Aktif'),(8,'Organisasi','Karang Taruna','42 orang','Aktif'),
(8,'Organisasi','Kelompok Tani','3 kelompok','Aktif');

-- ============================================================
-- INFOGRAFIS: Desa 2-8
-- ============================================================
INSERT INTO infografis (desa_id, judul, kategori, emoji, deskripsi, warna_bg) VALUES
(2,'Komposisi Penduduk','Kependudukan','👥','Sebaran penduduk Gunung Batu berdasarkan usia.','#EEF3FA'),
(2,'Potensi Kopi & Karet','Pertanian','☕','Komoditas utama Gunung Batu.','#F0FFF4'),
(3,'Komposisi Penduduk','Kependudukan','👥','Sebaran penduduk Batu Belang berdasarkan usia.','#EEF3FA'),
(3,'Lumbung Padi Batu Belang','Pertanian','🌾','Produksi padi sawah unggulan.','#F0FFF4'),
(4,'Komposisi Penduduk','Kependudukan','👥','Sebaran penduduk Tanjung Jaya.','#EEF3FA'),
(4,'Sistem Irigasi Tanjung Jaya','Infrastruktur','🌊','Jaringan irigasi sebagai kekuatan desa.','#EEF3FA'),
(5,'Komposisi Penduduk','Kependudukan','👥','Sebaran penduduk Sukarami.','#EEF3FA'),
(5,'Sawah Sukarami','Pertanian','🌾','Produksi padi terbesar di kecamatan.','#F0FFF4'),
(6,'Komposisi Penduduk','Kependudukan','👥','Sebaran penduduk Sinar Harapan.','#EEF3FA'),
(6,'Potensi Kopi Sinar Harapan','Pertanian','☕','Kopi sebagai komoditas andalan.','#FFF8EE'),
(7,'Komposisi Penduduk','Kependudukan','👥','Sebaran penduduk Pasar Muaradua.','#EEF3FA'),
(7,'Pusat Ekonomi Kecamatan','Ekonomi','🏪','Aktivitas perdagangan di pusat kecamatan.','#FFF8EE'),
(7,'Fasilitas Kesehatan Lengkap','Kesehatan','🏥','Puskesmas dan klinik pratama tersedia.','#FFF0F0'),
(8,'Komposisi Penduduk','Kependudukan','👥','Sebaran penduduk Padang Bindu.','#EEF3FA'),
(8,'Hutan & Karet Padang Bindu','Pertanian','🌿','Potensi hutan dan kebun karet.','#F0FFF4');

-- ============================================================
-- PENGUMUMAN: Desa 2-8
-- ============================================================
INSERT INTO pengumuman (desa_id, judul, isi, tanggal, aktif) VALUES
(2,'Musyawarah Desa Gunung Batu','Warga diundang hadir dalam musyawarah perencanaan pembangunan.','2025-04-25',1),
(2,'Posyandu Bulan Mei','Posyandu akan diadakan setiap Jumat pertama di bulan Mei.','2025-04-18',1),
(3,'Rapat Kelompok Tani','Rapat kelompok tani membahas musim tanam 2025.','2025-04-22',1),
(3,'Gotong Royong Batu Belang','Kegiatan gotong royong bersih desa Minggu pagi.','2025-04-10',1),
(4,'Jadwal Pembagian Air Irigasi','Pembagian air irigasi diatur sesuai jadwal musim tanam.','2025-04-28',1),
(4,'Posyandu Tanjung Jaya','Posyandu balita rutin diadakan setiap bulan.','2025-04-15',1),
(5,'Musyawarah Desa Sukarami','Musyawarah membahas rencana perbaikan jalan desa.','2025-04-20',1),
(5,'Panen Raya Padi Sukarami','Kegiatan panen raya padi bersama masyarakat.','2025-04-12',1),
(6,'Program Beasiswa Desa','Pendaftaran beasiswa pelajar berprestasi dibuka.','2025-04-26',1),
(6,'Posyandu Sinar Harapan','Posyandu balita rutin setiap bulan.','2025-04-16',1),
(7,'Pasar Muaradua Expo 2025','Pameran produk unggulan desa se-kecamatan.','2025-05-01',1),
(7,'Pembangunan Pasar Baru','Rencana renovasi pasar tradisional diumumkan.','2025-04-28',1),
(7,'Posyandu Pasar Muaradua','Jadwal posyandu rutin di 4 pos.','2025-04-15',1),
(8,'Musyawarah Desa Padang Bindu','Musyawarah tahunan perencanaan desa.','2025-04-24',1),
(8,'Program Penghijauan','Kegiatan penanaman pohon bersama warga.','2025-04-08',1);
