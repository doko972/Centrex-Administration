<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class VerifyProxyOriginTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'https://centrex.test']);

        Route::any('/_test/proxy', fn () => 'ok')->middleware('proxy.origin');
    }

    public function test_same_origin_requests_are_allowed(): void
    {
        $this->post('/_test/proxy', [], ['Sec-Fetch-Site' => 'same-origin'])->assertOk();
        $this->get('/_test/proxy', ['Sec-Fetch-Site' => 'same-origin'])->assertOk();
    }

    public function test_direct_navigation_is_allowed(): void
    {
        $this->get('/_test/proxy', ['Sec-Fetch-Site' => 'none'])->assertOk();
    }

    public function test_cross_site_requests_are_blocked(): void
    {
        $this->get('/_test/proxy', ['Sec-Fetch-Site' => 'cross-site'])->assertForbidden();
        $this->post('/_test/proxy', [], ['Sec-Fetch-Site' => 'cross-site'])->assertForbidden();
        $this->get('/_test/proxy', ['Sec-Fetch-Site' => 'same-site'])->assertForbidden();
    }

    public function test_legacy_browser_fallback_on_origin_and_referer(): void
    {
        $this->post('/_test/proxy', [], ['Origin' => 'https://centrex.test'])->assertOk();
        $this->post('/_test/proxy', [], ['Referer' => 'https://centrex.test/admin/centrex/1/view'])->assertOk();

        $this->post('/_test/proxy', [], ['Origin' => 'https://evil.example'])->assertForbidden();
        $this->post('/_test/proxy', [], ['Origin' => 'https://centrex.test.evil.example'])->assertForbidden();
        $this->get('/_test/proxy', ['Referer' => 'https://evil.example/page'])->assertForbidden();
    }

    public function test_missing_provenance_allows_reads_only(): void
    {
        $this->get('/_test/proxy')->assertOk();
        $this->post('/_test/proxy')->assertForbidden();
    }
}
