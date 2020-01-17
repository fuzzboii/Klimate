<?php
session_start();
// Ved adminside IF ($_SESSION['bruker'] and $_SESSION['brukertype'] == 1) {}
if ($_SESSION['brukernavn']) {
    // OK
} else {
    header("Location: default.php?error=1");
}


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

// Setter så PDO kaster ut feilmelding og stopper funksjonen ved database-feil (PDOException)
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Hoveddelen for redigering av konto
if (isset($_POST['subEndring'])) {
    // Boolske verdier vi tester på for å vite om noe er endret på
    $oppdatertBr = false;
    $oppdatertPw = false;
    
    try {
        // Del for oppdatering av brukernavn, epost, fornavn og/eller etternavn
        if ($_POST['nyttbrukernavn'] != "" || $_POST['nyepost'] != "" || $_POST['nyttfornavn'] != "" || $_POST['nyttetternavn'] != "") {

            // Da vet vi at bruker vil oppdatere en av verdiene over, sjekker individuelt
            if ($_POST['nyttbrukernavn'] == "") {
                // Bruker har valgt å ikke oppdatere brukernavn
                $nyttBrukernavn = $_SESSION['brukernavn'];
            } else {
                $nyttBrukernavn = $_POST['nyttbrukernavn'];
            }
        
            if ($_POST['nyepost'] == "") {
                // Bruker har valgt å ikke oppdatere epost
                $nyEpost = $_SESSION['epost'];
            } else {
                $nyEpost = $_POST['nyepost'];
            }
        
            if ($_POST['nyttfornavn'] == "") {
                // Bruker har valgt å ikke oppdatere fornavn
                $nyttFornavn = $_SESSION['fornavn'];
            } else {
                $nyttFornavn = $_POST['nyttfornavn'];
            }
        
            if ($_POST['nyttetternavn'] == "") {
                // Bruker har valgt å ikke oppdatere etternavn
                $nyttEtternavn = $_SESSION['etternavn'];
            } else {
                $nyttEtternavn = $_POST['nyttetternavn'];
            }
            // SQL script som oppdaterer info. Med testing over vil ikke informasjon som bruker ikke vil endre faktisk endres
            $oppdaterBruker = "update bruker set brukernavn = '" . $nyttBrukernavn . "', fnavn = '" . $nyttFornavn . "', enavn = '" . $nyttEtternavn . "', epost = '" . $nyEpost . "'  where idbruker='". $_SESSION['idbruker'] . "'";
            $stmt = $db->prepare($oppdaterBruker);
            $stmt->execute();

            // Ved update blir antall rader endret returnert, vi kan utnytte dette til å teste om noen endringer faktisk skjedde
            $antall = $stmt->rowCount();

            if (!$antall == "0") {
                // Oppdaterer session-info
                $_SESSION['brukernavn'] = $nyttBrukernavn;
                $_SESSION['fornavn'] = $nyttFornavn;
                $_SESSION['etternavn'] = $nyttEtternavn;
                $_SESSION['epost'] = $nyEpost;
                $oppdatertBr = true;
            }
        } 

        // Del for oppdatering av passord, sjekker om begge passordene er like, og om bruker faktisk har skrevet noe
        if ($_POST['nyttpassord'] != "") {
            if ($_POST['nyttpassord'] == $_POST['bekreftnyttpassord']) {
                // Saltet
                $salt = "IT2_2020"; 

                $lbr = strtolower($_SESSION['brukernavn']);
                $pw = $_POST['gammeltpassord'];
                $kombinert = $salt . $pw;
                // Krypterer det saltede passordet
                $spw = sha1($kombinert);

                $sjekkGammelt = "select * from bruker where lower(brukernavn)='" . $lbr . "' and passord='" . $spw . "'";
                // Prepared statement for å beskytte mot SQL injection
                $stmt = $db->prepare($sjekkGammelt);

                $stmt->execute();

                $resultat = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($resultat['brukernavn'] == $_SESSION['brukernavn'] && $resultat['passord'] == $spw) {
                    // Validering av passordstyrke
                    // Kilde: https://www.codexworld.com/how-to/validate-password-strength-in-php/
                    $storebokstaver = preg_match('@[A-Z]@', $_POST['nyttpassord']);
                    $smaabokstaver = preg_match('@[a-z]@', $_POST['nyttpassord']);
                    $nummer = preg_match('@[0-9]@', $_POST['nyttpassord']);
                    // Denne er for spesielle symboler, ikke i bruk for øyeblikket
                    // $spesielleB = preg_match('@[^\w]@', $pw);
                    if ($_POST['nyttpassord'] == "") {
                        // Ikke noe passord skrevet
                        header("Location: konto_rediger.php?error=2");
                    } else if (!$storebokstaver || !$smaabokstaver || !$nummer /*|| !$spesielleB*/ || strlen($pw) < 8) {
                        // Ikke tilstrekkelig passord skrevet
                        header("Location: konto_rediger.php?error=3");
                    } else {
                        // Passord er OK, vi fortsetter
                        $kombinert = $salt . $_POST['nyttpassord'];
                        $nyttPassord = sha1($kombinert);
            
                        $oppdaterBruker = "update bruker set passord = '" . $nyttPassord . "'  where brukernavn='". $_SESSION['brukernavn'] . "'";
            
                        $stmt = $db->prepare($oppdaterBruker);
                        $stmt->execute();

                        // Ved update blir antall rader endret returnert, vi kan utnytte dette til å teste om noen endringer faktisk skjedde
                        $antall = $stmt->rowCount();
                
                        if (!$antall == "0") {
                            $oppdatertPw = true;
                        }
                    }
                }
            } else {
                header("Location: konto_rediger.php?error=1");
            }
        }

        // Hvis vi har oppdatert brukerinfo eller passord, returner bruker til kontosiden, her ser vi oppdatert info direkte
        if ($oppdatertBr == true || $oppdatertPw == true) {
            header("location: konto.php?vellykket=1");
        } else {
            header("Location: konto.php?error=2");
        }
    } 
    catch (PDOException $ex) {
        if ($ex->getCode() == 23000) {
            // 23000, Duplikat brukernavn (Siden brukernavn er UNIQUE)
            header("location: konto_rediger.php?error=4");
        }
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
        <title>Endring av konto</title>
        <!-- Henter inn ekstern stylesheet -->
        <link rel="stylesheet" type="text/css" href="stylesheet.css">
        <!-- Henter inn favicon, bildet som dukker opp i fanene i nettleseren -->
        <link rel='icon' href='bilder/favicon.png' type='image/x-icon'>
        <!-- Henter inn JavaScript -->
        <script language="JavaScript" src="javascript.js"></script>
    </head>

    <body onload="kontoRullegardin()">
        <article class="innhold">    
            <!-- Begynnelse på øvre navigasjonsmeny -->
            <nav class="navTop">
                <!-- Legger til en knapp for å logge ut når man er innlogget-->
                <!-- Bruker et ikon som skal åpne gardinmenyen, henviser til funksjonen hamburgerMeny i javascript.js -->
                <a class="bildeKontroll" href="javascript:void(0)" onclick="hamburgerMeny()" tabindex="4">
                    <img src="bilder/hamburgerIkon.svg" alt="Hamburger-menyen" class="hamburgerKnapp">
                </a>
                <a class="bildeKontroll" href="javascript:void(0)" onClick="location.href='konto.php'" tabindex="3">
                    <img src="bilder/profil.png" alt="Profilbilde" class="profil_navmeny">
                </a>
                <!-- Legger til en knapp for å logge ut når man er innlogget -->
                <form method="POST" action="default.php">
                    <button name="loggUt" id="backendLoggUt" tabindex="2">LOGG UT</button>
                </form>
                <!-- Logoen øverst i venstre hjørne, denne leder alltid tilbake til default.php -->
                <a class="bildeKontroll" href="default.php" tabindex="1">
                    <img src="bilder/klimateNoText.png" alt="Klimate logo" class="Logo_navmeny">
                </a> 
            <!-- Slutt på navigasjonsmeny-->
            </nav>

            <!-- Gardinmenyen, denne går over alt annet innhold ved bruk av z-index -->
            <section id="navMeny" class="hamburgerMeny">

                <!-- innholdet i hamburger-menyen -->
                <section class="hamburgerInnhold">
                    <a id = "menytab1" tabIndex = "-1" href="#">Arrangementer</a>
                    <a id = "menytab2" tabIndex = "-1" href="#">Artikler</a>
                    <a id = "menytab3" tabIndex = "-1" href="#">Diskusjoner</a>
                    <a id = "menytab4" tabIndex = "-1" href="backend.php">Oversikt</a>
                    <a id = "menytab5" tabIndex = "-1" href="konto.php">Konto</a>
                </section>
            </section>

            <header class="konto_rediger_header" onclick="lukkHamburgerMeny()">
                <h1>Konto</h1>
            </header>


            <main id="konto_rediger_main" onclick="lukkHamburgerMeny()">
                <section class="brukerinformasjon_rediger"> 
                    <!-- Underoverskrift -->
                    <h2 class="redigerbruker_overskrift">Rediger brukeropplysninger</h2>
                    
                    <!-- Felt for brukeropplysning endringer -->
                    <form method="POST" action="konto_rediger.php" class="konto_rediger_Form">
                        <!-- Brukernavn -->
                        <section class="konto_rediger_inputBoks">
                            <h3 class="endre_brukernavn_overskrift">Endre brukernavn</h3>
                            <input type="text" class="KontoredigeringFelt" name="nyttbrukernavn" value="" placeholder="Nytt brukernavn" autofocus>
                        </section>
                        <!-- Epost -->
                        <section class="konto_rediger_inputBoks">
                            <h3 class="endre_epost_overskrift">Endre epost</h3>
                            <input type="email" class="KontoredigeringFelt" name="nyepost" value="" placeholder="Ny epost">
                        </section>     
                        <!-- Passord: gammelt, nytt, bekreft (Rullegardin) -->
                        <button type="button" id="kontoRullegardin" class="kontoRullegardin">Endre passord</button>
                        <section class="innholdRullegardin">
                            <section class="konto_rediger_inputBoks">
                                <h3 class="endre_gammeltpassord_overskrift">Gammelt passord</h3>
                                <input type="password" class="KontoredigeringFeltPW" name="gammeltpassord" value="" placeholder="Gammelt passord" autofocus>
                            </section>
                            <section class="konto_rediger_inputBoks">
                                <h3 class="endre_nyttpassord_overskrift">Nytt passord</h3>
                                <input type="password" class="KontoredigeringFeltPW" name="nyttpassord" value="" placeholder="Nytt passord">
                            </section>
                            <section class="konto_rediger_inputBoks">
                                <h3 class="endre_nyttpassordbekreft_overskrift">Bekreft nytt passord</h3>
                                <input type="password" class="KontoredigeringFeltPW" name="bekreftnyttpassord" value="" placeholder="Bekreft nytt passord">
                            </section>
                            <input style="margin-bottom: 1em;" type="checkbox" onclick="visPassordInst()">Vis passord</input>
                        </section>
                        <!-- Fornavn -->
                        <section class="konto_rediger_inputBoks">
                            <h3 class="endre_fornavn_overskrift">Endre fornavn</h3>
                            <input type="fornavn" class="KontoredigeringFelt" name="nyttfornavn" value="" placeholder="Nytt fornavn">
                        </section>
                        <!-- Etternavn -->
                        <section class="konto_rediger_inputBoks">
                            <h3 class="endre_etternavn_overskrift">Endre etternavn</h3>
                            <input type="etternavn" class="KontoredigeringFelt" name="nyttetternavn" value="" placeholder="Nytt etternavn">
                        </section>
                        
                        <!-- Knapp for å lagre endringer -->
                        <input type="submit" name="subEndring" class="KontoredigeringFelt_knappLagre" value="Lagre endringer">
                    </form>
                    <?php if(isset($_GET['error']) && $_GET['error'] == 1){ ?>
                        <p id="mldFEIL">Passordene er ikke like</p>
                    
                    <?php } else if(isset($_GET['error']) && $_GET['error'] == 2){ ?>
                        <p id="mldFEIL">Skriv inn et passord</p>
                    
                    <?php } else if(isset($_GET['error']) && $_GET['error'] == 3) { ?>
                        <p id="mldFEIL">Passord må være 8 tegn i lengden og inneholde en liten bokstav, en stor bokstav og ett tall</p>

                    <?php } if(isset($_GET['error']) && $_GET['error'] == 4){ ?>
                        <p id="mldFEIL">Brukernavnet er opptatt</p>    
                    <?php } ?>
                    <!-- Sender brukeren tilbake til forsiden -->
                    <button onClick="location.href='konto.php'" name="submit" class="lenke_knapp">Avbryt redigering</button>
                </section>
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
    
    <!-- Denne siden er utviklet av Robin Kleppang, Ajdin Bajrovic, Petter Fiskvik siste gang endret 04.12.2019 -->
    <!-- Sist kontrollert av Aron Snekkestad, 09.12.2019 -->

</html>