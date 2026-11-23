<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/** BAKR as an installable app (PWA): manifest, service worker, /app start page, head tags. */
class InstallableAppTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role, ?string $providerKey = null): User
    {
        $u = new User;
        $u->forceFill([
            'name' => 'App Tester', 'email' => $role.'@app.test', 'password' => Hash::make('x'),
            'role' => $role, 'status' => 'active', 'provider_key' => $providerKey,
        ])->save();

        return $u;
    }

    public function test_the_manifest_is_valid_and_its_icons_exist(): void
    {
        $manifest = json_decode(file_get_contents(public_path('manifest.json')), true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame('BAKR', $manifest['short_name']);
        $this->assertSame('standalone', $manifest['display']);
        $this->assertSame('./', $manifest['start_url']); // the app opens on the homepage; relative: works under /BAKR/public too
        $this->assertSame('./', $manifest['scope']);

        $sizes = array_column($manifest['icons'], 'sizes');
        $this->assertContains('192x192', $sizes);
        $this->assertContains('512x512', $sizes);
        $this->assertContains('maskable', array_column($manifest['icons'], 'purpose'));

        foreach ($manifest['icons'] as $icon) {
            $this->assertFileExists(public_path($icon['src']));
        }
        $this->assertFileExists(public_path('images/app/apple-touch-icon.png'));
    }

    public function test_the_service_worker_never_caches_pages(): void
    {
        $sw = file_get_contents(public_path('sw.js'));

        // Pages come from the network; offline → offline.html. Only css/js/images are cached.
        $this->assertStringContainsString("request.mode === 'navigate'", $sw);
        // Unreachable (no connection, Tailscale off, server down) or no answer within PAGE_TIMEOUT_MS → offline.html.
        $this->assertStringContainsString('event.respondWith(pageOrUnreachable(request))', $sw);
        $this->assertStringContainsString('Promise.race([fetch(request), timeout])', $sw);
        $this->assertStringContainsString('caches.match(OFFLINE_URL)', $sw);
        $this->assertStringNotContainsString('caches.put(request', substr($sw, strpos($sw, 'function pageOrUnreachable'), 900));   // pages never cached

        // The page itself: plain words and one "Try again" button, nothing else.
        $offline = file_get_contents(public_path('offline.html'));
        $this->assertStringContainsString('BAKR is unreachable or down at the moment. Please try again later.', $offline);
        $this->assertSame(1, substr_count($offline, '<a '));
        $this->assertStringContainsString('>Try again</a>', $offline);
        $this->assertStringContainsString("request.method !== 'GET'", $sw);
        $this->assertFileExists(public_path('offline.html'));
        $this->assertFileExists(public_path('css/bakr-offline.css'));
    }

    public function test_guests_get_the_portal_picker(): void
    {
        $this->get('/app')->assertOk()
            ->assertSee('Welcome to BAKR')
            ->assertSee(route('student.login'), false)
            ->assertSee(route('scholar.login'), false)
            ->assertSee(route('provider.login'), false)
            ->assertDontSee(route('admin.login'), false);
    }

    public function test_signed_in_people_open_their_own_portal(): void
    {
        $this->actingAs($this->user('student'))->get('/app')->assertRedirect(route('student.dashboard'));
        $this->actingAs($this->user('scholar'))->get('/app')->assertRedirect(route('scholar.overview'));
        $this->actingAs($this->user('provider', 'ched'))->get('/app')->assertRedirect(route('provider.dashboard'));
        $this->actingAs($this->user('admin'))->get('/app')->assertRedirect(route('admin.overview'));
    }

    public function test_every_layout_links_the_manifest(): void
    {
        $manifest = asset('manifest.json');

        $this->get('/')->assertSee($manifest, false)->assertSee('data-pwa-install', false)           // layouts.app + footer
            ->assertSee(asset('js/bakr-app-mode.js'), false)->assertSee(asset('css/bakr-app-mode.css'), false)
            ->assertSee('Frequently asked questions')->assertDontSee('Need to know');
        $this->get('/studentLogin')->assertSee($manifest, false);                                      // layouts.auth
        $this->actingAs($this->user('provider', 'ched'))->get('/provider/dashboard')
            ->assertSee($manifest, false)->assertSee('data-pwa-install', false);                      // provider layout + side-foot
        $this->actingAs($this->user('admin'))->get('/admin')->assertSee($manifest, false);              // admin layout
    }
}
