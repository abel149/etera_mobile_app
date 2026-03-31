<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SentEmail extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function proforma()
    {
        return $this->belongsTo(Proforma::class);
    }

    /**
     * Log a sent email record.
     */
    public static function log(string $type, string $toEmail, ?string $toName, ?int $userId, ?int $proformaId, ?string $subject, string $status = 'sent', ?string $errorMessage = null, ?string $body = null): self
    {
        return self::create([
            'type' => $type,
            'to_email' => $toEmail,
            'to_name' => $toName,
            'user_id' => $userId,
            'proforma_id' => $proformaId,
            'subject' => $subject,
            'status' => $status,
            'error_message' => $errorMessage,
            'body' => $body,
        ]);
    }
}

