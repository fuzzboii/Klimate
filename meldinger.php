<?php
session_start();

//-------------------------------//
// Innstillinger, faste variable //
//-------------------------------//
include("inkluderes/innstillinger.php");

// Kun innloggede brukere kan se meldinger
if (!isset($_SESSION['idbruker'])) {
    header("Location: default.php?error=1");
}

// Browser må validere cache med server før cached kopi kan benyttes
// Dette gjør at man kan gå frem og tilbake i innboksen uten at man får ERR_CACHE_MISS
header("Cache-Control: no cache");

if(isset($_POST['mottatt'])) {
    // Er vi her henter vi ting som brukes i visning av valgt melding
    $samtaleMeldingerQ = "select idmelding, tittel, tekst, tid, lest, sender, papirkurv
                            from melding where idmelding = " . $_POST['mottatt'] . " and mottaker = " . $_SESSION['idbruker'];
    $samtaleMeldingerSTMT = $db->prepare($samtaleMeldingerQ);
    $samtaleMeldingerSTMT->execute();
    $resMld = $samtaleMeldingerSTMT->fetch(PDO::FETCH_ASSOC); 

    $antMld = $samtaleMeldingerSTMT->rowCount();

    if($antMld > 0) {
        // Fant meldingen i databasen, setter variabel som testes på senere til true
        $fantSamtale = true;

        // Henter info om senderen
        $senderInfoQ = "select idbruker, brukernavn, fnavn, enavn from bruker where bruker.idbruker = " . $resMld['sender'];
        $senderInfoSTMT = $db->prepare($senderInfoQ);
        $senderInfoSTMT->execute();
        $resInfo = $senderInfoSTMT->fetch(PDO::FETCH_ASSOC); 

        // Test på om bruker har tidligere lest denne meldingen
        if($resMld['lest'] == null || $resMld['lest'] == 0) {
            // Setter meldingen til lest
            $lestQ = "update melding set lest = 1 where idmelding = " . $resMld['idmelding'];
            $lestSTMT = $db->prepare($lestQ);
            $lestSTMT->execute();
        }
        
        // Henter bildet til brukeren
        $hentBildeQ = "select hvor from bilder, brukerbilde where brukerbilde.bruker = " . $resMld['sender'] . " and brukerbilde.bilde = bilder.idbilder";
        $stmtBildeSTMT = $db->prepare($hentBildeQ);
        $stmtBildeSTMT->execute();
        $senderBilde = $stmtBildeSTMT->fetch(PDO::FETCH_ASSOC);
        $funnetSenderBilde = $stmtBildeSTMT->rowCount();

        // Tester på om etternavnet har noen gyldige tegn, hvis ikke vises brukernavn
        if(preg_match("/\S/", $resInfo['enavn']) == 1) {
            $navn = $resInfo['fnavn'] . " " . $resInfo['enavn'];  
        } else {
            $navn = $resInfo['brukernavn'];
        }

    } else {
        // Fant ikke meldingen i databasen, setter variabel som testes på senere til false
        $fantSamtale = false;
    }

} else if(isset($_POST['ny'])) {
    // Er vi her henter vi ting som brukes i ny melding

} else if(isset($_POST['utboks'])) {
    // Er vi her henter vi ting som brukes i innboksen
    $sendteMeldingerQ = "select idmelding, tittel, tid, lest, mottaker
                            from melding where sender = " . $_SESSION['idbruker'] . 
                                " order by tid DESC";
    $sendteMeldingerSTMT = $db->prepare($sendteMeldingerQ);
    $sendteMeldingerSTMT->execute();
    $resMld = $sendteMeldingerSTMT->fetchAll(PDO::FETCH_ASSOC); 
    $antMld = $sendteMeldingerSTMT->rowCount();

} else if(isset($_POST['papirkurv'])) {
    // Er vi her henter vi ting som brukes i innboksen
    $mottattMeldingerQ = "select idmelding, tittel, tid, lest, sender
                            from melding where mottaker = " . $_SESSION['idbruker'] . " and (papirkurv = 1)" . 
                                " order by tid DESC";
    $mottattMeldingerSTMT = $db->prepare($mottattMeldingerQ);
    $mottattMeldingerSTMT->execute();
    $resMld = $mottattMeldingerSTMT->fetchAll(PDO::FETCH_ASSOC); 

    $antMld = $mottattMeldingerSTMT->rowCount();

} else {
    // Er vi her henter vi ting som brukes i innboksen
    $mottattMeldingerQ = "select idmelding, tittel, tid, lest, sender
                            from melding where mottaker = " . $_SESSION['idbruker'] . " and (papirkurv is null or papirkurv = 0)" . 
                                " order by tid DESC";
    $mottattMeldingerSTMT = $db->prepare($mottattMeldingerQ);
    $mottattMeldingerSTMT->execute();
    $resMld = $mottattMeldingerSTMT->fetchAll(PDO::FETCH_ASSOC); 

    $antMld = $mottattMeldingerSTMT->rowCount();
}

