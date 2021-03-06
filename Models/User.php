<?php


namespace Models;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Column;
use PDOException;
use System\Database;
use \DateTime;

/**
 * @Entity
 * @Table(name="user")
 */
class User
{
    /**
     * @Id
     * @GeneratedValue
     * @Column(type="integer")
     */
    protected $id;

    /**
     * @Column(type="string", name="last_name", length=25)
     */
    protected $lastName;

    /**
     * @Column(type="string", name="first_name", length=25)
     */
    protected $firstName;

    /**
     * @Column(type="string", length=50)
     */
    protected $email;

    /**
     * @Column(type="boolean")
     */
    protected $role;

    /**
     * @Column(type="datetime")
     */
    protected $date;

    /**
     * @Column(type="string", length=25)
     */
    protected $username;

    /**
     * @Column(type="string", name="password", length=255)
     */
    protected $hashedPassword;

    /**
     * @OneToMany(targetEntity="Models\Comment", mappedBy="author")
     */
    protected $comments;

    public function __construct()
    {
        // Définit le fuseau horaire
        date_default_timezone_set('Europe/Paris');
        // Par défaut, la date est la date d'aujourd'hui
        $this->date = new DateTime();
        // Par défaut, le role est à 0 (false)
        $this->role = 0;
        // Liste des commentaires
        $this->comments = new ArrayCollection();
    }

    // Enregistre un nouveau utilisateur
    public function registerUserByForm($lastName, $firstName, $email, $username, $password)
    {
        // Définit les valeurs des variables
        $this->setLastName($lastName);
        $this->setFirstName($firstName);
        $this->setEmail($email);
        $this->setUsername($username);
        $this->setPassword($password);

        // Récupère EntityManager dans l'application
        $entityManager = Database::getEntityManager();
        // Planifie la sauvegarde de l'entité
        $entityManager->persist($this);
        // Effectue la sauvegarde de l'entité en bdd
        $entityManager->flush();
    }

    // Récupère un utilisateur avec son id
    public static function getUserById($id)
    {
        // Gestion des erreurs
        try {
            // Repository dédié à l'entité User
            $userRepository = Database::getEntityManager()->getRepository(User::class);
            // Recherche un id correspondant
            $user = $userRepository->find($id);
            // Retourne un utilisateur ou un tableau vide
            return $user;
        } catch (PDOException $e) {
            echo 'Échec lors du lancement de la requête: ' . $e->getMessage();
        }
    }

    // Récupère un utilisateur avec son mail
    public static function getUserByEmail($email)
    {
        // Gestion des erreurs
        try {
            // Repository dédié à l'entité User
            $userRepository = Database::getEntityManager()->getRepository(User::class);
            // Recherche un email correspondant
            $user = $userRepository->findBy(array('email' => $email));
            // Retourne un utilisateur ou un tableau vide
            return $user;
        } catch (PDOException $e) {
            echo 'Échec lors du lancement de la requête: ' . $e->getMessage();
        }
    }

    // Récupère un utilisateur avec son pseudo
    public static function getUserByUsername($username)
    {
        // Gestion des erreurs
        try {
            // Repository dédié à l'entité User
            $userRepository = Database::getEntityManager()->getRepository(User::class);
            // Recherche un pseudo correspondant
            $user = $userRepository->findOneBy(array('username' => $username));
            // Retourne un utilisateur ou un tableau vide
            return $user;
        } catch (PDOException $e) {
            echo 'Échec lors du lancement de la requête: ' . $e->getMessage();
        }
    }

    // Vérifie la correspondance de l'utilisateur avec le mot de passe
    public static function checkUserPassword($user, $password)
    {
       // Gestion des erreurs
        try {
            // Vérifie la correspondance de $password avec le mot de passe haché en BDD
            $checkPassword = password_verify($password, $user->getHashedPassword());

            // Retourne True ou False
            return array($checkPassword);
        } catch (PDOException $e) {
            echo 'Échec lors du lancement de la requête: ' . $e->getMessage();
        }
    }

    // Hachage du mot de passe
    public function hashPassword($password)
    {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        // Retourne le mot de passe haché
        return $hashedPassword;
    }

    ////// Getter //////

    public function getId()
    {
        return $this->id;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getHashedPassword()
    {
        return $this->hashedPassword;
    }

    public function getComments()
    {
        return $this->comments;
    }


    ////// Setter //////

    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function setRole($role)
    {
        $this->role = $role;
    }

    public function setUsername($username)
    {
        $this->username = $username;
    }

    public function setHashedPassword($hashedPassword)
    {
        $this->hashedPassword = $hashedPassword;
    }

    public function setPassword($password)
    {
        $this->hashedPassword = $this->hashPassword($password);
    }

    public function setComments($comments)
    {
        $this->comments = $comments;
    }
}
