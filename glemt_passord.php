<?php
session_start();
// Ved adminside IF ($_SESSION['bruker'] and $_SESSION['brukertype'] == 1) {}
// Sjekker om bruker er i en gyldig session, sender tilbake til hovedsiden hvis så
if (isset($_SESSION['brukernavn'])) {
    header("Location: default.php?error=2");
}

try {
    include("klimate_pdo_prod.php");
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

// Setter så PDO kaster ut feilmelding og stopper funksjonen ved database-feil (PDOException)
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


// Hoveddelen for glemt passord
if (isset($_POST['glemtPassord'])) {
    // Tester på om passordene er like
    if ($_POST['passord'] == $_POST['passord2']) {
        // Tester på om passordet er mindre eller lik 45 tegn
        if (strlen($_POST['passord']) <= 45) {
            // Tester på om brukernavnet er fyllt ut
            if ($_POST['brukernavn'] != "") {
                // Tester på om epost er fyllt ut
                if ($_POST['epost'] != "") {
                    // Saltet
                    $salt = "IT2_2020"; 
    
                    $epost = $_POST['epost'];
                    $br = $_POST['brukernavn'];
                    $pw = $_POST['passord'];
    
                    // Validering av passordstyrke
                    $storebokstaver = preg_match('@[A-Z]@', $pw);
                    $smaabokstaver = preg_match('@[a-z]@', $pw);
                    $nummer = preg_match('@[0-9]@', $pw);
                    // Denne er for spesielle symboler, ikke i bruk for øyeblikket
                    // $spesielleB = preg_match('@[^\w]@', $pw);
    
                    if ($pw == "") {
                        // Ikke noe passord skrevet
                        header("Location: glemt_passord.php?error=3");
                    } else if (!$storebokstaver || !$smaabokstaver || !$nummer /*|| !$spesielleB*/ || strlen($pw) < 8) {
                        // Ikke tilstrekkelig passord skrevet
                        header("Location: glemt_passord.php?error=4");
                    } else {
                        // OK, vi salter passord for eksiterende bruker
                        $kombinert = $salt . $pw;
                        // Krypterer passorder med salting
                        // Anser kombinasjonen av brukernavn og epost god nok til å kunne endre passord. 
                        // Om man legger inn flere brukere med samme brukernavn og epost vil dette ikke lenger være en god løsning. 
                        $spw = sha1($kombinert);
                        $sql = "update bruker set passord='" . $spw . "' where brukernavn='". $br . "' and epost='" . $epost . "'";
    
    
                        // Prepared statement for å beskytte mot SQL injection
                        $stmt = $db->prepare($sql);
    
                        $stmt->execute();
    
                        //Utfører en ny spørring for å sjekke om brukeren eksisterer
                        $sql1="select brukernavn from bruker where brukernavn='" . $br . "' and epost='" . $epost . "'";
                        $stmt1 = $db->prepare($sql1);
                        $stmt1->execute();
    
                        $stmt1->setFetchMode(PDO::FETCH_ASSOC);
                        $resultat = $stmt1->fetch();
                        
                        if($resultat['brukernavn']==$br) {
                            // Alt gikk OK, sender til logginn med melding til bruker
                            header("location: logginn.php?vellykket=2");
                        } else {
                            //Ikke ok, ber bruker om å oppgi brukernavn på nytt
                            header("location: glemt_passord.php?error=1");
                        }
                    }

                } else {
                    // Feilmelding 6, bruker har ikke fyllt ut felt
                    header("location: glemt_passord.php?error=6");
                }
            } else {
                // Feilmelding 6, bruker har ikke fyllt ut felt
                header("location: glemt_passord.php?error=6");
            }
        } else {
            // Feilmelding 5, passord for langt
            header("location: glemt_passord.php?error=5");
        }
    } else {
        // Feilmelding 2 = passord ikke like
        header("location: glemt_passord.php?error=2");
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
        <title>Glemt Passord</title>
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
                    <a id = "menytab1" tabIndex = "-1" href="#">Diskusjoner</a>
                    <a id = "menytab2" tabIndex = "-1" href="#">Arrangementer</a>
                    <a id = "menytab3" tabIndex = "-1" href="#">Artikler</a>
                    <a id = "menytab4" tabIndex = "-1" href="#">Profil</a>
                    <a id = "menytab5" tabIndex = "-1" href="#">Innstillinger</a>
                </section>
            </section>

            <!-- For å kunne lukke hamburgermenyen ved å kun trykke på et sted i vinduet må lukkHamburgerMeny() funksjonen ligge i deler av HTML-koden -->
            <!-- Kan ikke legge denne direkte i body -->
            <header onclick="lukkHamburgerMeny()">
                <!-- Logoen midten øverst på siden, med tittel -->
                <img src="bilder/klimate.png" alt="Klimate logo" class="Logo_forside">
            </header>

            <main onclick="lukkHamburgerMeny()">
                <!-- Form brukes til autentisering av bruker, bruker type="password" for å ikke vise innholdet brukeren skriver -->
                <form method="POST" action="glemt_passord.php" class="innloggForm">
                    <section class="inputBoks">
                        <img class="icon" src="bilder/brukerIkon.png" alt="Brukerikon"> <!-- Ikonet for bruker -->
                        <input type="text" class="RegInnFelt" name="brukernavn" value="" placeholder="Skriv inn ditt brukernavn" autofocus>
                    </section>
                    <section class="inputBoks">
                        <img class="icon" src="bilder/emailIkon.png" alt="Emailikon"> <!-- Ikonet for epost -->
                        <input type="email" class="RegInnFelt" name="epost" value="" placeholder="Skriv inn din epost">
                    </section>
                    <section class="inputBoks">
                        <img class="icon" src="bilder/pwIkon.png" alt="Passordikon"> <!-- Ikonet for passord -->
                        <input type="password" class="RegInnFelt" name="passord" value="" placeholder="Skriv inn nytt passord">
                    </section>
                    <section class="inputBoks">
                        <img class="icon" src="bilder/pwIkon.png" alt="Passordikon"> <!-- Ikonet for passord -->
                        <input type="password" class="RegInnFelt" name="passord2" value="" placeholder="Gjenta passord">
                    </section>
                    <!-- Meldinger til bruker -->
                    <?php if(isset($_GET['error']) && $_GET['error'] == 1){ ?>
                        <p id="mldFEIL">Du kan bare endre passord til en eksistererende bruker</p>    
                    
                    <?php } else if(isset($_GET['error']) && $_GET['error'] == 2){ ?>
                        <p id="mldFEIL">Passordene er ikke like</p>
                    
                    <?php } else if(isset($_GET['error']) && $_GET['error'] == 3){ ?>
                        <p id="mldFEIL">Skriv inn et passord</p>
                    
                    <?php } else if(isset($_GET['error']) && $_GET['error'] == 4) { ?>
                        <p id="mldFEIL">Passord må være 8 tegn i lengden og inneholde en liten bokstav, en stor bokstav og ett tall</p>

                    <?php } else if(isset($_GET['error']) && $_GET['error'] == 5){ ?>
                        <p id="mldFEIL">Passordet kan maksimalt være 45 tegn i lengden</p>
                    
                    <?php } else if(isset($_GET['error']) && $_GET['error'] == 6) { ?>
                        <p id="mldFEIL">Vennligst fyll ut alle feltene</p>
                    <?php } ?>

                    <input type="submit" name="glemtPassord" class="RegInnFelt_knappLogginn" value="Endre passord">   
                </form>

                <!-- Sender brukeren tilbake til forsiden -->
                <button onClick="location.href='logginn.php'" class="lenke_knapp">Tilbake til logg inn</button>

            </main>

            <!-- Knapp som vises når du har scrollet i vinduet, tar deg tilbake til toppen -->
            <button onclick="topFunction()" id="toppKnapp" title="Toppen"><img src="bilder/pilopp.png" alt="Tilbake til toppen"></button>

            <!-- Footer, epost er for øyeblikket på en catch-all, videresendes til RK -->
            <footer>
                <p class=footer_beskrivelse>&copy; Klimate 2019 | <a href="mailto:kontakt@klimate.no">Kontakt oss</a></p>
            </footer>
        </article>
    </body>

    <!-- Denne siden er utviklet av Aron Snekkestad og Robin Kleppang, siste gang endret 09.12.2019 -->
    <!-- Denne siden er kontrollert av Robin Kleppang, siste gang 09.12.2019 -->

</html>