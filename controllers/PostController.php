<?php


namespace Controllers;


use Models\Post;

class PostController extends Controller
{
    // Affiche le formulaire d'ajout OU le formulaire de modification d'un article
    public function post($idPost)
    {
        // Vérifie que l'utilisateur est connecté et que c'est un administrateur
        $this->redirectIfNotLoggedOrNotAdmin();

        // // Si id n'est pas vide = Modification d'un article
        if ($idPost != null) {

            // Crée une instance de Post
            $newPost = new Post;
            // Récupère le post
            $post = $newPost->getPost($idPost);

            // Si l'id correspond à un post
            if ($post != null) {
                // Récupère les données a afficher
                $id = $post->getId();
                $title = $post->getTitle();
                $author = $post->getAuthor();
                $summary = $post->getSummary();
                $content = $post->getContent();

                // Affiche la page d'ajout d'article avec ses infos à modifier
                $this->render('postForm.html.twig', array(
                    "id" => $id,
                    "title" => $title,
                    "author" => $author,
                    "summary" => $summary,
                    "content" => $content
                ));
            }
            // Si l'id n'a aucune correspondance
            else {
                // Message d'erreur
                $messageIdWithoutPost = "Erreur: Aucun article ne correspond à l'id";

                // Redirection vers la page d'administration
                header("Location: /admin?message=".$messageIdWithoutPost);
                // Empêche l'exécution du reste du script
                die();
            }
        }
        // Si id est vide = Ajout d'un article
        else {
            // Affiche la page d'ajout d'article
            $this->render('postForm.html.twig', array());
        }
    }

    // Ajoute un article dans la bdd
    public function addPost()
    {
        // Vérifie que l'utilisateur est connecté et que c'est un administrateur
        $this->redirectIfNotLoggedOrNotAdmin();

        // Si présence des variables
        if (isset($_POST['title-post']) && isset($_POST['summary']) && isset($_POST['content'])) {

            // Récupère les variables
            $title = $_POST['title-post'];
            $summary = $_POST['summary'];
            $content = $_POST['content'];
            // Par défaut, l'auteur est l'utilisateur connecté
            $author = $_SESSION['user']->getUsername();

            // Vérifie que les valeurs des variables ne soient pas vide
            $verifiedIfEmpty = $this->checkIfEmpty($title, $author, $summary, $content);

            // Si toutes les variables sont renseignées
            if ($verifiedIfEmpty == 1) {

                // Crée une instance de Post
                $post = new Post;
                // Appelle la méthode qui enregistre un post avec les paramètres du formulaire
                $post->addPostByForm($title, $author, $summary, $content);

                // Message de confirmation
                $messagePostAddConfirmed = "Article ajouté";

                // Redirection vers la page d'administration
                header("Location: /admin?message=".$messagePostAddConfirmed);
                // Empêche l'exécution du reste du script
                die();
            }
            // Si une des variables n'est pas renseignées
            else {
                // Affiche le formulaire d'ajout d'article avec le message d'erreur
                $this->render('postForm.html.twig', array("message" => $verifiedIfEmpty));
            }
        }
        // Si il manque une variable
        else {
            // Message d'erreur
            $messageIssetVariable = "Erreur: Manque une variable pour pouvoir ajouter l'article";

            // Affiche le formulaire d'ajout d'article avec le message d'erreur
            $this->render('postForm.html.twig', array("message" => $messageIssetVariable));
        }
    }

    // Modifier un article
    public function editPost($idPost)
    {
        // Vérifie que l'utilisateur est connecté et que c'est un administrateur
        $this->redirectIfNotLoggedOrNotAdmin();

        // Si présence des variables
        if (isset($_POST['title-post']) &&
            isset($_POST['author'])  &&
            isset($_POST['summary']) &&
            isset($_POST['content']))
        {

            // Récupère les variables
            $title = $_POST['title-post'];
            $author = $_POST['author'];
            $summary = $_POST['summary'];
            $content = $_POST['content'];
            $updateDate = new \DateTime();

            // Vérifie que les valeurs des variables ne soient pas vide
            $verifiedIfEmpty = $this->checkIfEmpty($title, $author, $summary, $content);

            // Si toutes les variables sont renseignées
            if ($verifiedIfEmpty == 1) {

                // Récupère le post
                $post = Post::getPost($idPost);
                // Appelle la méthode qui enregistre un post avec les paramètres du formulaire
                $post->editPostByForm($title, $author, $summary, $content, $updateDate);

                // Message de confirmation
                $messagePostEditConfirmed = "Article modifié";

                // Redirection vers la page d'administration
                header("Location: /admin?message=".$messagePostEditConfirmed);
                // Empêche l'exécution du reste du script
                die();
            }
            // Si il manque une variable
            else {
                // Redirection vers la page d'administration
                header("Location: /admin?message=".$verifiedIfEmpty);
                // Empêche l'exécution du reste du script
                die();
            }
        }
        // Si il manque une variable
        else {
            // Message d'erreur
            $messageIssetVariable = "Erreur: Manque une variable pour pouvoir ajouter l'article";

            // Affiche le formulaire d'ajout d'article avec le message d'erreur
            $this->render('postForm.html.twig', array("message" => $messageIssetVariable));
        }
    }

    // Supprimer un article
    public function deletePost($idPost)
    {
        // Vérifie que l'utilisateur est connecté et que c'est un administrateur
        $this->redirectIfNotLoggedOrNotAdmin();

        // Vérifie la présence de l'id
        if ($idPost != null) {

            // Crée une instance de Post
            $newPost = new Post;
            // Récupère le post
            $post = $newPost->getPost($idPost);

            // Si l'id correspond à un post
            if ($post != null) {
                // Appelle la méthode qui supprime un post
                $post->deletePostByHomeAdmin($post);

                // Message de confirmation
                $messagePostDeleteConfirmed = "Article supprimé";

                // Redirection vers la page d'administration
                header("Location: /admin?message=".$messagePostDeleteConfirmed);
                // Empêche l'exécution du reste du script
                die();
            }
            // Si l'id n'a aucune correspondance
            else {
                // Message d'erreur
                $messagePostDeleteFailed = "Erreur: Aucun article correspond à cet id";

                // Redirection vers la page d'administration
                header("Location: /admin?message=".$messagePostDeleteFailed);
                // Empêche l'exécution du reste du script
                die();
            }
        }
        // Si l'id est vide
        $messageIdWithoutPost = "Erreur: Aucun id n'est renseigné";

        // Redirection vers la page d'administration
        header("Location: /admin?message=".$messageIdWithoutPost);
        // Empêche l'exécution du reste du script
        die();
    }
}