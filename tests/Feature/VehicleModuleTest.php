<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Vehicle\Jobs\DeleteVehicleMediaFiles;
use App\Modules\Vehicle\Models\Vehicle;
use App\Modules\Vehicle\Models\VehicleExpense;
use App\Modules\Vehicle\Models\VehicleParty;
use App\Modules\Vehicle\Providers\VehicleServiceProvider;
use App\Modules\Vehicle\Services\VehicleMediaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class VehicleModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        File::ensureDirectoryExists(storage_path('framework/views'));
        config(['view.compiled' => storage_path('framework/views')]);
        $this->withoutVite();
        RateLimiter::clear('vehicle-login-ip:'.hash('sha256', '127.0.0.1'));
    }

    private function party(string $name = 'Aydın'): VehicleParty
    {
        return VehicleParty::create(['ad' => $name]);
    }

    private function vehicle(string $plate = '26 AEV 158', string $password = 'AbCdEf123456', array $extra = []): Vehicle
    {
        return Vehicle::create(array_merge(['plaka' => $plate, 'marka' => 'Citroen', 'model' => 'Berlingo', 'model_yili' => 2020, 'sase_no' => 'SASE'.preg_replace('/\\D/', '', $plate), 'motor_no' => 'MOTOR'.preg_replace('/\\D/', '', $plate), 'renk' => 'Beyaz', 'yakit_cinsi' => 'Dizel', 'guncel_km' => 100000, 'erisim_sifresi_hash' => Hash::make($password)], $extra));
    }

    private function expense(Vehicle $v, VehicleParty $payer, array $extra = []): VehicleExpense
    {
        return $v->expenses()->create(array_merge(['tarih' => '2026-07-01', 'aciklama' => 'Bakım', 'tutar' => 100, 'kategori' => 'bakim', 'odeyen_party_id' => $payer->id, 'mahsup_durumu' => 'borc_yok'], $extra));
    }

    public function test_vehicle_module_routes_respect_toggle(): void
    {
        $router = $this->app['router'];
        $original = $router->getRoutes();
        $router->setRoutes(new RouteCollection);
        config(['modules.vehicle' => false]);
        (new VehicleServiceProvider($this->app))->boot();
        $this->assertFalse($router->has('vehicle.owner'));
        $router->setRoutes(new RouteCollection);
        config(['modules.vehicle' => true]);
        (new VehicleServiceProvider($this->app))->boot();
        $router->getRoutes()->refreshNameLookups();
        $this->assertTrue($router->has('vehicle.owner'));
        $router->setRoutes($original);
    }

    public function test_admin_can_create_list_and_manage_vehicle_dashboard_with_nav(): void
    {
        $user = User::factory()->create();
        $party = $this->party();
        $this->actingAs($user)->post(route('admin.vehicles.store'), ['plaka' => '34 ABC 987', 'marka' => 'Ford', 'model' => 'Focus', 'model_yili' => 2022, 'sase_no' => 'SASE34', 'motor_no' => 'MOTOR34', 'renk' => 'Mavi', 'yakit_cinsi' => 'Benzin', 'guncel_km' => 12, 'sahip_party_id' => $party->id])->assertRedirect();
        $vehicle = Vehicle::where('plate_key', '34ABC987')->firstOrFail();
        $this->actingAs($user)->get(route('admin.vehicles.index'))->assertOk()->assertSee('34 ABC 987')->assertSee('Araçlar');
        $this->actingAs($user)->get(route('admin.vehicles.show', $vehicle))->assertOk()->assertSee('Masraflar')->assertSee('Sorun / görev / kaza')->assertSee('Medya');
    }

    public function test_owner_login_failure_pair_throttle_success_and_privacy_headers(): void
    {
        $v = $this->vehicle();
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('vehicle.access.login'), ['plaka' => $v->plaka, 'sifre' => 'yanlis'])->assertSessionHasErrors('plaka');
        } $this->post(route('vehicle.access.login'), ['plaka' => $v->plaka, 'sifre' => 'AbCdEf123456'])->assertStatus(429);
        RateLimiter::clear('vehicle-login:'.hash('sha256', '127.0.0.1|'.$v->plate_key));
        $this->post(route('vehicle.access.login'), ['plaka' => $v->plaka, 'sifre' => 'AbCdEf123456'])->assertRedirect(route('vehicle.owner'));
        $this->get(route('vehicle.owner'))->assertOk()->assertHeader('Cache-Control')->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    public function test_ip_hourly_cap_blocks_plate_scanning(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $this->post(route('vehicle.access.login'), ['plaka' => 'X'.$i, 'sifre' => 'wrong']);
        } $this->post(route('vehicle.access.login'), ['plaka' => 'NEWPLATE', 'sifre' => 'wrong'])->assertStatus(429);
    }

    public function test_owner_session_is_vehicle_scoped_absolute_and_reset_invalidates_it(): void
    {
        $a = $this->vehicle('34 AAA 111');
        $b = $this->vehicle('34 BBB 222', 'ZyXwVu654321');
        $party = $this->party('UNIQUE_OTHER_PARTY');
        $this->expense($b, $party, ['aciklama' => 'UNIQUE_OTHER_EXPENSE']);
        $this->withSession(['vehicle_owner' => ['id' => $a->id, 'credential_version' => 1, 'logged_at' => now()->timestamp]])->get('/aracim')->assertOk()->assertDontSee('UNIQUE_OTHER_EXPENSE');
        $this->withSession(['vehicle_owner' => ['id' => $a->id, 'credential_version' => 1, 'logged_at' => now()->subDay()->timestamp]])->get('/aracim')->assertRedirect('/arac-sorgula');
        $a->increment('credential_version');
        $this->withSession(['vehicle_owner' => ['id' => $a->id, 'credential_version' => 1, 'logged_at' => now()->timestamp]])->get('/aracim')->assertRedirect('/arac-sorgula');
    }

    public function test_cross_vehicle_data_and_media_parent_injection_are_rejected(): void
    {
        $a = $this->vehicle('34 CCC 111');
        $b = $this->vehicle('34 DDD 222');
        $p = $this->party();
        $expense = $this->expense($b, $p);
        $service = app(VehicleMediaService::class);
        Storage::fake('local');
        $this->expectException(ValidationException::class);
        $service->store($a, UploadedFile::fake()->image('x.jpg'), 'genel', $expense);
    }

    public function test_media_authorization_matrix_for_admin_owner_wrong_owner_and_showcase(): void
    {
        Storage::fake('local');
        $a = $this->vehicle('34 EEE 111', extra: ['vitrin_acik' => true]);
        $b = $this->vehicle('34 FFF 222');
        Storage::disk('local')->put('vehicles/a.jpg', 'image');
        Storage::disk('local')->put('vehicles/doc.jpg', 'document');
        $public = $a->media()->create(['tur' => 'foto', 'role' => 'genel', 'dosya_yolu' => 'vehicles/a.jpg', 'mime_type' => 'image/jpeg']);
        $secret = $a->media()->create(['tur' => 'foto', 'role' => 'ruhsat', 'dosya_yolu' => 'vehicles/doc.jpg', 'mime_type' => 'image/jpeg']);
        $this->actingAs(User::factory()->create())->get(route('admin.vehicles.media.show', [$a, $public]))->assertOk();
        auth()->logout();
        $this->withSession(['vehicle_owner' => ['id' => $a->id, 'credential_version' => 1, 'logged_at' => now()->timestamp]])->get(route('vehicle.owner.media', $public))->assertOk();
        $this->withSession(['vehicle_owner' => ['id' => $b->id, 'credential_version' => 1, 'logged_at' => now()->timestamp]])->get(route('vehicle.owner.media', $public))->assertNotFound();
        $this->get(route('vehicle.showcase.media', [$a->plate_key, $public]))->assertOk();
        $this->get(route('vehicle.showcase.media', [$a->plate_key, $secret]))->assertNotFound();
    }

    public function test_showcase_closed_is_404_and_open_response_has_only_allowlisted_data(): void
    {
        $owner = $this->party('UNIQUE_SECRET_OWNER_8F3');
        $v = $this->vehicle('34 GGG 111', extra: ['sahip_party_id' => $owner->id, 'sase_no' => 'UNIQUE_CHASSIS_77', 'motor_no' => 'UNIQUE_ENGINE_88', 'notlar' => 'UNIQUE_INTERNAL_NOTE_99']);
        $p = $this->party('UNIQUE_PAYER_6C2');
        $this->expense($v, $p, ['aciklama' => 'UNIQUE_RAW_DESCRIPTION_4A1', 'tutar' => 987654.32, 'vitrinde_goster' => true, 'public_summary' => 'Yetkili servis bakımı']);
        $v->media()->create(['tur' => 'foto', 'role' => 'ruhsat', 'dosya_yolu' => 'vehicles/UNIQUE_SECRET_DOC.jpg', 'mime_type' => 'image/jpeg']);
        $this->get(route('vehicle.showcase', $v->plate_key))->assertNotFound();
        $v->update(['vitrin_acik' => true]);
        $this->get(route('vehicle.showcase', $v->plate_key))->assertOk()->assertSee('Yetkili servis bakımı')->assertDontSee('987654.32')->assertDontSee('987.654,32')->assertDontSee('UNIQUE_SECRET_OWNER_8F3')->assertDontSee('UNIQUE_PAYER_6C2')->assertDontSee('UNIQUE_CHASSIS_77')->assertDontSee('UNIQUE_ENGINE_88')->assertDontSee('UNIQUE_INTERNAL_NOTE_99')->assertDontSee('UNIQUE_RAW_DESCRIPTION_4A1')->assertDontSee('UNIQUE_SECRET_DOC.jpg');
    }

    public function test_soft_delete_preserves_media_and_force_delete_dispatches_retryable_cleanup(): void
    {
        Storage::fake('local');
        Queue::fake();
        $v = $this->vehicle();
        $p = $this->party();
        $e = $this->expense($v, $p);
        Storage::disk('local')->put('vehicles/keep.jpg', 'x');
        $e->media()->create(['vehicle_id' => $v->id, 'tur' => 'foto', 'role' => 'genel', 'dosya_yolu' => 'vehicles/keep.jpg', 'mime_type' => 'image/jpeg']);
        $e->delete();
        Storage::disk('local')->assertExists('vehicles/keep.jpg');
        $e->restore();
        app(VehicleMediaService::class)->purgeParent($e);
        Queue::assertPushed(DeleteVehicleMediaFiles::class, function ($job) {
            $this->assertSame(5, $job->tries);
            $job->handle();

            return true;
        });
        Storage::disk('local')->assertMissing('vehicles/keep.jpg');
    }

    public function test_issue_soft_delete_preserves_media_and_force_delete_is_restricted_when_expense_exists(): void
    {
        Storage::fake('local');
        $v = $this->vehicle();
        $p = $this->party();
        $i = $v->issues()->create(['baslik' => 'Arıza', 'kategori' => 'ariza', 'durum' => 'acik', 'oncelik' => 'normal', 'bildirilme_tarihi' => '2026-07-01']);
        Storage::disk('local')->put('vehicles/issue.jpg', 'x');
        $i->media()->create(['vehicle_id' => $v->id, 'tur' => 'foto', 'role' => 'hasar_once', 'dosya_yolu' => 'vehicles/issue.jpg', 'mime_type' => 'image/jpeg']);
        $i->delete();
        Storage::disk('local')->assertExists('vehicles/issue.jpg');
        $i->restore();
        $this->expense($v, $p, ['vehicle_issue_id' => $i->id]);
        $this->expectException(ValidationException::class);
        app(VehicleMediaService::class)->purgeParent($i);
    }

    public function test_balance_groups_open_receivables_and_excludes_closed_and_deleted(): void
    {
        $v = $this->vehicle();
        $a = $this->party('A');
        $b = $this->party('B');
        $c = $this->party('C');
        $this->expense($v, $a, ['tutar' => 100, 'mahsup_durumu' => 'alinacak', 'borclu_party_id' => $b->id]);
        $this->expense($v, $a, ['tutar' => 25.50, 'mahsup_durumu' => 'alinacak', 'borclu_party_id' => $b->id]);
        $this->expense($v, $c, ['tutar' => 40, 'mahsup_durumu' => 'alinacak', 'borclu_party_id' => $b->id]);
        $this->expense($v, $a, ['tutar' => 999, 'mahsup_durumu' => 'kapandi', 'borclu_party_id' => $b->id, 'settled_at' => now()]);
        $deleted = $this->expense($v, $a, ['tutar' => 500, 'mahsup_durumu' => 'alinacak', 'borclu_party_id' => $b->id]);
        $deleted->delete();
        $balances = $v->balances();
        $this->assertCount(2, $balances);
        $this->assertSame('125.5', (string) $balances->firstWhere('odeyen_party_id', $a->id)->toplam);
        $this->assertSame('40', (string) $balances->firstWhere('odeyen_party_id', $c->id)->toplam);
    }

    public function test_admin_nested_crud_validation_matrix_and_change_logs(): void
    {
        $this->actingAs(User::factory()->create());
        $payer = $this->party('Payer');
        $debtor = $this->party('Debtor');
        $v = $this->vehicle();
        $this->post(route('admin.vehicles.issues.store', $v), ['baslik' => 'Bitti', 'kategori' => 'gorev', 'durum' => 'tamamlandi', 'oncelik' => 'normal', 'bildirilme_tarihi' => '2026-07-01'])->assertSessionHasErrors('cozulme_tarihi');
        $this->post(route('admin.vehicles.issues.store', $v), ['baslik' => 'Kontrol', 'kategori' => 'gorev', 'durum' => 'acik', 'oncelik' => 'normal', 'bildirilme_tarihi' => '2026-07-01'])->assertRedirect();
        $issue = $v->issues()->firstOrFail();
        $base = ['tarih' => '2026-07-02', 'aciklama' => 'Parça', 'tutar' => 150, 'kategori' => 'parca', 'odeyen_party_id' => $payer->id, 'mahsup_durumu' => 'alinacak', 'vehicle_issue_id' => $issue->id];
        $this->post(route('admin.vehicles.expenses.store', $v), $base)->assertSessionHasErrors('mahsup_durumu');
        $this->post(route('admin.vehicles.expenses.store', $v), $base + ['borclu_party_id' => $debtor->id])->assertRedirect();
        $expense = $v->expenses()->firstOrFail();
        $this->put(route('admin.vehicles.expenses.update', [$v, $expense]), array_merge($base, ['borclu_party_id' => $debtor->id, 'tutar' => 175]))->assertRedirect();
        $this->assertDatabaseHas('vehicle_change_logs', ['vehicle_expense_id' => $expense->id, 'alan_adi' => 'tutar', 'eski_deger' => '150', 'yeni_deger' => '175']);
        $this->put(route('admin.vehicles.update', $v), ['plaka' => $v->plaka, 'marka' => $v->marka, 'model' => $v->model, 'model_yili' => $v->model_yili, 'sase_no' => $v->sase_no, 'motor_no' => $v->motor_no, 'renk' => $v->renk, 'yakit_cinsi' => $v->yakit_cinsi, 'guncel_km' => 90000, 'vitrin_acik' => 0])->assertSessionHasErrors('km_duzeltme_nedeni');
        $this->put(route('admin.vehicles.update', $v), ['plaka' => $v->plaka, 'marka' => $v->marka, 'model' => $v->model, 'model_yili' => $v->model_yili, 'sase_no' => $v->sase_no, 'motor_no' => $v->motor_no, 'renk' => $v->renk, 'yakit_cinsi' => $v->yakit_cinsi, 'guncel_km' => 90000, 'vitrin_acik' => 0, 'km_duzeltme_nedeni' => 'Sayaç düzeltmesi'])->assertRedirect();
        $this->assertDatabaseHas('vehicle_change_logs', ['vehicle_id' => $v->id, 'alan_adi' => 'guncel_km', 'eski_deger' => '100000', 'yeni_deger' => '90000']);
    }
}
