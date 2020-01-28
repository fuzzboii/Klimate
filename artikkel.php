<?php
session_start();

//------------------------------//
// Innstillinger, faste variable //
//------------------------------//
include("innstillinger.php");

var_dump($_FILES);
var_dump($_POST);

/*if (isset($_POST['publiserArtikkel'])) {
    if (strlen($_POST['tittel']) <= 45 && strlen($_POST['tittel'] > 0)) {
        if (strlen($_POST['ingress']) <= 255 || $_POST['ingress'] == "") {
            if (strlen($_POST['innhold'] <= 1000) && strlen($_POST['tittel'] > 0)) {
                // Tar utgangspunkt i at bruker ikke har lastet opp bilde
                $harBilde = false;

                $tittel = filter_var($_POST['tittel'], FILTER_SANITIZE_STRING);
                if ($_POST['ingress'] != "") {
                    $ingress = filter_var($_POST['ingress'], FILTER_SANITIZE_STRING);
                } else {
                    $ingress = filter_var(substr($_POST['innhold'], 0, 255), FILTER_SANITIZE_STRING);
                }
                $innhold = filter_var($_POST['innhold'], FILTER_SANITIZE_STRING);

                $nyArtikkelQ = "insert into artikkel(artnavn, artingress, arttekst, bruker) values('" . $tittel . "', '" . $ingress . "', '" . $innhold . "', '" . $_SESSION['idbruker'] . "')";
                $nyArtikkelSTMT = $db->prepare($nyArtikkelQ);
                $nyArtikkelSTMT->execute();
                $artikkelid = $db->lastInsertId();

                // Del for filopplastning
                if (is_uploaded_file($_FILES['bilde']['tmp_name'])) {
                    $bildenavn = "artikkel" . $artikkelid;
                    // Tillater maks 2MB (Standard i PHP.ini, går utifra at alt kjører standardinstillinger)
                    if ($_FILES['bilde']['size'] > 2097152‬) {
                        // Lagringsplass hentes fra innstillinger
                        if (move_uploaded_file($_FILES['bilde']['tmp_name'], $lagringsplass . $bildenavn)) {
                            $harbilde = true;
                        } // Filen ble ikke lastet opp
                    } // Filen er for stor
                } // Filen ble ikke mottatt
                
                if ($harbilde == true) {
                    // Legger til bildet i databasen, dette kan være sin egne spørring
                    $nyArtikkelQBilde = "insert into bilder(hvor) values('" . $
                }


            } // Innholdet er for langt
        } // Ingress er for langt
    } // Tittel er for lang
}*/


?>

