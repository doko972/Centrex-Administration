<?php

namespace App\Exceptions;

use Exception;

class OvhApiException extends Exception
{
    /**
     * Corps de la réponse OVH (décodé) quand l'erreur vient de l'API.
     */
    public ?array $payload = null;

    public static function notConfigured(): self
    {
        return new self(
            "Les identifiants de l'API OVH ne sont pas configurés (OVH_APPLICATION_KEY, "
            . "OVH_APPLICATION_SECRET, OVH_CONSUMER_KEY)."
        );
    }

    public static function fromResponse(string $method, string $path, int $status, ?array $payload): self
    {
        $message = $payload['message'] ?? 'Erreur inconnue';

        $exception = new self("OVH API {$method} {$path} a répondu {$status} : {$message}", $status);
        $exception->payload = $payload;

        return $exception;
    }
}
