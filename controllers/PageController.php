<?php


namespace Controllers;


use Models\Comment;
use Models\Post;

class PageController extends Controller // Hérite de la class Controller et CheckFormValuesController
{
    // Affiche la page d'Accueil
    public function index()
    {
        // Récupère les 3 derniers postes
        $threeLastPosts = Post::getThreeLastPosts();

        // Redirection par défaut
        if (empty($_GET['message'])) {
            // Affiche la page d'accueil
            $this->render('index.html.twig', array('threeLastPosts' => $threeLastPosts));
        }
        // Redirection après envoie d'un mail
        else {
            // Affiche la page d'accueil un message
            $this->render('index.html.twig', array(
                'threeLastPosts' => $threeLastPosts,
                'message' => $_GET['message']
            ));
        }
    }

    // Envoie le mail du formulaire de contact
    public function sendMail()
    {
        // Si présence des variables
        if (isset($_POST['lastName']) && isset($_POST['firstName']) && isset($_POST['email']) &&
            isset($_POST['subject']) && isset($_POST['message'])) {

            $lastName = $_POST['lastName'];
            $firstName = $_POST['firstName'];
            $email = $_POST['email'];
            $subject = $_POST['subject'];
            $message = $_POST['message'];

            // Si aucune des variables n'est vide
            if (!empty($lastName) && !empty($firstName) && !empty($email) && !empty($subject) && !empty($message)) {

                // Vérifie la valeur des variables
                $verifiedName = $this->checkName($lastName, $firstName);
                $verifiedEmail = $this->checkEmail($email);
                // Si les valeurs des variables sont bonnes
                if (($verifiedName == 1) && ($verifiedEmail == 1)) {

                    // Destinataire
                    $to = "";

                    // Spécifie la longueur des lignes avant un saut de ligne
                    $message = wordwrap($message, 70, "\r\n");

                    // Pour envoyer un mail HTML, l'en-tête Content-type doit être défini
                    $headers  = "MIME-Version: 1.0" . "\r\n";
                    $headers .= "Content-Type: text/html; charset=utf-8" . "\r\n";

                    // En-têtes additionnels
                    $headers .= "From: " . $lastName. " <" . $email . ">" . "\r\n";
                    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

                    var_dump(mail($to, $subject, $message, $headers));
                    // Envoi du mail
                    mail($to, $subject, $message, $headers);

                    // Message
                    $messageSent = "Votre message à bien été envoyé";

                    // Récupère l'url du site
                    $httpOrigin = $_SERVER['HTTP_ORIGIN'];

                    // Redirection vers la page d'accueil - En évitant l'affichage de plusieurs "message"
                    header("Location: " . $httpOrigin . "?message=" . $messageSent . "#anchor");

                    // Empêche l'exécution du reste du script
                    die();
                } else {
                    // Affiche la page d'accueil avec le message d'erreur
                    $this->render('index.html.twig', array(
                        "messageLastName" => $verifiedName[0],
                        "messageFirstName" => $verifiedName[1],
                        "messageEmail" => $verifiedEmail,
                        "anchor" => "anchor" // Permet de diriger l'utilisateur directement sur le formulaire
                    ));
                }
            }
            // Si une des variables est vide
            else {
                // Message d'erreur
                $messageEmptyVariable = "Erreur: Un champ n'a pas été renseigné";

                // Récupère l'url du site
                $httpOrigin = $_SERVER['HTTP_ORIGIN'];

                // Redirection vers la page d'accueil - En évitant l'affichage de plusieurs "message"
                header("Location: " . $httpOrigin . "?message=" . $messageEmptyVariable . "#anchor");

                // Empêche l'exécution du reste du script
                die();
            }
        }
        // Si une des variables est manquante
        else {
            // Message d'erreur
            $messageIssetVariable = "Erreur: Manque une variable pour pouvoir envoyer le mail";

            // Récupère l'url du site
            $httpOrigin = $_SERVER['HTTP_ORIGIN'];

            // Redirection vers la page d'accueil - En évitant l'affichage de plusieurs "message"
            header("Location: " . $httpOrigin . "?message=" . $messageIssetVariable . "#anchor");

            // Empêche l'exécution du reste du script
            die();
        }
    }

    // Affiche la page des articles avec une pagination
    public function posts($page)
    {
        // Si la page éxiste
        if ($page >= 1) {
            // Définit le nombres d'articles par page
            $nbPerPage = 3;

            // Récupère tous les postes
            $posts = Post::getAllPostsWithPaging($page, $nbPerPage);

            // Calcule le nombre total de pages
            $nbPages = ceil(count($posts)/$nbPerPage);

            // Si la page éxiste
            if ($page <= $nbPages) {
                // Affiche la page des articles
                $this->render('posts.html.twig', array(
                    "posts" => $posts,
                    "nbPages" => $nbPages,
                    "page" => $page
                ));
            }
            // Si il y a aucun article
            else {
                $this->render('posts.html.twig', array(
                    "posts" => $posts,
                ));
            }
        }
        // Si la page n'éxiste pas
        else {
            // Redirection vers la 404
            header("Location: /error404");
            // Empêche l'exécution du reste du script
            die();
        }
    }

    // Affiche un post avec ses commentaires
    public function post($path)
    {
        // Récupère l'article
        $post = Post::getPostByPath($path);
        // Récupère les commentaires associés
        $comments = Comment::getValidateComment($post->getId());

        // Si la route correspond à un article
        if ($post != null) {
            // Redirection par défaut
            if (empty($_GET['message'])) {
                // Affiche la page de l'article
                $this->render('post.html.twig', array(
                    "post" => $post,
                    "comments" => $comments
                ));
            }
            // Redirection après ajout d'un commentaire
            else {
                // Affiche la page d'administration avec les posts et le message
                $this->render('post.html.twig', array(
                    "post" => $post,
                    "comments" => $comments,
                    "message" => $_GET['message']
                ));
            }
        }
        // Si la route ne correspond à aucun article
        else {
            // Redirection vers la 404
            header("Location: /error404");
            // Empêche l'exécution du reste du script
            die();
        }
    }
}