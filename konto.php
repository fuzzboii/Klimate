<?php
session_start();

//-------------------------------//
// Innstillinger, faste variable //
//-------------------------------//
include("inkluderes/innstillinger.php");


// Sjekker om bruker er i en gyldig session, sender tilbake til hovedsiden hvis ikke
if (!$_SESSION['idbruker']) {
    header("Location: default.php?error=1");
}

if (isset($_POST['avregistrerMeg'])) {

    // Del for oppdatering av passord, sjekker om begge passordene er like, og om bruker faktisk har skrevet noe
    if ($_POST['passord'] != "") {
        if ($_SESSION['brukertype'] != 1) {
            $pw = $_POST['passord'];

            // Salter passordet
            $kombinert = $salt . $pw;

            // Krypterer det saltede passordet
            $spw = sha1($kombinert);

            $sjekkPassordQ = "select idbruker, passord from bruker where idbruker = " . $_SESSION['idbruker'] . " and passord = '" . $spw . "'";
            $sjekkPassordSTMT = $db->prepare($sjekkPassordQ);
            $sjekkPassordSTMT->execute();
            $resultat = $sjekkPassordSTMT->fetch(PDO::FETCH_ASSOC);

            if (($resultat != false) &&$resultat['idbruker'] == $_SESSION['idbruker'] && $resultat['passord'] == $spw) {
                // Passordet er riktig, vi fortsetter med avregistrering
                $avregistreringQ = "update bruker set brukertype = 4 where idbruker = '" . $_SESSION['idbruker'] . "'";
                $avregistreringSTMT = $db->prepare($avregistreringQ);
                $avregistreringSTMT->execute();
                $avregistreringRes = $avregistreringSTMT->fetch(PDO::FETCH_ASSOC);

                $antallEndret = $avregistreringSTMT->rowCount();

                if($antallEndret != 0) {
                    session_destroy();
                    header('Location: default.php?avregistrert=true');
                }
            } else {
                // Feil passord skrevet
                header("Location: konto.php?error=3");
            }
        } else {
            // Brukertype er administrator
            header("Location: konto.php?error=4");
        }
    } else {
        // Ikke noe passord skrevet
        header("Location: konto.php?error=1");
    }
}



// Enkel test som gjør det mulig å beholde brukerinput etter siden er lastet på nytt (Form submit)
$input_brukernavn = "";
$input_epost = "";
$input_fornavn = "";
$input_etternavn = "";
$input_telefonnummer = "";
if (isset($_SESSION['input_brukernavn'])) {
    // Legger innhold i variable som leses senere på siden
    $input_brukernavn = $_SESSION['input_brukernavn'];
    $input_epost = $_SESSION['input_epost'];
    $input_fornavn = $_SESSION['input_fornavn'];
    $input_etternavn = $_SESSION['input_etternavn'];
    $input_telefonnummer = $_SESSION['input_telefonnummer'];
    // Sletter innholdet så dette ikke eksisterer utenfor denne siden
    unset($_SESSION['input_brukernavn']);
    unset($_SESSION['input_epost']);
    unset($_SESSION['input_fornavn']);
    unset($_SESSION['input_etternavn']);
    unset($_SESSION['input_telefonnummer']);
}

