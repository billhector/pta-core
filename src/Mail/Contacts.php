<?php
namespace Pta\Core\Mail;

defined('ABSPATH') || exit;

final class Contacts
{
    public function __construct(private Client $client) {}

    public function add_to_list(string $email, ?int $list_id = null, array $properties = []): void
    {
        $list_id = $list_id ?? (int) PTA_MAILJET_DEFAULT_LIST_ID;
        if ($list_id <= 0) {
            return;
        }

        $body = [
            'Email'  => strtolower($email),
            'Action' => 'addnoforce',
        ];
        if ($properties) {
            $body['Properties'] = $properties;
        }
        $this->client->post('/REST/contactslist/' . $list_id . '/managecontact', $body);
    }
}
