<?php

declare(strict_types=1);

/*
 * This file is part of the robertsaupe/phpbat package.
 *
 * (c) Robert Saupe <mail@robertsaupe.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace robertsaupe\phpbat\Console;

use robertsaupe\phpbat\Console\Application;
use robertsaupe\phpbat\Console\Logger;
use robertsaupe\phpbat\Configuration\Entry\Mail;
use robertsaupe\SystemInfo\OS;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * @internal
 */
final class Mailer {

    public function __construct(
        protected Application $application,
        protected bool $isMailSendEnabled,
        protected Mail $mailConfig,
        protected Logger $logger
    ) {
        $phpmailer = new PHPMailer(true);
        try {
            $phpmailer->isSMTP();

            $phpmailer->SMTPDebug = SMTP::DEBUG_LOWLEVEL;
            $phpmailer->Debugoutput = function($str, $level) {
                switch ($level) {
                    case SMTP::DEBUG_LOWLEVEL:
                        $this->logger->debug('PHPMAILER->LOWLEVEL' . ': ' . trim($str));
                        break;

                    case SMTP::DEBUG_CONNECTION:
                        $this->logger->veryverbose('PHPMAILER->CONNECTION' . ': ' . trim($str));
                        break;

                    case SMTP::DEBUG_SERVER:
                        $this->logger->veryverbose('PHPMAILER->SERVER' . ': ' . trim($str));
                        break;

                    case SMTP::DEBUG_CLIENT:
                        $this->logger->verbose('PHPMAILER->CLIENT' . ': ' . trim($str));
                        break;
                    
                    default:
                        $this->logger->verbose('PHPMAILER->UNKNOWN' . ': ' . trim($str));
                        break;
                }
            };

            if ($this->mailConfig->getSSL() === true) {
                $phpmailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $phpmailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }

            $phpmailer->Port = $this->mailConfig->getPort();
            $phpmailer->Host = $this->mailConfig->getHost();

            $phpmailer->SMTPAuth = true;
            $phpmailer->Username = $this->mailConfig->getUser();
            $phpmailer->Password = $this->mailConfig->getPassword();

            $phpmailer->setFrom($this->mailConfig->getFrom(), $this->application->getName());
            $phpmailer->addAddress($this->mailConfig->getTo());

            $phpmailer->isHTML(true);
            $phpmailer->Subject = $this->application->getName() . ' ' . OS::getHostName();
            $phpmailer->Body    = $this->logger->getFormattedMessagesByVerbosity(true);
            $phpmailer->AltBody = $this->logger->getFormattedMessagesByVerbosity(false);

            $phpmailer->send();
            $this->logger->info('Mail has been sent');
        } catch(Exception $e) {
            $this->logger->error('Mail couldn\'t sent: ' . $phpmailer->ErrorInfo);
        }
    }

}

?>