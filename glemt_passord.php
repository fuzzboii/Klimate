<?php
session_start();

//------------------------------//
// Innstillinger, faste variable //
//------------------------------//
include("innstillinger.php");


// Sjekker om bruker er i en gyldig session, sender tilbake til hovedsiden hvis så
if (isset($_SESSION['idbruker'])) {
    header("Location: default.php?error=2");
}


// Hoveddelen for glemt passord
if (isset($_POST['glemtPassord'])) {
    // Tester på om passordene er like
    if ($_POST['passord'] == $_POST['passord2']) {
        // Tester på om brukernavnet er fyllt ut
        if ($_POST['brukernavn'] != "") {
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
                $spw = sha1($kombinert);
                $lbr = strtolower($_POST['brukernavn']);
                $sql = "update bruker set passord='" . $spw . "' where lower(brukernavn)='". $lbr . "'";


                // Prepared statement for å beskytte mot SQL injection
                $stmt = $db->prepare($sql);

                $stmt->execute();

                // Ved update blir antall rader endret returnert, vi kan utnytte dette til å teste om noen endringer faktisk skjedde
                $antall = $stmt->rowCount();

                if (!$antall == "0") {
                    // Alt gikk OK, sender til logginn med melding til bruker
                    header("location: logginn.php?vellykket=2");
                } else {
                    //Ikke ok, ber bruker om å oppgi brukernavn på nytt
                    header("location: glemt_passord.php?error=1");
                }
            }
        } else {
            // Feilmelding 5, bruker har ikke fyllt ut felt
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
                
                <form id="sokForm_navmeny" action="sok.php">
                    <input id="sokBtn_navmeny" type="submit" value="Søk" tabindex="3">
                    <input id="sokInp_navmeny" type="text" name="artTittel" placeholder="Søk på artikkel" tabindex="2">
                </form>
                <a href="javascript:void(0)" onClick="location.href='sok.php'">
                    <img src="bilder/sokIkon.png" alt="Søkeikon" class="sok_navmeny">
                </a>
                <!-- Logoen øverst i venstre hjørne -->
                <a href="default.php" tabindex="1">
                    <img class="Logo_navmeny" src="bilder/klimateNoText.png" alt="Klimate logo">
                </a>
            <!-- Slutt på navigasjonsmeny-->
            </nav>

            <!-- Gardinmenyen, denne går over alt annet innhold ved bruk av z-index -->
            <section id="navMeny" class="hamburgerMeny">

                <!-- innholdet i hamburger-menyen -->
                <!-- -1 tabIndex som standard, man tabber ikke inn i menyen når den er lukket -->
                <section class="hamburgerInnhold">
                    <a class = "menytab" tabIndex = "-1" href="arrangement.php">Arrangementer</a>
                    <a class = "menytab" tabIndex = "-1" href="artikkel.php">Artikler</a>
                    <a class = "menytab" tabIndex = "-1" href="#">Diskusjoner</a>
                    <a class = "menytab" tabIndex = "-1" href="sok.php">Avansert Søk</a>
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
                        <input type="text" class="RegInnFelt" name="brukernavn" value="" placeholder="Skriv inn ditt brukernavn" required autofocus>
                    </section>
                    <section class="inputBoks">
                        <img class="icon" src="bilder/pwIkon.png" alt="Passordikon"> <!-- Ikonet for passord -->
                        <input type="password" class="RegInnFelt" name="passord" value="" placeholder="Skriv inn nytt passord" required>
                    </section>
                    <section class="inputBoks">
                        <img class="icon" src="bilder/pwIkon.png" alt="Passordikon"> <!-- Ikonet for passord -->
                        <input type="password" class="RegInnFelt" name="passord2" value="" placeholder="Gjenta passord" required>
                    </section>
                    <input type="submit" name="glemtPassord" class="RegInnFelt_knappLogginn" value="Endre passord"> 
                    <!-- Meldinger til bruker -->
                    <?php if(isset($_GET['error']) && $_GET['error'] == 1){ ?>
                        <p id="mldFEIL">Du kan bare endre passord til en eksistererende bruker</p>    
                    
                    <?php } else if(isset($_GET['error']) && $_GET['error'] == 2){ ?>
                        <p id="mldFEIL">Passordene er ikke like</p>
                    
                    <?php } else if(isset($_GET['error']) && $_GET['error'] == 3){ ?>
                        <p id="mldFEIL">Skriv inn et passord</p>
                    
                    <?php } else if(isset($_GET['error']) && $_GET['error'] == 4) { ?>
                        <p id="mldFEIL">Passord må være 8 tegn i lengden og inneholde en liten bokstav, en stor bokstav og ett tall</p>
                    
                    <?php } else if(isset($_GET['error']) && $_GET['error'] == 5) { ?>
                        <p id="mldFEIL">Vennligst fyll ut alle feltene</p>
                    <?php } ?>  
                </form>

                <!-- Sender brukeren tilbake til forsiden -->
                <button onClick="location.href='logginn.php'" class="lenke_knapp">Tilbake til logg inn</button>

            </main>

            <!-- Knapp som vises når du har scrollet i vinduet, tar deg tilbake til toppen -->
            <button onclick="tilbakeTilTopp()" id="toppKnapp" title="Toppen"><img src="bilder/pilopp.png" alt="Tilbake til toppen"></button>

            <!-- Footer, epost er for øyeblikket på en catch-all, videresendes til RK -->
            <footer>
                <p class=footer_beskrivelse>&copy; Klimate 2020 | <a href="mailto:kontakt@klimate.no">Kontakt oss</a></p>
            </footer>
        </article>
    </body>

    <!-- Denne siden er utviklet av Aron Snekkestad og Robin Kleppang, siste gang endret 07.02.2020 -->
    <!-- Denne siden er kontrollert av Robin Kleppang, siste gang 07.02.2020 -->

</html>