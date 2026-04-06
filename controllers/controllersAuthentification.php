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
        header('Content-Type: application/json');
        if (controlPostForm()) {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            $user = $this->clientModel->authentification($email, $password);
            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['nom'];
                $_SESSION['admin'] = false;
                if (isset($_SESSION['user_id']) && $user) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Connexion reussie.',
                        'data' => [
                            'nom' => $user['nom'],
                            'prenom' => $user['prenom'],
                            'email' => $user['email'],
                        ]
                    ]);
                    return;
                }
            } else {
                $_SESSION['error'] = "Identifiant ou mot de passe incorrect.";
            }
        }
            echo json_encode(['success' => false, 'error' => $_SESSION['error'] ?? 'Erreur de connexion.']);
            unset($_SESSION['error']);
    }

    // deconnexion
    public function deconnexion(){
        if(controlPostForm()){
            $_SESSION = [];
            session_destroy();
            session_start();
            header('Content-Type: application/json');
            echo json_encode(['success' => true, "csrf_token" => generateCsrfToken()]);
        }
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
            }
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Si ce mail existe, un nouveau mot de passe a été envoyé.']);
    }

    // changement mdp
    public function changementMotDePasse(){
        header('Content-Type: application/json');
        if(controlPostForm()){
            if(isLoggedIn()){
                if(isset($_POST['ancienPassword'], $_POST['nouvellePassword'])){
                    $ancienPassword = $_POST['ancienPassword'];
                    $nouvellePassword = $_POST['nouvellePassword'];

                    // vérification ancien mdp
                    $id = $_SESSION['user_id'];
                    $user = $this->clientModel->findById($id);
                    if (!($user && password_verify($ancienPassword, $user['password']))){
                        $_SESSION['error'] = "Le mot de passe ne correspond pas";
                        // redirection error
                    }
                    // Secu nouveau mdp
                    // ancien et nouveau mdp diff
                    if(verifierMotDePasse($nouvellePassword) && !password_verify($nouvellePassword, $user['password'])){
                        // changement mdp
                        $this->clientModel->changementMotDePasse($id, $nouvellePassword);
                        echo json_encode(['success' => true, 'message' => 'Mot de passe modifié.']);
                        return;
                        // redirection réussi
                    } else{
                        if(empty($_SESSION['error'])) $_SESSION['error'] = "Il faut que le nouveau mot de passe soit différent de l'ancien";
                    }  
                } 
            } 
            
        }
        
        if (isset($_SESSION['error'])) {
            echo json_encode(['success' => false, 'error' => $_SESSION['error']]);
            unset($_SESSION['error']);
        }
    }

    // connexion admin
    public function inscriptionAdmin(){
       if(controlPostForm()){
            $prenom = $_POST['prenom'] ?? '';
            $nom = $_POST['nom'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            $idAdmin = $this->adminModel->create($nom, $prenom, $email, $password);
            if ($admin) {
                $_SESSION['admin_id'] = $idAdmin;
                $_SESSION['user_name'] = $nom;
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