// Hoveddelen for redigering av konto
if (isset($_POST['subEndring'])) {
    // Boolske verdier vi tester på for å vite om noe er endret på
    $oppdatertBr = false;
    $oppdatertPw = false;
    
    try {
        // Del for oppdatering av brukernavn, epost, fornavn og/eller etternavn
        if ($_POST['nyttbrukernavn'] != "" || $_POST['nyepost'] != "" || $_POST['nyttfornavn'] != "" || $_POST['nyttetternavn'] != "" || $_POST['nytttelefonnummer'] != "") {
            $_SESSION['input_brukernavn'] = $_POST['nyttbrukernavn'];
            $_SESSION['input_epost'] = $_POST['nyepost'];
            $_SESSION['input_fornavn'] = $_POST['nyttfornavn'];
            $_SESSION['input_etternavn'] = $_POST['nyttetternavn'];
            $_SESSION['input_telefonnummer'] = $_POST['nytttelefonnummer'];
            
            // Tester på om en epost faktisk er oppgitt (Om bruker endrer input type til text eller hvis browser ikke støtter type epost)
            $epostValidert = false;

            if ($_POST['nyepost'] == "") {
                // Bruker har ikke oppgitt en epost, ignorerer dette
                $epostValidert = true;
            } else {
                $epostValidert = filter_var($_POST["nyepost"], FILTER_VALIDATE_EMAIL);
            }
            if ($epostValidert == true) {
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
            
                // Sjekker på om bruker har skrevet et telefonnummer, maks 12 tegn (0047) 
                if (!preg_match('/^[0-9]{0,12}$/', $_POST['nytttelefonnummer'])) {
                    // Bruker har valgt å ikke oppdatere telefonnummer, eller et ugyldig telefonnummer er skrevet
                    $nyttTelefonnummer = $_SESSION['telefonnummer'];
                } else {
                    $nyttTelefonnummer = $_POST['nytttelefonnummer'];
                }
                // SQL script som oppdaterer info. Med testing over vil ikke informasjon som bruker ikke vil endre faktisk endres
                $oppdaterBruker = "update bruker set brukernavn = '" . $nyttBrukernavn . "', fnavn = '" . $nyttFornavn . "', enavn = '" . $nyttEtternavn . "', epost = '" . $nyEpost . "', telefonnummer = '" . $nyttTelefonnummer . "'  where idbruker='". $_SESSION['idbruker'] . "'";
                $stmt = $db->prepare($oppdaterBruker);
                $stmt->execute();

                // Ved update blir antall rader endret returnert, vi kan utnytte dette til å teste om noen endringer faktisk skjedde
                $antall = $stmt->rowCount();

                if ($antall > 0) {
                    // Oppdaterer session-info
                    $_SESSION['brukernavn'] = $nyttBrukernavn;
                    $_SESSION['fornavn'] = $nyttFornavn;
                    $_SESSION['etternavn'] = $nyttEtternavn;
                    $_SESSION['epost'] = $nyEpost;
                    $_SESSION['telefonnummer'] = $nyttTelefonnummer;
                    $oppdatertBr = true;

                    // Alt gikk ok, fjerner session variable for brukerinput
                    unset($_SESSION['input_brukernavn']);
                    unset($_SESSION['input_epost']);
                    unset($_SESSION['input_fornavn']);
                    unset($_SESSION['input_etternavn']);
                    unset($_SESSION['input_telefonnummer']);
                }
            } else {
                header("Location: konto.php?error=9");
            }
        } 

        // Del for oppdatering av passord, sjekker om begge passordene er like, og om bruker faktisk har skrevet noe
        if ($_POST['nyttpassord'] != "") {
            if ($_POST['nyttpassord'] == $_POST['bekreftnyttpassord']) {

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
                        header("Location: konto.php?error=6");
                    } else if (!$storebokstaver || !$smaabokstaver || !$nummer /*|| !$spesielleB*/ || strlen($pw) < 8) {
                        // Ikke tilstrekkelig passord skrevet
                        header("Location: konto.php?error=7");
                    } else {
                        // Passord er OK, vi fortsetter
                        $kombinert = $salt . $_POST['nyttpassord'];
                        $nyttPassord = sha1($kombinert);
            
                        $oppdaterBruker = "update bruker set passord = '" . $nyttPassord . "'  where brukernavn='". $_SESSION['brukernavn'] . "'";
            
                        $stmt = $db->prepare($oppdaterBruker);
                        $stmt->execute();

                        // Ved update blir antall rader endret returnert, vi kan utnytte dette til å teste om noen endringer faktisk skjedde
                        $antall = $stmt->rowCount();
                
                        if ($antall > 0) {
                            $oppdatertPw = true;
                        }
                    }
                }
            } else {
                // Passordene er ikke like
                header("Location: konto.php?error=5");
            }
        }

        // Hvis vi har oppdatert brukerinfo eller passord, returner bruker til kontosiden, her ser vi oppdatert info direkte
        if ($oppdatertBr == true || $oppdatertPw == true) {
            header("location: konto.php?vellykket=1");
        } 
    } 
    catch (PDOException $ex) {
        if ($ex->getCode() == 23000) {
            // 23000, Duplikat brukernavn (Siden brukernavn er UNIQUE)
            header("location: konto.php?error=8");
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
        <title>
            <?php if(isset($_POST['rediger'])) { ?>
                Konto | Rediger
            <?php } else { ?>
                Konto
            <?php } ?>
        </title>
        <!-- Henter inn ekstern stylesheet -->
        <link rel="stylesheet" type="text/css" href="stylesheet.css">
        <!-- Henter inn favicon, bildet som dukker opp i fanene i nettleseren -->
        <link rel='icon' href='bilder/favicon.png' type='image/x-icon'>
        <!-- Henter inn JavaScript -->
        <script language="JavaScript" src="javascript.js"></script>
    </head>

    <body>
        <article class="innhold">
            <?php include("inkluderes/navmeny.php") ?>

            <!-- For å kunne lukke hamburgermenyen ved å kun trykke på et sted i vinduet må lukkHamburgerMeny() funksjonen ligge i deler av HTML-koden -->
            <!-- Kan ikke legge denne direkte i body -->
            <header class="konto_header" onclick="lukkHamburgerMeny()">
                <h1>Konto</h1>
            </header>

            <!-- Meldinger til bruker -->
            <?php if(isset($_GET['vellykket']) && $_GET['vellykket'] == 1){ ?>
                <p id="mldOK">Konto oppdatert</p>  

            <?php } else if(isset($_GET['error']) && $_GET['error'] == 1){ ?>
                <p id="mldFEIL">Du må oppgi et passord ved avregistrering.</p>

            <?php } else if(isset($_GET['error']) && $_GET['error'] == 2){ ?>
                <p id="mldFEIL">Kunne ikke oppdatere konto, vennligst prøv igjen senere</p>  

            <?php } else if(isset($_GET['error']) && $_GET['error'] == 3){ ?>
                <p id="mldFEIL">Feil passord skrevet ved avregistrering</p>

            <?php } else if(isset($_GET['error']) && $_GET['error'] == 4){ ?>
                <p id="mldFEIL">Du kan ikke avregistrere en administrator</p>  

            <?php } else if(isset($_GET['error']) && $_GET['error'] == 5){ ?>
                <p id="mldFEIL">Passordene er ikke like</p>

            <?php } else if(isset($_GET['error']) && $_GET['error'] == 6){ ?>
                <p id="mldFEIL">Skriv inn et passord</p>

            <?php } else if(isset($_GET['error']) && $_GET['error'] == 7) { ?>
                <p id="mldFEIL">Passord må være 8 tegn i lengden og inneholde en liten bokstav, en stor bokstav og ett tall</p>

            <?php } else if(isset($_GET['error']) && $_GET['error'] == 8){ ?>
                <p id="mldFEIL">Brukernavnet er opptatt</p>    

            <?php } else if(isset($_GET['error']) && $_GET['error'] == 9){ ?>
                <p id="mldFEIL">Epost er ikke gyldig</p>    

            <?php } ?>

            <?php if(isset($_POST['rediger'])) { ?>
                <main id="konto_rediger_main" onclick="lukkHamburgerMeny()">
                    <section class="brukerinformasjon_rediger"> 
                        <!-- Underoverskrift -->
                        <h2 class="redigerbruker_overskrift">Rediger brukeropplysninger</h2>

                        
                        <form id="konto_rediger_formSlett" method="POST" action="konto.php" name="slettInfo">

                        </form>
                        
                        <!-- Felt for brukeropplysning endringer -->
                        <form id="konto_rediger_form" method="POST" action="konto.php" class="konto_rediger_Form">
                            <!-- Brukernavn -->
                            <section class="konto_rediger_inputBoks">
                                <h3 class="endre_bruker_overskrift">Endre brukernavn</h3>
                                <input type="text" class="KontoredigeringFelt" name="nyttbrukernavn" value="<?php echo($input_brukernavn) ?>" placeholder="Nytt brukernavn" autofocus>
                            </section>
                            <!-- Epost -->
                            <section class="konto_rediger_inputBoks">
                                <h3 class="endre_bruker_overskrift">Endre epost</h3>
                                <input type="email" class="KontoredigeringFelt" name="nyepost" value="<?php echo($input_epost) ?>" placeholder="Ny epost">
                            </section>    
                            <!-- Fornavn -->
                            <section class="konto_rediger_inputBoks">
                                <h3 class="endre_bruker_overskrift">Endre fornavn</h3>
                                <input type="text" class="KontoredigeringFelt" name="nyttfornavn" value="<?php echo($input_fornavn) ?>" placeholder="Nytt fornavn">
                            </section>
                            <!-- Etternavn -->
                            <section class="konto_rediger_inputBoks">
                                <h3 class="endre_bruker_overskrift">Endre etternavn</h3>
                                <input type="text" class="KontoredigeringFelt" name="nyttetternavn" value="<?php echo($input_etternavn) ?>" placeholder="Nytt etternavn">
                            </section>
                            <!-- Telefonnummer -->
                            <section class="konto_rediger_inputBoks">
                                <h3 class="endre_bruker_overskrift">Endre telefonnummer</h3>
                                <input type="text" class="KontoredigeringFelt" name="nytttelefonnummer" value="<?php echo($input_telefonnummer) ?>" placeholder="Nytt telefonnummer">
                            </section>
                            
                        </form>

                        <!-- Passord: gammelt, nytt, bekreft (Rullegardin) -->
                        <button type="button" id="kontoRullegardin" class="kontoRullegardin">Endre passord</button>
                        <section id="konto_rediger_pw" class="innholdRullegardin">
                            <section class="konto_rediger_inputBoks">
                                <h3 class="endre_bruker_overskrift">Gammelt passord</h3>
                                <input type="password" class="KontoredigeringFeltPW" name="gammeltpassord" value="" placeholder="Gammelt passord" form="konto_rediger_form" autofocus>
                            </section>
                            <section class="konto_rediger_inputBoks">
                                <h3 class="endre_bruker_overskrift">Nytt passord</h3>
                                <input type="password" class="KontoredigeringFeltPW" name="nyttpassord" value="" placeholder="Nytt passord" form="konto_rediger_form">
                            </section>
                            <section class="konto_rediger_inputBoks">
                                <h3 class="endre_bruker_overskrift">Bekreft nytt passord</h3>
                                <input type="password" class="KontoredigeringFeltPW" name="bekreftnyttpassord" value="" placeholder="Bekreft nytt passord" form="konto_rediger_form">
                            </section>
                            <input style="margin-bottom: 1em;" type="checkbox" onclick="visPassordInst()">Vis passord</input>
                        </section>

                        <!-- Knapp for å lagre endringer -->
                        <input type="submit" name="subEndring" class="KontoredigeringFelt_knappLagre" value="Lagre endringer" form="konto_rediger_form">

                        <?php if(isset($_GET['error']) && $_GET['error'] == 1){ ?>
                            <p id="mldFEIL">Passordene er ikke like</p>
                        
                        <?php } else if(isset($_GET['error']) && $_GET['error'] == 2){ ?>
                            <p id="mldFEIL">Skriv inn et passord</p>
                        
                        <?php } else if(isset($_GET['error']) && $_GET['error'] == 3) { ?>
                            <p id="mldFEIL">Passord må være 8 tegn i lengden og inneholde en liten bokstav, en stor bokstav og ett tall</p>

                        <?php } else if(isset($_GET['error']) && $_GET['error'] == 4){ ?>
                            <p id="mldFEIL">Brukernavnet er opptatt</p>    

                        <?php } else if(isset($_GET['error']) && $_GET['error'] == 5){ ?>
                            <p id="mldFEIL">Epost er ikke gyldig</p>    
                        <?php } ?>
                        <!-- Sender brukeren tilbake til forsiden -->
                        <button onClick="location.href='konto.php'" name="submit" class="lenke_knapp">Avbryt redigering</button>
                    </section>
                </main>
            <?php } else { ?>
                <!-- Konto brukeropplysninger -->
                <main id="konto_main" onclick="lukkHamburgerMeny()">
                    <section class="brukerinformasjon">
                        <table class="brukerinformasjon_tabell">
                            <!-- Brukernavn output -->
                            <tr>
                                <th>Brukernavn:</th>
                                    <td><?php echo($_SESSION['brukernavn']) ?></td>
                            <!-- Epost output -->
                            <tr>
                                <th>Epost:</th>
                                    <td><?php echo($_SESSION['epost']) ?></td>
                            </tr>  
                            <!-- Fornavn output -->
                            <tr>
                                <th>Fornavn:</th>
                                    <td><?php echo($_SESSION['fornavn']) ?></td>
                            </tr>
                            <!-- Etternavn output -->
                            <tr>
                                <th>Etternavn:</th>
                                    <td><?php echo($_SESSION['etternavn']) ?></td>
                            </tr>
                            <!-- Telefonnummer output -->
                            <tr>
                                <th>Telefonnummer:</th>
                                    <td><?php echo($_SESSION['telefonnummer']) ?></td>
                            </tr>
                        
                        </table>
                        <form method="POST" action="konto.php">
                            <input type="submit" name="rediger" class="rediger_konto_knapp" value="Rediger konto">
                        </form>
                        
                        <button onclick="bekreftMelding('konto_bekreftAvr')" class="konto_avregistrer" id="konto_avregistrerKnapp">Avregistrering</button>

                        <section id="konto_bekreftAvr" style="display: none;">
                            <section id="konto_bekreftAvrInnhold">
                                <h2>Avregistrering</h2>
                                <p>Er du sikker på av du vil avregistrere?</p>
                                <form method="POST" action="konto.php">
                                    <input type="password" id="konto_avregistrerpassord" name="passord" placeholder="Oppgi passord" title="Oppgi passordet ditt for å bekrefte avregistrering" required>
                                    <button id="konto_avregistrerKnapp" name="avregistrerMeg">Avregistrer</button>
                                </form>
                                <button id="konto_avbrytKnapp" onclick="bekreftMelding('konto_bekreftAvr')">Avbryt</button>
                            </section>
                        </section>
                    </section> 
                </main>
            <?php } ?>
            <?php include("inkluderes/footer.php") ?>
        </article>
    </body>

    
<!-- Denne siden er utviklet av Ajdin Bajrovic, siste gang endret 07.02.2020 -->
<!-- Sist kontrollert av Robin Kleppang, siste gang 07.02.2020 -->

</html>
