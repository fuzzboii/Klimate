<?php
session_start();
// Ved adminside IF ($_SESSION['bruker'] and $_SESSION['brukertype'] == 1) {}
if ($_SESSION['brukernavn']) {
    // OK
} else {
    // Ikke OK
    // Header, ikke velkommen
}
include("klimate_pdo.php");
$db = new myPDO();

/*
$bruker = $_POST['bruker'];
$passord = $_POST['passord'];

$salted = $salt . $passord;

if (isset($_POST['bruker']) == "leggtil") {
    $sql = "insert into bruker(brukernavn, passord)";
    $sql.= " values(:br, :pw)";
    $stmt = $db -> prepare($sql);
    $stmt -> bindParam(':br', $bruker);
    $stmt -> bindParam(':pw', $salted);
    $stmt -> execute();
    echo("<!-- SQL-setningen: " . $sql . " -->");
}
*/

if (isset($_POST['submit'])) {
    // Saltet
    $salt = "IT2_2019"; 

    $br = $_POST['brukernavn'];
    $pw = $_POST['passord'];
    $kombinert = $salt . $pw;
    // Krypterer passorder med salting
    $spw = sha1($kombinert);

    $sql = "select * from bruker where brukernavn='" . $br . "' and passord='" . $spw . "'";
    $stmt = $db->prepare($sql);

    $stmt->execute();

    $resultat = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($resultat['brukernavn'] == $br and $resultat['passord'] == $spw) {
        $_SESSION['brukernavn'] = $br;
        $_SESSION['fornavn'] = $resultat['fornavn'];
        $_SESSION['etternavn'] = $resultat['etternavn'];;
        $_SESSION['epost'] = $resultat['epost'];
        $_SESSION['brukertype'] = $resultat['brukertype'];
        //$_SESSION['brukertype'] = $row['brukertype'];
        header("Location: backend.php");
        //loggInn();
    } else {
        //header("location: Forelesning 2019-11-04 Kryptering.php");
    }
}

function loggInn() {
}

?>

<!DOCTYPE html>
<html lang="no">

<head>
    <!-- Setter riktig charset -->
    <meta charset="UTF-8">
    <!-- Legger til viewport -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Setter tittelen på prosjektet -->
    <title>Klimate</title>
    <!-- Henter inn ekstern stylesheet -->
    <link rel="stylesheet" type="text/css" href="stylesheet.css">
    <!-- Henter inn favicon, bildet som dukker opp i fanene i nettleseren -->
    <link rel='icon' href='bilder/favicon.png' type='image/x-icon'>
    <!-- Henter inn JavaScript -->
    <script language="JavaScript" src="javascript.js"> </script>
</head>

<body>
    <!-- Begynnelse på øvre navigasjonsmeny -->
    <nav class="navTop">
        <!-- Bruker et ikon som skal åpne gardinmenyen, henviser til funksjonen hamburgerMeny i javascript.js -->
        <a class="bildeKontroll" href="#" onclick="hamburgerMeny()" tabindex="3">
            <img src="bilder/hamburgerIkon.svg" alt="Hamburger-menyen" class="hamburgerKnapp">
        </a>
        <!-- Legger til en knapp for å gå fra innlogging til registrering -->
        <button class="singelKnapp" onClick="location.href='registrer.php'" tabindex="2">REGISTRER</button>
        <!-- Logoen øverst i venstre hjørne, denne leder alltid tilbake til default.php -->
        <a class="bildeKontroll" href="default.php" tabindex="1">
            <img src="bilder/klimateNoText.png" alt="Klimate logo" class="Logo_navmeny">
        </a>  
        
    <!-- Slutt på navigasjonsmeny-->
    </nav>
    <!-- Gardinmenyen, denne går over alt annet innhold ved bruk av z-index -->
    <section id="navMeny" class="hamburgerMeny">

        <!-- innholdet i gardinmenyen -->
        <section class="hamburgerInnhold">
            <a href="#">Diskusjoner</a>
            <a href="#">Arrangementer</a>
            <a href="#">Artikler</a>
            <a href="#">Profil</a>
            <a href="#">Innstillinger</a>
        </section>
    </section>

    <!-- Logoen midten øverst på siden, med tittel -->
    <header onclick="lukkHamburgerMeny()">
        <img src="bilder/klimate.png" alt="Klimate logo" class="Logo_forside">
    </header>
    <main onclick="lukkHamburgerMeny()">
        <!-- Formen som i senere tid skal brukes til autentisering på bruker, bruker type="password" for å ikke vise innholdet brukeren skriver -->
        <form method="POST" action="logginn.php" class="innloggForm"> <!-- Uten autentisering, for å kunne navigere hele siden uten funksjonalitet -->
            <section class="inputBoks">
                <img class="icon" src="bilder/brukerIkon.png" alt="Brukerikon"> <!-- Ikonet for bruker -->
                <input type="text" class="RegInnFelt" name="brukernavn" value="" placeholder="Skriv inn brukernavn" autofocus>
            </section>
            <section class="inputBoks">
                <img class="icon" src="bilder/pwIkon.png" alt="Passordikon"> <!-- Ikonet for passord -->
                <input type="password" class="RegInnFelt" name="passord" value="" placeholder="Skriv inn passord">
            </section>
            <input type="submit" name="submit" class="RegInnFelt_knappLogginn" value="Logg inn">   
        </form>
        
        <!-- Sender brukeren tilbake til forsiden -->
        <button onClick="" class="lenke_knapp">Glemt passord?</button>
        <button onClick="location.href='default.php'" class="lenke_knapp">Tilbake til forside</button>

    </main>
</body>

<!-- Denne siden er utviklet av Robin Kleppang, siste gang endret 11.10.2019 -->
<!-- Denne siden er kontrollert av Glenn Petter Pettersen, siste gang 11.10.2019 -->

</html>