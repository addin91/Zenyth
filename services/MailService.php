<?php 

class MailService{
    private $mail;

    public function __construct(){
        $this->pdo = new PHPMailer(true);
        initialisation();
    }

    private function initialisation(){
       try {
            // Configuration SMTP
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'tonemail@gmail.com';
            $mail->Password = 'mot_de_passe_app'; // pas ton vrai mdp !
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

        } catch (Exception $e) {
            echo "Erreur : {$mail->ErrorInfo}";
        }
    }

    public function envoiePassword($destinataire, $password){
        try{
            // Expéditeur et destinataire
            $mail->setFrom('tonemail@gmail.com', 'Mon App');
            $mail->addAddress($destinataire);

            // Contenu
            $mail->isHTML(true);
            $mail->Subject = 'Votre mot de passe';
            $mail->Body = 'Voici votre mot de passe : <b>'. $password .'</b>';

            $mail->send();
        } catch (Exception $e) {
            echo "Erreur : {$mail->ErrorInfo}";
        }
    }
}

?>