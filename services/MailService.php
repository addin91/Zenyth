<?php 

require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService{
    private $mail;

    public function __construct(){
        $this->mail = new PHPMailer(true);
        $this->initialisation();
    }

    private function initialisation(){
       try {
            // Configuration SMTP
            $this->mail->isSMTP();
            $this->mail->Host = 'smtp.gmail.com';
            $this->mail->SMTPAuth = true;
            $this->mail->Username = 'zenyth.tourisme@gmail.com';
            $this->mail->Password = 'jkws qgzh rdpx jfqk';
            $this->mail->SMTPSecure = 'tls';
            $this->mail->Port = 587;

        } catch (Exception $e) {
            error_log("Erreur : {$this->mail->ErrorInfo}");
        }
    }

    public function envoiePassword($destinataire, $password){
        try{
            // Expéditeur et destinataire
            $this->mail->setFrom('zenyth.tourisme@gmail.com', 'Zenyth');
            $this->mail->addAddress($destinataire);

            // Contenu
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Votre mot de passe';
            $this->mail->Body = 'Voici votre mot de passe : <b>'. $password .'</b>';

            $this->mail->send();
            $this->mail->clearAddresses();
        } catch (Exception $e) {
            error_log("Erreur : {$this->mail->ErrorInfo}");
        }
    }

    public function envoieMail($destinataire, $objet, $message, $isHTML){
        try{    
            // Expéditeur et destinataire
            $this->mail->setFrom('zenyth.tourisme@gmail.com', 'Zenyth');
            $this->mail->addAddress($destinataire);

            // Contenu
            $this->mail->isHTML($isHTML);
            $this->mail->Subject = $objet;
            $this->mail->Body = $message;

            $this->mail->send();
            $this->mail->clearAddresses();
        } catch (Exception $e) {
            error_log("Erreur : {$this->mail->ErrorInfo}");
        }
    }
}

?>