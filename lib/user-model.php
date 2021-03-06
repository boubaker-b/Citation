<?php
require_once "lib/pdo.php";

/**
 * Liste de tous les rôles depuis de la BD
 *
 * @return array
 */
function getAllRoles()
{
    $pdo = getPDO();
    $sql = "SELECT * FROM roles";
    return $pdo->query($sql)
        ->fetchAll(PDO::FETCH_ASSOC);
}

function validateRegisterInput($data)
{
    $errors = [];

    if (empty($data["pseudo"])) {
        $errors[] = "Le pseudo ne peut être vide";
    }
    if (empty($data["email"])) {
        $errors[] = "l'email ne peut être vide";
    }
    if (empty($data["mot_de_passe"])) {
        $errors[] = "le mot de passe ne peut être vide";
    } else if (mb_strlen($data["mot_de_passe"]) < 6) {
        $errors[] = "Le mot de passe doit comporter au moins 6 caractères";
    }
    if (empty($data["role_id"])) {
        $errors[] = "Le role ne peut être vide";
    }
    return $errors;
}

function insertUser(array $data)
{
    $pdo = getPDO();
    $sql = "INSERT INTO utilisateurs (pseudo, email, mot_de_passe,role_id)
            VALUES                  (:pseudo,:email,:mot_de_passe,:role_id)";

    //hachage du mot de passe pour sécuriser l'applicaiton
    $data["mot_de_passe"] = password_hash(
        $data["mot_de_passe"], PASSWORD_BCRYPT);
    $statement = $pdo->prepare($sql);
    $statement->execute($data);
}
/**
 * Traitement du formulaire d'ajout de citation
 *
 * @return array $errors
 */

function handleRegisterForm(int $id = null)
{
    //On récupere la saisie
    //  $text = filter_input(INPUT_POST, "texte", FILTER_SANITIZE_STRING);
    //   $author = filter_input(INPUT_POST, "auteur", FILTER_SANITIZE_STRING);
    $data = filter_input_array(INPUT_POST, [
        "pseudo" => FILTER_SANITIZE_STRING,
        "email" => FILTER_SANITIZE_STRING,
        "mot_de_passe" => FILTER_DEFAULT,
        "role_id" => FILTER_SANITIZE_NUMBER_INT,

    ]);
    //Validation de la saisie
    $errors = validateRegisterInput($data);

//Insertion uniquement s'il n'y pas d'erreurs
    if (count($errors) == 0) {

        try {
            //Ajout ou modification
            //enfonction de la valeur de $id
            if ($id) {
                //  updateQuote($data, $id);
            } else {
                insertUser($data);
            }

//Redirection vers la l'index
            header("location:index.php");
            exit;
        } catch (Exception $exception) {
            $errors[] = "Erreur interne du serveur";
            $errors[] = $exception->getMessage();
        }
    }
    return $errors;
}

/**
 * Authentification d'un utilisateur en fonction des données dans la BD
 *
 * @param string $login
 * @param string $pass
 * @return boolean
 */
function authenticate(string $login, string $pass):bool
{
    $authenticated=false;
    $pdo = getPDO();
//Jointure entre deux tables
    //la clause ON indique les correspondances ou les conditions de jointures
    $sql = "SELECT u.*, r.nom as role_utilisateur
        FROM utilisateurs as u
        JOIN roles as r ON u.role_id= r.id
        WHERE (pseudo=:login OR email= :login)";

    //Paramètres de la requête
    $credentials = ["login" => $login];
    //Preparation et exécution de la requête
    $statement = $pdo->prepare($sql);
    $statement->execute($credentials);
    //Récupération du resultat de la requête
    $user = $statement->fetch(PDO::FETCH_ASSOC);
    //Si on a trouvé un utilisateur
    if ($user) {
        //On vérifie le mot de passe de l'utilisateur
        if (password_verify($pass, $user["mot_de_passe"])) {
            //Enregistrement de l'utilisateur dans la session
            $_SESSION["user"] =$user;
            $authenticated = true;
        }
    } 
        return $authenticated;
    }

function isUserLogged(){
    return isset($_SESSION["user"]);
}
function getUserName(){
    return $_SESSION["user"]["pseudo"];
}



function hasFlashMessage(){
    return isset($_SESSION["message"]);
}
function getFlashMessage(){
    //Stockage du message dans une variable
    $message =$_SESSION["message"];
    //Supprission du message dans la session
    unset($_SESSION["message"]);
    //retourne le message sauvegardé
   return $message;
  
}