<!DOCTYPE html>
<html lang="no">

    <head>
        <!-- Setter riktig charset -->
        <meta charset="UTF-8">
        <!-- Legger til viewport -->
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-- Setter tittelen på prosjektet -->
        <title>Artikler</title>
        <!-- Henter inn ekstern stylesheet -->
        <link rel="stylesheet" type="text/css" href="stylesheet.css">
        <!-- Henter inn favicon, bildet som dukker opp i fanene i nettleseren -->
        <link rel='icon' href='bilder/favicon.png' type='image/x-icon'>
        <!-- Henter inn JavaScript -->
        <script language="JavaScript" src="javascript.js"> </script>
    </head>

    <body onload="hentSide('side_artikkel', 'artikkel_tilbKnapp', 'artikkel_nesteKnapp')" onresize="hentSide('side_artikkel', 'artikkel_tilbKnapp', 'artikkel_nesteKnapp')">
        <article class="innhold">
            <!-- Begynnelse på øvre navigasjonsmeny -->
            <nav class="navTop"> 
                <!-- Bruker et ikon som skal åpne gardinmenyen, henviser til funksjonen hamburgerMeny i javascript.js -->
                <!-- javascript:void(0) blir her brukt så siden ikke scroller til toppen av seg selv når du trykker på hamburger-ikonet -->
                <a class="bildeKontroll" href="javascript:void(0)" onclick="hamburgerMeny()" tabindex="4">
                    <img src="bilder/hamburgerIkon.svg" alt="Hamburger-menyen" class="hamburgerKnapp">
                </a>
                <!-- Legger til knapper for å registrere ny bruker eller innlogging -->
                <!-- Om bruker er innlogget, vis kun en 'Logg ut' knapp -->
                <?php if (isset($_SESSION['brukernavn'])) {

                    // Vises når bruker er innlogget

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
                        <a class="bildeKontroll" href="javascript:void(0)" onClick="location.href='profil.php'" tabindex="3">
                            <img src="bilder/opplastet/<?php echo($bilde['hvor'])?>" alt="Profilbilde" class="profil_navmeny">
                        </a>

                    <?php } else { ?>
                        <!-- Hvis bruker ikke har noe profilbilde, bruk standard profilbilde -->
                        <a class="bildeKontroll" href="javascript:void(0)" onClick="location.href='profil.php'" tabindex="3">
                            <img src="bilder/profil.png" alt="Profilbilde" class="profil_navmeny">
                        </a>
                    <?php } ?>

                    <!-- Legger til en knapp for å logge ut når man er innlogget -->
                    <form method="POST" action="default.php">
                        <button name="loggUt" id="registrerKnapp" tabindex="2">LOGG UT</button>
                    </form>
                <?php } else { ?>
                    <!-- Vises når bruker ikke er innlogget -->
                    <button id="registrerKnapp" onClick="location.href='registrer.php'" tabindex="3">REGISTRER</button>
                    <button id="logginnKnapp" onClick="location.href='logginn.php'" tabindex="2">LOGG INN</button>
                <?php } ?>

                <form id="sokForm_navmeny" action="sok.php">
                    <input id="sokBtn_navmeny" type="submit" value="Søk" tabindex="3">
                    <input id="sokInp_navmeny" type="text" name="artTittel" placeholder="Søk på artikkel" tabindex="2">
                </form>
                <a href="javascript:void(0)" onClick="location.href='sok.php'">
                    <img src="bilder/sokIkon.png" alt="Søkeikon" class="sok_navmeny">
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
                    <?php if (isset($_SESSION['brukernavn'])) { ?>
                        <!-- Hva som vises om bruker er innlogget -->
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
                            // ------------------------------ artikler som blir klikket på -----------------------------
                            // Del for å vise en spesifik artikkel
                            // Henter bilde fra database utifra artikkelid
                            $hentBilde = "select hvor 
                                         from artikkel, artikkelbilde, bilder 
                                         where idartikkel = " . $_GET['artikkel'] . " and idartikkel = artikkel and bilde = idbilder";
                            $stmtBilde = $db->prepare($hentBilde);
                            $stmtBilde->execute();
                            $bilde = $stmtBilde->fetch(PDO::FETCH_ASSOC);
                            $antallBilderFunnet = $stmtBilde->rowCount();
                            // rowCount() returnerer antall resultater fra database, er dette null finnes det ikke noe bilde i databasen
                            if ($antallBilderFunnet != 0) { ?>
                                <!-- Hvis vi finner et bilde til artikkelen viser vi det -->
                                <img src="bilder/opplastet/<?php echo($bilde["hvor"]) ?>" alt="Bilde av artikkel" style="height: 20em;">

                            <?php } ?>
                            <h1 class="artikkel_header"><?php echo($artikkel['artnavn'])?></h1>
                            <p><?php echo($artikkel['artingress'])?></p>
                            <p><?php echo($artikkel['arttekst'])?></p>

                            <?php if(preg_match("/\S/", $artikkel['enavn']) == 0){?>
                                <a class="artikkelForfatter" onClick="location.href='profil.php?bruker=<?php echo($artikkel['bruker'])?>'"> Skrevet av <span style="text-decoration: underline;"><?php echo($artikkel['brukernavn'])?></span></p>
                            <?php } else {?> 
                                <a class="artikkelForfatter" onClick="location.href='profil.php?bruker=<?php echo($artikkel['bruker'])?>'">Skrevet av <span style="text-decoration: underline;"><?php echo($artikkel['enavn'] . " " . $artikkel['fnavn'])?></span></p>
                            <?php }?>
                        <?php } ?>
                    <?php  } else if (isset($_GET['nyartikkel']) && ($_SESSION['brukertype'] == 2 || $_SESSION['brukertype'] == 1)) { ?>      
            
                        <header class="artikkel_header" onclick="lukkHamburgerMeny()">
                            <h1>Ny artikkel</h1>
                        </header>

                        <main id="artikkel_main" onclick="lukkHamburgerMeny()">

                        <article id="artikkel_articleNy">
                            <form method="POST" action="artikkel.php">
                                <h2>Tittel</h2>
                                <input type="text" maxlength="45" name="tittel" placeholder="Skriv inn tittel" autofocus required>
                                <h2>Ingress</h2>
                                <textarea maxlength="255" name="ingress" rows="3" cols="35" placeholder="Skriv inn inngress, la være blank for å ta 255 tegn fra innhold"></textarea>
                                <h2>Innhold</h2>
                                <textarea maxlength="1000" name="innhold" rows="5" cols="35" placeholder="Skriv inn innhold" required></textarea>
                                <h2>Bilde</h2>
                                <input type="file" name="bilde" id="bilde" accept=".jpg, .jpeg, .png">
                                <input id="artikkel_submitNy" type="submit" name="publiserArtikkel" value="Opprett artikkel">
                            </form>
                        </article>

                    <?php } else {
                        // -------------------- Artikler som vises på artikkel.php forside----------------
                    
                        // Del for å vise alle artikler 
                        $hentAlleArt = "select idartikkel, artnavn, artingress, arttekst, brukernavn, enavn, fnavn, bruker
                                        FROM artikkel, bruker
                                        WHERE bruker=idbruker order by idartikkel";
                    
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
                          
                        <?php if ($resAntall > 0 ) { ?>
                            <?php for ($j = 0; $j < count($resArt); $j++) {
                                // Hvis rest av $j delt på 8 er 0, start section (Ny side)
                                if ($j % 8 == 0) { ?>
                                <section class="side_artikkel">
                                <?php $antallSider++; } $avsluttTag++; ?>
                                <section class="res_artikkel" onClick="location.href='artikkel.php?artikkel=<?php echo($resArt[$j]['idartikkel']) ?>'">
                                    <figure class="infoBoks_artikkel">

                                        <?php // Henter bilde til artikkel
                                        $hentArtBilde = "select hvor from bilder, artikkelbilde where artikkelbilde.idartikkel = " . $resArt[$j]['idartikkel'] . " and artikkelbilde.idbilde = bilder.idbilder";
                                        $stmtArtBilde = $db->prepare($hentArtBilde);
                                        $stmtArtBilde->execute();
                                        $resBilde = $stmtArtBilde->fetch(PDO::FETCH_ASSOC);
                                        
                                        if (!$resBilde) { ?>
                                            <!-- Standard atikkelbilde om redaktør ikke har lastet opp noe enda -->
                                            <img class="BildeBoks_artikkel" src="bilder/stockevent.jpg" alt="Bilde av Oleg Magni fra Pexels">
                                        <?php } else { ?>
                                            <!-- Artikkeltbilde som resultat av spørring -->
                                            <img class="BildeBoks_artikkel" src="bilder/opplastet/<?php echo($resBilde['hvor'])?>" alt="Profilbilde for <?php echo($resArt[$j]['eventnavn'])?>">
                                        <?php } ?>
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
                        <section id="sok_bunnSection">
                            <?php if ($antallSider > 1) {?>
                                <p id="sok_antSider">Antall sider: <?php echo($antallSider) ?></p>
                            <?php } ?>
                            <button type="button" id="artikkel_tilbKnapp" onclick="visForrigeSide('side_artikkel', 'artikkel_tilbKnapp', 'artikkel_nesteKnapp')">Forrige</button>
                            <button type="button" id="artikkel_nesteKnapp" onclick="visNesteSide('side_artikkel', 'artikkel_tilbKnapp', 'artikkel_nesteKnapp')">Neste</button>
                        </section>
                    <?php }  ?>
            </main>
            
            <!-- Knapp som vises når du har scrollet i vinduet, tar deg tilbake til toppen -->
            <button onclick="tilbakeTilTopp()" id="toppKnapp" title="Toppen"><img src="bilder/pilopp.png" alt="Tilbake til toppen"></button>

            <!-- Footer, epost er for øyeblikket på en catch-all, videresendes til RK -->
            <footer>
                <p class=footer_beskrivelse>&copy; Klimate 2020 | <a href="mailto:kontakt@klimate.no">Kontakt oss</a>
                    <!-- Om brukeren ikke er administrator eller redaktør, vis link for søknad til å bli redaktør -->
                    <?php if (isset($_SESSION['brukernavn']) and $_SESSION['brukertype'] == "3") { ?> | <a href="soknad.php">Søknad om å bli redaktør</a><?php } ?>
                </p>
            </footer>
    </body>

    <!-- Denne siden er utviklet av Robin Kleppang, siste gang endret xx.xx.xxxx -->
    <!-- Denne siden er kontrollert av Glenn Petter Pettersen, siste gang xx.xx.xxxx -->

</html>