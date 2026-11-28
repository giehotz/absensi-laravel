<?php

namespace App\Models\Supervisi;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class SupervisiCoachingTemplate extends Model
{
    protected $table = 'supervisi_coaching_templates';

    protected $fillable = [
        'tahap',
        'pertanyaan_tujuan',
        'pertanyaan_identifikasi',
        'pertanyaan_rencana',
        'pertanyaan_tanggung_jawab',
        'template_tujuan',
        'template_identifikasi',
        'template_rencana',
        'template_tanggung_jawab',
        'template_catatan_coach',
        'updated_by',
    ];

    /**
     * Bawaan sistem resmi untuk pertanyaan pemandu & isian template TIRTA per tahap.
     *
     * @return array<string, string>
     */
    public static function defaultsFor(string $tahap): array
    {
        if ($tahap === SupervisiCoachingTirta::TAHAP_PRA) {
            return [
                'tahap' => SupervisiCoachingTirta::TAHAP_PRA,
                'pertanyaan_tujuan' => 'Apa tujuan pembelajaran dan fokus kompetensi yang ingin Bapak/Ibu capai dalam sesi observasi kelas ini?',
                'pertanyaan_identifikasi' => 'Potensi murid dan tantangan kelas apa saja yang sudah teridentifikasi sebelum pembelajaran berlangsung?',
                'pertanyaan_rencana' => 'Strategi, metode, dan media apa yang Bapak/Ibu siapkan untuk mencapai target perilaku tersebut?',
                'pertanyaan_tanggung_jawab' => 'Kapan persiapan diselesaikan dan indikator apa yang menjadi ukuran keberhasilan sesi observasi?',
                'template_tujuan' => 'Mengoptimalkan keterlibatan aktif murid dan kejelasan penyampaian tujuan pembelajaran pada sesi observasi.',
                'template_identifikasi' => 'Sebagian murid masih memerlukan pemicu pertanyaan terbuka agar berani menyampaikan pendapat di kelas.',
                'template_rencana' => 'Menerapkan apersepsi kontekstual, tanya-jawab berjenjang, serta pengelompokan aktivitas terbimbing.',
                'template_tanggung_jawab' => 'Perangkat ajar dan instrumen asesmen disiapkan sebelum jadwal observasi dilaksanakan.',
                'template_catatan_coach' => 'Supervisor siap mendampingi secara kolaboratif selama pra-observasi hingga refleksi pasca-observasi.',
            ];
        }

        return [
            'tahap' => SupervisiCoachingTirta::TAHAP_PASCA,
            'pertanyaan_tujuan' => 'Apa tujuan yang ingin Bapak/Ibu capai dan kompetensi apa yang ingin ditingkatkan setelah observasi kelas?',
            'pertanyaan_identifikasi' => 'Berdasarkan hasil observasi kelas, bagian mana yang sudah berjalan baik dan kendala apa yang masih muncul?',
            'pertanyaan_rencana' => 'Langkah konkret, media, atau sumber daya apa yang akan dijalankan untuk perbaikan pembelajaran berikutnya?',
            'pertanyaan_tanggung_jawab' => 'Kapan rencana perbaikan mulai dijalankan dan bagaimana indikator keberhasilannya akan dipantau?',
            'template_tujuan' => 'Meningkatkan efektivitas pengelolaan kelas, keaktifan belajar murid, dan ketepatan umpan balik formatif.',
            'template_identifikasi' => 'Alur pembelajaran inti berjalan baik, namun transisi kegiatan dan pendampingan murid yang pasif masih dapat diperkuat.',
            'template_rencana' => 'Menyusun pertanyaan pemantik bertahap, memperkuat cek pemahaman berkala, dan mengevaluasi lembar kerja murid.',
            'template_tanggung_jawab' => 'Diterapkan mulai pertemuan pekan depan dan ditinjau ulang bersama supervisor pada evaluasi tindak lanjut.',
            'template_catatan_coach' => 'Pertahankan praktik baik yang sudah tampak di kelas dan laksanakan rencana penguatan secara konsisten.',
        ];
    }

    /**
     * Muat konfigurasi global untuk tahap Pra-Observasi dan Pasca-Observasi (fallback ke default sistem).
     *
     * @return Collection<string, self>
     */
    public static function forAllStages(): Collection
    {
        $stored = static::query()->get()->keyBy('tahap');
        $result = collect();

        foreach ([SupervisiCoachingTirta::TAHAP_PRA, SupervisiCoachingTirta::TAHAP_PASCA] as $tahap) {
            $defaults = static::defaultsFor($tahap);
            $record = $stored->get($tahap);

            if ($record) {
                foreach ($defaults as $field => $fallbackValue) {
                    if ($field !== 'tahap' && blank($record->{$field}) && str_starts_with($field, 'pertanyaan_')) {
                        $record->{$field} = $fallbackValue;
                    }
                }
                $result->put($tahap, $record);
            } else {
                $result->put($tahap, new self($defaults));
            }
        }

        return $result;
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
