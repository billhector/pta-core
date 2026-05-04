<?php
namespace Pta\Core\Mail;

defined('ABSPATH') || exit;

final class Send
{
    public function __construct(private Client $client) {}

    public function send_html(
        string $to_email,
        string $to_name,
        string $subject,
        string $html_body,
        string $text_body,
        array $custom_id_meta = []
    ): array {
        $message = [
            'From' => [
                'Email' => PTA_MAILJET_FROM_EMAIL,
                'Name'  => PTA_MAILJET_FROM_NAME,
            ],
            'To' => [
                ['Email' => $to_email, 'Name' => $to_name],
            ],
            'Subject'  => $subject,
            'TextPart' => $text_body,
            'HTMLPart' => $html_body,
        ];
        if (!empty($custom_id_meta['custom_id'])) {
            $message['CustomID'] = (string) $custom_id_meta['custom_id'];
        }
        if (!empty($custom_id_meta['event_payload'])) {
            $message['EventPayload'] = (string) $custom_id_meta['event_payload'];
        }

        return $this->client->post('/send', ['Messages' => [$message]], '3.1');
    }
}
