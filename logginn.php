<?php
session_start();

// Ved adminside IF ($_SESSION['bruker'] and $_SESSION['brukertype'] == 1) {}
// Sjekker om bruker er i en gyldig session, sender tilbake til hovedsiden hvis så
if (isset($_SESSION['brukernavn'])) {
    header("Location: default.php?error=2");
}

// Setter tidssonen, dette er for at One.com domenet skal fungere, brukes i sjekk mot innloggingsforsøk
date_default_timezone_set("Europe/Oslo");


try {
    include("klimate_pdo.php");
    $db = new mysqlPDO();
} 
catch (Exception $ex) {
    // Disse feilmeldingene leder til samme tilbakemelding for bruker, dette kan ønskes å utvide i senere tid, så beholder alle for nå.
    if ($ex->getCode() == 1049) {
        // 1049, Fikk koblet til men databasen finnes ikke
        header('location: default.php?error=3');
    }
    if ($ex->getCode() == 2002) {
        // 2002, Kunne ikke koble til server
        header('location: default.php?error=3');
    }
    if ($ex->getCode() == 1045) {
        // 1045, Bruker har ikke tilgang
        header('location: default.php?error=3');
    }
}

if (isset($_POST['submit'])) {
    // Ventetiden når en bruker er lukket ute
    $ventetid = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s") . "- 180 seconds"));

    // Hvis feiltelleren ikke eksisterer har ikke bruker forsøkt å logge inn hittils, oppretter da disse med standardverdier
    if (!isset($_SESSION['feilteller'])) {
        $_SESSION['feilteller'] = 0;
        $_SESSION['sistFeilet'] = date("Y-m-d H:i:s");
    }

    // Hvis det har gått 3 minutter mellom siste gang bruker feiler innlogging, tøm telleren
    if ($_SESSION['sistFeilet'] <= $ventetid) {
        $_SESSION['feilteller'] = 0;
    }

    // Sjekker først om bruker har feilet innlogging for mange ganger
    if ($_SESSION['feilteller'] < 5) {
        // Saltet
        $salt = "IT2_2020"; 

        $br = $_POST['brukernavn'];
        $lbr = strtolower($_POST['brukernavn']);
        $pw = $_POST['passord'];
        $kombinert = $salt . $pw;
        // Krypterer passorder med salting
        $spw = sha1($kombinert);

        $sql = "select * from bruker where lower(brukernavn)='" . $lbr . "' and passord='" . $spw . "'";
        // Prepared statement for å beskytte mot SQL injection
        $stmt = $db->prepare($sql);

        $stmt->execute();

        $resultat = $stmt->fetch(PDO::FETCH_ASSOC);

        if (strtolower($resultat['brukernavn']) == $lbr and $resultat['passord'] == $spw) {
            $_SESSION['idbruker'] = $resultat['idbruker'];
            $_SESSION['brukernavn'] = $resultat['brukernavn'];
            $_SESSION['fornavn'] = $resultat['fnavn'];
            $_SESSION['etternavn'] = $resultat['enavn'];;
            $_SESSION['epost'] = $resultat['epost'];
            $_SESSION['brukertype'] = $resultat['brukertype'];
            
            $_SESSION['feilteller'] = 0;

            header("Location: backend.php");
        } else {    
            // Øker teller for feilet innlogging med 1
            $_SESSION['feilteller']++;
            $_SESSION['sistFeilet'] = date("Y-m-d H:i:s");
            
            header("Location: logginn.php?error=1");
        }
    } else {
        // Bruker har feilet for mange ganger, gir tilbakemelding til bruker
        header("Location: logginn.php?error=2");
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
        <title>Innlogging</title>
        <!-- Henter inn ekstern stylesheet -->
        <link rel="stylesheet" type="text/css" href="stylesheet.css">
        <!-- Henter inn favicon, bildet som dukker opp i fanene i nettleseren -->
        <link rel='icon' href='bilder/favicon.png' type='image/x-icon'>
        <!-- Henter inn JavaScript -->
        <script language="JavaScript" src="javascript.js"> </script>
    </head>

    <body>
        <article class="innhold">
            <!-- Begynnelse på øvre navigasjonsmeny -->
            <nav class="navTop">
                <!-- Bruker et ikon som skal åpne gardinmenyen, henviser til funksjonen hamburgerMeny i javascript.js -->
                <!-- javascript:void(0) blir her brukt så siden ikke scroller til toppen av seg selv når du trykker på hamburger-ikonet -->
                <a class="bildeKontroll" href="javascript:void(0)" onclick="hamburgerMeny()" tabindex="3">
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
                <!-- -1 tabIndex som standard da menyen er lukket -->
                <section class="hamburgerInnhold">
                    <a class = "menytab" tabIndex = "-1" href="#">Arrangementer</a>
                    <a class = "menytab" tabIndex = "-1" href="#">Artikler</a>
                    <a class = "menytab" tabIndex = "-1" href="#">Diskusjoner</a>
                </section>
            </section>

            <!-- Logoen midten øverst på siden, med tittel -->
            <header onclick="lukkHamburgerMeny()">
                <img src="bilder/klimate.png" alt="Klimate logo" class="Logo_forside">
            </header>
            <main onclick="lukkHamburgerMeny()">
                <!-- Form brukes til autentisering av bruker, bruker type="password" for å ikke vise innholdet brukeren skriver -->
                <form method="POST" action="logginn.php" class="innloggForm">
                    <section class="inputBoks">
                        <img class="icon" src="bilder/brukerIkon.png" alt="Brukerikon"> <!-- Ikonet for bruker -->
                        <input type="text" class="RegInnFelt" name="brukernavn" value="" placeholder="Skriv inn brukernavn" autofocus>
                    </section>
                    <section class="inputBoks">
                        <img class="icon" src="bilder/pwIkon.png" alt="Passordikon"> <!-- Ikonet for passord -->
                        <input type="password" class="RegInnFeltPW" name="passord" value="" placeholder="Skriv inn passord">
                    </section>
                    <input style="margin-bottom: 1em;" type="checkbox" onclick="visPassordReg()">Vis passord</input>
                    <!-- Meldinger til bruker -->
                    <?php if(isset($_GET['error']) && $_GET['error'] == 1){ ?>
                        <p id="mldFEIL">Sjekk brukernavn og passord</p>    
                    
                    <?php } else if(isset($_GET['error']) && $_GET['error'] == 2){ ?>
                        <p id="mldFEIL">Du har feilet innlogging for mange ganger, vennligst vent</p>
                    
                    <?php } else if(isset($_GET['vellykket']) && $_GET['vellykket'] == 1){ ?>
                        <p id="mldOK">Bruker opprettet, vennligst logg inn</p>    
                    
                    <?php } else if(isset($_GET['vellykket']) && $_GET['vellykket'] == 2){ ?>
                        <p id="mldOK">Passord endret</p>
                    <?php } ?>

                    <input type="submit" name="submit" class="RegInnFelt_knappLogginn" value="Logg inn">   
                </form>

                <!-- Sender brukeren tilbake til forsiden -->
                <button onClick="location.href='glemt_passord.php'" class="lenke_knapp">Glemt passord?</button>
                <button onClick="location.href='default.php'" class="lenke_knapp">Tilbake til forside</button>

            </main>
            
            <!-- Knapp som vises når du har scrollet i vinduet, tar deg tilbake til toppen -->
            <button onclick="topFunction()" id="toppKnapp" title="Toppen"><img src="bilder/pilopp.png" alt="Tilbake til toppen"></button>

            <!-- Footer, epost er for øyeblikket på en catch-all, videresendes til RK -->
            <footer>
                <p class=footer_beskrivelse>&copy; Klimate 2019 | <a href="mailto:kontakt@klimate.no">Kontakt oss</a>
                    <?php if (isset($_SESSION['brukernavn']) and $_SESSION['brukertype'] == "3") { ?> | <a href="soknad.php">Søknad om å bli redaktør</a><?php } ?>
                </p>
            </footer>
        </article>
    </body>

    <!-- Denne siden er utviklet av Aron Snekkestad, Robin Kleppang, siste gang endret 04.11.2019 -->
    <!-- Denne siden er kontrollert av Glenn Petter Pettersen, Ajdin Bajrovic siste gang 09.12.2019 -->

</html>