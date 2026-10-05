<?php

namespace Tests\Feature;

use App\Services\FreePbxSessionStore;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;
use Tests\TestCase;

class FreePbxSessionStoreTest extends TestCase
{
    private FreePbxSessionStore $store;

    protected function setUp(): void
    {
        parent::setUp();

        config(['cache.default' => 'array']);
        session()->setId(str_repeat('a', 40));

        $this->store = new FreePbxSessionStore();
    }

    public function test_cookies_survive_between_requests(): void
    {
        $jar = new CookieJar();
        $jar->setCookie(new SetCookie(['Name' => 'PHPSESSID', 'Value' => 'abc', 'Domain' => '10.0.0.1']));

        $this->store->saveCookieJar('centrex', 1, $jar);

        $this->assertSame('abc', $this->store->cookieJar('centrex', 1)->getCookieByName('PHPSESSID')?->getValue());
        $this->assertNull($this->store->cookieJar('admin_centrex', 1)->getCookieByName('PHPSESSID'));
    }

    public function test_login_runs_only_once_while_authenticated(): void
    {
        $calls = 0;
        $login = function () use (&$calls) {
            $calls++;
            $this->store->setAuthenticated('centrex', 1, true);

            return true;
        };

        $this->assertTrue($this->store->ensureLoggedIn('centrex', 1, $login));
        $this->assertTrue($this->store->ensureLoggedIn('centrex', 1, $login));
        $this->assertSame(1, $calls);
    }

    public function test_failed_login_is_reported(): void
    {
        $this->assertFalse($this->store->ensureLoggedIn('centrex', 1, fn () => false));
        $this->assertFalse($this->store->isAuthenticated('centrex', 1));
    }

    public function test_state_is_isolated_per_laravel_session(): void
    {
        $this->store->setAuthenticated('centrex', 1, true);

        session()->setId(str_repeat('b', 40));

        $this->assertFalse($this->store->isAuthenticated('centrex', 1));
    }
}
