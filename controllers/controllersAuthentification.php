<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Client.php';
require_once __DIR__ . '/../models/Admin.php';
require_once __DIR__ . '/../services/MailService.php';

class controllersAuthentification{
    private $clientModel;
    private $adminModel;

    public function __construct()
    {
        $this->clientModel = new Client();
        $this->adminModel = new Admin();
    }

    // inscrption
    // connexion
    public function connexion(){
        error_log("conn");
        if (controlPostForm()) {
                    error_log("conn1");

            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            $user = $this->clientModel->authentification($email, $password);
            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['nom'];
                $_SESSION['admin'] = false;
                $response = [
                    'status' => 'success',
                    'nom' => $user['nom'],
                ];
            } else {
                $_SESSION['error'] = "Identifiant ou mot de passe incorrect.";
                $response = [
                    'status' => 'error',
                    'msg' => 'Identifiant ou mot de passe incorrect.',
                ];
            }
        } else {
            $response = [
                'status' => 'error',
                'msg' => '',
            ];
        }
        error_log($_SESSION['error']);
        echo json_encode($response);
    }

    // deconnexion
    public function deconnexion(){
        if(controlPostForm()){
            session_destroy();
        }
        header("Location: index.php");
    }

    // mdp oublié
    public function motDePasseOublie(){
        if(controlPostForm()){
                // verifie email existe
            $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
            error_log($email);
            if($email){
                $user = $this->clientModel->findByEmail($email);
                if($user){
                    error_log($user["id"]);
                    // envoie code provisoire email
                    $newPassword = $this->clientModel->definiMotDePasseClient($user["id"]);
                    $mailservice = new MailService();
                    $mailservice->envoiePassword($email, $newPassword);
                }

                $response = [
                    'status' => 'success',
                    'msg' => "",
                ];
            }else {
                $response = [
                    'status' => 'error',
                    'msg' => 'Email invalide',
                ];
            }
        }
        echo json_encode($response);
    }

    // changement mdp
    public function changementMotDePasse(){
        if(controlPostForm()){
            if(isLoggedIn){
                if(isset($_POST['ancienPassword'], $_POST['nouvellePassword'])){
                    $ancienPassword = $_POST['ancienPassword'];
                    $nouvellePassword = $_POST['nouvellePassword'];

                    // vérification ancien mdp
                    $id = $_SESSION['user_id'];
                    $user = $this->clientModel.findById($id);
                    if (!($user && password_verify($ancienPassword, $user['password']))){
                        $_SESSION['error'] = "Le mot de passe ne correspond pas";
                        // redirection error
                    }
                    // Secu nouveau mdp
                    // ancien et nouveau mdp diff
                    if(verifierMotDePasse($nouvellePassword) && password_verify($nouvellePassword, $user['password'])){
                        // changement mdp
                        $this->clientModel.changementMotDePasse($id, $nouvellePassword);
                        // redirection réussi
                    } else{
                        if(empty($_SESSION['error'] = "Il faut que le nouveau mot de passe soit différent de l'ancien"));
                    }  
                } 
            } // redirection auto
            
        }
    }

    // connexion admin
    public function inscriptionAdmin(){
       if(controlPostForm()){
            $prenom = $_POST['prenom'] ?? '';
            $nom = $_POST['nom'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            $admin = $this->adminModel->create($prenom, $nom, $email, $password);
            if ($admin) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['user_name'] = $admin['nom'];
                $_SESSION['admin'] = true;
                //redirection auto
            } else {
                $_SESSION['error'] = "Identifiant ou mot de passe incorrect.";
                // redirection error
            }
        } else {
            // redirection auto
        }

    }

    // connexion admin
    public function connexionAdmin(){
       if(controlPostForm()){
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            $admin = $this->adminModel->authentification($email, $password);
            if ($admin) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['user_name'] = $admin['nom'];
                $_SESSION['admin'] = true;
                //redirection auto
            } else {
                $_SESSION['error'] = "Identifiant ou mot de passe incorrect.";
                // redirection error
            }
        } else {
            // redirection auto
        }

       }
}



?>