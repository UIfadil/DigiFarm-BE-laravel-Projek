<?php

namespace App\Http\Controllers;

use App\Models\History;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PredictionController extends Controller
{
    // ── DISEASE MAP ──────────────────────────────────────────────────
    private array $diseaseMap = [
        'penggerek_batang' => [
            'name'        => 'Penggerek Batang',
            'latin_name'  => 'Scirpophaga incertulas',
            'kategori'    => 'Hama',
            'status'      => 'Bahaya',
            'description' => 'Hama ini menyerang batang padi dan dapat menyebabkan tanaman mati atau gagal panen.',
            'actions'     => [
                'Gunakan perangkap hama',
                'Lakukan sanitasi lahan',
                'Gunakan insektisida sesuai dosis',
            ],
        ],
        'blast' => [
            'name'        => 'Blast',
            'latin_name'  => 'Pyricularia oryzae',
            'kategori'    => 'Penyakit',
            'status'      => 'Bahaya',
            'description' => 'Penyakit blast menyerang daun padi dan menyebabkan bercak berbentuk belah ketupat.',
            'actions'     => [
                'Gunakan varietas tahan blast',
                'Kurangi pupuk nitrogen berlebih',
                'Semprot fungisida bila diperlukan',
            ],
        ],
        'blight' => [
            'name'        => 'Blight',
            'latin_name'  => 'Xanthomonas oryzae',
            'kategori'    => 'Penyakit',
            'status'      => 'Bahaya',
            'description' => 'Blight menyebabkan daun menguning dan mengering dari ujung daun.',
            'actions'     => [
                'Gunakan benih sehat',
                'Perbaiki drainase sawah',
                'Gunakan bakterisida',
            ],
        ],
        'wereng_coklat' => [
            'name'        => 'Wereng Coklat',
            'latin_name'  => 'Nilaparvata lugens',
            'kategori'    => 'Hama',
            'status'      => 'Bahaya',
            'description' => 'Wereng coklat menghisap cairan tanaman dan dapat menyebabkan puso.',
            'actions'     => [
                'Kurangi penggunaan pestisida berlebihan',
                'Gunakan musuh alami wereng',
                'Gunakan insektisida sesuai anjuran',
            ],
        ],
        'tungro' => [
            'name'        => 'Tungro',
            'latin_name'  => 'Rice Tungro Virus',
            'kategori'    => 'Penyakit',
            'status'      => 'Bahaya',
            'description' => 'Tungro menyebabkan tanaman kerdil dan daun menguning.',
            'actions'     => [
                'Cabut tanaman terinfeksi',
                'Gunakan varietas tahan tungro',
                'Kendalikan vektor wereng hijau',
            ],
        ],
        'tikus' => [
            'name'        => 'Tikus Sawah',
            'latin_name'  => 'Rattus argentiventer',
            'kategori'    => 'Hama',
            'status'      => 'Bahaya',
            'description' => 'Tikus menyerang batang dan bulir padi sehingga menyebabkan gagal panen.',
            'actions'     => [
                'Pasang perangkap tikus',
                'Lakukan gropyokan',
                'Jaga kebersihan area sawah',
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
            'image.image' => 'Format file tidak didukung',
            'image.max' => 'Ukuran file terlalu besar'
        ]
        );

        $file = $request->file('image');

        try {
            // Simpan foto asli
            $imagePath = $file->store('histories', 'public');

            $response = Http::timeout(30)
                ->attach(
                    'file',
                    file_get_contents($file->getRealPath()),
                    $file->getClientOriginalName()
                )
                ->post('http://127.0.0.1:5000/predict');

            if ($response->failed()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Model server tidak merespons'
                ], 502);
            }

            $result     = $response->json();
            $detections = $result['detections'] ?? [];
            $detection  = $detections[0] ?? null;

            if (!$detection) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Hama atau penyakit tidak terdeteksi'
                ], 404);
            }

            // Mapping semua deteksi
            $mappedDetections = [];

            foreach ($detections as $det) {

                $rawClass = strtolower($det['class']);

                $info = $this->diseaseMap[$rawClass] ?? [
                    'name'        => $det['class'],
                    'latin_name'  => '-',
                    'kategori'    => 'Tidak Dikenal',
                    'status'      => 'Waspada',
                    'description' => 'Hasil deteksi AI. Data penyakit belum tersedia.',
                    'actions'     => ['Konsultasikan dengan ahli pertanian'],
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

            usort($mappedDetections, function ($a, $b) {
                return $b['confidence'] <=> $a['confidence'];
            });

            $primaryDetection = $mappedDetections[0];

            History::create([
                'user_id' => auth()->id(),
                'foto'         => asset('storage/' . $imagePath),

                // bbox kolom lama — tetap simpan dari deteksi pertama (backward compat)
                'bbox'         => $detection['bbox'],

                // ✅ detections kolom baru — simpan SEMUA deteksi lengkap
                'detections'   => $mappedDetections,

                'disease_name' => $primaryDetection['name'],
                'latin_name'   => $primaryDetection['latin_name'],
                'accuracy'     => $primaryDetection['confidence'],
                'status'       => $primaryDetection['status'],
                'kategori'     => $primaryDetection['kategori'],
                'description'  => $primaryDetection['description'],
                'actions'      => $primaryDetection['actions'],

                'tanggal'      => now()->format('Y-m-d'),
                'waktu'        => now()->format('H:i'),
            ]);

            return response()->json([
                'status' => 'success',
                'detections' => $mappedDetections
            ]);

        } catch (\Illuminate\Http\Client\ConnectionException $e)
        {
            return response()->json([
                'status'  => 'error',
                'message' => 'Model server tidak merespons'
            ], 502);
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
                'status' => 'empty',
                'message' => 'Belum ada riwayat deteksi',
                'data' => []
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => $histories
        ]);
    }
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