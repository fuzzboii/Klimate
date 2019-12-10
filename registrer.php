<?php
session_start();
// Ved adminside IF ($_SESSION['bruker'] and $_SESSION['brukertype'] == 1) {}
// Sjekker om bruker er i en gyldig session, sender tilbake til hovedsiden hvis så
if (isset($_SESSION['brukernavn'])) {
    header("Location: default.php?error=2");
}

// Forsøker å koble til database

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

if (isset($_POST['subRegistrering'])) {
    // Tester på om passordene er like
    if ($_POST['passord'] == $_POST['passord2']) {
        // Tester på om passordet er mindre eller lik 45 tegn
        if (strlen($_POST['passord']) <= 45) {
            // Tester på om bruker har fyllt ut alle de obligatoriske feltene
            if ($_POST['brukernavn'] != "" && $_POST['fornavn'] != "" && $_POST['etternavn'] != "" && $_POST['epost'] != "") {
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
                            $fn = $_POST['fornavn'];
                            $en = $_POST['etternavn'];
                            $epost = $_POST['epost'];

                            // Salter passorder
                            $kombinert = $salt . $pw;
                            // Krypterer saltet passord
                            $spw = sha1($kombinert);
                            $sql = "insert into bruker(brukernavn, passord, fnavn, enavn, epost, brukertype) VALUES('" . $br . "', '" . $spw . "', '" . $fn . "', '" . $en . "', '" . $epost . "', 3)";


                            // Prepared statement for å beskytte mot SQL injection
                            $stmt = $db->prepare($sql);

                            $vellykket = $stmt->execute(); 
                            
                            // Alt gikk OK, sender til logginn med melding til bruker
                            if ($vellykket) {
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
                // Feilmelding 7, bruker har ikke skrevet noe i ett av de obligatoriske feltene
                header("location: registrer.php?error=7");
            }
        } else {
            // Feilmelding 6, passord for langt
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
                    <a id = "menytab1" tabIndex = "-1" href="#">Arrangementer</a>
                    <a id = "menytab2" tabIndex = "-1" href="#">Artikler</a>
                    <a id = "menytab3" tabIndex = "-1" href="#">Diskusjoner</a>
                </section>
            </section>

            <!-- For å kunne lukke hamburgermenyen ved å kun trykke på et sted i vinduet må lukkHamburgerMeny() funksjonen ligge i deler av HTML-koden -->
            <!-- Kan ikke legge denne direkte i body -->
            <header onclick="lukkHamburgerMeny()">
                <!-- Logoen midten øverst på siden, med tittel -->
                <img src="bilder/klimate.png" alt="Klimate logo" class="Logo_forside">
            </header>

            <main onclick="lukkHamburgerMeny()">
                <!-- Formen som bruker til registrering av bruker, mulighet for å vise passord til bruker om de er usikre -->
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
                        <img class="icon" src="bilder/fnenIkon.png" alt="Etternavnikon"> <!-- Ikonet for etternavn -->
                        <input type="etternavn" class="RegInnFelt" name="etternavn" value="" placeholder="Skriv inn etternavn">
                    </section>
                    <section class="inputBoks">
                        <img class="icon" src="bilder/emailIkon.png" alt="Epostikon"> <!-- Ikonet for epostadresse -->
                        <input type="email" class="RegInnFelt" name="epost" value="" placeholder="Skriv inn e-postadresse">
                    </section>
                    <section class="inputBoks">
                        <img class="icon" src="bilder/pwIkon.png" alt="Passordikon"> <!-- Ikonet for passord -->
                        <input type="password" class="RegInnFeltPW" name="passord" value="" placeholder="Skriv inn passord">
                    </section>
                    <section class="inputBoks">
                        <img class="icon" src="bilder/pwIkon.png" alt="Passordikon"> <!-- Ikonet for passord -->
                        <input type="password" class="RegInnFeltPW" name="passord2" value="" placeholder="Bekreft passord">
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
                        <p id="mldFEIL">Passordet er for langt, du kan maksimalt ha 45 tegn</p>

                    <?php } else if(isset($_GET['error']) && $_GET['error'] == 7) { ?>
                        <p id="mldFEIL">Vennligst fyll ut alle feltene</p>
                    <?php } ?>      

                    <input type="submit" name="subRegistrering" class="RegInnFelt_knappRegistrer" value="Registrer ny bruker">
                </form>

                <!-- Sender brukeren tilbake til forsiden -->
                <button onClick="location.href='default.php'" name="submit" class="lenke_knapp">Tilbake til forside</button>
                
            </main>

            <!-- Knapp som vises når du har scrollet i vinduet, tar deg tilbake til toppen -->
            <button onclick="topFunction()" id="toppKnapp" title="Toppen"><img src="bilder/pilopp.png" alt="Tilbake til toppen"></button>
            
            <!-- Footer, epost er for øyeblikket på en catch-all, videresendes til RK -->
            <footer>
                <p class=footer_beskrivelse>&copy; Klimate 2019 | <a href="mailto:kontakt@klimate.no">Kontakt oss</a></p>
            </footer>
        </article>
    </body>

    <!-- Denne siden er utviklet av Robin Kleppang, siste gang endret 07.12.2019 -->
    <!-- Denne siden er kontrollert av Glenn Petter Pettersen, siste gang 09.12.2019 -->

</html>