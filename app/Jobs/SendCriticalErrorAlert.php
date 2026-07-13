<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendCriticalErrorAlert implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $errorMessage,
        public string $file,
        public int $line,
        public string $environment,
        public string $timestamp,
        public array $context = [],
    ) {}

    /**
     * Execute the job.
     *
     * Em ambiente de desenvolvimento, dispara um alerta de e-mail
     * (via Mail::raw) e registra o incidente no log crítico.
     *
     * Em produção, apenas loga — o alerta de e-mail real deve vir
     * do Grafana Alerting ou Sentry, não desta fila.
     */
    public function handle(): void
    {
        $payload = [
            'alert'       => 'critical_error',
            'message'     => $this->errorMessage,
            'file'        => $this->file,
            'line'        => $this->line,
            'environment' => $this->environment,
            'timestamp'   => $this->timestamp,
            'context'     => $this->context,
        ];

        Log::channel('single')->critical('Critical Error Alert', $payload);

        // Em dev local, dispara e-mail de alerta via fila Redis
        if (app()->environment('local') && config('mail.mailer') !== 'log') {
            \Illuminate\Support\Facades\Mail::raw(
                view('emails.critical-error-alert', $payload)->render(),
                function ($message) {
                    $message
                        ->to(config('mail.from.address'))
                        ->subject('[CRÍTICO] Demanda3D — Erro detectado em ambiente de desenvolvimento');
                }
            );
        }
    }

    /**
     * Determine the number of seconds before the job should be retried.
     */
    public function backoff(): array
    {
        return [10, 30, 60];
    }

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;
}