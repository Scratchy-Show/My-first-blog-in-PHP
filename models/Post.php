<?php


namespace Models;


use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Column;
use PDOException;
use System\Database;

/**
 * @Entity
 * @Table(name="Post")
 */
class Post
{
    /**
     * @Id
     * @GeneratedValue
     * @Column(type="integer")
     */
    protected $id;

    /**
     * @Column(type="string", length=25)
     */
    protected $title;

    /**
     * @Column(type="text", length=65535)
     */
    protected $summary;

    /**
     * @Column(type="text", length=65535)
     */
    protected $content;

    /**
     * @Column(type="datetime")
     */
    protected $date;

    /**
     * @Column(type="string", length=25)
     */
    protected $author;



    public function __construct()
    {
        // Définit le fuseau horaire
        date_default_timezone_set('Europe/Paris');
        // Par défaut, la date est la date d'aujourd'hui
        $this->date = new \DateTime();
        // Par défaut, l'auteur est l'utilisateur connecté
        $this->author = $_SESSION['user']->getUsername();
    }

    // Ajoute un nouvel article
    public function addPostByForm($title, $summary, $content)
    {
        // Définit les valeurs des variables
        $this->setTitle($title);
        $this->setSummary($summary);
        $this->setContent($content);

        // Récupère EntityManager dans l'application
        $entityManager = Database::getEntityManager();
        // Planifie la sauvegarde de l'entité
        $entityManager->persist($this);
        // Effectue la sauvegarde de l'entité en bdd
        $entityManager->flush();

        // Message de confirmation
        $messagePostAddConfirmed = "L'article à été ajouté";
        return $messagePostAddConfirmed;
    }


    ////// Getter //////

    public function getId()
    {
        return $this->id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getSummary()
    {
        return $this->summary;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getAuthor()
    {
        return $this->author;
    }

    ////// Setter //////

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function setSummary($summary)
    {
        $this->summary = $summary;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function setAuthor($author)
    {
        $this->author = $author;
    }
}