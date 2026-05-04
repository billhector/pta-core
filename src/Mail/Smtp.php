<?php
namespace Pta\Core\Mail;

defined('ABSPATH') || exit;

final class Smtp
{
    public function configure(\PHPMailer\PHPMailer\PHPMailer $mailer): void
    {
        if (PTA_MAILJET_API_KEY === '' || PTA_MAILJET_SECRET_KEY === '') {
            return;
        }

        $mailer->isSMTP();
        $mailer->Host       = 'in-v3.mailjet.com';
        $mailer->Port       = 587;
        $mailer->SMTPAuth   = true;
        $mailer->Username   = PTA_MAILJET_API_KEY;
        $mailer->Password   = PTA_MAILJET_SECRET_KEY;
        $mailer->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mailer->Timeout    = 15;

        if (PTA_MAILJET_FROM_EMAIL !== '') {
            $mailer->setFrom(PTA_MAILJET_FROM_EMAIL, PTA_MAILJET_FROM_NAME);
        }
    }
}
