<?php
session_start();
// Ved adminside IF ($_SESSION['bruker'] and $_SESSION['brukertype'] == 1) {}
// Sjekker om bruker er i en gyldig session, sender tilbake til hovedsiden hvis så
if (isset($_SESSION['brukernavn'])) {
    header("Location: default.php?error=2");
}

include("klimate_pdo.php");
$db = new myPDO();
// PDO emulerer til standard 'prepared statements', det er anbefalt å kun tillate ekte statements
// 
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (isset($_POST['subRegistrering'])) {
    if ($_POST['passord'] == $_POST['passord2']) {
        try {
          
            // Saltet
            $salt = "IT2_2020"; 

            $br = $_POST['brukernavn'];
            $pw = $_POST['passord'];

            // Validering av passordstyrke
            // Kilde: https://www.codexworld.com/how-to/validate-password-strength-in-php/
            $storebokstaver = preg_match('@[A-Z]@', $pw);
            $smaabokstaver = preg_match('@[a-z]@', $pw);
            $nummer = preg_match('@[0-9]@', $pw);
            // Denne er for spesielle symboler
            // $specialChars = preg_match('@[^\w]@', $password);

            if ($pw == "") {
                // Ikke noe passord skrevet
                header("Location: registrer.php?error=3");
            } else if (!$storebokstaver || !$smaabokstaver || !$nummer /*|| !$specialChars*/ || strlen($pw) < 8) {
                // Ikke tilstrekkelig passord skrevet
                header("Location: registrer.php?error=4");
            } else {
                // OK, vi forsøker å registrere bruker
                $fn = $_POST['fornavn'];
                $en = $_POST['etternavn'];
                $epost = $_POST['epost'];
                $kombinert = $salt . $pw;
                // Krypterer passorder med salting
                $spw = sha1($kombinert);
                $sql = "insert into bruker(brukernavn, passord, fornavn, etternavn, epost, brukertype) VALUES('" . $br . "', '" . $spw . "', '" . $fn . "', '" . $en . "', '" . $epost . "', 3)";


                //$sql = "select * from bruker where lower(brukernavn)='" . $lbr . "' and passord='" . $spw . "'";
                // Prepared statement for å beskytte mot SQL injection
                $stmt = $db->prepare($sql);

                $vellykket = $stmt->execute(); 
                
                if($vellykket) {
                    header("location: logginn.php?vellykket=1");
                }
            }
        }
        catch (PDOException $ex) {
            if ($ex->getCode() == 23000){
                // Duplikat brukernavn
                header("location: registrer.php?error=1");
            }
        } 
        // Feilmelding 2 = passord ikke like
        // 
    } else {
        header("location: registrer.php?error=2");
    }
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
        <a class="bildeKontroll" href="javascript:void(0)" onclick="hamburgerMeny()" tabindex="3">
            <img src="bilder/hamburgerIkon.svg" alt="Hamburger-menyen" class="hamburgerKnapp">
        </a>
        <!-- Legger til en knapp for å gå fra registrering til innlogging -->
        <button class="singelKnapp" onClick="location.href='logginn.php'" tabindex="2">LOGG INN</button>
        <!-- Logoen øverst i venstre hjørne, denne leder alltid tilbake til default.php -->
        <a class="bildeKontroll" href="default.php" tabindex="1">
            <img src="bilder/klimateNoText.png" alt="Klimate logo" class="Logo_navmeny">
        </a> 
    <!-- Slutt på navigasjonsmeny-->
    </nav>
    <!-- Gardinmenyen, denne går over alt annet innhold ved bruk av z-index -->
    <section id="navMeny" class="hamburgerMeny">
      
        <!-- innholdet i hamburger-menyen -->
        <!-- -1 tabIndex som standard da menyen er lukket -->
        <section class="hamburgerInnhold">
            <a id = "menytab1" tabIndex = "-1" href="#">Diskusjoner</a>
            <a id = "menytab2" tabIndex = "-1" href="#">Arrangementer</a>
            <a id = "menytab3" tabIndex = "-1" href="#">Artikler</a>
            <a id = "menytab4" tabIndex = "-1" href="#">Profil</a>
            <a id = "menytab5" tabIndex = "-1" href="#">Innstillinger</a>
        </section>
    </section>

    <!-- Logoen midten øverst på siden, med tittel -->
    <header onclick="lukkHamburgerMeny()">
        <img src="bilder/klimate.png" alt="Klimate logo" class="Logo_forside">
    </header>
    <main onclick="lukkHamburgerMeny()">
        <!-- Formen som i senere tid skal brukes til registrering på bruker, bruker type="password" for å ikke vise innholdet brukeren skriver -->
        <form method="POST" action="registrer.php" class="innloggForm">
        <section class="inputBoks">
            <img class="icon" src="bilder/brukerIkon.png" alt="Brukerikon"> <!-- Ikonet for bruker -->
            <input type="text" class="RegInnFelt" name="brukernavn" value="" placeholder="Skriv inn brukernavn" autofocus>
        </section>
        <section class="inputBoks">
            <img class="icon" src="bilder/fnenIkon.png" alt="Fornavnikon"> <!-- Ikonet for Fornavn -->
            <input type="fornavn" class="RegInnFelt" name="fornavn" value="" placeholder="Skriv inn fornavn">
        </section>
        <section class="inputBoks">
            <img class="icon" src="bilder/fnenIkon.png" alt="Etternavnikon"> <!-- Ikonet for passord -->
            <input type="etternavn" class="RegInnFelt" name="etternavn" value="" placeholder="Skriv inn etternavn">
        </section>
        <section class="inputBoks">
            <img class="icon" src="bilder/emailIkon.png" alt="Epostikon"> <!-- Ikonet for epostadresse -->
            <input type="email" class="RegInnFelt" name="epost" value="" placeholder="Skriv inn e-postadresse">
        </section>
        <section class="inputBoks">
            <img class="icon" src="bilder/pwIkon.png" alt="Passordikon"> <!-- Ikonet for passord -->
            <input type="password" class="RegInnFelt" name="passord" value="" placeholder="Skriv inn passord">
        </section>
        <section class="inputBoks">
            <img class="icon" src="bilder/pwIkon.png" alt="Passordikon"> <!-- Ikonet for passord -->
            <input type="password" class="RegInnFelt" name="passord2" value="" placeholder="Bekreft passord">
        </section>
        <!-- Håndtering av feilmeldinger -->
        <?php   
            if(isset($_GET['error']) && $_GET['error'] == 1){ 
        ?>
        <p id="mldFEIL">Bruker eksisterer fra før</p>    
        <?php 
            } else if(isset($_GET['error']) && $_GET['error'] == 2) {
        ?>
        <p id="mldFEIL">Passordene er ikke like</p>
        <?php
            } else if(isset($_GET['error']) && $_GET['error'] == 3) {
        ?>
        <p id="mldFEIL">Skriv inn ett passord</p>
        <?php
            } else if(isset($_GET['error']) && $_GET['error'] == 4) {
        ?>
        <p id="mldFEIL">Passord må være 8 tegn i lengden og inneholde en liten bokstav, en stor bokstav og ett tall</p>
        <?php
            }
        ?>
            <input type="submit" name="subRegistrering" class="RegInnFelt_knappRegistrer" value="Registrer ny bruker">
        </form>

        <!-- Sender brukeren tilbake til forsiden -->
        <button onClick="location.href='default.php'" name="submit" class="lenke_knapp">Tilbake til forside</button>
        
    </main>
    <footer>
        <p class=footer_beskrivelse>&copy; Klimate 2019 | <a href="mailto:kontakt@klimate.no">Kontakt oss</a></p>
    </footer>
</body>

<!-- Denne siden er utviklet av Robin Kleppang, siste gang endret 11.10.2019 -->
<!-- Denne siden er kontrollert av Glenn Petter Pettersen, siste gang 11.10.2019 -->

</html>