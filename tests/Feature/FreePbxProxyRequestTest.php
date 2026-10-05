<?php

namespace Tests\Feature;

use App\Services\FreePbxProxyRequest;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class FreePbxProxyRequestTest extends TestCase
{
    public function test_form_body_is_forwarded_untouched(): void
    {
        // Champ vide, espaces, champ répété : tout doit arriver tel quel à FreePBX
        $body = 'extension=101&outboundcid=&name=+Accueil+&dict[]=a&dict[]=b';
        $request = Request::create('/admin/centrex/1/proxy/admin/config.php?display=extensions', 'POST', [], [], [], [
            'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            'HTTP_ACCEPT' => 'application/json',
        ], $body);

        $options = FreePbxProxyRequest::options($request);

        $this->assertSame($body, $options['body']);
        $this->assertSame('application/x-www-form-urlencoded', $options['headers']['Content-Type']);
        $this->assertSame('XMLHttpRequest', $options['headers']['X-Requested-With']);
        $this->assertSame('application/json', $options['headers']['Accept']);
        $this->assertArrayNotHasKey('form_params', $options);
    }

    public function test_large_forms_are_not_truncated(): void
    {
        $body = http_build_query(array_fill_keys(array_map(fn ($i) => "field{$i}", range(1, 3000)), 'x'));
        $request = Request::create('/x', 'POST', [], [], [], ['CONTENT_TYPE' => 'application/x-www-form-urlencoded'], $body);

        $this->assertSame($body, FreePbxProxyRequest::options($request)['body']);
    }

    public function test_get_requests_have_no_body(): void
    {
        $options = FreePbxProxyRequest::options(Request::create('/x?display=ivr', 'GET'));

        $this->assertArrayNotHasKey('body', $options);
        $this->assertArrayNotHasKey('multipart', $options);
    }

    public function test_multipart_keeps_empty_fields_and_files(): void
    {
        $file = UploadedFile::fake()->create('musique.wav', 10);
        $request = Request::create('/x', 'POST', ['name' => 'Attente', 'description' => '', 'opts' => ['a' => '1']], [], ['files' => [$file]], [
            'CONTENT_TYPE' => 'multipart/form-data; boundary=xyz',
        ]);

        $parts = collect(FreePbxProxyRequest::options($request)['multipart'])->keyBy('name');

        $this->assertSame('Attente', $parts['name']['contents']);
        $this->assertSame('', $parts['description']['contents']);
        $this->assertSame('1', $parts['opts[a]']['contents']);
        $this->assertSame('musique.wav', $parts['files[0]']['filename']);
    }
}
