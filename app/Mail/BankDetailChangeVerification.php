<?php

namespace App\Mail;

use App\Models\BankDetail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BankDetailChangeVerification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public BankDetail $bankDetail,
        public string $token,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirmação de Alteração de Dados Bancários — Demanda3D',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.bank-detail-change-verification',
            with: [
                'tenantName' => $this->bankDetail->tenant?->fantasy_name ?? 'Vendedor',
                'verificationUrl' => route('bank.verify', ['token' => $this->token]),
                'token' => $this->token,
            ],
        );
    }
}