<?php
/**
 * api/kuesioner.php
 * GET    ?desa_id=X&tahun=Y       → data kuesioner (1 record + semua nilai)
 * GET    ?rekap=1&tahun=Y         → rekap semua desa (jumlah angka, untuk kecamatan)
 * POST   { desa_id, tahun, nilai:{kode:val,...}, pengisi, jabatan, tanggal, status }
 * DELETE ?desa_id=X&tahun=Y       → hapus kuesioner tahun tertentu
 */
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php';

$method = $_SERVER['REQUEST_METHOD'];

function requireAdmin() {
    if (empty($_SESSION['admin_logged_in'])) {
        http_response_code(401);
        echo json_encode(['success'=>false,'message'=>'Login diperlukan.']);
        exit;
    }
}

// ──────────────────── GET ────────────────────
if ($method === 'GET') {

    // Rekap semua desa per tahun (untuk halaman kecamatan)
    if (!empty($_GET['rekap'])) {
        $tahun = intval($_GET['tahun'] ?? date('Y'));
        $rows = $pdo->prepare("
            SELECT k.desa_id, d.nama AS nama_desa, d.slug,
                   k.id AS kuesioner_id, k.status
            FROM kuesioner k
            JOIN desa d ON d.id = k.desa_id
            WHERE k.tahun = ? AND k.status = 'selesai'
            ORDER BY d.nama
        ");
        $rows->execute([$tahun]);
        $desas = $rows->fetchAll();

        $result = [];
        foreach ($desas as $desa) {
            $nv = $pdo->prepare("SELECT kode, nilai FROM kuesioner_nilai WHERE kuesioner_id=?");
            $nv->execute([$desa['kuesioner_id']]);
            $values = [];
            foreach ($nv->fetchAll() as $r) $values[$r['kode']] = $r['nilai'];
            $result[] = [
                'desa_id'   => $desa['desa_id'],
                'nama_desa' => $desa['nama_desa'],
                'slug'      => $desa['slug'],
                'status'    => $desa['status'],
                'nilai'     => $values,
            ];
        }
        echo json_encode(['success'=>true,'tahun'=>$tahun,'data'=>$result]);
        exit;
    }

    // Data kuesioner 1 desa
    $desa_id = intval($_GET['desa_id'] ?? 0);
    $tahun   = intval($_GET['tahun']   ?? date('Y'));
    if (!$desa_id) { echo json_encode(['success'=>false,'message'=>'desa_id wajib.']); exit; }

    $stmt = $pdo->prepare("SELECT * FROM kuesioner WHERE desa_id=? AND tahun=? LIMIT 1");
    $stmt->execute([$desa_id, $tahun]);
    $header = $stmt->fetch();

    if (!$header) {
        echo json_encode(['success'=>true,'data'=>null,'tahun_list'=>getTahunList($pdo,$desa_id)]);
        exit;
    }

    $nv = $pdo->prepare("SELECT kode, nilai FROM kuesioner_nilai WHERE kuesioner_id=?");
    $nv->execute([$header['id']]);
    $nilai = [];
    foreach ($nv->fetchAll() as $r) $nilai[$r['kode']] = $r['nilai'];

    echo json_encode([
        'success'    => true,
        'data'       => $header,
        'nilai'      => $nilai,
        'tahun_list' => getTahunList($pdo, $desa_id),
    ]);
    exit;
}

function getTahunList(PDO $pdo, int $desa_id): array {
    $s = $pdo->prepare("SELECT tahun, status FROM kuesioner WHERE desa_id=? ORDER BY tahun DESC");
    $s->execute([$desa_id]);
    return $s->fetchAll();
}

// ──────────────────── POST ────────────────────
if ($method === 'POST') {
    requireAdmin();
    $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $desa_id = intval($data['desa_id'] ?? $_SESSION['admin_desa_id'] ?? 0);
    $tahun   = intval($data['tahun']   ?? date('Y'));

    if (!$desa_id || $desa_id !== intval($_SESSION['admin_desa_id'])) {
        http_response_code(403);
        echo json_encode(['success'=>false,'message'=>'Akses ditolak.']);
        exit;
    }
    if ($tahun < 2020 || $tahun > 2099) {
        echo json_encode(['success'=>false,'message'=>'Tahun tidak valid.']);
        exit;
    }

    $pengisi  = trim($data['pengisi_nama']     ?? '');
    $jabatan  = trim($data['pengisi_jabatan']  ?? '');
    $tanggal  = $data['tanggal_pengisian']     ?? date('Y-m-d');
    $status   = in_array($data['status'] ?? '', ['draft','selesai']) ? $data['status'] : 'draft';
    $nilai    = is_array($data['nilai'] ?? null) ? $data['nilai'] : [];

    // Upsert header
    $cek = $pdo->prepare("SELECT id FROM kuesioner WHERE desa_id=? AND tahun=? LIMIT 1");
    $cek->execute([$desa_id, $tahun]);
    $existId = $cek->fetchColumn();

    if ($existId) {
        $pdo->prepare("UPDATE kuesioner SET status=?,pengisi_nama=?,pengisi_jabatan=?,tanggal_pengisian=?,updated_at=NOW() WHERE id=?")
            ->execute([$status,$pengisi,$jabatan,$tanggal,$existId]);
        $kuesId = $existId;
    } else {
        $pdo->prepare("INSERT INTO kuesioner (desa_id,tahun,status,pengisi_nama,pengisi_jabatan,tanggal_pengisian) VALUES (?,?,?,?,?,?)")
            ->execute([$desa_id,$tahun,$status,$pengisi,$jabatan,$tanggal]);
        $kuesId = $pdo->lastInsertId();
    }

    // Upsert nilai (key-value)
    $upsert = $pdo->prepare("INSERT INTO kuesioner_nilai (kuesioner_id,kode,nilai) VALUES (?,?,?) ON DUPLICATE KEY UPDATE nilai=VALUES(nilai)");
    foreach ($nilai as $kode => $val) {
        $kode = preg_replace('/[^a-z0-9_]/', '', $kode);
        if (!$kode) continue;
        $upsert->execute([$kuesId, $kode, $val === '' ? null : $val]);
    }

    echo json_encode(['success'=>true,'message'=>'Kuesioner tahun '.$tahun.' berhasil disimpan.','id'=>$kuesId]);
    exit;
}

// ──────────────────── DELETE ────────────────────
if ($method === 'DELETE') {
    requireAdmin();
    $desa_id = intval($_GET['desa_id'] ?? 0);
    $tahun   = intval($_GET['tahun']   ?? 0);
    if (!$desa_id || !$tahun || $desa_id !== intval($_SESSION['admin_desa_id'])) {
        http_response_code(403);
        echo json_encode(['success'=>false,'message'=>'Akses ditolak.']);
        exit;
    }
    $cek = $pdo->prepare("SELECT id FROM kuesioner WHERE desa_id=? AND tahun=? LIMIT 1");
    $cek->execute([$desa_id, $tahun]);
    $id = $cek->fetchColumn();
    if ($id) {
        $pdo->prepare("DELETE FROM kuesioner_nilai WHERE kuesioner_id=?")->execute([$id]);
        $pdo->prepare("DELETE FROM kuesioner WHERE id=?")->execute([$id]);
    }
    echo json_encode(['success'=>true,'message'=>'Data kuesioner tahun '.$tahun.' dihapus.']);
    exit;
}

echo json_encode(['success'=>false,'message'=>'Method tidak didukung.']);
