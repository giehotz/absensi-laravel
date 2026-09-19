<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthAndDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_login_page_can_be_rendered(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('MASUK KE SISTEM');
        $response->assertSee('Universal ID');
    }

    public function test_admin_can_login_using_email_and_access_admin_dashboard(): void
    {
        $response = $this->post('/login', [
            'login_identifier' => 'admin@sekolah.sch.id',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();

        $dash = $this->get('/admin/dashboard');
        $dash->assertStatus(200);
        $dash->assertSee('PANEL KONTROL ABSENSI');
    }

    public function test_guru_can_login_using_nip_and_access_guru_dashboard(): void
    {
        $response = $this->post('/login', [
            'login_identifier' => '198501012010011001', // NIP Budi Santoso
            'password' => 'password',
        ]);

        $response->assertRedirect(route('guru.dashboard'));
        $this->assertAuthenticated();

        $dash = $this->get('/guru/dashboard');
        $dash->assertStatus(200);
        $dash->assertSee('DEWAN GURU');
    }

    public function test_siswa_can_login_using_nis_and_access_siswa_dashboard(): void
    {
        $response = $this->post('/login', [
            'login_identifier' => '12345', // NIS Ahmad Fauzi
            'password' => 'password',
        ]);

        $response->assertRedirect(route('siswa.dashboard'));
        $this->assertAuthenticated();

        $dash = $this->get('/siswa/dashboard');
        $dash->assertStatus(200);
        $dash->assertSee('PORTAL SISWA');
        $dash->assertSee('Kartu Pelajar Digital');
    }

    public function test_orangtua_can_login_using_email_and_access_orangtua_dashboard(): void
    {
        $response = $this->post('/login', [
            'login_identifier' => 'ortu@sekolah.sch.id',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('orangtua.dashboard'));
        $this->assertAuthenticated();

        $dash = $this->get('/orangtua/dashboard');
        $dash->assertStatus(200);
        $dash->assertSee('PORTAL WALI MURID');
    }

    public function test_role_middleware_blocks_siswa_from_accessing_admin_dashboard(): void
    {
        $siswaUser = User::where('email', 'siswa@sekolah.sch.id')->first();
        $this->actingAs($siswaUser);

        $response = $this->get('/admin/dashboard');
        $response->assertRedirect(route('siswa.dashboard'));
        $response->assertSessionHas('error');
    }

    public function test_user_can_logout(): void
    {
        $adminUser = User::where('email', 'admin@sekolah.sch.id')->first();
        $this->actingAs($adminUser);

        $response = $this->post('/logout');
        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
