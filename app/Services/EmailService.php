<?php

namespace App\Services;

use Webklex\IMAP\Facades\Client;

class EmailService
{
    protected $client;

    public function __construct()
    {
        $this->client = Client::account('default');
        $this->client->connect();
    }

    public function getInbox()
    {
        $folder = $this->client->getFolder('INBOX');
        $messages = $folder->messages()->all()->limit(20)->get();
        return $messages->map(fn($msg) => $this->parseEmail($msg));
    }

    public function getEmailByUid($folderName, $uid)
    {
        try {
        $folder = $this->client->getFolder($folderName);

        // Pastikan UID jadi integer
        $uid = (int) $uid;

        $message = $folder->messages()->getMessage($uid);

        if (!$message) {
            return null; // tidak ketemu
        }

        return $this->parseEmail($message);
        } catch (\Exception $e) {
            // kalau ada error dari library, misalnya UID tidak valid
            return null;
        }
    }


    public function getSent()
    {
        $folder = $this->client->getFolder('Sent Items');
        $messages = $folder->messages()->all()->limit(20)->get();
        return $messages->map(fn($msg) => $this->parseEmail($msg));
    }

    public function getDrafts()
    {
        $folder = $this->client->getFolder('Drafts');
        $messages = $folder->messages()->all()->limit(20)->get();
        return $messages->map(fn($msg) => $this->parseEmail($msg));
    }

    public function getDeleteItem()
    {
        $folder = $this->client->getFolder('Deleted Items');
        $messages = $folder->messages()->all()->limit(20)->get();
        return $messages->map(fn($msg) => $this->parseEmail($msg));
    }

    public function getJunk()
    {
        $folder = $this->client->getFolder('Junk Mail');
        $messages = $folder->messages()->all()->limit(20)->get();
        return $messages->map(fn($msg) => $this->parseEmail($msg));
    }   

    /**
     * 🔑 Parsing email dengan decode header
     */
    protected function parseEmail($msg)
    {
        // Subject decode
        $subject = $this->decodeHeader($msg->getSubject() ?? '');

        // Ambil body text (plain text lebih diprioritaskan)
        $body = $msg->getTextBody();

        // Kalau kosong coba ambil HTML body
        if (empty($body)) {
            $body = $msg->getHTMLBody();
        }

        // Kalau masih kosong, cek MIME parts
        if (empty($body)) {
            foreach ($msg->getBodies() as $part) {
                if ($part->type == 'text' && in_array(strtolower($part->subtype), ['plain', 'html'])) {
                    $body = $part->content;
                    break;
                }
            }
        }

        // 🔑 Bersihkan body:
        if (!empty($body)) {
            // Hapus header MIME kalau masih ikut kebawa
            $body = preg_replace('/^(.*?\r\n\r\n)/s', '', $body);

            // Kalau masih HTML → jadikan teks biasa
            $body = strip_tags($body);

            // Normalisasi whitespace
            $body = trim(preg_replace('/\s+/', ' ', $body));
        }

        return [
            "uid"     => $msg->getUid(),
            "subject" => $subject,
            "from"    => $this->decodeHeader(optional($msg->getFrom())->first()?->mail ?? ''),
            "to"      => $this->decodeHeader(optional($msg->getTo())->first()?->mail ?? ''),
            "date"    => optional($msg->getDate())->get()->format('Y-m-d H:i:s'),
            "body"    => $body ?? '',
            "body_html" => $msg->getHTMLBody() ?? '',
        ];
    }

    /**
     * 📨 Decode subject & header agar tidak kosong {}
     */
    private function decodeHeader($header)
    {
        if (empty($header)) {
            return '';
        }

        $decoded = imap_mime_header_decode($header);
        $result = '';

        foreach ($decoded as $part) {
            $result .= $part->text;
        }

        return trim($result);
    }

    private $folderMap = [
    'inbox'   => 'INBOX',
    'sent'    => 'Sent Items',
    'draft'   => 'Drafts',
    'deleted' => 'Deleted Items',
    'junk'    => 'Junk Mail',
    ];

    public function resolveFolder($key) {
        return $this->folderMap[strtolower($key)] ?? 'INBOX';
    }
}
