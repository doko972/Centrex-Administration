<?php

namespace App\Services;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Options Guzzle pour relayer une requête du navigateur vers FreePBX sans la dénaturer.
 *
 * Le corps est transmis brut (php://input) plutôt que reconstruit depuis $request->all(), qui :
 *  - supprimait les champs vides (ConvertEmptyStringsToNull → null → champ omis par Guzzle),
 *  - tronquait les gros formulaires au-delà de max_input_vars (1000 par défaut),
 *  - mélangeait les paramètres de l'URL dans le corps et transformait le JSON en formulaire.
 * Résultat : certaines pages FreePBX s'enregistraient mal ou pas du tout via le proxy.
 */
class FreePbxProxyRequest
{
    /** En-têtes du navigateur utiles à FreePBX (détection des appels AJAX, format de réponse) */
    private const FORWARDED_HEADERS = ['X-Requested-With', 'Accept'];

    public static function options(Request $request): array
    {
        $options = ['headers' => []];

        foreach (self::FORWARDED_HEADERS as $header) {
            if ($request->headers->has($header)) {
                $options['headers'][$header] = $request->header($header);
            }
        }

        if ($request->isMethodSafe()) {
            return $options;
        }

        $contentType = (string) $request->header('Content-Type', '');

        // multipart : PHP ne fournit pas le corps brut, on le reconstruit (champs non nettoyés, voir bootstrap/app.php)
        if (str_contains(strtolower($contentType), 'multipart/form-data')) {
            $options['multipart'] = array_merge(
                self::fields($request->request->all()),
                self::files($request->files->all())
            );

            return $options;
        }

        $options['body'] = $request->getContent();

        if ($contentType !== '') {
            $options['headers']['Content-Type'] = $contentType;
        }

        return $options;
    }

    private static function fields(array $data, string $prefix = ''): array
    {
        $parts = [];

        foreach ($data as $key => $value) {
            $name = $prefix === '' ? (string) $key : "{$prefix}[{$key}]";

            if (is_array($value)) {
                $parts = array_merge($parts, self::fields($value, $name));
            } else {
                $parts[] = ['name' => $name, 'contents' => (string) $value];
            }
        }

        return $parts;
    }

    private static function files(array $files, string $prefix = ''): array
    {
        $parts = [];

        foreach ($files as $key => $file) {
            $name = $prefix === '' ? (string) $key : "{$prefix}[{$key}]";

            if (is_array($file)) {
                $parts = array_merge($parts, self::files($file, $name));
            } elseif ($file instanceof UploadedFile && $file->isValid()) {
                $parts[] = [
                    'name' => $name,
                    'contents' => fopen($file->getRealPath(), 'r'),
                    'filename' => $file->getClientOriginalName(),
                ];
            }
        }

        return $parts;
    }
}
