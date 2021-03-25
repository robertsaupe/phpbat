<?php
/**
 * phpBAT
 * 
 * Please report bugs on https://github.com/robertsaupe/phpbat/issues
 *
 * @author Robert Saupe <mail@robertsaupe.de>
 * @copyright Copyright (c) 2018, Robert Saupe. All rights reserved
 * @link https://github.com/robertsaupe/phpbat
 * @license MIT License
 */

namespace RobertSaupe\phpBAT\Helper;

use PHPMailer\PHPMailer\{PHPMailer, Exception, SMTP};

class Mail {

    public function __construct(
        private array $mail,
        private bool $debug = false
    ) {
        if (!isset($this->mail) || !is_array($this->mail)) {
            Logging::Logger()->Error('Mail: ' . 'Job wrong defined (array needed)');
            return;
        } else if (!isset($this->mail['Enabled']) || !is_bool($this->mail['Enabled']) || $this->mail['Enabled'] != true) {
            Logging::Logger()->Debug('Mail: ' . 'skipped (disabled)');
            return;
        } else if (!isset($this->mail['Host']) || !is_string($this->mail['Host'])) {
            Logging::Logger()->Error('Mail: ' . 'Host not or wrong defined');
            return;
        } else if (!isset($this->mail['User']) || !is_string($this->mail['User'])) {
            Logging::Logger()->Error('Mail: ' . 'User not or wrong defined');
            return;
        } else if (!isset($this->mail['Password']) || !is_string($this->mail['Password'])) {
            Logging::Logger()->Error('Mail: ' . 'Password not or wrong defined');
            return;
        } else if (!isset($this->mail['From']) || !is_string($this->mail['From'])) {
            Logging::Logger()->Error('Mail: ' . 'From not or wrong defined');
            return;
        } else if (!isset($this->mail['To']) || !is_string($this->mail['To'])) {
            Logging::Logger()->Error('Mail: ' . 'To not or wrong defined');
            return;
        }
        if (!isset($this->mail['SSL']) || !is_bool($this->mail['SSL'])) $this->mail['SSL'] = true;
        if (!isset($this->mail['Port']) || !is_int($this->mail['Port'])) $this->mail['Port'] = 465;
        $phpmailer = new PHPMailer(true);
        try {
            if (isset($this->debug) && is_bool($this->debug) && $this->debug == true) $phpmailer->SMTPDebug = SMTP::DEBUG_SERVER;
            $phpmailer->isSMTP();
            $phpmailer->Host                                                = $this->mail['Host'];
            $phpmailer->SMTPAuth                                            = true;
            $phpmailer->Username                                            = $this->mail['User'];
            $phpmailer->Password                                            = $this->mail['Password'];
            if ($this->mail['SSL'] == true) $phpmailer->SMTPSecure          = PHPMailer::ENCRYPTION_SMTPS;
            else $phpmailer->SMTPSecure                                     = PHPMailer::ENCRYPTION_STARTTLS;
            $phpmailer->Port                                                = $this->mail['Port'];

            $phpmailer->setFrom($this->mail['From'], 'phpBAT');
            $phpmailer->addAddress($this->mail['To']);

            $phpmailer->isHTML(true);
            $phpmailer->Subject = 'phpBAT ' . php_uname('n');
            $phpmailer->Body    = Logging::Logger()->Get(null, true);
            $phpmailer->AltBody = Logging::Logger()->Get();

            $phpmailer->send();

            Logging::Logger()->Info('Mail: ' . 'Message has been sent');
        
        } catch(Exception $e) {
            Logging::Logger()->Error('Mail: ' . "Message couldn\'t sent. {$phpmailer->ErrorInfo}");
        }
    }

}

?>