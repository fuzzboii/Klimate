<?php
session_start();

//-------------------------------//
// Innstillinger, faste variable //
//-------------------------------//
include("inkluderes/innstillinger.php");



// Forsikrer seg om kun tilgang for administrator
if (!isset($_SESSION['idbruker'])) {
    header("Location: default.php?error=1");
} else if ($_SESSION['brukertype'] != '1') {
    header("Location: default.php?error=4");
}
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
                    if($_POST['brukertype'] != "4") {
                        $btype = $_POST['brukertype'];
                    } else {
                        $btype = "3";
                    }

                    // Validering av passordstyrke, server validering
                    $storebokstaver = preg_match('@[A-Z]@', $pw);
                    $smaabokstaver = preg_match('@[a-z]@', $pw);
                    $nummer = preg_match('@[0-9]@', $pw);
                    // Denne er for spesielle symboler, ikke i bruk for øyeblikket
                    // $spesielleB = preg_match('@[^\w]@', $pw);

                    if ($pw == "") {
                        // Ikke noe passord skrevet
                        header("Location: administrator.php?error=3");
                    } else if (!$storebokstaver || !$smaabokstaver || !$nummer /*|| !$spesielleB*/ || strlen($pw) < 8) {
                        // Ikke tilstrekkelig passord skrevet
                        header("Location: administrator.php?error=4");
                    } else {
                        // Sjekker om brukernavn er opptatt (Brukes så lenge brukernavn ikke er satt til UNIQUE i db)
                        $lbr = strtolower($_POST['brukernavn']);
                        $sjekkbnavn = "select lower(brukernavn) as brukernavn from bruker where lower(brukernavn)='" . $lbr . "'";
                        $sjekket = $db->prepare($sjekkbnavn);
                        $sjekket->execute();
                        $testbnavn = $sjekket->fetch(PDO::FETCH_ASSOC);

                        // Hvis resultatet over er likt det bruker har oppgitt som brukernavn stopper vi og advarer bruker om at brukernavnet er allerede tatt
                        if (!isset($testbnavn['brukernavn']) || $testbnavn['brukernavn'] != $lbr) {
                            // OK, vi forsøker å registrere bruker
                            $epost = $_POST['epost'];

                            // Salter passorder
                            $kombinert = $salt . $pw;
                            // Krypterer saltet passord
                            $spw = sha1($kombinert);
                            $sql = "insert into bruker(brukernavn, passord, epost, brukertype) VALUES('" . $br . "', '" . $spw . "', '" . $epost . "', $btype)";


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

                                header("location: administrator.php?vellykket=1");
                            }
                        } else {
                            // Brukernavnet er tatt
                            header("location: administrator.php?error=1");
                        }
                    }
                }
                catch (PDOException $ex) {
                    if ($ex->getCode() == 23000) {
                        // 23000, Duplikat, tenkes brukt til brukernavn da det ønskes å være satt UNIQUE i db
                        header("location: administrator.php?error=1");
                    } else if ($ex->getCode() == '42S22') {
                        // 42S22, Kolonne eksisterer ikke
                        header("location: administrator.php?error=5");
                    }
                } 
            } else {
                // Feilmelding 7, bruker har oppgitt en ugyldig epost
                header("location: administrator.php?error=7");
            }
        } else {
            // Feilmelding 6, bruker har ikke skrevet noe i ett av de obligatoriske feltene
            header("location: administrator.php?error=6");
        }
    } else {
        // Feilmelding 2 = passord ikke like
        header("location: administrator.php?error=2");
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
        <title>Administrator innstillinger</title>
        <!-- Henter inn ekstern stylesheet -->
        <link rel="stylesheet" type="text/css" href="stylesheet.css">
        <!-- Henter inn favicon, bildet som dukker opp i fanene i nettleseren -->
        <link rel='icon' href='bilder/favicon.png' type='image/x-icon'>
        <!-- Henter inn JavaScript -->
        <script language="JavaScript" src="javascript.js"> </script>
    </head>

    <body id="admin_body" onclick="lukkMelding('mldFEIL_boks')">
        <?php include("inkluderes/navmeny.php") ?>

        <!-- For å kunne lukke hamburgermenyen ved å kun trykke på et sted i vinduet må lukkHamburgerMeny() funksjonen ligge i deler av HTML-koden -->
        <!-- Kan ikke legge denne direkte i body -->
        <header class="adminMain" onclick="lukkHamburgerMeny()">
            <!-- Overskrift på siden -->
            <h1 id="admin_overskrift">Administrator innstillinger</h1>
            <h2 id="admin_underskrift">Opprett en bruker</h2>
        </header>
            <main onclick="lukkHamburgerMeny()">
                <!-- Formen som bruker til registrering av bruker, mulighet for å vise passord -->
                <form method="POST" action="administrator.php" class="innloggForm">
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
                    <section>
                        <select id="brukertypeValg" name="brukertype">
                            <option value="3">Ordinær bruker</option>
                            <option value="2">Redaktør</option>
                            <option value="1">Administrator</option>
                        </select>
                    </section>
                    <input style="margin-bottom: 1em; margin-top: 1em;" type="checkbox" onclick="visPassordReg()">Vis passord</input>

                    <!-- Håndtering av feilmeldinger -->

                    <?php if (isset($_GET['error']) && $_GET['error'] >= 1 && $_GET['error'] <= 7) { ?>
                        <section id="mldFEIL_boks">
                            <section id="mldFEIL_innhold">
                                <?php if($_GET['error'] == 1){ ?>
                                    <p id="mldFEIL">Brukernavnet eksisterer fra før</p>    

                                <?php } else if($_GET['error'] == 2) { ?>
                                    <p id="mldFEIL">Passordene er ikke like</p>

                                <?php } else if($_GET['error'] == 3) { ?>
                                    <p id="mldFEIL">Skriv inn ett passord</p>

                                <?php } else if($_GET['error'] == 4) { ?>
                                    <p id="mldFEIL">Passord må være 8 tegn i lengden og inneholde en liten bokstav, en stor bokstav og ett tall</p>

                                <?php } else if($_GET['error'] == 5) { ?>
                                    <p id="mldFEIL">Bruker kunne ikke opprettes grunnet systemfeil, vennligst prøv igjen om kort tid</p>

                                <?php } else if($_GET['error'] == 6) { ?>
                                    <p id="mldFEIL">Vennligst fyll ut alle feltene</p>

                                <?php } else if($_GET['error'] == 7) { ?>
                                    <p id="mldFEIL">Epost oppgitt er ikke gyldig</p>
                                <?php } ?>
                                <!-- Denne gjør ikke noe, men er ikke utelukkende åpenbart at man kan trykke hvor som helst -->
                                <button id="mldFEIL_knapp">Lukk</button>
                            </section>  
                        </section>
                    <?php } else if(isset($_GET['vellykket']) && $_GET['vellykket'] == 1) { ?>
                        <p id="mldOK">Brukeren er opprettet</p>
                    <?php } ?>
                    


                    <input type="submit" name="subRegistrering" class="RegInnFelt_knappRegistrer" value="Legg til brukeren">
                </form>

                <!-- Sender brukeren tilbake til forsiden -->
                <button onClick="location.href='default.php'" name="submit" class="lenke_knapp">Tilbake til forside</button>
                
            </main>
        
        <?php include("inkluderes/footer.php") ?>
    

    </body>
</html>

    <!-- Denne siden er utviklet av Glenn Petter Pettersen, siste gang endret 06.03.2020 -->
    <!-- Denne siden er kontrollert av Aron Snekkestad, siste gang 06.03.2020 -->