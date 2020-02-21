<?php
session_start();

//------------------------------//
// Innstillinger, faste variable //
//------------------------------//
include("innstillinger.php");

// Kun innloggede brukere kan se meldinger
if (!isset($_SESSION['idbruker'])) {
    header("Location: default.php?error=1");
}

// Browser må validere cache med server før cached kopi kan benyttes
// Dette gjør at man kan gå frem og tilbake i innboksen uten at man får ERR_CACHE_MISS
header("Cache-Control: no cache");

if(isset($_POST['mottatt'])) {
    var_dump($_POST['mottatt']);
    // Er vi her henter vi ting som brukes i visning av valgt melding
    $samtaleMeldingerQ = "select tittel, tekst, tid, lest, sender
                            from melding where idmelding = " . $_POST['mottatt'] . " and mottaker = " . $_SESSION['idbruker'];
    $samtaleMeldingerSTMT = $db->prepare($samtaleMeldingerQ);
    $samtaleMeldingerSTMT->execute();
    $resMld = $samtaleMeldingerSTMT->fetch(PDO::FETCH_ASSOC); 

    $antMld = $samtaleMeldingerSTMT->rowCount();

    if($antMld > 0) {
        $fantSamtale = true;

        $senderInfoQ = "select brukernavn, fnavn, enavn, hvor from bruker, brukerbilde, bilder where bruker.idbruker = " . $resMld['sender'] . 
                        " and bruker.idbruker = brukerbilde.bruker and brukerbilde.bilde = bilder.idbilder";
        $senderInfoSTMT = $db->prepare($senderInfoQ);
        $senderInfoSTMT->execute();
        $resInfo = $senderInfoSTMT->fetch(PDO::FETCH_ASSOC); 
        
        if(preg_match("/\S/", $resInfo['enavn']) == 1) {
            $navn = $resInfo['fnavn'] . " " . $resInfo['enavn'];  
        } else {
            $navn = $resInfo['brukernavn'];
        }

    } else {
        $fantSamtale = false;
    }

} else if(isset($_POST['ny'])) {
    // Er vi her henter vi ting som brukes i ny melding

} else if(isset($_POST['utboks'])) {
    // Er vi her henter vi ting som brukes i utboksen

} else {
    // Er vi her henter vi ting som brukes i innboksen
    $mottattMeldingerQ = "select idmelding, tittel, tid, lest, sender
                            from melding where mottaker = " . $_SESSION['idbruker'];
    $mottattMeldingerSTMT = $db->prepare($mottattMeldingerQ);
    $mottattMeldingerSTMT->execute();
    $resMld = $mottattMeldingerSTMT->fetchAll(PDO::FETCH_ASSOC); 

    $antMld = $mottattMeldingerSTMT->rowCount();
    

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
                Samtale med <?php echo($navn); ?>
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

    <body class="innhold">
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
                    <a class = "menytab" tabIndex = "-1" href="meldinger.php">Innboks</a>
                    <a class = "menytab" tabIndex = "-1" href="backend.php">Oversikt</a>
                    <a class = "menytab" tabIndex = "-1" href="konto.php">Konto</a>
                    <a class = "menytab" tabIndex = "-1" href="sok.php">Avansert Søk</a>
                </section>
            </section>
            <?php if(isset($_POST['mottatt'])) { ?>
                <!-- For å kunne lukke hamburgermenyen ved å kun trykke på et sted i vinduet må lukkHamburgerMeny() funksjonen ligge i deler av HTML-koden -->
                <!-- Kan ikke legge denne direkte i body -->
                <header id="meldinger_header" onclick="lukkHamburgerMeny()">
                    <img src="bilder/meldingIkon.png" alt="Ikon for meldinger">
                    <h1>Mottatt fra</h1>
                </header>

                <!-- Funksjon for å lukke hamburgermeny når man trykker på en del i Main -->
                <main id="meldinger_main" onclick="lukkHamburgerMeny()"> 
                    <?php if($fantSamtale == true) { ?>
                        <img id="meldinger_sender_bilde" src="bilder/opplastet/<?php echo($resInfo['hvor']) ?>" alt="Profilbilde til <?php echo($navn) ?>">
                        <p><?php echo($navn) ?></p>
                        <p><?php echo($resMld['tid']) ?></p>
                        <p><?php echo($resMld['tittel']) ?></p>
                        <p><?php echo($resMld['tekst']) ?></p>
                    <?php } else { ?>
                        <p>Kunne ikke vise denne meldingen</p>
                    <?php } ?>
                    <button id="meldinger_tilbKnapp" onClick="location.href='meldinger.php'">Tilbake</button>
                </main>
            
            <?php } else if(isset($_POST['ny'])) { ?>
                <!-- For å kunne lukke hamburgermenyen ved å kun trykke på et sted i vinduet må lukkHamburgerMeny() funksjonen ligge i deler av HTML-koden -->
                <!-- Kan ikke legge denne direkte i body -->
                <header id="meldinger_header" onclick="lukkHamburgerMeny()">
                    <img src="bilder/meldingIkon.png" alt="Ikon for meldinger">
                    <h1>Ny melding</h1>
                </header>

                <!-- Funksjon for å lukke hamburgermeny når man trykker på en del i Main -->
                <main id="meldinger_main" onclick="lukkHamburgerMeny()"> 
                    <?php var_dump($_POST['ny']); ?>
                </main>
            
            <?php } else if(isset($_POST['utboks'])) { ?>
                <!-- For å kunne lukke hamburgermenyen ved å kun trykke på et sted i vinduet må lukkHamburgerMeny() funksjonen ligge i deler av HTML-koden -->
                <!-- Kan ikke legge denne direkte i body -->
                <header id="meldinger_header" onclick="lukkHamburgerMeny()">
                    <img src="bilder/meldingIkon.png" alt="Ikon for meldinger">
                    <h1>Utboks</h1>
                </header>

                <!-- Funksjon for å lukke hamburgermeny når man trykker på en del i Main -->
                <main id="meldinger_main" onclick="lukkHamburgerMeny()">  
                    <?php var_dump($_POST['utboks']); ?>
                </main>

            <?php } else { ?>
                <!-- For å kunne lukke hamburgermenyen ved å kun trykke på et sted i vinduet må lukkHamburgerMeny() funksjonen ligge i deler av HTML-koden -->
                <!-- Kan ikke legge denne direkte i body -->
                <header id="meldinger_header" onclick="lukkHamburgerMeny()">
                    <img src="bilder/meldingIkon.png" alt="Ikon for meldinger">
                    <h1>Innboks</h1>
                </header>

                <!-- Funksjon for å lukke hamburgermeny når man trykker på en del i Main -->
                <main id="meldinger_main" onclick="lukkHamburgerMeny()">  

                    <?php
                    if($antMld > 0) { ?>
                        <form method="POST" id="meldinger_form" action="meldinger.php">
                            <input type="hidden" id="meldinger_innboks_valgt" name="mottatt" value="">
                            <?php 
                            for($i = 0; $i < count($resMld); $i++) {
                                $senderInfoQ = "select brukernavn, fnavn, enavn, hvor from bruker, brukerbilde, bilder where bruker.idbruker = " . $resMld[$i]['sender'] . 
                                                " and bruker.idbruker = brukerbilde.bruker and brukerbilde.bilde = bilder.idbilder";
                                $senderInfoSTMT = $db->prepare($senderInfoQ);
                                $senderInfoSTMT->execute();
                                $resInfo = $senderInfoSTMT->fetch(PDO::FETCH_ASSOC); 
                                
                                if(preg_match("/\S/", $resInfo['enavn']) == 1) {
                                    $navn = $resInfo['fnavn'] . " " . $resInfo['enavn'];  
                                } else {
                                    $navn = $resInfo['brukernavn'];
                                } ?>
                                <section class="meldinger_innboks_samtale" onclick="aapneSamtale(<?php echo($resMld[$i]['idmelding']) ?>)">
                                    <?php if($resInfo['hvor'] != "") { ?>
                                        <img class="meldinger_innboks_bilde" src="bilder/opplastet/<?php echo($resInfo['hvor']) ?>" alt="Profilbilde til <?php echo($navn) ?>">
                                    <?php } else { ?>
                                        <img class="meldinger_innboks_bilde" src="bilder/profil.png" alt="Standard profilbilde">
                                    <?php } ?>
                                    <p><?php echo($navn) ?></p>
                                    <p class="meldinger_innboks_tid"><?php echo(substr($resMld[$i]['tid'], 0, 10) . " kl: "); echo(substr($resMld[$i]['tid'], 11, 5)) ?></p>
                            
                                    <p><?php echo($resMld[$i]['tittel']) ?></p>
                                </section>
                            <?php } ?>
                        </form>
                    <?php } else { ?>
                        <p>Innboksen din er tom</p>
                    <?php } ?>

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