<?php

namespace Tests\Feature;

use App\Models\Subject;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubjectApiSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_api_reference_subjects_returns_json_list(): void
    {
        $response = $this->getJson(route('api.reference-subjects'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'level',
                'total',
                'data' => [
                    '*' => ['code', 'name', 'category', 'levels'],
                ],
            ]);

        $this->assertTrue($response->json('success'));
        $this->assertGreaterThan(0, $response->json('total'));
    }

    public function test_api_reference_subjects_filters_by_level(): void
    {
        $response = $this->getJson(route('api.reference-subjects', ['level' => 'SMP']));

        $response->assertStatus(200);
        $data = $response->json('data');

        $codes = collect($data)->pluck('code')->all();
        // SMP harus punya IPA dan IPS
        $this->assertContains('IPA', $codes);
        $this->assertContains('IPS', $codes);
        // SMP tidak boleh punya Fisika SMA (khusus SMA/MA)
        $this->assertNotContains('FIS', $codes);
    }

    public function test_api_reference_subjects_supports_kma_1503_mi_madrasah_curriculum(): void
    {
        $response = $this->getJson(route('api.reference-subjects', ['level' => 'MI']));

        $response->assertStatus(200);
        $data = $response->json('data');
        $codes = collect($data)->pluck('code')->all();

        // Ciri khas madrasah sesuai KMA 1503 Tahun 2025
        $this->assertContains('ALQ', $codes);
        $this->assertContains('AA', $codes);
        $this->assertContains('FIQ', $codes);
        $this->assertContains('SKI', $codes);
        $this->assertContains('ARB', $codes);
        $this->assertContains('KKA', $codes); // Koding dan AI
        $this->assertContains('IPAS', $codes);
        $this->assertContains('PPKN', $codes);
        $this->assertContains('IND', $codes);
        $this->assertContains('MTK', $codes);
    }

    public function test_api_reference_subjects_filters_by_search(): void
    {
        $response = $this->getJson(route('api.reference-subjects', ['search' => 'Matematika']));

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertNotEmpty($data);
        foreach ($data as $item) {
            $matched = str_contains(strtolower($item['name']), 'matematika')
                || str_contains(strtolower($item['code']), 'matematika')
                || str_contains(strtolower($item['category']), 'matematika');
            $this->assertTrue($matched);
        }
    }

    public function test_admin_can_sync_selected_subjects_into_database(): void
    {
        $admin = User::where('role', 'admin')->first();
        $this->actingAs($admin);

        $payload = [
            'subjects' => [
                ['code' => 'TEST-MAPEL-1', 'name' => 'Mata Pelajaran Uji Coba 1'],
                ['code' => 'TEST-MAPEL-2', 'name' => 'Mata Pelajaran Uji Coba 2'],
            ],
        ];

        $response = $this->post(route('admin.subjects.sync'), $payload);

        $response->assertRedirect(route('admin.subjects.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('subjects', [
            'code' => 'TEST-MAPEL-1',
            'name' => 'Mata Pelajaran Uji Coba 1',
        ]);
        $this->assertDatabaseHas('subjects', [
            'code' => 'TEST-MAPEL-2',
            'name' => 'Mata Pelajaran Uji Coba 2',
        ]);
    }

    public function test_sync_skips_already_existing_subject_codes(): void
    {
        $admin = User::where('role', 'admin')->first();
        $this->actingAs($admin);

        // Buat satu mapel yang sudah ada
        Subject::create([
            'code' => 'EXISTING-01',
            'name' => 'Mapel Sudah Ada',
        ]);

        $payload = [
            'subjects' => [
                ['code' => 'EXISTING-01', 'name' => 'Nama Baru Tidak Menggantikan'],
                ['code' => 'BARU-01', 'name' => 'Mapel Yang Benar-Benar Baru'],
            ],
        ];

        $response = $this->post(route('admin.subjects.sync'), $payload);

        $response->assertRedirect(route('admin.subjects.index'));
        $response->assertSessionHas('success');

        // Pastikan nama mapel lama tidak tertimpa/terduplikasi
        $this->assertEquals(1, Subject::where('code', 'EXISTING-01')->count());
        $this->assertDatabaseHas('subjects', [
            'code' => 'EXISTING-01',
            'name' => 'Mapel Sudah Ada',
        ]);

        // Mapel baru tetap tersimpan
        $this->assertDatabaseHas('subjects', [
            'code' => 'BARU-01',
            'name' => 'Mapel Yang Benar-Benar Baru',
        ]);
    }

    public function test_guest_or_non_admin_cannot_sync_subjects(): void
    {
        $guru = User::where('role', 'guru')->first();
        $this->actingAs($guru);

        $response = $this->post(route('admin.subjects.sync'), [
            'subjects' => [
                ['code' => 'HACK', 'name' => 'Hacked Subject'],
            ],
        ]);

        $response->assertRedirect(route('guru.dashboard'));
        $this->assertDatabaseMissing('subjects', [
            'code' => 'HACK',
        ]);
    }
}
