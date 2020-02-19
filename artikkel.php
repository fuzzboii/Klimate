<?php
session_start();

//------------------------------//
// Innstillinger, faste variable //
//------------------------------//
include("innstillinger.php");

// Enkel test som gjør det mulig å beholde brukerinput etter siden er lastet på nytt (Form submit)
$input_tittel = "";
$input_ingress = "";
$input_innhold = "";

if (isset($_SESSION['input_tittel'])) {
    // Legger innhold i variable som leses senere på siden
    $input_tittel = $_SESSION['input_tittel'];
    $input_ingress = $_SESSION['input_ingress'];
    $input_innhold = $_SESSION['input_innhold'];
    // Sletter innholdet så dette ikke eksisterer utenfor denne siden
    unset($_SESSION['input_tittel']);
    unset($_SESSION['input_ingress']);
    unset($_SESSION['input_innhold']);
}

if (isset($_POST['publiserArtikkel'])) {

    $_SESSION['input_tittel'] = $_POST['tittel'];
    $_SESSION['input_ingress'] = $_POST['ingress'];
    $_SESSION['input_innhold'] = $_POST['innhold'];

    if (strlen($_POST['tittel']) <= 45 && strlen($_POST['tittel']) > 0) {
        if (strlen($_POST['ingress']) <= 255 && strlen($_POST['ingress']) > 0) {
            if (strlen($_POST['innhold'] <= 1000) && strlen($_POST['tittel']) > 0) {
                // Tar utgangspunkt i at bruker ikke har lastet opp bilde
                $harBilde = false;

                // Sanitiserer innholdet før det blir lagt i databasen
                $tittel = filter_var($_POST['tittel'], FILTER_SANITIZE_STRING);
                $ingress = filter_var($_POST['ingress'], FILTER_SANITIZE_STRING);
                $innhold = filter_var($_POST['innhold'], FILTER_SANITIZE_STRING);
                
                // Spørringen som oppretter artikkelen
                $nyArtikkelQ = "insert into artikkel(artnavn, artingress, arttekst, bruker) values('" . $tittel . "', '" . $ingress . "', '" . $innhold . "', '" . $_SESSION['idbruker'] . "')";
                $nyArtikkelSTMT = $db->prepare($nyArtikkelQ);
                $nyArtikkelSTMT->execute();
                $artikkelid = $db->lastInsertId();
                
                // Del for filopplastning
                if (is_uploaded_file($_FILES['bilde']['tmp_name'])) {
                    // Kombinerer artikkel med den siste artikkelid'en
                    $navn = "artikkel" . $artikkelid;
                    // Henter filtypen
                    $filtype = "." . substr($_FILES['bilde']['type'], 6, 4);
                    // Kombinerer navnet med filtypen
                    $bildenavn = $navn . $filtype;
                    // Selve prosessen som flytter bildet til bestemt lagringsplass
                    if (move_uploaded_file($_FILES['bilde']['tmp_name'], "$lagringsplass/$bildenavn")) {
                        $harbilde = true;
                    } else {
                        // Feilmelding her
                    }
                }
                if ($harbilde == true) {
                    // Legger til bildet i databasen, dette kan være sin egne spørring
                    $nyttBildeQ = "insert into bilder(hvor) values('" . $bildenavn . "')";
                    $nyttBildeSTMT = $db->prepare($nyttBildeQ);
                    $nyttBildeSTMT->execute();
                    // Returnerer siste bildeid'en
                    $bildeid = $db->lastInsertId();

                    // Spørringen som lager koblingen mellom bilder og artikkel
                    $nyKoblingQ = "insert into artikkelbilde(idartikkel, idbilde) values('" . $artikkelid . "', '" . $bildeid . "')";
                    $nyKoblingSTMT = $db->prepare($nyKoblingQ);
                    $nyKoblingSTMT->execute();
                }

                header('Location: artikkel.php?artikkel=' . $artikkelid);

            } else { header('Location: artikkel.php?nyartikkel=error3'); } // Innhold tomt / for langt
        } else { header('Location: artikkel.php?nyartikkel=error2'); } // Ingress tomt / for langt
    } else { header('Location: artikkel.php?nyartikkel=error1'); } // Tittel tomt / for langt
}


