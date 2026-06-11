<?php

namespace App\Http\Controllers;

use App\Models\History;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Exception;

class PredictionController extends Controller
{
    // ── DISEASE MAP (Sumber Terpercaya: BB Padi Kementan & IRRI) ──────
    private array $diseaseMap = [
        'penggerek_batang' => [
            'name'        => 'Penggerek Batang',
            'latin_name'  => 'Scirpophaga incertulas',
            'kategori'    => 'Hama',
            'status'      => 'Bahaya',
            'description' => 'Hama yang merusak bagian dalam batang padi. Menyebabkan pucuk layu dan mati (sundep) pada fase vegetatif atau malai padi menjadi putih hampa (beluk) pada fase generatif.',
            'actions'     => [
                'Kultural: Lakukan penggenangan sawah sedalam 10 cm selama beberapa hari pasca-panen untuk membunuh sisa-sisa larva di tunggul jerami.',
                'Fisik: Kumpulkan kelompok telur penggerek batang secara manual di persemaian dan lapangan lalu musnahkan.',
                'Hayati: Pasang perangkap cahaya (light trap) di malam hari untuk memantau dan menangkap ngengat dewasa.',
                'Kimiawi: Taburkan insektisida granular sistemik berbahan aktif Karbofuran jika intensitas serangan sundep melebihi 5%.'
            ],
        ],
        'blast' => [
            'name'        => 'Blas Daun (Rice Blast)',
            'latin_name'  => 'Pyricularia oryzae',
            'kategori'    => 'Penyakit',
            'status'      => 'Bahaya',
            'description' => 'Penyakit akibat infeksi jamur yang menimbulkan bercak berbentuk belah ketupat abu-abu di bagian tengah dengan tepi coklat kemerahan pada daun padi.',
            'actions'     => [
                'Kultural: Gunakan sistem tanam Jajar Legowo untuk memperbaiki sirkulasi udara dan mengurangi kelembaban di kanopi tanaman.',
                'Kultural: Hindari pemberian pupuk Nitrogen (Urea) secara berlebihan karena dapat memicu kerentanan jaringan daun.',
                'Hayati: Tanam varietas padi yang memiliki ketahanan genetik terhadap penyakit blas (seperti Inpari 32 atau Inpari 48 Blas).',
                'Kimiawi: Lakukan penyemprotan fungisida sistemik berbahan aktif Tricyclazole atau Pyroquilon saat intensitas serangan daun mencapai >10%.'
            ],
        ],
        'blight' => [
            'name'        => 'Hawar Daun Bakteri (Kresek)',
            'latin_name'  => 'Xanthomonas oryzae pv. oryzae',
            'kategori'    => 'Penyakit',
            'status'      => 'Bahaya',
            'description' => 'Penyakit bakteri yang menyebabkan kerusakan tepi daun basah keabu-abuan, lalu layu mengering (kresek) mulai dari ujung helai daun.',
            'actions'     => [
                'Kultural: Terapkan sistem pengairan berselang (intermittent irrigation) dan hindari penggenangan sawah yang tinggi terus-menerus.',
                'Kultural: Kurangi dosis pupuk Nitrogen dan tingkatkan aplikasi pupuk Kalium (KCl) untuk memperkokoh dinding sel daun.',
                'Kimiawi: Semprotkan bakterisida berbahan aktif Streptomisin sulfat atau senyawa Tembaga Hidroksida jika gejala kresek mulai meluas.'
            ],
        ],
        'wereng_coklat' => [
            'name'        => 'Wereng Batang Coklat (WBC)',
            'latin_name'  => 'Nilaparvata lugens',
            'kategori'    => 'Hama',
            'status'      => 'Sangat Bahaya',
            'description' => 'Serangga pengisap cairan sel tanaman padi yang bergerombol di pangkal batang dekat permukaan air. Serangan parah menyebabkan padi mengering melingkar seperti terbakar (hopperburn).',
            'actions'     => [
                'Kultural: Keringkan sawah secara berkala (pengairan berselang) untuk menurunkan tingkat kelembaban mikro di pangkal rumpun padi.',
                'Hayati: Jaga populasi predator alami wereng seperti laba-laba penenun (Lycosa pseudoannulata) dan kepik mikro.',
                'Kimiawi: Semprotkan insektisida berbahan aktif Pymetrozine, Nitenpyram, atau Buprofezin tepat ke arah pangkal batang jika populasi melampaui 15 ekor per rumpun.'
            ],
        ],
        'tungro' => [
            'name'        => 'Penyakit Tungro',
            'latin_name'  => 'Rice Tungro Virus (RTV)',
            'kategori'    => 'Penyakit',
            'status'      => 'Sangat Bahaya',
            'description' => 'Penyakit akibat virus ganda yang ditularkan oleh vektor Wereng Hijau. Mengakibatkan tanaman tumbuh kerdil secara ekstrem dan warna daun berubah kuning-oranye dari pucuk daun.',
            'actions'     => [
                'Kultural: Cabut dan musnahkan (eradikasi) segera rumpun tanaman yang bergejala kuning kerdil agar tidak menulari tanaman sehat lainnya.',
                'Kultural: Lakukan rotasi tanaman dengan komoditas non-padi untuk memutus siklus hidup serangga vektor penular.',
                'Kimiawi: Kendalikan populasi serangga penular (Wereng Hijau) dengan menyemprotkan insektisida berbahan aktif Imidacloprid atau Pymetrozine.'
            ],
        ],
        'tikus' => [
            'name'        => 'Tikus Sawah',
            'latin_name'  => 'Rattus argentiventer',
            'kategori'    => 'Hama',
            'status'      => 'Sangat Bahaya',
            'description' => 'Hama pengerat merusak yang memotong batang padi membentuk sudut 45 derajat dekat tanah, sering mengakibatkan kerusakan melingkar luas dari tengah petakan sawah.',
            'actions'     => [
                'Kultural: Lakukan sanitasi lingkungan pematang sawah dengan membersihkan semak belukar agar tidak menjadi sarang tikus.',
                'Fisik: Pasang sistem pagar plastik perangkap linier atau TBS (Trap Barrier System) di sekitar petakan sawah.',
                'Kimiawi: Terapkan pengumpanan beracun dengan rodentisida berbahan aktif Brodifakum atau pengemposan belerang pada lubang aktif.'
            ],
        ],
    ];

