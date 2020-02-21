<?php
session_start();

//------------------------------//
// Innstillinger, faste variable //
//------------------------------//
include("innstillinger.php");


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


?>
<!DOCTYPE html>
<html lang="no">

    <head>
        <!-- Setter riktig charset -->
        <meta charset="UTF-8">
        <!-- Legger til viewport -->
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-- Setter tittelen på prosjektet -->
        <title>Konto</title>
        <!-- Henter inn ekstern stylesheet -->
        <link rel="stylesheet" type="text/css" href="stylesheet.css">
        <!-- Henter inn favicon, bildet som dukker opp i fanene i nettleseren -->
        <link rel='icon' href='bilder/favicon.png' type='image/x-icon'>
        <!-- Henter inn JavaScript -->
        <script language="JavaScript" src="javascript.js"></script>
    </head>

    <body>
        <article class="innhold">
            <!-- Begynnelse på øvre navigasjonsmeny -->
            <nav class="navTop">
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
                    <a class = "menytab" tabIndex = "-1" href="meldinger.php">Innboks</a>
                    <a class = "menytab" tabIndex = "-1" href="backend.php">Oversikt</a>
                    <a class = "menytab" tabIndex = "-1" href="konto.php">Konto</a>
                    <a class = "menytab" tabIndex = "-1" href="sok.php">Avansert Søk</a>
                </section>
            </section>

            <!-- For å kunne lukke hamburgermenyen ved å kun trykke på et sted i vinduet må lukkHamburgerMeny() funksjonen ligge i deler av HTML-koden -->
            <!-- Kan ikke legge denne direkte i body -->
            <header class="konto_header" onclick="lukkHamburgerMeny()">
                <h1>Konto</h1>
            </header>

            <!-- Meldinger til bruker -->
            <?php if(isset($_GET['error']) && $_GET['error'] == 1){ ?>
                <p id="mldFEIL">Du må oppgi et passord ved avregistrering.</p>

            <?php } else if(isset($_GET['vellykket']) && $_GET['vellykket'] == 1){ ?>
                <p id="mldOK">Konto oppdatert</p>    

            <?php } else if(isset($_GET['error']) && $_GET['error'] == 2){ ?>
                <p id="mldFEIL">Kunne ikke oppdatere konto, vennligst prøv igjen senere</p>  

            <?php } else if(isset($_GET['error']) && $_GET['error'] == 3){ ?>
                <p id="mldFEIL">Feil passord skrevet ved avregistrering</p>

            <?php } else if(isset($_GET['error']) && $_GET['error'] == 4){ ?>
                <p id="mldFEIL">Du kan ikke avregistrere en administrator</p>  

            <?php } ?>

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

                    <button onClick="location.href='konto_rediger.php'" name="redigerkonto" class="rediger_konto_knapp">Rediger konto</button>
                    
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

    
<!-- Denne siden er utviklet av Ajdin Bajrovic, siste gang endret 07.02.2020 -->
<!-- Sist kontrollert av Robin Kleppang, siste gang 07.02.2020 -->

</html>
