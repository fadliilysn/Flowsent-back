<?php

namespace App\Services;

use Webklex\IMAP\Facades\Client;

class EmailService
{
    public function getInbox()
    {
        $client = Client::account('default');
        $client->connect();

        $folder = $client->getFolder('INBOX');
        $messages = $folder->messages()->all()->limit(20)->get();

        $emails = [];
        foreach ($messages as $message) {
            $emails[] = [
                'uid'     => $message->getUid(),
                'subject' => $message->getSubject(),
                'from'    => $message->getFrom()[0]->mail ?? null,
                'date' => $message->getDate()->get()->format('Y-m-d H:i:s'),
                'body'    => $message->getTextBody(),
            ];
        }

        return $emails;
    }

    public function getSent()
    {
        $client = Client::account('default');
        $client->connect();

        $folder = $client->getFolder('Sent Items');
        $messages = $folder->messages()->all()->limit(20)->get();

        $emails = [];
        foreach ($messages as $message) {
            $emails[] = [
                'uid'     => $message->getUid(),
                'subject' => $message->getSubject(),
                'to'      => $message->getTo()[0]->mail ?? null,
                'date' => $message->getDate()->get()->format('Y-m-d H:i:s'),
                'body'    => $message->getTextBody(),
            ];
        }

        return $emails;
    }
}
