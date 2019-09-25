<?php


namespace Controllers;

use Models\Post;
use Models\User;

class AdminController extends Controller // Hérite de la class Controller et CheckFormValuesController
{
    public function registration()
    {
        // Si l'utilisateur n'est pas connecté, il accède à la page
        if (empty($_SESSION['user'])) {
            // Si présence des variables
            if (isset($_POST['lastName']) && isset($_POST['firstName']) && isset($_POST['email'])
                && isset($_POST['username']) && isset($_POST['password'])) {
                $lastName = $_POST['lastName'];
                $firstName = $_POST['firstName'];
                $email = $_POST['email'];
                $username = $_POST['username'];
                $password = $_POST['password'];
                $confirmPassword = $_POST['confirm'];

                // Vérifie si $email et $username sont unique
                $verifiedSingleUsernameEmail = $this->checkSingleUsernameEmail($email, $username);
                // Vérifie la valeur des variables
                $verifiedName = $this->checkName($lastName, $firstName);
                $verifiedEmail = $this->checkEmail($email);
                $verifiedUsername = $this->checkUsername($username);
                $verifiedPassword = $this->checkPassword($password, $confirmPassword);

                if (($verifiedName == 1) &&
                    ($verifiedEmail == 1) &&
                    ($verifiedUsername == 1) &&
                    ($verifiedPassword == 1) &&
                    ($verifiedSingleUsernameEmail == null)) {

                    // Crée une instance de User
                    $user = new User;
                    // Appelle la fonction qui enregistre un utilisateur avec les paramètres du formulaire
                    $user->registerUserByForm($lastName, $firstName, $email, $username, $password);

                    // Appel la methode qui va définir les variables de session
                    $this->setSessionVariables($user);

                    // Si HTTP_REFERER est déclaré renvoie sur l'URL précédente
                    if (isset($_SESSION['previousUrl'])) {
                        header('Location: ' . $_SESSION['previousUrl']);
                        // Empêche l'exécution du reste du script
                        die();
                    }
                    // Si HTTP_REFERER n'est pas déclaré renvoie sur la page d'accueil
                    else  {
                        header('Location: ' . '/');
                        // Empêche l'exécution du reste du script
                        die();
                    }
                } else {
                    // Affiche le page d'inscription avec le message d'erreur
                    $this->render('registration.html.twig', array(
                        "messageLastName" => $verifiedName[0],
                        "messageFirstName" => $verifiedName[1],
                        "messageEmail" => $verifiedEmail,
                        "messageUsername" => $verifiedUsername,
                        "messagePassword" => $verifiedPassword,
                        "messageSingleEmail" => $verifiedSingleUsernameEmail[0],
                        "messageSingleUsername"  => $verifiedSingleUsernameEmail[1]
                    ));
                }
            }
            else {
                // Affiche la page d'inscription par défaut
                $this->render('registration.html.twig', array());
            }
        }
        // Si l'utilisateur est connecté
        else {
            // Redirection vers la page d'accueil
            header('Location: ' . '/');
            // Empêche l'exécution du reste du script
            die();
        }
    }

    public function login()
    {
        // Si l'utilisateur n'est pas connecté, il accède à la page d'identification
        if (empty($_SESSION['user'])) {
            // Si présence des variables 'username' et 'password'
            if (isset($_POST['username']) && isset($_POST['password'])) {
                $username = $_POST['username'];
                $password = $_POST['password'];

                // Appelle la fonction static getUserByLogin() avec les paramètres du formulaire
                $checkUser = User::getUserByLogin($username, $password);

                // Si l'utilisateur est identifié
                if ($checkUser[0] == true) {
                    // Appel la methode qui va définir les variables de session
                    $this->setSessionVariables($checkUser[1]);

                    // Si l'utilisateur est un administrateur
                    if ($checkUser[1]->getRole() == 1) {
                        //  Redirige vers la page d'administration
                        header('Location: ' . '/admin');
                        // Empêche l'exécution du reste du script
                        die();
                    }
                    // Si l'utilisateur n'est pas un administrateur
                    else {
                        // Si HTTP_REFERER est déclaré renvoie sur l'URL précédente
                        if (isset($_SESSION['previousUrl'])) {
                            header('Location: ' . $_SESSION['previousUrl']);
                            // Empêche l'exécution du reste du script
                            die();
                        }
                        // Si HTTP_REFERER n'est pas déclaré renvoie sur la page d'accueil
                        else  {
                            header('Location: ' . '/');
                            // Empêche l'exécution du reste du script
                            die();
                        }
                    }
                }
                // Si l'utilisateur ne c'est pas identifié correctement
                else {
                    // Message d'erreur
                    $message = "Logins incorrect, veuillez réessayer";
                    // Redirection vers la page d'identification
                    $this->render('login.html.twig', array("message" => $message));
                }
            }
            else {
                // Affiche le page d'identification par défaut
                $this->render('login.html.twig', array());
            }
        }
        // Si l'utilisateur est connecté
        else {
            // Redirection vers la page d'accueil
            header('Location: ' . '/');
            // Empêche l'exécution du reste du script
            die();
        }
    }

    // Affiche la page d'administration
    public function admin()
    {
        // Vérifie que l'utilisateur est connecté et que c'est un administrateur
        $this->redirectIfNotLoggedOrNotAdmin();

        // Récupère tous les posts de la bdd
        $listsPosts = Post::getAllPosts();

        // Redirection par défaut
        if ($_GET == null ) {
            // Affiche la page d'administration avec les posts
            $this->render('homeAdmin.html.twig', array("listPosts" => $listsPosts));
        }
        // Redirection après ajout, modification ou suppression d'un article
        else {
            // Affiche la page d'administration avec les posts et le message
            $this->render('homeAdmin.html.twig', array(
                "message" => $_GET['message'],
                "listPosts" => $listsPosts
            ));
        }
    }

    // Déconnecte l'utilisateur
    public function logout()
    {
        // Détruit les variables de la session
        session_unset();
        // Détruit la session
        session_destroy();
        // Redirection vers la page d'identification
        header('Location: ' . '/login');
        // Empêche l'exécution du reste du script
        die();
    }
}