    // ── PREDICT ──────────────────────────────────────────────────────
    public function predict(Request $request)
    {
        $request->validate(
            [
                'image' => 'required|image|max:5120'
            ],
            [
                'image.required' => 'Gambar wajib diupload',
                'image.image'    => 'Format file tidak didukung',
                'image.max'      => 'Ukuran file terlalu besar'
            ]
        );

        $file = $request->file('image');
        $imagePath = null;

        try {
            // Simpan foto asli ke storage histories lokal
            $imagePath = $file->store('histories', 'public');

            // Kirim request multipart ke FastAPI
            $response = Http::timeout(30)
                ->attach(
                    'file',
                    file_get_contents($file->getRealPath()),
                    $file->getClientOriginalName()
                )
                ->post('http://127.0.0.1:5000/predict');

            if ($response->failed()) {
                throw new Exception('Model server tidak merespons', 502);
            }

            $result     = $response->json();
            $detections = $result['detections'] ?? [];

            // Jika AI tidak mendeteksi hama/penyakit apa pun (Tanaman Sehat)
            if (empty($detections)) {
                $healthyData = [
                    'disease_name' => 'Tanaman Sehat / Normal',
                    'latin_name'   => '-',
                    'kategori'     => 'Normal',
                    'status'       => 'Aman',
                    'accuracy'     => 100.00,
                    'description'  => 'Tanaman padi terlihat sehat. Tidak ditemukan gejala penyakit (blas, hawar daun, tungro) maupun tanda-tanda kerusakan hama (wereng, ulat penggerek, tikus).',
                    'actions'      => [
                        'Lakukan pemantauan rutin mandiri minimal 1-2 kali dalam seminggu.',
                        'Pertahankan pola pemupukan berimbang sesuai anjuran perangkat uji tanah.',
                        'Jaga sanitasi pematang sawah dan kelestarian aliran air parit sawah.'
                    ],
                    'bbox'         => [],
                    'detections'   => [],
                    'safety_warning' => null
                ];

                // Simpan riwayat tanaman sehat ke DB
                History::create([
                    'user_id'      => auth()->id(),
                    'foto'         => asset('storage/' . $imagePath),
                    'bbox_image'   => asset('storage/' . $imagePath),
                    'bbox'         => $healthyData['bbox'],
                    'detections'   => $healthyData['detections'],
                    'disease_name' => $healthyData['disease_name'],
                    'latin_name'   => $healthyData['latin_name'],
                    'accuracy'     => $healthyData['accuracy'],
                    'status'       => $healthyData['status'],
                    'kategori'     => $healthyData['kategori'],
                    'description'  => $healthyData['description'],
                    'actions'      => $healthyData['actions'],
                    'tanggal'      => now()->format('Y-m-d'),
                    'waktu'        => now()->format('H:i'),
                ]);

                return response()->json([
                    'status'     => 'success',
                    'detections' => [],
                    'summary'    => $healthyData
                ]);
            }

            // Mapping semua deteksi yang berhasil ditemukan AI
            $mappedDetections = [];
            foreach ($detections as $det) {
                $rawClass = strtolower($det['class']);
                $info = $this->diseaseMap[$rawClass] ?? [
                    'name'        => $det['class'],
                    'latin_name'  => '-',
                    'kategori'    => 'Tidak Dikenal',
                    'status'      => 'Waspada',
                    'description' => 'Hasil deteksi model AI. Data referensi agronomi belum tersedia di database lokal.',
                    'actions'     => ['Konsultasikan gejala tanaman padi dengan penyuluh pertanian setempat.'],
                ];

                $mappedDetections[] = [
                    'class'       => $det['class'],
                    'confidence'  => round($det['confidence'] * 100, 2),
                    'bbox'        => $det['bbox'],
                    'name'        => $info['name'],
                    'latin_name'  => $info['latin_name'],
                    'kategori'    => $info['kategori'],
                    'status'      => $info['status'],
                    'description' => $info['description'],
                    'actions'     => $info['actions'],
                ];
            }

            // Urutkan berdasarkan tingkat keyakinan (confidence) tertinggi
            usort($mappedDetections, function ($a, $b) {
                return $b['confidence'] <=> $a['confidence'];
            });

            // ── LOGIKA MERGE MULTI-KELAS (MULTI-CLASS RECONCILIATION) ──────
            $uniqueDetected = collect($mappedDetections)->unique('name');
            $isMultiClass = $uniqueDetected->count() > 1;

            $finalName        = '';
            $finalLatin       = '';
            $finalKategori    = '';
            $finalStatus      = '';
            $finalDescription = '';
            $finalActions     = [];
            $safetyWarning    = null;

            // Ambil akurasi dari deteksi dengan keyakinan tertinggi
            $primaryDetection = $mappedDetections[0];
            $finalAccuracy    = $primaryDetection['confidence'];

            if ($isMultiClass) {
                // Penggabungan Informasi Dasar
                $finalName        = $uniqueDetected->pluck('name')->implode(' & ');
                $finalLatin       = $uniqueDetected->pluck('latin_name')->filter(fn($val) => $val !== '-')->implode(' / ');
                $finalKategori    = $uniqueDetected->pluck('kategori')->unique()->implode(' dan ');
                
                // Ambil status paling kritis (Sangat Bahaya > Bahaya > Waspada)
                $statuses         = $uniqueDetected->pluck('status')->toArray();
                if (in_array('Sangat Bahaya', $statuses)) {
                    $finalStatus = 'Sangat Bahaya';
                } elseif (in_array('Bahaya', $statuses)) {
                    $finalStatus = 'Bahaya';
                } else {
                    $finalStatus = 'Waspada';
                }

                // Penggabungan narasi deskriptif terstruktur
                $descParts = [];
                foreach ($uniqueDetected as $item) {
                    $descParts[] = $item['name'] . ": " . $item['description'];
                }
                $finalDescription = "Perhatian! Terdeteksi serangan multi-kelas secara bersamaan. " . implode(' ', $descParts);

                // Penggabungan saran tindakan tanpa duplikasi
                $allActions = [];
                foreach ($uniqueDetected as $item) {
                    $allActions = array_merge($allActions, $item['actions']);
                }
                $finalActions = array_values(array_unique($allActions));

                // Deteksi bentrokan campuran kimia pertanian (Safety Warning Guardrail)
                $classes = collect($detections)->map(fn($d) => strtolower($d['class']))->toArray();
                if (in_array('blast', $classes) && in_array('blight', $classes)) {
                    $safetyWarning = "PERINGATAN BAHAN AKTIF: Tanaman padi Anda terserang Jamur (Blas) dan Bakteri (Hawar Daun) sekaligus. Jangan mencampur fungisida Tricyclazole langsung dengan bakterisida Tembaga Hidroksida dalam satu tangki semprot. Sifatnya antagonis dan menurunkan kemanjuran obat. Berikan selang waktu penyemprotan minimal 2 hari.";
                } elseif (in_array('wereng_coklat', $classes) && in_array('blast', $classes)) {
                    $safetyWarning = "TIPS SINERGI: Aplikasi pembasmian hama Wereng Coklat dan Jamur Blas Daun dapat dipadukan bersama. Pastikan menggunakan air bersih ber-pH normal saat melarutkan campuran Pymetrozine dengan Tricyclazole.";
                }
            } else {
                // Jika hanya terdeteksi satu kelas tunggal
                $finalName        = $primaryDetection['name'];
                $finalLatin       = $primaryDetection['latin_name'];
                $finalKategori    = $primaryDetection['kategori'];
                $finalStatus      = $primaryDetection['status'];
                $finalDescription = $primaryDetection['description'];
                $finalActions     = $primaryDetection['actions'];
            }

            // Url gambar hasil olah bounding box (fallback ke foto asli jika FastAPI tidak memproses visualisasi)
            $bboxImageUrl = isset($result['bbox_image_url']) 
                ? $result['bbox_image_url'] 
                : asset('storage/' . $imagePath);

            // 8. Operasi Database Transaksional (Aman & Konsisten)
            $history = DB::transaction(function () use (
                $imagePath, $bboxImageUrl, $primaryDetection, $mappedDetections,
                $finalName, $finalLatin, $finalAccuracy, $finalStatus, $finalKategori, $finalDescription, $finalActions
            ) {
                return History::create([
                    'user_id'      => auth()->id(),
                    'foto'         => asset('storage/' . $imagePath),
                    'bbox_image'   => $bboxImageUrl,
                    'bbox'         => $primaryDetection['bbox'], // Menyimpan koordinat box primer
                    'detections'   => $mappedDetections, // Menyimpan array komparasi deteksi lengkap
                    'disease_name' => $finalName,
                    'latin_name'   => $finalLatin,
                    'accuracy'     => $finalAccuracy,
                    'status'       => $finalStatus,
                    'kategori'     => $finalKategori,
                    'description'  => $finalDescription,
                    'actions'      => $finalActions, // Otomatis diserialisasikan ke JSON di DB oleh model
                    'tanggal'      => now()->format('Y-m-d'),
                    'waktu'        => now()->format('H:i'),
                ]);
            });

            // 9. Kirim respons payload terpadu ke Mobile Client
            return response()->json([
                'status'     => 'success',
                'detections' => $mappedDetections,
                'summary'    => [
                    'history_id'     => $history->id,
                    'foto'           => asset('storage/' . $imagePath),
                    'bbox_image'     => $bboxImageUrl,
                    'disease_name'   => $finalName,
                    'latin_name'     => $finalLatin,
                    'accuracy'       => $finalAccuracy,
                    'status'         => $finalStatus,
                    'kategori'       => $finalKategori,
                    'description'    => $finalDescription,
                    'actions'        => $finalActions,
                    'safety_warning' => $safetyWarning
                ]
            ]);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Bersihkan file lokal jika komunikasi FastAPI gagal agar storage tidak penuh
            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }

            return response()->json([
                'status'  => 'error',
                'message' => 'Model server (FastAPI) gagal dihubungi.'
            ], 502);
        } catch (Exception $e) {
            // Bersihkan file lokal jika terjadi kegagalan sistem umum lainnya
            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }

            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kegagalan sistem: ' . $e->getMessage()
            ], 500);
        }
    }

    // ── RIWAYAT ───────────────────────────────────────────────────────
    public function histories(Request $request)
    {
        $histories = History::where('user_id', $request->user()->id)
            ->latest()
            ->get();

        if ($histories->isEmpty()) {
            return response()->json([
                'status'  => 'empty',
                'message' => 'Belum ada riwayat deteksi',
                'data'    => []
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $histories
        ]);
    }

    // ── SHOW DETAIL ──────────────────────────────────────────────────
    public function show($id)
    {
        $history = History::find($id);

        if (!$history) {
            return response()->json([
                'message' => 'Riwayat tidak ditemukan'
            ], 404);
        }

        return response()->json($history);
    }
}