if (isset($_POST['slettDenne'])) {
    // Sjekker om vi fortsatt er på riktig side (Og at bruker ikke har endret IDen vi ønsker å slette selv)
    if ($_POST['slettDenne'] == $_GET['artikkel']) {

        // Henter henvisningen til bildet fra databasen.
        $slettBildeFQ = "select hvor from artikkelbilde, bilder where artikkelbilde.idbilde = bilder.idbilder and artikkelbilde.idartikkel = " . $_POST['slettDenne'];
        $slettBildeFSTMT = $db->prepare($slettBildeFQ);
        $slettBildeFSTMT->execute();
        $bildenavn = $slettBildeFSTMT->fetch(PDO::FETCH_ASSOC); 

        $testPaa = $bildenavn['hvor'];
        // Test om det finnes en fil med samme navn
        if(file_exists("$lagringsplass/$testPaa")) {
            // Sletter bildet
            unlink("$lagringsplass/$testPaa");
        }

        // Begynner med å slette referansen til bildet artikkelen har
        $slettBildeQ = "delete from artikkelbilde where idartikkel = " . $_POST['slettDenne'];
        $slettBildeSTMT = $db->prepare($slettBildeQ);
        $slettBildeSTMT->execute();

        // Sletter så artikkelen
        $slettingQ = "delete from artikkel where idartikkel = " . $_POST['slettDenne'];
        $slettingSTMT= $db->prepare($slettingQ);
        $slettingSTMT->execute();

        $antallSlettet = $slettingSTMT->rowCount();

        if ($antallSlettet > 0) {
            header('location: artikkel.php?slettingok');
        } else {
            header('location: artikkel.php?slettingfeil');
        }
    }
}