// Del for å legge til en ny melding, brukes både i ny melding og svar på melding
if(isset($_POST['sendMelding'])) {
    if(isset($_POST['brukernavn'])) {
        // Henter idbruker til brukeren som ble oppgitt
        $hentIDQ = "select idbruker from bruker where brukernavn = '" . $_POST['brukernavn'] . "'";
        $hentIDSTMT = $db->prepare($hentIDQ);
        $hentIDSTMT->execute();
        $resID = $hentIDSTMT->fetch(PDO::FETCH_ASSOC); 

        $_POST['idbruker'] = $resID['idbruker'];
    }
    // Legger til en ny melding
    $nyMeldingQ = "insert into melding(tittel, tekst, tid, lest, sender, mottaker) 
                        values('" . $_POST['tittel'] . "', '" . $_POST['tekst'] . "', 
                            NOW(), 0, " . $_SESSION['idbruker'] . ", " . $_POST['idbruker'] . ")";
    $nyMeldingSTMT = $db->prepare($nyMeldingQ);
    $nyMeldingSTMT->execute();
    $sendt = $nyMeldingSTMT->rowCount();
    
    if($sendt > 0) {
        // Melding sendt, gir tilbakemelding
        header("location: meldinger.php?meldingsendt");
    } else {
        // Error 1, melding ikke sendt
        header("location: meldinger.php?error=1");
    }
}

// Del for å legge en melding i søplekurven
if(isset($_POST['slettMelding'])) {
    // Bare tillate at innlogget bruker kan slette sine egne meldinger
    $sjekkPaaQ = "select idmelding from melding where idmelding = " . $_POST['slettMelding'] . " and mottaker = " . $_SESSION['idbruker'];
    $sjekkPaaSTMT = $db->prepare($sjekkPaaQ);
    $sjekkPaaSTMT->execute();
    $funnetMelding = $sjekkPaaSTMT->rowCount();

    if($funnetMelding > 0) {
        // Oppdaterer meldingen, setter også lest til 1
        $slettMeldingQ = "update melding set papirkurv = 1, lest = 1 where idmelding = " . $_POST['slettMelding'];
        $slettMeldingSTMT = $db->prepare($slettMeldingQ);
        $slettMeldingSTMT->execute();

        $endretMelding = $slettMeldingSTMT->rowCount();
        if($endretMelding > 0) { header("Location: meldinger.php?meldingslettet"); /* Melding slettet, OK */ } 
        else { header("Location: meldinger.php?error=2"); /* Error 2, melding ikke slettet */ }
    } else { header("Location: meldinger.php?error=2"); /* Error 2, melding ikke slettet */ }
}

