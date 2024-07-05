<?php

namespace App\Notifications;

class Ntfy
{
    public static function send(
        string $title,
        string $actions,
        string $body,
        ?string $attachment = null,
        ?string $tags = null
    ): void {
        $auth = [];

        if (env("NTFY_USER") && env("NTFY_PASSWORD")) {
            $auth["Authorization"] = "Basic " . base64_encode(env("NTFY_USER") . ":" . env("NTFY_PASSWORD"));
        } elseif (env("NTFY_TOKEN")) {
            $auth["Authorization"] = "Bearer " . env("NTFY_TOKEN");
        }

        if (isset($auth["Authorization"]) === false) {
            return;
        }

        $details = [
            "Content-Type" => "text/markdown",
            'X-Markdown'   => "1",
            'Markdown'     => "1",
            'md'           => "1",
            "Cache: no",
            'Title'        => $title,
            "Actions"      => $actions,
        ];
        if ($tags) {
            $details["Tags"] = $tags;
        }

        if ($attachment) {
            $details["Attach"] = $attachment;
        }

        $result = \Http::withHeaders(array_merge($auth, $details))
            ->withBody($body)
            ->post(env("NTFY_LINK"));

        if ($result->failed()) {
            \Log::error("Ntfy failed to send notification. Reason: " . $result->body());
        }
    }

    public static function error(string $title, string $body): void
    {
        if (empty(env("NTFY_ERROR_LINK")) === true) {
            return;
        }

        self::send($title, "", $body, null, "rotating_light");
    }
}
