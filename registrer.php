<?php
session_start();

//-------------------------------//
// Innstillinger, faste variable //
//-------------------------------//
include("inkluderes/innstillinger.php");


// Sjekker om bruker er i en gyldig session, sender tilbake til hovedsiden hvis så
if (isset($_SESSION['idbruker'])) {
    header("Location: default.php?error=2");
}

// Enkel test som gjør det mulig å beholde brukerinput etter siden er lastet på nytt (Form submit)
$input_brukernavn = "";
$input_epost = "";
if (isset($_SESSION['input_brukernavn'])) {
    $input_brukernavn = $_SESSION['input_brukernavn'];
    $input_epost = $_SESSION['input_epost'];
    unset($_SESSION['input_brukernavn']);
    unset($_SESSION['input_epost']);
}

if (isset($_POST['subRegistrering'])) {
    $_SESSION['input_brukernavn'] = $_POST['brukernavn'];
    $_SESSION['input_epost'] = $_POST['epost'];
    // Tester på om passordene er like
    if ($_POST['passord'] == $_POST['passord2']) {
        // Tester på om bruker har fyllt ut alle de obligatoriske feltene
        if ($_POST['brukernavn'] != "" && $_POST['epost'] != "") {
            // Tester på om en gyldig epost ("NAVN@NAVN.DOMENE") er oppgitt
            if (filter_var($_POST["epost"], FILTER_VALIDATE_EMAIL)) {
                try {

                    $br = $_POST['brukernavn'];
                    $pw = $_POST['passord'];

                    // Validering av passordstyrke, server validering
                    $storebokstaver = preg_match('@[A-Z]@', $pw);
                    $smaabokstaver = preg_match('@[a-z]@', $pw);
                    $nummer = preg_match('@[0-9]@', $pw);
                    // Denne er for spesielle symboler, ikke i bruk for øyeblikket
                    // $spesielleB = preg_match('@[^\w]@', $pw);

                    if ($pw == "") {
                        // Ikke noe passord skrevet
                        header("Location: registrer.php?error=3");
                    } else if (!$storebokstaver || !$smaabokstaver || !$nummer /*|| !$spesielleB*/ || strlen($pw) < 8) {
                        // Ikke tilstrekkelig passord skrevet
                        header("Location: registrer.php?error=4");
                    } else {
                        // Sjekker om brukernavn er opptatt (Brukes så lenge brukernavn ikke er satt til UNIQUE i db)
                        $lbr = strtolower($_POST['brukernavn']);
                        $sjekkbnavn = "select lower(brukernavn) as brukernavn from bruker where lower(brukernavn)='" . $lbr . "'";
                        $sjekket = $db->prepare($sjekkbnavn);
                        $sjekket->execute();
                        $testbnavn = $sjekket->fetch(PDO::FETCH_ASSOC);

                        // Hvis resultatet over er likt det bruker har oppgitt som brukernavn stopper vi og advarer bruker om at brukernavnet er allerede tatt
                        if ($testbnavn['brukernavn'] != $lbr) {
                            // OK, vi forsøker å registrere bruker
                            $epost = $_POST['epost'];

                            // Salter passorder
                            $kombinert = $salt . $pw;
                            // Krypterer saltet passord
                            $spw = sha1($kombinert);
                            $sql = "insert into bruker(brukernavn, passord, epost, brukertype) VALUES('" . $br . "', '" . $spw . "', '" . $epost . "', 3)";


                            // Prepared statement for å beskytte mot SQL injection
                            $stmt = $db->prepare($sql);

                            $vellykket = $stmt->execute(); 
                            
                            // Alt gikk OK, sender til logginn med melding til bruker
                            if ($vellykket) {
                                // Fjerner session variable for brukerinput om ingen feil oppstår
                                unset($_SESSION['input_brukernavn']);
                                unset($_SESSION['input_epost']);

                                // Sjekker på om bruker har registrert preferanser
                                $sjekkPrefQ = "select idpreferanse from preferanse where bruker = " . $_SESSION['idbruker'];
                                $sjekkPrefSTMT = $db->prepare($sjekkPrefQ);
                                $sjekkPrefSTMT->execute();
                                $resPref = $sjekkPrefSTMT->fetch(PDO::FETCH_ASSOC); 

                                // Bruker har ikke preferanser, oppretter de
                                // Variabelen $personvern kommer fra innstillinger
                                if(!$resPref) {
                                    $opprettPrefQ = "insert into preferanse(visfnavn, visenavn, visepost, visinteresser, visbeskrivelse, vistelefonnummer, bruker) values('" . 
                                                        $personvern[0] . "', '" . $personvern[1] . "', '" . $personvern[2] . "', '" . $personvern[3] . "', '" . $personvern[4] . "', '" . $personvern[5] . "', " .
                                                            $_SESSION['idbruker'] . ")";

                                    $opprettPrefSTMT = $db->prepare($opprettPrefQ);
                                    $opprettPrefSTMT->execute();
                                }

                                header("location: logginn.php?vellykket=1");
                            }
                        } else {
                            // Brukernavnet er tatt
                            header("location: registrer.php?error=1");
                        }
                    }
                }
                catch (PDOException $ex) {
                    if ($ex->getCode() == 23000) {
                        // 23000, Duplikat, tenkes brukt til brukernavn da det ønskes å være satt UNIQUE i db
                        header("location: registrer.php?error=1");
                    } else if ($ex->getCode() == '42S22') {
                        // 42S22, Kolonne eksisterer ikke
                        header("location: registrer.php?error=5");
                    }
                } 
            } else {
                // Feilmelding 7, bruker har oppgitt en ugyldig epost
                header("location: registrer.php?error=7");
            }
        } else {
            // Feilmelding 6, bruker har ikke skrevet noe i ett av de obligatoriske feltene
            header("location: registrer.php?error=6");
        }
    } else {
        // Feilmelding 2 = passord ikke like
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
        <title>Registrering</title>
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
                <a class="bildeKontroll" href="javascript:void(0)" onclick="hamburgerMeny()" tabindex="5">
                    <img src="bilder/hamburgerIkon.svg" alt="Hamburger-menyen" class="hamburgerKnapp">
                </a>
                <!-- Legger til en knapp for å gå fra registrering til innlogging -->
                <button class="singelKnapp" onClick="location.href='logginn.php'" tabindex="4">LOGG INN</button>
                
                <form id="sokForm_navmeny" action="sok.php">
                    <input id="sokBtn_navmeny" type="submit" value="Søk" tabindex="3">
                    <input id="sokInp_navmeny" type="text" name="artTittel" placeholder="Søk på artikkel" tabindex="2">
                </form>
                <a href="javascript:void(0)" onClick="location.href='sok.php'" tabindex="-1">
                    <img src="bilder/sokIkon.png" alt="Søkeikon" class="sok_navmeny" tabindex="2">
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
                <!-- -1 tabIndex som standard da menyen er lukket -->
                <section class="hamburgerInnhold">
                    <a class = "menytab" tabIndex = "-1" href="arrangement.php">Arrangementer</a>
                    <a class = "menytab" tabIndex = "-1" href="artikkel.php">Artikler</a>
                    <a class = "menytab" tabIndex = "-1" href="sok.php">Avansert Søk</a>
                </section>
            </section>

            <main id="toppMain" onclick="lukkHamburgerMeny()">
                <!-- Formen som bruker til registrering av bruker, mulighet for å vise passord til bruker om de er usikre -->
                <form method="POST" action="registrer.php" class="innloggForm">
                    <section class="inputBoks">
                        <img class="icon" src="bilder/brukerIkon.png" alt="Brukerikon"> <!-- Ikonet for bruker -->
                        <input type="text" class="RegInnFelt" name="brukernavn" value="<?php echo($input_brukernavn) ?>" placeholder="Skriv inn brukernavn" required title="Skriv inn ett brukernavn" autofocus>
                    </section>
                    <section class="inputBoks">
                        <img class="icon" src="bilder/emailIkon.png" alt="Epostikon"> <!-- Ikonet for epostadresse -->
                        <input type="email" class="RegInnFelt" name="epost" value="<?php echo($input_epost) ?>" placeholder="Skriv inn e-postadresse" required title="Skriv inn en gyldig epostadresse">
                    </section>
                    <section class="inputBoks">
                        <img class="icon" src="bilder/pwIkon.png" alt="Passordikon"> <!-- Ikonet for passord -->
                        <input type="password" class="RegInnFeltPW" name="passord" value="" placeholder="Skriv inn passord" required pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" title="Minimum 8 tegn, 1 liten og 1 stor bokstav">
                    </section>
                    <section class="inputBoks">
                        <img class="icon" src="bilder/pwIkon.png" alt="Passordikon"> <!-- Ikonet for passord -->
                        <input type="password" class="RegInnFeltPW" name="passord2" value="" placeholder="Bekreft passord" required pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" title="Minimum 8 tegn, 1 liten og 1 stor bokstav">
                    </section>
                    <input style="margin-bottom: 1em;" type="checkbox" onclick="visPassordReg()">Vis passord</input>

                    <!-- Håndtering av feilmeldinger -->
                    <?php if(isset($_GET['error']) && $_GET['error'] == 1){ ?>
                        <p id="mldFEIL">Brukernavnet eksisterer fra før</p>    

                    <?php } else if(isset($_GET['error']) && $_GET['error'] == 2) { ?>
                        <p id="mldFEIL">Passordene er ikke like</p>

                    <?php } else if(isset($_GET['error']) && $_GET['error'] == 3) { ?>
                        <p id="mldFEIL">Skriv inn ett passord</p>

                    <?php } else if(isset($_GET['error']) && $_GET['error'] == 4) { ?>
                        <p id="mldFEIL">Passord må være 8 tegn i lengden og inneholde en liten bokstav, en stor bokstav og ett tall</p>

                    <?php } else if(isset($_GET['error']) && $_GET['error'] == 5) { ?>
                        <p id="mldFEIL">Bruker kunne ikke opprettes grunnet systemfeil, vennligst prøv igjen om kort tid</p>

                    <?php } else if(isset($_GET['error']) && $_GET['error'] == 6) { ?>
                        <p id="mldFEIL">Vennligst fyll ut alle feltene</p>

                    <?php } else if(isset($_GET['error']) && $_GET['error'] == 7) { ?>
                        <p id="mldFEIL">Epost oppgitt er ikke gyldig</p>
                    <?php } ?>    

                    <input type="submit" name="subRegistrering" class="RegInnFelt_knappRegistrer" value="Registrer ny bruker">
                </form>

                <!-- Sender brukeren tilbake til forsiden -->
                <button onClick="location.href='default.php'" name="submit" class="lenke_knapp">Tilbake til forside</button>
                
            </main>

            <!-- Knapp som vises når du har scrollet i vinduet, tar deg tilbake til toppen -->
            <button onclick="tilbakeTilTopp()" id="toppKnapp" title="Toppen"><img src="bilder/pilopp.png" alt="Tilbake til toppen"></button>
            
            <!-- Footer, epost er for øyeblikket på en catch-all, videresendes til RK -->
            <footer>
                <p class=footer_beskrivelse>&copy; Klimate <?php echo date("Y");?> | <a href="mailto:kontakt@klimate.no">Kontakt oss</a></p>
            </footer>
        </article>
    </body>

    <!-- Denne siden er utviklet av Robin Kleppang, siste gang endret 07.02.2020 -->
    <!-- Denne siden er kontrollert av Robin Kleppang, siste gang 07.02.2020 -->

</html>