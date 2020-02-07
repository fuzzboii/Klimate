<?php
session_start();

//------------------------------//
// Innstillinger, faste variable //
//------------------------------//
include("innstillinger.php");

// Sjekker om bruker har tilgang på denne siden
if (!isset($_SESSION['idbruker'])) {
    header("Location: default.php?error=1");
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
                header("Location: konto_rediger.php?error=5");
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
                
                        if ($antall > 0) {
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
        } 
    } 
    catch (PDOException $ex) {
        if ($ex->getCode() == 23000) {
            // 23000, Duplikat brukernavn (Siden brukernavn er UNIQUE)
            header("location: konto_rediger.php?error=4");
        }
    } 
    
}

if (isset($_POST['fnavn']) || isset($_POST['enavn']) || isset($_POST['telefonnummer'])) {
    if (isset($_POST['fnavn'])) {
        $slettfNavnQ = "update bruker set fnavn = null where idbruker = " . $_SESSION['idbruker'];
        $slettfNavnSTMT = $db->prepare($slettfNavnQ);
        $slettfNavnSTMT->execute();

        $_SESSION['fornavn'] = "";
        
    } if (isset($_POST['enavn'])) {
        $sletteNavnQ = "update bruker set enavn = null where idbruker = " . $_SESSION['idbruker'];
        $sletteNavnSTMT = $db->prepare($sletteNavnQ);
        $sletteNavnSTMT->execute();

        $_SESSION['etternavn'] = "";

    } if (isset($_POST['telefonnummer'])) {

        $slettTlfQ = "update bruker set telefonnummer = null where idbruker = " . $_SESSION['idbruker'];
        $sletteTlfSTMT = $db->prepare($slettTlfQ);
        $sletteTlfSTMT->execute();

        $_SESSION['telefonnummer'] = "";

    }

    header("location: konto.php?vellykket=1");
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

    <body onload="kontoRullegardin()" onresize="fiksRullegardin()">
        <article class="innhold">    
            <!-- Begynnelse på øvre navigasjonsmeny -->
            <nav class="navTop">
                <!-- Legger til en knapp for å logge ut når man er innlogget-->
                <!-- Bruker et ikon som skal åpne gardinmenyen, henviser til funksjonen hamburgerMeny i javascript.js -->
                <a class="bildeKontroll" href="javascript:void(0)" onclick="hamburgerMeny()" tabindex="6">
                    <img src="bilder/hamburgerIkon.svg" alt="Hamburger-menyen" class="hamburgerKnapp">
                </a>
                <!-- Profilbilde i navmenyen, leder til profil-siden -->
                <?php

                /* -------------------------------*/
                /* Del for visning av profilbilde */
                /* -------------------------------*/

                // Henter bilde fra database utifra brukerid

                $hentBilde = "select hvor from bruker, brukerbilde, bilder where idbruker = " . $_SESSION['idbruker'] . " and idbruker = bruker and bilde = idbilder";
                $stmtBilde = $db->prepare($hentBilde);
                $stmtBilde->execute();
                $bilde = $stmtBilde->fetch(PDO::FETCH_ASSOC);
                $antallBilderFunnet = $stmtBilde->rowCount();

                // rowCount() returnerer antall resultater fra database, er dette null finnes det ikke noe bilde i databasen
                if ($antallBilderFunnet != 0) { ?>
                    <!-- Hvis vi finner et bilde til bruker viser vi det -->
                    <a class="bildeKontroll" href="javascript:void(0)" onClick="location.href='profil.php?bruker=<?php echo($_SESSION['idbruker']) ?>'" tabindex="5">
                        <?php
                        $testPaa = $bilde['hvor'];
                        // Tester på om filen faktisk finnes
                        if(file_exists("$lagringsplass/$testPaa")) {   
                            if ($_SESSION['brukertype'] == 2) { ?>
                                <!-- Setter redaktør border "Oransje" -->
                                <img src="bilder/opplastet/<?php echo($bilde['hvor'])?>" alt="Profilbilde"  class="profil_navmeny" style="border: 1px solid green;">
                            
                            <?php 
                            }
                            if ($_SESSION['brukertype'] == 1) { ?>
                                <!-- Setter administrator border "Rød" -->
                                <img src="bilder/opplastet/<?php echo($bilde['hvor'])?>" alt="Profilbilde"  class="profil_navmeny" style="border: 1px solid red;"> 
                            <?php 
                            }
                            if ($_SESSION['brukertype'] == 3) { ?> 
                                <!-- Setter vanlig profil bilde -->
                                <img src="bilder/opplastet/<?php echo($bilde['hvor'])?>" alt="Profilbilde"  class="profil_navmeny"> 
                            <?php 
                            }
                        } else { 
                            // Om filen ikke ble funnet, vis standard profilbilde
                            if ($_SESSION['brukertype'] == 2) { ?>
                                <img src="bilder/profil.png" alt="Profilbilde" class="profil_navmeny" style="border: 1px solid green;">
                            <!-- Setter administrator border "Rød" -->
                            <?php } else if ($_SESSION['brukertype'] == 1) { ?>
                                <img src="bilder/profil.png" alt="Profilbilde" class="profil_navmeny" style="border: 1px solid red;"> 
                            <!-- Setter vanlig profil bilde -->
                            <?php } else if ($_SESSION['brukertype'] != 1 || 2) { ?>
                                <img src="bilder/profil.png" alt="Profilbilde" class="profil_navmeny"> 
                            <?php
                            }
                        } ?>
                    </a>

                <?php } else { ?>
                    <a class="bildeKontroll" href="javascript:void(0)" onClick="location.href='profil.php?bruker=<?php echo($_SESSION['idbruker']) ?>'" tabindex="5">
                        <!-- Setter redaktør border "Oransje" -->
                        <?php if ($_SESSION['brukertype'] == 2) { ?>
                            <img src="bilder/profil.png" alt="Profilbilde" class="profil_navmeny" style="border: 1px solid green;">
                        <!-- Setter administrator border "Rød" -->
                        <?php } else if ($_SESSION['brukertype'] == 1) { ?>
                            <img src="bilder/profil.png" alt="Profilbilde" class="profil_navmeny" style="border: 1px solid red;"> 
                        <!-- Setter vanlig profil bilde -->
                        <?php } else if ($_SESSION['brukertype'] != 1 || 2) { ?>
                            <img src="bilder/profil.png" alt="Profilbilde" class="profil_navmeny"> 
                        <?php } ?>
                    </a>

                <?php } ?>

                <!-- Legger til en knapp for å logge ut når man er innlogget -->
                <form method="POST" action="default.php">
                    <button name="loggUt" id="backendLoggUt" tabindex="4">LOGG UT</button>
                </form>
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
                <!-- -1 tabIndex som standard, man tabber ikke inn i menyen når den er lukket -->
                <section class="hamburgerInnhold">

                    <!-- Redaktør meny "Oransje" -->
                    <?php if ($_SESSION['brukertype'] == 2) { ?>
                        <p style="color: green"> Innlogget som Redaktør </p>
                    <!-- Administrator meny "Rød" -->
                    <?php } else if ($_SESSION['brukertype'] == 1) { ?>
                        <p style="color: red"> Innlogget som Administrator </p>
                    <?php } ?>

                    <a class = "menytab" tabIndex = "-1" href="arrangement.php">Arrangementer</a>
                    <a class = "menytab" tabIndex = "-1" href="artikkel.php">Artikler</a>
                    <a class = "menytab" tabIndex = "-1" href="#">Diskusjoner</a>
                    <a class = "menytab" tabIndex = "-1" href="backend.php">Oversikt</a>
                    <a class = "menytab" tabIndex = "-1" href="konto.php">Konto</a>
                    <a class = "menytab" tabIndex = "-1" href="sok.php">Avansert Søk</a>
                </section>
            </section>

            <header class="konto_rediger_header" onclick="lukkHamburgerMeny()">
                <h1>Konto</h1>
            </header>


            <main id="konto_rediger_main" onclick="lukkHamburgerMeny()">
                <section class="brukerinformasjon_rediger"> 
                    <!-- Underoverskrift -->
                    <h2 class="redigerbruker_overskrift">Rediger brukeropplysninger</h2>

                    
                    <form id="konto_rediger_formSlett" method="POST" action="konto_rediger.php" name="slettInfo">

                    </form>
                    
                    <!-- Felt for brukeropplysning endringer -->
                    <form id="konto_rediger_form" method="POST" action="konto_rediger.php" class="konto_rediger_Form">
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
                            <h3 class="endre_bruker_overskrift">Endre fornavn
                                <input type="submit" form="konto_rediger_formSlett" class="konto_rediger_slettKnapp" name="fnavn" value="(Slett)">
                            </h3>
                            <input type="text" class="KontoredigeringFelt" name="nyttfornavn" value="<?php echo($input_fornavn) ?>" placeholder="Nytt fornavn">
                        </section>
                        <!-- Etternavn -->
                        <section class="konto_rediger_inputBoks">
                            <h3 class="endre_bruker_overskrift">Endre etternavn
                                <input type="submit" form="konto_rediger_formSlett" class="konto_rediger_slettKnapp" name="enavn" value="(Slett)">
                            </h3>
                            <input type="text" class="KontoredigeringFelt" name="nyttetternavn" value="<?php echo($input_etternavn) ?>" placeholder="Nytt etternavn">
                        </section>
                        <!-- Telefonnummer -->
                        <section class="konto_rediger_inputBoks">
                            <h3 class="endre_bruker_overskrift">Endre telefonnummer
                                <input type="submit" form="konto_rediger_formSlett" class="konto_rediger_slettKnapp" name="telefonnummer" value="(Slett)">
                            </h3>
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

            <!-- Knapp som vises når du har scrollet i vinduet, tar deg tilbake til toppen -->
            <button onclick="tilbakeTilTopp()" id="toppKnapp" title="Toppen"><img src="bilder/pilopp.png" alt="Tilbake til toppen"></button>

            <!-- Footer, epost er for øyeblikket på en catch-all, videresendes til RK -->
            <footer>
                <p class=footer_beskrivelse>&copy; Klimate 2020 | <a href="mailto:kontakt@klimate.no">Kontakt oss</a>
                    <!-- Om brukeren ikke er administrator eller redaktør, vis link for søknad til å bli redaktør -->
                    <?php if ($_SESSION['brukertype'] == "3") { ?> | <a href="soknad.php">Søknad om å bli redaktør</a><?php } ?>
                </p>
            </footer>
        </article>
    </body>
    
    <!-- Denne siden er utviklet av Robin Kleppang, Ajdin Bajrovic, Petter Fiskvik siste gang endret 04.12.2019 -->
    <!-- Sist kontrollert av Aron Snekkestad, 09.12.2019 -->

</html>