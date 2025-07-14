<?php

namespace App\Core\Tenant\Services;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailerService {
    public function __construct(
        private MailerInterface $mailer
    ) { }
    public function sendConfirmationEmail(string $to): void {
        $email = (new Email())
            ->from('noreply@yourdomain.com')
            ->to($to)
            ->subject('Подтверждение регистрации')
            ->text('Пожалуйста, подтвердите ваш email')
            ->html('хуесосы');

        $this->mailer->send($email);
    }
}