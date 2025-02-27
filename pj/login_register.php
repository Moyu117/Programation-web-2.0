<?php
// login_register.php
require_once 'config.php';
require_once 'fonction.inc.php';

// connecter a DB
$link = db_connect($db_host, $db_user, $db_pass, $db_name);

$action = $_REQUEST['action'] ?? '';

//connecter
if ($action === 'login') {
    $login = $_POST['login'] ?? '';
    $password = $_POST['password'] ?? '';

    $loginEsc = mysqli_real_escape_string($link, $login);
    $passwordEsc = mysqli_real_escape_string($link, $password);

    $sql = "SELECT * FROM users WHERE login='$loginEsc' AND password='$passwordEsc'";
    $res = query($link, $sql);
    $user = mysqli_fetch_assoc($res);

    if ($user) {
        // Connexion réussie
        $_SESSION['user'] = $user;
        header("Location: index.php");
        exit;
    } else {
        echo "Échec de la connexion : nom d'utilisateur ou mot de passe incorrect<br>";
        echo "<a href='index.php'>Retour à la page d'accueil</a>";
        exit;
    }
}

// logout
if ($action === 'logout') {
    session_destroy();
    header("Location: index.php");
    exit;
}

// afficher list de Inscription
if ($action === 'registerForm') {
    ?>
    <h2>Inscription de l'utilisateur</h2>
    <form method="POST" action="login_register.php">
        <input type="hidden" name="action" value="register">
        <label>Login: <input type="text" name="login"></label><br>
        <label>mot de passe: <input type="text" name="password"></label><br>
        <label>prenon(Prénom): <input type="text" name="prenom"></label><br>
        <label>age: <input type="text" name="age"></label><br>
        <button type="submit">Inscrire</button>
    </form>
    <?php
    exit;
}

// Inscrire
if ($action === 'register') {
    $login = $_POST['login'] ?? '';
    $password = $_POST['password'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $age = $_POST['age'] ?? '';

    // verifer
    if (!preg_match('/^[A-Za-z0-9]+$/', $login)) {
        die("Le login ne peut contenir que des lettres ou des chiffres");
    }
    if (!is_numeric($age)) {
        die("L'âge doit être un nombre entier");
    }

    //transfére
    list($loginEsc, $passwordEsc, $prenomEsc, $ageEsc) = array_escape($link, [$login, $password, $prenom, $age]);

    // Vérifier si le nom de utilisateur existe déjà
    $sqlCheck = "SELECT login FROM users WHERE login='$loginEsc'";
    $resCheck = query($link, $sqlCheck);
    if (mysqli_fetch_assoc($resCheck)) {
        die("Ce nom de utilisateur a été enregistré");
    }

    // Insertion dans la base de données
    $sqlInsert = "
        INSERT INTO users (login, password, prenom, age)
        VALUES ('$loginEsc', '$passwordEsc', '$prenomEsc', '$ageEsc')
    ";
    query($link, $sqlInsert);

    echo "Inscription réussie！<br>";
    echo "<a href='index.php'>Retour à la page d'accueil</a>";
    exit;
}

// Modifier information
if ($action === 'edit') {
    if (!isset($_SESSION['user'])) {
        die("Veuillez d'abord vous connecter");
    }

    // post
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $newPassword = $_POST['password'] ?? '';
        $newPrenom   = $_POST['prenom'] ?? '';
        $newAge      = $_POST['age'] ?? '';

        list($newPasswordEsc, $newPrenomEsc, $newAgeEsc) = array_escape($link, [$newPassword, $newPrenom, $newAge]);

        $userLogin = $_SESSION['user']['login'];
        $userLoginEsc = mysqli_real_escape_string($link, $userLogin);

        $sqlUpdate = "
            UPDATE users
            SET password='$newPasswordEsc', prenom='$newPrenomEsc', age='$newAgeEsc'
            WHERE login='$userLoginEsc'
        ";
        query($link, $sqlUpdate);

        // SESSION
        $_SESSION['user']['password'] = $newPassword;
        $_SESSION['user']['prenom']   = $newPrenom;
        $_SESSION['user']['age']      = $newAge;

        echo "L'information a été mise à jour avec succès！<br>";
        echo "<a href='index.php'>Retour à la page d'accueil</a>";
        exit;
    } else {
        // Afficher le formulaire de modification
        $user = $_SESSION['user'];
        ?>
        <h2>Modifier le profil</h2>
        <form method="POST" action="login_register.php?action=edit">
            <label>Login (ne change pas): <?php echo htmlspecialchars($user['login']); ?></label><br><br>
            <label>mot de passe: <input type="text" name="password" value="<?php echo htmlspecialchars($user['password']); ?>"></label><br>
            <label>prenom(Prénom): <input type="text" name="prenom" value="<?php echo htmlspecialchars($user['prenom']); ?>"></label><br>
            <label>age: <input type="text" name="age" value="<?php echo htmlspecialchars($user['age']); ?>"></label><br>
            <button type="submit">enregistrer</button>
        </form>
        <?php
        exit;
    }
}

// si il n'a pas de action
header("Location: index.php");
exit;