// Del for å gjenopprette en slettet melding
if(isset($_POST['gjenopprettMelding'])) {
    // Bare tillate at innlogget bruker kan gjenopprette sine egne meldinger
    $sjekkPaaQ = "select idmelding from melding where idmelding = " . $_POST['gjenopprettMelding'] . " and mottaker = " . $_SESSION['idbruker'];
    $sjekkPaaSTMT = $db->prepare($sjekkPaaQ);
    $sjekkPaaSTMT->execute();
    $funnetMelding = $sjekkPaaSTMT->rowCount();

    if($funnetMelding > 0) {
        // Oppdaterer meldingen
        $gjenopprettMeldingQ = "update melding set papirkurv = 0 where idmelding = " . $_POST['gjenopprettMelding'];
        $gjenopprettMeldingSTMT = $db->prepare($gjenopprettMeldingQ);
        $gjenopprettMeldingSTMT->execute();

        $endretMelding = $gjenopprettMeldingSTMT->rowCount();
        if($endretMelding > 0) { header("Location: meldinger.php"); /* Melding gjenopprettet, OK */ } 
        else { header("Location: meldinger.php?error=3"); /* Error 2, melding ikke gjenopprettet */ }
    } else { header("Location: meldinger.php?error=3"); /* Error 2, melding ikke gjenopprettet */ }
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
            <?php if(isset($_POST['mottatt'])) { ?>
                Melding fra <?php echo($navn); ?>
            <?php } else if(isset($_POST['ny'])) { ?>
                Ny melding
            <?php } else if(isset($_POST['utboks'])) { ?>
                Utboks
            <?php } else { ?>
                Innboks
            <?php } ?>
        </title>
        <!-- Henter inn ekstern stylesheet -->
        <link rel="stylesheet" type="text/css" href="stylesheet.css">
        <!-- Henter inn favicon, bildet som dukker opp i fanene i nettleseren -->
        <link rel='icon' href='bilder/favicon.png' type='image/x-icon'>
        <!-- Henter inn JavaScript -->
        <script language="JavaScript" src="javascript.js"> </script>
    </head>

    <body class="innhold" onload="meldingTabbing()">
            <!-- Begynnelse på øvre navigasjonsmeny -->
            <nav class="navTop"> 
                <!-- Bruker et ikon som skal åpne gardinmenyen, henviser til funksjonen hamburgerMeny i javascript.js -->
                <!-- javascript:void(0) blir her brukt så siden ikke scroller til toppen av seg selv når du trykker på hamburger-ikonet -->
                <a class="bildeKontroll" href="javascript:void(0)" onclick="hamburgerMeny()" tabindex="7">
                    <img src="bilder/hamburgerIkon.svg" alt="Hamburger-menyen" class="hamburgerKnapp">
                </a>
                <!-- Legger til knapper for å registrere ny bruker eller innlogging -->
                <!-- Om bruker er innlogget, vis kun en 'Logg ut' knapp -->
                <?php if (isset($_SESSION['idbruker'])) {
                    // Vises når bruker er innlogget

                    /* -------------------------------*/
                    /* Del for visning av profilbilde */
                    /* -------------------------------*/

                    // Henter bilde fra database utifra brukerid

                    $hentBilde = "select hvor from bilder, brukerbilde where brukerbilde.bruker = " . $_SESSION['idbruker'] . " and brukerbilde.bilde = bilder.idbilder";
                    $stmtBilde = $db->prepare($hentBilde);
                    $stmtBilde->execute();
                    $bilde = $stmtBilde->fetch(PDO::FETCH_ASSOC);
                    $antallBilderFunnet = $stmtBilde->rowCount();
                    // rowCount() returnerer antall resultater fra database, er dette null finnes det ikke noe bilde i databasen
                    if ($antallBilderFunnet != 0) { ?>
                        <!-- Hvis vi finner et bilde til bruker viser vi det -->
                        <a class="bildeKontroll" href="javascript:void(0)" onClick="location.href='profil.php?bruker=<?php echo($_SESSION['idbruker']) ?>'" tabindex="6">
                            <?php
                            $testPaa = $bilde['hvor'];
                            // Tester på om filen faktisk finnes
                            if(file_exists("$lagringsplass/$testPaa")) {   
                                if ($_SESSION['brukertype'] == 2) { ?>
                                    <!-- Setter redaktør border "Oransje" -->
                                    <img src="bilder/opplastet/<?php echo($bilde['hvor'])?>" alt="Profilbilde"  class="profil_navmeny" style="border: 2px solid green;">
                                
                                <?php 
                                }
                                if ($_SESSION['brukertype'] == 1) { ?>
                                    <!-- Setter administrator border "Rød" -->
                                    <img src="bilder/opplastet/<?php echo($bilde['hvor'])?>" alt="Profilbilde"  class="profil_navmeny" style="border: 2px solid red;"> 
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
                                    <img src="bilder/profil.png" alt="Profilbilde" class="profil_navmeny" style="border: 2px solid green;">
                                <!-- Setter administrator border "Rød" -->
                                <?php } else if ($_SESSION['brukertype'] == 1) { ?>
                                    <img src="bilder/profil.png" alt="Profilbilde" class="profil_navmeny" style="border: 2px solid red;"> 
                                <!-- Setter vanlig profil bilde -->
                                <?php } else if ($_SESSION['brukertype'] != 1 || 2) { ?>
                                    <img src="bilder/profil.png" alt="Profilbilde" class="profil_navmeny"> 
                                <?php
                                }
                            } ?>
                        </a>

                    <?php } else { ?>
                        <a class="bildeKontroll" href="javascript:void(0)" onClick="location.href='profil.php?bruker=<?php echo($_SESSION['idbruker']) ?>'" tabindex="6">
                            <!-- Setter redaktør border "Oransje" -->
                            <?php if ($_SESSION['brukertype'] == 2) { ?>
                                <img src="bilder/profil.png" alt="Profilbilde" class="profil_navmeny" style="border: 2px solid green;">
                            <!-- Setter administrator border "Rød" -->
                            <?php } else if ($_SESSION['brukertype'] == 1) { ?>
                                <img src="bilder/profil.png" alt="Profilbilde" class="profil_navmeny" style="border: 2px solid red;"> 
                            <!-- Setter vanlig profil bilde -->
                            <?php } else if ($_SESSION['brukertype'] != 1 || 2) { ?>
                                <img src="bilder/profil.png" alt="Profilbilde" class="profil_navmeny"> 
                            <?php } ?>
                        </a>

                    <?php } ?>
                    <!-- Legger til en knapp for å logge ut når man er innlogget -->
                    <form method="POST" action="default.php">
                        <button name="loggUt" id="registrerKnapp" tabindex="5">LOGG UT</button>
                    </form>
                <?php } ?>
                
                <form id="sokForm_navmeny" action="sok.php">
                    <input id="sokBtn_navmeny" type="submit" value="Søk" tabindex="4">
                    <input id="sokInp_navmeny" type="text" name="artTittel" placeholder="Søk på artikkel" tabindex="3">
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

                    <!-- Redaktør meny "Oransje" -->
                    <?php if ($_SESSION['brukertype'] == 2) { ?>
                        <p style="color: green"> Innlogget som Redaktør </p>
                    <!-- Administrator meny "Rød" -->
                    <?php } else if ($_SESSION['brukertype'] == 1) { ?>
                        <p style="color: red"> Innlogget som Administrator </p>
                    <?php } ?>

                    <a class = "menytab" tabIndex = "-1" href="arrangement.php">Arrangementer</a>
                    <a class = "menytab" tabIndex = "-1" href="artikkel.php">Artikler</a>
                    <a class = "menytab" tabIndex = "-1" href="meldinger.php">Innboks<?php if($antUlest['antall'] > 0) {?> (<?php echo($antUlest['antall'])?>)<?php } ?></a>
                    <a class = "menytab" tabIndex = "-1" href="backend.php">Oversikt</a>
                    <a class = "menytab" tabIndex = "-1" href="konto.php">Konto</a>
                    <a class = "menytab" tabIndex = "-1" href="sok.php">Avansert Søk</a>
                </section>
            </section>
            <?php 
            if(isset($_POST['mottatt'])) { 
                /*--------------------------------*/
                /*--------------------------------*/
                /*--Del for å vise valgt melding--*/
                /*--------------------------------*/
                /*--------------------------------*/ ?>

                <!-- For å kunne lukke hamburgermenyen ved å kun trykke på et sted i vinduet må lukkHamburgerMeny() funksjonen ligge i deler av HTML-koden -->
                <!-- Kan ikke legge denne direkte i body -->
                <header id="meldinger_header" onclick="lukkHamburgerMeny()">
                    <img src="bilder/meldingIkon.png" alt="Ikon for meldinger">
                    <h1><?php if(isset($navn)) { ?>Melding fra <?php echo($navn); } ?></h1>
                    <form method="POST" action="meldinger.php">
                        <input type="submit" class="lenke_knapp" name="utboks" title="Meldingene du har sendt" value="Utboks" tabindex = "8">
                    </form>
                    <form method="POST" id="meldinger_header_papirkurv" action="meldinger.php">
                        <input type="submit" class="lenke_knapp" name="papirkurv" title="Meldingene du har slettet" value="Papirkurv" tabindex = "9">
                    </form>
                </header>

                <!-- Funksjon for å lukke hamburgermeny når man trykker på en del i Main -->
                <main id="meldinger_main" onclick="lukkHamburgerMeny()"> 
                    <?php if($fantSamtale == true) { ?>
                        <section id="meldinger_samtale_toppDel">
                            <?php if ($funnetSenderBilde != 0) {
                                $testPaa = $senderBilde['hvor'];
                                // Tester på om filen faktisk finnes
                                if(file_exists("$lagringsplass/$testPaa")) {
                                    // Profilbilde som resultat av spørring
                                    if(file_exists("$lagringsplass/" . "thumb_" . $testPaa)) {
                                        // Hvis vi finner et miniatyrbilde bruker vi det ?>
                                        <img id="meldinger_sender_bilde" src="bilder/opplastet/thumb_<?php echo($senderBilde['hvor']) ?>" alt="Profilbilde til <?php echo($navn) ?>">
                                    <?php } else { ?>
                                        <img id="meldinger_sender_bilde" src="bilder/opplastet/<?php echo($senderBilde['hvor']) ?>" alt="Profilbilde til <?php echo($navn) ?>">
                                    <?php } ?>
                                <?php } else { ?>
                                    <img id="meldinger_sender_bilde" src="bilder/profil.png" alt="Standard profilbilde">
                                <?php } ?>
                            <?php } else { ?>
                                <img id="meldinger_sender_bilde" src="bilder/profil.png" alt="Standard profilbilde">
                            <?php } ?>
                            <p id="meldinger_samtale_navn"><?php echo($navn) ?></p>
                            <p id="meldinger_samtale_tid"><?php echo(date_format(date_create($resMld['tid']), "j F Y H:i")) ?></p>
                            <?php if($resMld['papirkurv'] == 0) { ?>
                                <img src="bilder/soppelIkon.png" alt="Papirkurvikon" class="meldinger_innboks_soppel" id="meldinger_samtale_soppel" title="Slett denne meldingen" onclick="slettSamtale(<?php echo($resMld['idmelding']) ?>)" tabindex = "10">
                                <form method="POST" id="meldinger_innboks_soppel">
                                    <input type="hidden" id="meldinger_innboks_soppel_valgt" name="slettMelding" value="">
                                </form>
                            <?php } else { ?>
                                <img src="bilder/restoreIkon.png" alt="Gjenopprettikon" class="meldinger_innboks_restore" id="meldinger_samtale_restore" title="Gjenopprett denne meldingen" onclick="gjenopprettSamtale(<?php echo($resMld['idmelding']) ?>)" tabindex = "10">
                                <form method="POST" id="meldinger_innboks_restore">
                                    <input type="hidden" id="meldinger_innboks_restore_valgt" name="gjenopprettMelding" value="">
                                </form>
                            <?php } ?>
                        </section>
                        <p id="meldinger_samtale_tittel"><?php echo($resMld['tittel']) ?></p>
                        <p id="meldinger_samtale_tekst"><?php echo($resMld['tekst']) ?></p>

                        <form method="POST" id="meldinger_form_samtale" action="meldinger.php">
                            <input type="hidden" name="idbruker" value="<?php echo($resInfo['idbruker']) ?>">
                            <input type="hidden" name="tittel" value="Re: <?php echo(substr($resMld['tittel'], 0, 40)) ?>"> 
                            <textarea id="meldinger_samtale_svar" type="textbox" maxlength="1024" name="tekst" placeholder="Skriv her..." title="Oppgi innholdet til svaret" required></textarea>
                            <input id="meldinger_samtale_knapp" type="submit" name="sendMelding" value="">
                        </form>
                    <?php } else { ?>
                        <p>Kunne ikke vise denne meldingen</p>
                    <?php } ?>
                    <button onclick="location.href='meldinger.php'" id="meldinger_samtale_lenke" class="lenke_knapp" title="Meldingene du har mottatt">Tilbake til innboks</button>
               </main>
            

            <?php } else if(isset($_POST['ny'])) { 
                /*--------------------------------*/
                /*--------------------------------*/
                /*---Del for å vise ny melding----*/
                /*--------------------------------*/
                /*--------------------------------*/ ?>

                <!-- For å kunne lukke hamburgermenyen ved å kun trykke på et sted i vinduet må lukkHamburgerMeny() funksjonen ligge i deler av HTML-koden -->
                <!-- Kan ikke legge denne direkte i body -->
                <header id="meldinger_header" onclick="lukkHamburgerMeny()">
                    <img src="bilder/meldingIkon.png" alt="Ikon for meldinger">
                    <h1>Ny melding</h1>
                    <form method="POST" action="meldinger.php">
                        <input type="submit" class="lenke_knapp" name="utboks" title="Meldingene du har sendt" value="Utboks">
                    </form>
                    <form method="POST" action="meldinger.php">
                        <input type="submit" class="lenke_knapp" name="papirkurv" title="Meldingene du har slettet" value="Papirkurv">
                    </form>
                </header>

                <!-- Funksjon for å lukke hamburgermeny når man trykker på en del i Main -->
                <main id="meldinger_main_ny" onclick="lukkHamburgerMeny()"> 

                    <form method="POST" action="meldinger.php">
                        <input name="brukernavn"  id="meldinger_ny_bruker" type="text" list="brukere" placeholder="Skriv inn brukernavn" title="Brukernavnet du ønsker å sende melding til" autofocus required>
                        <datalist id="brukere">
                            <?php 
                            // Henter brukernavn fra database
                            $hentNavnQ = "select brukernavn from bruker order by brukernavn DESC";
                            $hentNavnSTMT = $db->prepare($hentNavnQ);
                            $hentNavnSTMT->execute();
                            $liste = $hentNavnSTMT->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($liste as $brukernavn) { ?>
                                <option value="<?php echo($brukernavn['brukernavn'])?>"><?php echo($brukernavn['brukernavn'])?></option>
                            <?php } ?>
                        </datalist>

                        <input type="text" id="meldinger_ny_tittel" name="tittel" maxlength="45" placeholder="Skriv inn tittel" title="Tittelen på meldingen" required>
                        <textarea id="meldinger_ny_tekst" type="textbox" maxlength="1024" name="tekst" placeholder="Skriv inn innhold" title="Innholdet i meldingen" required></textarea>
                        <input id="meldinger_ny_knapp" type="submit" name="sendMelding" value="Send melding" title="Send denne meldingen">
                    </form>

                    <button onclick="location.href='meldinger.php'" class="lenke_knapp" title="Meldingene du har mottatt">Tilbake til innboks</button>
                </main>
            

            <?php } else if(isset($_POST['utboks'])) { 
                /*--------------------------------*/
                /*--------------------------------*/
                /*-----Del for å vise utboksen----*/
                /*--------------------------------*/
                /*--------------------------------*/ ?>

                <!-- For å kunne lukke hamburgermenyen ved å kun trykke på et sted i vinduet må lukkHamburgerMeny() funksjonen ligge i deler av HTML-koden -->
                <!-- Kan ikke legge denne direkte i body -->
                <header id="meldinger_header_utboks" onclick="lukkHamburgerMeny()">
                    <img src="bilder/meldingIkon.png" alt="Ikon for meldinger">
                    <h1>Utboks</h1>
                    <form method="POST" action="meldinger.php">
                        <input type="submit" class="lenke_knapp" name="papirkurv" title="Meldingene du har slettet" value="Papirkurv" tabindex = "8">
                    </form>
                </header>

                <!-- Funksjon for å lukke hamburgermeny når man trykker på en del i Main -->
                <main id="meldinger_main" onclick="lukkHamburgerMeny()">  
                    <?php
                    $tabMld = 9;
                    $tabGjen = 10;

                    if($antMld > 0) { ?>
                        <form method="POST" id="meldinger_form_utboks" action="meldinger.php">
                            <input type="hidden" id="meldinger_innboks_valgt" name="mottatt" value="">
                            <?php 
                            for($i = 0; $i < count($resMld); $i++) {
                                $senderInfoQ = "select brukernavn, fnavn, enavn from bruker where bruker.idbruker = " . $resMld[$i]['mottaker'];
                                $senderInfoSTMT = $db->prepare($senderInfoQ);
                                $senderInfoSTMT->execute();
                                $resInfo = $senderInfoSTMT->fetch(PDO::FETCH_ASSOC); 

                                // Henter bildet til brukeren
                                $mottakerBildeQ = "select hvor from bilder, brukerbilde where brukerbilde.bruker = " . $resMld[$i]['mottaker'] . " and brukerbilde.bilde = bilder.idbilder";
                                $mottakerBildeSTMT = $db->prepare($mottakerBildeQ);
                                $mottakerBildeSTMT->execute();
                                $mottakerBilde = $mottakerBildeSTMT->fetch(PDO::FETCH_ASSOC);
                                $funnetMottakerBilde = $mottakerBildeSTMT->rowCount();
                                
                                if(preg_match("/\S/", $resInfo['enavn']) == 1) {
                                    $navn = $resInfo['fnavn'] . " " . $resInfo['enavn'];  
                                } else {
                                    $navn = $resInfo['brukernavn'];
                                } ?>
                                <section class="meldinger_innboks_samtale" tabindex = "<?php echo($tabMld); $tabMld++; $tabMld++; ?>">
                                    <?php if($funnetMottakerBilde > 0) {
                                        $testPaa = $mottakerBilde['hvor'];
                                        // Tester på om filen faktisk finnes
                                        if(file_exists("$lagringsplass/$testPaa")) {
                                            // Profilbilde som resultat av spørring
                                            if(file_exists("$lagringsplass/" . "thumb_" . $testPaa)) {
                                                // Hvis vi finner et miniatyrbilde bruker vi det ?>
                                                <img class="meldinger_innboks_bilde" src="bilder/opplastet/thumb_<?php echo($mottakerBilde['hvor']) ?>" alt="Profilbilde til <?php echo($navn) ?>">
                                            <?php } else { ?>
                                                <img class="meldinger_innboks_bilde" src="bilder/opplastet/<?php echo($mottakerBilde['hvor']) ?>" alt="Profilbilde til <?php echo($navn) ?>">
                                            <?php } ?>
                                        <?php } else { ?>
                                            <img class="meldinger_innboks_bilde" src="bilder/profil.png" alt="Standard profilbilde">
                                        <?php } ?>
                                    <?php } else { ?>
                                        <img class="meldinger_innboks_bilde" src="bilder/profil.png" alt="Standard profilbilde">
                                    <?php } ?>
                                    <p class="meldinger_innboks_navn">Til: <?php echo($navn) ?></p>
                                    <p class="meldinger_innboks_tid"><?php echo(date_format(date_create($resMld[$i]['tid']), "j M H:i")) ?></p>
                            
                                    <p class="meldinger_innboks_tittel"><?php echo($resMld[$i]['tittel']) ?></p>
                                </section>
                            <?php } ?>
                        </form>

                    <?php } else { ?>
                        <p>Utboksen din er tom</p>
                    <?php } ?>
                    <form method="POST" id="meldinger_form_ny" action="meldinger.php">
                        <input type="submit" id="meldinger_nyKnapp" name="ny" title="Skriv en ny melding" value="Ny melding">
                    </form>
                    <button onclick="location.href='meldinger.php'" class="lenke_knapp" title="Meldingene du har mottatt">Tilbake til innboks</button>
                </main>


            <?php } else if(isset($_POST['papirkurv'])) {
                /*--------------------------------*/
                /*--------------------------------*/
                /*----Del for å vise papirkurv----*/
                /*--------------------------------*/
                /*--------------------------------*/ ?>

                <!-- For å kunne lukke hamburgermenyen ved å kun trykke på et sted i vinduet må lukkHamburgerMeny() funksjonen ligge i deler av HTML-koden -->
                <!-- Kan ikke legge denne direkte i body -->
                <header id="meldinger_header" onclick="lukkHamburgerMeny()">
                    <img src="bilder/meldingIkon.png" alt="Ikon for meldinger">
                    <h1>Papirkurv</h1>
                    <form method="POST" action="meldinger.php">
                        <input type="submit" class="lenke_knapp" name="utboks" title="Meldingene du har sendt" value="Utboks" tabindex = "8">
                    </form>
                </header>

                <!-- Funksjon for å lukke hamburgermeny når man trykker på en del i Main -->
                <main id="meldinger_main" onclick="lukkHamburgerMeny()">  

                    <?php
                    $tabMld = 9;
                    $tabGjen = 10;

                    if($antMld > 0) { ?>
                        <form method="POST" id="meldinger_form_innboks" action="meldinger.php">
                            <input type="hidden" id="meldinger_innboks_valgt" name="mottatt" value="">
                            <?php 
                            for($i = 0; $i < count($resMld); $i++) {
                                $senderInfoQ = "select brukernavn, fnavn, enavn from bruker where bruker.idbruker = " . $resMld[$i]['sender'];
                                $senderInfoSTMT = $db->prepare($senderInfoQ);
                                $senderInfoSTMT->execute();
                                $resInfo = $senderInfoSTMT->fetch(PDO::FETCH_ASSOC); 

                                // Henter bildet til brukeren
                                $senderBildeQ = "select hvor from bilder, brukerbilde where brukerbilde.bruker = " . $resMld[$i]['sender'] . " and brukerbilde.bilde = bilder.idbilder";
                                $senderBildeSTMT = $db->prepare($senderBildeQ);
                                $senderBildeSTMT->execute();
                                $senderBilde = $senderBildeSTMT->fetch(PDO::FETCH_ASSOC);
                                $funnetSenderBilde = $senderBildeSTMT->rowCount();
                                
                                if(preg_match("/\S/", $resInfo['enavn']) == 1) {
                                    $navn = $resInfo['fnavn'] . " " . $resInfo['enavn'];  
                                } else {
                                    $navn = $resInfo['brukernavn'];
                                } ?>
                                <section class="meldinger_innboks_samtale" title="Vis denne meldingen" onclick="aapneSamtale(<?php echo($resMld[$i]['idmelding']) ?>)" tabindex = "<?php echo($tabMld); $tabMld++; $tabMld++; ?>">
                                    <?php if($funnetSenderBilde > 0) {
                                        $testPaa = $senderBilde['hvor'];
                                        // Tester på om filen faktisk finnes
                                        if(file_exists("$lagringsplass/$testPaa")) {
                                            // Profilbilde som resultat av spørring
                                            if(file_exists("$lagringsplass/" . "thumb_" . $testPaa)) {
                                                // Hvis vi finner et miniatyrbilde bruker vi det ?>
                                                <img class="meldinger_innboks_bilde" src="bilder/opplastet/thumb_<?php echo($senderBilde['hvor']) ?>" alt="Profilbilde til <?php echo($navn) ?>">
                                            <?php } else { ?>
                                                <img class="meldinger_innboks_bilde" src="bilder/opplastet/<?php echo($senderBilde['hvor']) ?>" alt="Profilbilde til <?php echo($navn) ?>">
                                            <?php } ?>
                                        <?php } else { ?>
                                            <img class="meldinger_innboks_bilde" src="bilder/profil.png" alt="Standard profilbilde">
                                        <?php } ?>
                                    <?php } else { ?>
                                        <img class="meldinger_innboks_bilde" src="bilder/profil.png" alt="Standard profilbilde">
                                    <?php } ?>
                                    <p class="meldinger_innboks_navn"><?php echo($navn) ?></p>
                                    <p class="meldinger_innboks_tid"><?php echo(date_format(date_create($resMld[$i]['tid']), "j M H:i")) ?></p>
                            
                                    <p class="meldinger_innboks_tittel"><?php echo($resMld[$i]['tittel']) ?></p>
                                </section>
                                    <img src="bilder/restoreIkon.png" alt="Gjenopprettikon" title="Gjenopprett denne meldingen" class="meldinger_innboks_restore" onclick="gjenopprettSamtale(<?php echo($resMld[$i]['idmelding']) ?>)" tabindex = "<?php echo($tabGjen); $tabGjen++; $tabGjen++; ?>">
                            <?php } ?>
                        </form>
                        <form method="POST" id="meldinger_innboks_restore">
                            <input type="hidden" id="meldinger_innboks_restore_valgt" name="gjenopprettMelding" value="">
                        </form>

                    <?php } else { ?>
                        <p>Papirkurven din er tom</p>
                    <?php } ?>

                    <form method="POST" id="meldinger_form_ny" action="meldinger.php">
                        <input type="submit" id="meldinger_nyKnapp" name="ny" title="Skriv en ny melding" value="Ny melding">
                    </form>
                    <button onclick="location.href='meldinger.php'" class="lenke_knapp" title="Meldingene du har mottatt">Tilbake til innboks</button>

                </main>
            <?php } else { 
                /*--------------------------------*/
                /*--------------------------------*/
                /*----Del for å vise innboksen----*/
                /*--------------------------------*/
                /*--------------------------------*/ ?>

                <!-- For å kunne lukke hamburgermenyen ved å kun trykke på et sted i vinduet må lukkHamburgerMeny() funksjonen ligge i deler av HTML-koden -->
                <!-- Kan ikke legge denne direkte i body -->
                <header id="meldinger_header" onclick="lukkHamburgerMeny()">
                    <img src="bilder/meldingIkon.png" alt="Ikon for meldinger">
                    <h1>Innboks</h1>
                    <form method="POST" action="meldinger.php">
                        <input type="submit" class="lenke_knapp" name="utboks" title="Meldingene du har sendt" value="Utboks" tabindex = "8">
                    </form>
                    <form method="POST" action="meldinger.php">
                        <input type="submit" class="lenke_knapp" name="papirkurv" title="Meldingene du har slettet"  value="Papirkurv" tabindex = "9">
                    </form>
                </header>

                <!-- Funksjon for å lukke hamburgermeny når man trykker på en del i Main -->
                <main id="meldinger_main" onclick="lukkHamburgerMeny()">  
                    <?php if(isset($_GET['meldingsendt'])) { ?>
                        <p id="mldOK">Melding sendt</p>

                    <?php } else if(isset($_GET['meldingslettet'])) { ?>
                        <p id="mldOK">Melding sendt til papirkurv</p>

                    <?php } else if(isset($_GET['error']) && $_GET['error'] == 1) { ?>
                        <p id="mldFEIL">Kunne ikke sende melding</p>
                        
                    <?php } else if(isset($_GET['error']) && $_GET['error'] == 2) { ?>
                        <p id="mldFEIL">Kunne ikke slette meldingen</p>
                    <?php } ?>

                    <?php

                    $tabMld = 10;
                    $tabSoppel = 11;

                    if($antMld > 0) { ?>
                        <form method="POST" id="meldinger_form_innboks" action="meldinger.php">
                            <input type="hidden" id="meldinger_innboks_valgt" name="mottatt" value="">
                            <?php 
                            for($i = 0; $i < count($resMld); $i++) {
                                $senderInfoQ = "select brukernavn, fnavn, enavn from bruker where bruker.idbruker = " . $resMld[$i]['sender'];
                                $senderInfoSTMT = $db->prepare($senderInfoQ);
                                $senderInfoSTMT->execute();
                                $resInfo = $senderInfoSTMT->fetch(PDO::FETCH_ASSOC); 

                                // Henter bildet til brukeren
                                $senderBildeQ = "select hvor from bilder, brukerbilde where brukerbilde.bruker = " . $resMld[$i]['sender'] . " and brukerbilde.bilde = bilder.idbilder";
                                $senderBildeSTMT = $db->prepare($senderBildeQ);
                                $senderBildeSTMT->execute();
                                $senderBilde = $senderBildeSTMT->fetch(PDO::FETCH_ASSOC);
                                $funnetSenderBilde = $senderBildeSTMT->rowCount();
                                
                                if(preg_match("/\S/", $resInfo['enavn']) == 1) {
                                    $navn = $resInfo['fnavn'] . " " . $resInfo['enavn'];  
                                } else {
                                    $navn = $resInfo['brukernavn'];
                                }
                                if($resMld[$i]['lest'] == 1) { ?>
                                    <section class="meldinger_innboks_samtale" title="Vis denne meldingen" onclick="aapneSamtale(<?php echo($resMld[$i]['idmelding']) ?>)" tabindex = "<?php echo($tabMld); $tabMld++; $tabMld++; ?>">
                                <?php } else { ?>
                                    <section class="meldinger_innboks_samtale_ulest" title="Vis denne uleste meldingen" onclick="aapneSamtale(<?php echo($resMld[$i]['idmelding']) ?>)" tabindex = "<?php echo($tabMld); $tabMld++; $tabMld++; ?>">
                                <?php } 
                                    if($funnetSenderBilde > 0) {
                                        $testPaa = $senderBilde['hvor'];
                                        // Tester på om filen faktisk finnes
                                        if(file_exists("$lagringsplass/$testPaa")) {
                                            // Profilbilde som resultat av spørring
                                            if(file_exists("$lagringsplass/" . "thumb_" . $testPaa)) {
                                                // Hvis vi finner et miniatyrbilde bruker vi det ?>
                                                <img class="meldinger_innboks_bilde" src="bilder/opplastet/thumb_<?php echo($senderBilde['hvor']) ?>" alt="Profilbilde til <?php echo($navn) ?>">
                                            <?php } else { ?>
                                                <img class="meldinger_innboks_bilde" src="bilder/opplastet/<?php echo($senderBilde['hvor']) ?>" alt="Profilbilde til <?php echo($navn) ?>">
                                            <?php } ?>
                                        <?php } else { ?>
                                            <img class="meldinger_innboks_bilde" src="bilder/profil.png" alt="Standard profilbilde">
                                        <?php } ?>
                                    <?php } else { ?>
                                        <img class="meldinger_innboks_bilde" src="bilder/profil.png" alt="Standard profilbilde">
                                    <?php } ?>
                                    <p class="meldinger_innboks_navn"><?php echo($navn) ?></p>
                                    <p class="meldinger_innboks_tid"><?php echo(date_format(date_create($resMld[$i]['tid']), "j M H:i")) ?></p>
                            
                                    <p class="meldinger_innboks_tittel"><?php echo($resMld[$i]['tittel']) ?></p>
                                </section>
                                    <img src="bilder/soppelIkon.png" alt="Søppelikon" title="Slett denne meldingen" class="meldinger_innboks_soppel" onclick="slettSamtale(<?php echo($resMld[$i]['idmelding']) ?>)" tabindex = "<?php echo($tabSoppel); $tabSoppel++; $tabSoppel++; ?>">
                            <?php } ?>
                        </form>
                        <form method="POST" id="meldinger_innboks_soppel">
                            <input type="hidden" id="meldinger_innboks_soppel_valgt" name="slettMelding" value="">
                        </form>

                    <?php } else { ?>
                        <p>Innboksen din er tom</p>
                    <?php } ?>

                    <form method="POST" id="meldinger_form_ny" action="meldinger.php">
                        <input type="submit" id="meldinger_nyKnapp" name="ny" title="Skriv en ny melding"  value="Ny melding">
                    </form>

                </main>

            <?php } ?>
            
            <!-- Knapp som vises når du har scrollet i vinduet, tar deg tilbake til toppen -->
            <button onclick="tilbakeTilTopp()" id="toppKnapp" title="Toppen"><img src="bilder/pilopp.png" alt="Tilbake til toppen"></button>

            <!-- Footer, epost er for øyeblikket på en catch-all, videresendes til RK -->
            <footer>
                <p class=footer_beskrivelse>&copy; Klimate 2020 | <a href="mailto:kontakt@klimate.no">Kontakt oss</a>
                    <!-- Om brukeren ikke er administrator eller redaktør, vis link for søknad til å bli redaktør -->
                    <?php if ($_SESSION['brukertype'] == "3") { ?> | <a href="soknad.php">Søknad om å bli redaktør</a><?php } ?>
                </p>
            </footer>
    </body>

    <!-- Denne siden er utviklet av Robin Kleppang og Glenn Petter Pettersen, siste gang endret 07.02.2020 -->
    <!-- Denne siden er kontrollert av Glenn Petter Pettersen, siste gang 07.02.2020 -->

</html>