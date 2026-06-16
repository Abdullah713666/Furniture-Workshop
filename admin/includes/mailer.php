<?php
/**
 * Resend Mailer â€” Antique Furniture Workshop
 *
 * Single-file Resend API wrapper. No Composer required.
 * Uses curl when available, falls back to file_get_contents stream context.
 *
 * Required env vars:
 *   RESEND_API_KEY    â€” your Resend API key (re_â€¦)
 *   MAIL_FROM         â€” sender address, e.g. hello@antiqueworkshop.com
 *   MAIL_FROM_NAME    â€” display name, e.g. "Antique Workshop"
 *
 * Set on Railway via: railway variables set RESEND_API_KEY=re_xxx MAIL_FROM=...
 */

if (!function_exists('afw_send_email')) {
    /**
     * Send a transactional email via Resend.
     *
     * @param string $to       Recipient email address
     * @param string $subject  Email subject
     * @param string $html     HTML body
     * @param string|null $text Optional plain-text body (auto-generated from $html if null)
     * @return array{ok:bool, status:int, body:string, error?:string}
     */
    function afw_send_email(string $to, string $subject, string $html, ?string $text = null): array {
        $apiKey = getenv('RESEND_API_KEY') ?: '';
        $from   = getenv('MAIL_FROM') ?: 'noreply@antiqueworkshop.local';
        $fromNm = getenv('MAIL_FROM_NAME') ?: 'Antique Workshop';

        if ($apiKey === '') {
            return [
                'ok'    => false,
                'status'=> 0,
                'body'  => '',
                'error' => 'RESEND_API_KEY not set in environment',
            ];
        }

        if ($text === null) {
            $text = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($html), ENT_QUOTES, 'UTF-8')));
        }

        $payload = json_encode([
            'from'    => $fromNm . ' <' . $from . '>',
            'to'      => [$to],
            'subject' => $subject,
            'html'    => $html,
            'text'    => $text,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        // Prefer curl when available
        if (function_exists('curl_init')) {
            $ch = curl_init('https://api.resend.com/emails');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $payload,
                CURLOPT_HTTPHEADER     => [
                    'Authorization: Bearer ' . $apiKey,
                    'Content-Type: application/json',
                ],
                CURLOPT_TIMEOUT        => 10,
            ]);
            $body = curl_exec($ch);
            $err  = curl_error($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($body === false) {
                return ['ok' => false, 'status' => 0, 'body' => '', 'error' => 'curl: ' . $err];
            }
            return ['ok' => $code >= 200 && $code < 300, 'status' => $code, 'body' => (string) $body];
        }

        // Fallback: file_get_contents with stream context
        $ctx = stream_context_create([
            'http' => [
                'method'        => 'POST',
                'header'        => "Authorization: Bearer {$apiKey}\r\nContent-Type: application/json\r\n",
                'content'       => $payload,
                'ignore_errors' => true,
                'timeout'       => 10,
            ]
        ]);
        $body = @file_get_contents('https://api.resend.com/emails', false, $ctx);
        $code = 0;
        if (isset($http_response_header[0]) && preg_match('#HTTP/\S+\s+(\d+)#', $http_response_header[0], $m)) {
            $code = (int) $m[1];
        }
        return ['ok' => $code >= 200 && $code < 300, 'status' => $code, 'body' => (string) $body];
    }
}