// tabindex som skal brukes til å bestemme startpunkt på visningen av arrangementene, denne endres hvis vi legger til flere elementer i navbar eller lignende
$tabindex = 8;

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
            <?php
                if(isset($_GET['nyartikkel'])) { ?>
                    Ny artikkel
                <?php } else if (isset($_GET['artikkel'])) {
                    $hentTittelQ = "select artnavn from artikkel where idartikkel = " . $_GET['artikkel'];
                    $hentTittelSTMT = $db -> prepare($hentTittelQ);
                    $hentTittelSTMT->execute();
                    $artikkel_title = $hentTittelSTMT->fetch(PDO::FETCH_ASSOC);
                    echo($artikkel_title['artnavn']);
                } else { ?>
                    Artikler
            <?php } ?> 
        </title>
        <!-- Henter inn ekstern stylesheet -->
        <link rel="stylesheet" type="text/css" href="stylesheet.css">
        <!-- Henter inn favicon, bildet som dukker opp i fanene i nettleseren -->
        <link rel='icon' href='bilder/favicon.png' type='image/x-icon'>
        <!-- Henter inn JavaScript -->
        <script language="JavaScript" src="javascript.js"> </script>
    </head>

    <body id="artikkel_body" onload="hentSide('side_artikkel', 'artikkel_tilbKnapp', 'artikkel_nesteKnapp'), artTabbing()" onresize="hentSide('side_artikkel', 'artikkel_tilbKnapp', 'artikkel_nesteKnapp')">
        <!-- Begynnelse på øvre navigasjonsmeny -->
        <nav class="navTop"> 
            <!-- Bruker et ikon som skal åpne gardinmenyen, henviser til funksjonen hamburgerMeny i javascript.js -->
            <!-- javascript:void(0) blir her brukt så siden ikke scroller til toppen av seg selv når du trykker på hamburger-ikonet -->
            <a class="bildeKontroll" href="javascript:void(0)" onclick="hamburgerMeny()" tabindex="6">
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
                    <a class="bildeKontroll" href="javascript:void(0)" onClick="location.href='profil.php?bruker=<?php echo($_SESSION['idbruker']) ?>'" tabindex="5">
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
                    <a class="bildeKontroll" href="javascript:void(0)" onClick="location.href='profil.php?bruker=<?php echo($_SESSION['idbruker']) ?>'" tabindex="5">
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
                    <button name="loggUt" id="registrerKnapp" tabindex="4">LOGG UT</button>
                </form>
            <?php } else { ?>
                <!-- Vises når bruker ikke er innlogget -->
                <button id="registrerKnapp" onClick="location.href='registrer.php'" tabindex="5">REGISTRER</button>
                <button id="logginnKnapp" onClick="location.href='logginn.php'" tabindex="4">LOGG INN</button>
            <?php } ?>

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
                <?php if (isset($_SESSION['idbruker'])) { ?>
                    <!-- Hva som vises om bruker er innlogget -->

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
                <?php } else { ?>
                    <!-- Hvis bruker ikke er innlogget -->
                    <a class = "menytab" tabIndex = "-1" href="arrangement.php">Arrangementer</a>
                    <a class = "menytab" tabIndex = "-1" href="artikkel.php">Artikler</a>
                    <a class = "menytab" tabIndex = "-1" href="#">Diskusjoner</a>
                    <a class = "menytab" tabIndex = "-1" href="sok.php">Avansert Søk</a>
                <?php } ?>
            </section>
        </section>

        <!-- Funksjon for å lukke hamburgermeny når man trykker på en del i Main -->
        
                <?php if(isset($_GET['artikkel'])){
                    // Henter artikkelen bruker ønsker å se
                    $hent = "select * from artikkel, bruker where bruker=idbruker and idartikkel = " . $_GET['artikkel'];
                    $stmt = $db->prepare($hent);
                    $stmt->execute();
                    $artikkel = $stmt->fetch(PDO::FETCH_ASSOC);
                    $antallArtikkel = $stmt->rowCount();
                    // rowCount() returnerer antall resultater fra database, er dette null finnes det ikke noe artikkel med denne artikkelid i databasen
                    if ($antallArtikkel == 0) { ?>
                        <!-- Del for å vise feilmelding til bruker om at artikkel ikke eksisterer -->
                        <h1>Artikkel ikke funnet</h1>
                    <?php } else { 
                        // ------------------------------ artikler som er klikket på -----------------------------
                        // Del for å vise en spesifik artikkel
                        // Henter bilde fra database utifra artikkelid
                        $hentBilde = "select hvor from bilder, artikkelbilde where artikkelbilde.idartikkel = " . $_GET['artikkel'] . " and artikkelbilde.idbilde = bilder.idbilder";
                        $stmtBilde = $db->prepare($hentBilde);
                        $stmtBilde->execute();
                        $bilde = $stmtBilde->fetchAll(PDO::FETCH_ASSOC);
                        $antallBilderFunnet = $stmtBilde->rowCount();
                        ?>
                        <!--  -->
                        <!-- Innholdet i påklikket artikkel -->
                        <main id="artikkel_main" style="margin-top: 6em;"onclick="lukkHamburgerMeny()">
                        <!-- rowCount() returnerer antall resultater fra database, er dette null finnes det ikke noe bilde i databasen -->
                        <?php if ($antallBilderFunnet != 0) { ?>
                            <!-- Hvis vi finner et bilde til artikkelen viser vi det -->
                            <section class="bildeArtikkelSeksjon">
                                <?php // Tester på om filen faktisk finnes
                                $testPaa = $bilde['hvor'];
                                if(file_exists("$lagringsplass/$testPaa")) {  ?>  
                                    <img class="bildeArtikkel" src="bilder/opplastet/<?php echo($bilde["hvor"]) ?>" alt="Bilde av artikkel">  
                                <?php } ?>

                            </section>
                        <?php } ?>
                            <section class="Artikkel_innhold">
                                <h1 class="artikkel_overskrift"><?php echo($artikkel['artnavn'])?></h1>
                                <p><?php echo($artikkel['artingress'])?></p>
                                <p><?php echo($artikkel['arttekst'])?></p>
                            </section>
                            <section class="ForfatterInfo">
                                <?php if(preg_match("/\S/", $artikkel['enavn']) == 0){?>
                                    <p>Skrevet av</p>
                                    <a class="artikkelForfatter" onClick="location.href='profil.php?bruker=<?php echo($artikkel['bruker'])?>'"><?php echo($artikkel['brukernavn'])?></a>
                                <?php } else {?> 
                                    <p>Skrevet av</p> <a class="artikkelForfatter" onClick="location.href='profil.php?bruker=<?php echo($artikkel['bruker'])?>'"><?php echo($artikkel['fnavn'] . " " . $artikkel['enavn'])?></a>
                                <?php }?>
                            </section>
                            <button id="artikkelValgt_tilbKnapp" onClick="location.href='artikkel.php'">Tilbake</button>
                            <?php 
                            if(isset($_SESSION['idbruker'])) {
                                $hentEierQ = "select bruker from artikkel where bruker = " . $_SESSION['idbruker'] . " and idartikkel = " . $_GET['artikkel'];
                                $hentEierSTMT = $db->prepare($hentEierQ);
                                $hentEierSTMT->execute();
                                $artikkelEier = $hentEierSTMT->fetch(PDO::FETCH_ASSOC);

                                if ($artikkelEier != false || $_SESSION['brukertype'] == 1) { ?>
                                    <input type="button" id="artikkel_slettKnapp" onclick="bekreftMelding('artikkel_bekreftSlett')" value="Slett denne artikkelen">
                                    <section id="artikkel_bekreftSlett" style="display: none;">
                                        <section id="artikkel_bekreftSlettInnhold">
                                            <h2>Sletting</h2>
                                            <p>Er du sikker på av du vil slette denne artikkelen?</p>
                                            <form method="POST" action="artikkel.php?artikkel=<?php echo($_GET['artikkel'])?>">
                                                <button id="artikkel_bekreftSlettKnapp" name="slettDenne" value="<?php echo($_GET['artikkel']) ?>">Slett</button>
                                            </form>
                                            <button id="artikkel_avbrytKnapp" onclick="bekreftMelding('artikkel_bekreftSlett')">Avbryt</button>
                                        </section>
                                    </section>
                                <?php } ?>
                            <?php } ?>
                            
                        </main>
                    <?php } ?>
                <?php  } else if (isset($_GET['nyartikkel']) && ($_SESSION['brukertype'] == 2 || $_SESSION['brukertype'] == 1)) { ?>      
        
                    <header class="artikkel_header" onclick="lukkHamburgerMeny()">
                        <h1>Ny artikkel</h1>
                    </header>

                    <main id="artikkel_mainNy" onclick="lukkHamburgerMeny()">

                    <article id="artikkel_articleNy">
                        <form method="POST" action="artikkel.php" enctype="multipart/form-data">
                            <h2>Tittel</h2>
                            <input id="artikkel_inputTittel" type="text" maxlength="45" name="tittel" value="<?php echo($input_tittel) ?>" placeholder="Skriv inn tittel" autofocus required>
                            <h2>Ingress</h2>
                            <textarea id="artikkel_inputIngress" maxlength="255" name="ingress" rows="3" cols="35" placeholder="Skriv inn inngress" required><?php echo($input_ingress) ?></textarea>
                            <h2>Innhold</h2>
                            <textarea id="artikkel_inputInnhold" maxlength="1000" name="innhold" rows="5" cols="35" placeholder="Skriv inn innhold" required><?php echo($input_innhold) ?></textarea>
                            <h2>Bilde</h2>
                            <input type="file" name="bilde" id="bilde" accept=".jpg, .jpeg, .png">
                            
                            <?php if($_GET['nyartikkel'] == "error1"){ ?>
                                <p id="mldFEIL">Tittel for lang eller ikke oppgitt</p>
                        
                            <?php } else if($_GET['nyartikkel'] == "error2"){ ?>
                                <p id="mldFEIL">Ingress for lang eller ikke oppgitt</p>
                            
                            <?php } else if($_GET['nyartikkel'] == "error3") { ?>
                                <p id="mldFEIL">Innhold for lang eller ikke oppgitt</p>
                            <?php } ?>

                            <input id="artikkel_submitNy" type="submit" name="publiserArtikkel" value="Opprett artikkel">
                        </form>
                    </article>

                <?php } else {
                    // -------------------- Artikler som vises på artikkel.php forside----------------
                
                    // Del for å vise alle artikler 
                    $hentAlleArt = "select idartikkel, artnavn, artingress, arttekst, brukernavn, enavn, fnavn, bruker
                                    FROM artikkel, bruker
                                    WHERE bruker=idbruker order by idartikkel desc";
                
                    $stmtArt = $db->prepare($hentAlleArt);
                    $stmtArt->execute();
                    $resArt = $stmtArt->fetchAll(PDO::FETCH_ASSOC); 
                    
                    // Variabel som brukes til å fortelle når vi kan avslutte side_sok
                    $avsluttTag = 0;
                    $antallSider = 0;

                    $resAntall = $stmtArt->rowCount(); 
                    ?>            
        
                    <header class="artikkel_header" onclick="lukkHamburgerMeny()">
                        <h1>Artikler</h1>
                    </header>
                    <main id="artikkel_main" onclick="lukkHamburgerMeny()">
                        <section id="artikkel_redpanel">
                            <?php if(isset($_SESSION['brukertype']) && ($_SESSION['brukertype'] == 2 || $_SESSION['brukertype'] == 1)) { ?>
                            <a href="artikkel.php?nyartikkel" tabindex="-1"><p>Ny artikkel</p></a>
                            <a href="artikkel.php?nyartikkel" tabindex="7"> <!-- VIKTIG, tabindex -->
                                <img src="bilder/plussIkon.png" alt="Plussikon for å opprette ny artikkel">
                            </a>
                            <?php } ?>
                        </section>
                        <?php if(isset($_GET['slettingok'])) { ?> <p id="mldOK">Du har slettet artikkelen</p> <?php } ?>
                        <?php if(isset($_GET['slettingfeil'])) { ?> <p id="mldFEIL">Kunne ikke slette artikkelen</p> <?php } ?>
                    <?php if ($resAntall > 0 ) { ?>
                        <?php for ($j = 0; $j < count($resArt); $j++) {
                            // Hvis rest av $j delt på 8 er 0, start section (Ny side)
                            if ($j % 8 == 0) { ?>
                            <section class="side_artikkel">
                            <?php $antallSider++; } $avsluttTag++; ?>
                            <section class="res_artikkel" onClick="location.href='artikkel.php?artikkel=<?php echo($resArt[$j]['idartikkel']) ?>'" tabindex = <?php echo($tabindex); $tabindex++; ?>>
                                <figure class="infoBoks_artikkel">

                                    <?php // Henter bilde til artikkel
                                    $hentArtBilde = "select hvor from bilder, artikkelbilde where artikkelbilde.idartikkel = " . $resArt[$j]['idartikkel'] . " and artikkelbilde.idbilde = bilder.idbilder";
                                    $stmtArtBilde = $db->prepare($hentArtBilde);
                                    $stmtArtBilde->execute();
                                    $resBilde = $stmtArtBilde->fetch(PDO::FETCH_ASSOC);
                                    
                                    if (!$resBilde) { ?>
                                        <!-- Standard atikkelbilde om redaktør ikke har lastet opp noe enda -->
                                        <img class="BildeBoks_artikkel" src="bilder/stockevent.jpg" alt="Bilde av Oleg Magni fra Pexels">
                                    <?php } else {
                                        // Tester på om filen faktisk finnes
                                        $testPaa = $resBilde['hvor'];
                                        if(file_exists("$lagringsplass/$testPaa")) {  ?>  
                                            <!-- Artikkeltbilde som resultat av spørring -->
                                            <img class="BildeBoks_artikkel" src="bilder/opplastet/<?php echo($resBilde['hvor'])?>" alt="Artikkelbilde for <?php echo($resArt[$j]['artnavn'])?>">
                                        <?php } else { ?>
                                            <img class="BildeBoks_artikkel" src="bilder/stockevent.jpg" alt="Bilde av Oleg Magni fra Pexels">
                                    <?php }
                                    } ?>
                                </figure>
                                <!-- brukerens profilbilde -->
                                <!-- blir hentet fram avhengig av hvilken bruker som har skrevet artikkelen -->
                                <?php
                                $hentPb="select bruker, hvor from brukerbilde, bilder where bilde=idbilder and bruker= " . $resArt[$j]["bruker"] ;
                                $stmtHentPb = $db->prepare($hentPb);
                                $stmtHentPb->execute();
                                $brukerPB = $stmtHentPb->fetch(PDO::FETCH_ASSOC);
                                ?>
                                <img class="navn_artikkel_bilde" src="bilder/opplastet/<?php echo($brukerPB["hvor"])?>">
                                <?php 
                                // Hvis bruker ikke har etternavn (Eller har oppgitt et mellomrom eller lignende som navn) hvis brukernavn
                                if (preg_match("/\S/", $resArt[$j]['enavn']) == 0) { ?>
                                    <p class="navn_artikkel"><?php echo($resArt[$j]['brukernavn'])?></p>
                                <?php } else { ?>
                                    <p class="navn_artikkel"><?php echo($resArt[$j]['fnavn']) ?> <?php echo($resArt[$j]['enavn']) ?></p>
                                <?php } ?>
                                <h2><?php echo($resArt[$j]['artnavn'])?></h2>
                                <p><?php echo($resArt[$j]['artingress'])?></p>
                            </section>
                            <?php

                            // Hvis telleren har nådd 8
                            if (($avsluttTag == 8) || $j == (count($resArt) - 1)) { ?>
                                </section>     
                            <?php 
                                // Sett telleren til 0, mulighet for mer enn 2 sider
                                $avsluttTag = 0;
                            }
                        }
                    } ?>
                    <section id="artikkel_bunnSection">
                        <?php if ($antallSider > 1) {?>
                            <p id="sok_antSider">Antall sider: <?php echo($antallSider) ?></p>
                            <button type="button" id="artikkel_tilbKnapp" onclick="visForrigeSide('side_artikkel', 'artikkel_tilbKnapp', 'artikkel_nesteKnapp')">Forrige</button>
                            <button type="button" id="artikkel_nesteKnapp" onclick="visNesteSide('side_artikkel', 'artikkel_tilbKnapp', 'artikkel_nesteKnapp')">Neste</button>
                        <?php } ?>
                    </section>
                <?php }  ?>
        </main>
        
        <!-- Knapp som vises når du har scrollet i vinduet, tar deg tilbake til toppen -->
        <button onclick="tilbakeTilTopp()" id="toppKnapp" title="Toppen"><img src="bilder/pilopp.png" alt="Tilbake til toppen"></button>

        <!-- Footer, epost er for øyeblikket på en catch-all, videresendes til RK -->
        <footer>
            <p class=footer_beskrivelse>&copy; Klimate 2020 | <a href="mailto:kontakt@klimate.no">Kontakt oss</a>
                <!-- Om brukeren ikke er administrator eller redaktør, vis link for søknad til å bli redaktør -->
                <?php if (isset($_SESSION['idbruker']) and $_SESSION['brukertype'] == "3") { ?> | <a href="soknad.php">Søknad om å bli redaktør</a><?php } ?>
            </p>
        </footer>
    </body>

    <!-- Denne siden er utviklet av Robin Kleppang, Aron Snekkestad, Ajdin Bajrovic siste gang endret 07.02.2020 -->
    <!-- Denne siden er kontrollert av Aron Snekkestad, siste gang 07.02.2020 -->

</html>