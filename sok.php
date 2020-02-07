<?php
session_start();

//------------------------------//
// Instillinger, faste variable //
//------------------------------//
include("innstillinger.php");



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
            <?php if(isset($_GET['brukernavn']) || isset($_GET['epost']) || isset($_GET['interesse'])) { ?>
                Søk på bruker
            <?php } else if(isset($_GET['artTittel']) || isset($_GET['artForfatter'])) { ?>
                Søk på artikkel
            <?php } else if(isset($_GET['arrTittel']) || isset($_GET['arrDato']) || isset($_GET['fylke'])) { ?>
                Søk på arrangement
            <?php } else { ?>
                Avansert søk
            <?php } ?>
        </title>
        <!-- Henter inn ekstern stylesheet -->
        <link rel="stylesheet" type="text/css" href="stylesheet.css">
        <!-- Henter inn favicon, bildet som dukker opp i fanene i nettleseren -->
        <link rel='icon' href='bilder/favicon.png' type='image/x-icon'>
        <!-- Henter inn JavaScript -->
        <script language="JavaScript" src="javascript.js"> </script>
    </head>

    <body id="sok_body" onload="sokRullegardin(), hentSide('side_sok', 'sok_tilbKnapp', 'sok_nesteKnapp')" onresize="hentSide('side_sok', 'sok_tilbKnapp', 'sok_nesteKnapp')">
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
                                <img src="bilder/opplastet/<?php echo($bilde['hvor'])?>" alt="Profilbilde"  class="profil_navmeny" style="border: 1px solid orange;">
                            
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
                                <img src="bilder/profil.png" alt="Profilbilde" class="profil_navmeny" style="border: 1px solid orange;">
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
                    <a class="bildeKontroll" href="javascript:void(0)" onClick="location.href='profil.php?bruker=<?php echo($_SESSION['idbruker']) ?>'" tabindex="3">
                        <!-- Setter redaktør border "Oransje" -->
                        <?php if ($_SESSION['brukertype'] == 2) { ?>
                            <img src="bilder/profil.png" alt="Profilbilde" class="profil_navmeny" style="border: 1px solid orange;">
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
                    <a class = "menytab" tabindex = "-1" href="arrangement.php">Arrangementer</a>
                    <a class = "menytab" tabindex = "-1" href="artikkel.php">Artikler</a>
                    <a class = "menytab" tabindex = "-1" href="#">Diskusjoner</a>
                    <a class = "menytab" tabindex = "-1" href="backend.php">Oversikt</a>
                    <a class = "menytab" tabindex = "-1" href="konto.php">Konto</a>
                    <a class = "menytab" tabindex = "-1" href="sok.php">Avansert Søk</a>
                <?php } else { ?>
                    <!-- Hvis bruker ikke er innlogget -->
                    <a class = "menytab" tabindex = "-1" href="arrangement.php">Arrangementer</a>
                    <a class = "menytab" tabindex = "-1" href="artikkel.php">Artikler</a>
                    <a class = "menytab" tabindex = "-1" href="#">Diskusjoner</a>
                    <a class = "menytab" tabindex = "-1" href="sok.php">Avansert Søk</a>
                <?php } ?>
            </section>
        </section>

        <!-- Start på PHP IF-ELSE som utgjør siden -->
        <?php if(isset($_GET['brukernavn']) || isset($_GET['epost']) || isset($_GET['interesse'])) {

            /* ----------------------*/
            /* --------------------- */
            /* Del for søk på bruker */
            /* --------------------- */
            /* --------------------- */

            // Spørringen som endrer seg utifra brukers valg
            $sokPaaBr = "";

            // Visuelt viser hva bruker faktisk søkte på, vises på selve siden
            $infoOmSok = "";

            if (($_GET['brukernavn'] != "") && ($_GET['epost'] != "") && ($_GET['interesse'] == "")) {

                /* -----------------------------------*/
                /* Del for søk på brukernavn og epost */
                /* ---------------------------------- */
                
                $sokPaaBr = "select idbruker, brukernavn from bruker where brukernavn LIKE '%" . $_GET['brukernavn'] . "%' and epost = '" . $_GET['epost'] . "'";
                $infoOmSok = "Du har søkt etter: " . $_GET['brukernavn'] . ", " . $_GET['epost'];


            } else if (($_GET['brukernavn'] != "") && ($_GET['epost'] == "") && ($_GET['interesse'] == "")) {

                /* ------------------------------*/
                /* Del for søk på kun brukernavn */
                /* ----------------------------- */
            
                $sokPaaBr = "select idbruker, brukernavn from bruker where brukernavn LIKE '%" . $_GET['brukernavn'] . "%' order by brukernavn ASC";
                $infoOmSok = "Du har søkt etter: " . $_GET['brukernavn'];


            } else if (($_GET['brukernavn'] == "") && ($_GET['epost'] != "") && ($_GET['interesse'] == "")) {

                /* -------------------------*/
                /* Del for søk på kun epost */
                /* ------------------------ */

                $sokPaaBr = "select idbruker, brukernavn from bruker where epost = '" . $_GET['epost'] . "' order by brukernavn ASC";
                $infoOmSok = "Du har søkt etter: " . $_GET['epost'];
                    
                
            } else if (($_GET['brukernavn'] != "") && ($_GET['epost'] != "") && ($_GET['interesse'] != "")) {

                /* --------------------------------------------- */
                /* Del for søk på brukernavn, epost og interesse */
                /* --------------------------------------------- */

                $sokPaaBr = "select idbruker, brukernavn, interessenavn from bruker, interesse, brukerinteresse where brukernavn LIKE '%" . $_GET['brukernavn'] . "%' and epost = '" . $_GET['epost'] . "' and interessenavn = '" . $_GET['interesse'] . "' and bruker.idbruker = brukerinteresse.bruker and brukerinteresse.interesse = interesse.idinteresse";
                $infoOmSok = "Du har søkt etter: " . $_GET['brukernavn'] . ", " . $_GET['epost'] . ", " . $_GET['interesse'];
                    
                
            } else if (($_GET['brukernavn'] != "") && ($_GET['epost'] == "") && ($_GET['interesse'] != "")) {

                /* ------------------------------------------ */
                /* Del for søk på kun brukernavn og interesse */
                /* ------------------------------------------ */

                $sokPaaBr = "select idbruker, brukernavn, interessenavn from bruker, interesse, brukerinteresse where brukernavn LIKE '%" . $_GET['brukernavn'] . "%' and interessenavn = '" . $_GET['interesse'] . "' and bruker.idbruker = brukerinteresse.bruker and brukerinteresse.interesse = interesse.idinteresse";
                $infoOmSok = "Du har søkt etter: " . $_GET['brukernavn'] . ", " . $_GET['interesse']; 

                
            } else if (($_GET['brukernavn'] == "") && ($_GET['epost'] != "") && ($_GET['interesse'] != "")) {

                /* ------------------------------------- */
                /* Del for søk på kun epost og interesse */
                /* ------------------------------------- */

                $sokPaaBr = "select idbruker, brukernavn, interessenavn from bruker, interesse, brukerinteresse where epost = '" . $_GET['epost'] . "' and interessenavn = '" . $_GET['interesse'] . "' and bruker.idbruker = brukerinteresse.bruker and brukerinteresse.interesse = interesse.idinteresse";
                $infoOmSok = "Du har søkt etter: " . $_GET['epost'] . ", " . $_GET['interesse']; 

                
            } else if (($_GET['brukernavn'] == "") && ($_GET['epost'] == "") && ($_GET['interesse'] != "")) {

                /* ---------------------------- */
                /* Del for søk på kun interesse */
                /* ---------------------------- */

                $sokPaaBr = "select idbruker, brukernavn, interessenavn from bruker, interesse, brukerinteresse where interessenavn = '" . $_GET['interesse'] . "' and bruker.idbruker = brukerinteresse.bruker and brukerinteresse.interesse = interesse.idinteresse";
                $infoOmSok = "Du har søkt etter: " . $_GET['interesse']; 

                
            } 



            if($sokPaaBr != "") {

                $stmtBr = $db->prepare($sokPaaBr);
                $stmtBr->execute();
                $resBr = $stmtBr->fetchAll(PDO::FETCH_ASSOC); 
                
                // Variabel som brukes til å fortelle når vi kan avslutte side_sok
                $avsluttTag = 0;
                $antallSider = 0;

                $resAntall = $stmtBr->rowCount(); ?>
                
                <header class="sok_header" onclick="lukkHamburgerMeny()">
                    <?php if ($resAntall > 1) { ?>
                        <h2><?php echo($resAntall)?> Resultater</h2>
                    <?php } else if ($resAntall == 1) { ?>
                        <h2><?php echo($resAntall)?> Resultat</h2>
                    <?php } else { ?>
                        <h2>Ingen resultater</h2>
                    <?php } ?>
                    <p><?php echo($infoOmSok) ?></p>
                </header>

                <main id="sok_main" onclick="lukkHamburgerMeny()"> 


                <?php if ($resAntall > 0 ) { ?>
                    <?php for ($j = 0; $j < count($resBr); $j++) {
                        // Hvis rest av $j delt på 8 er 0, start section (Ny side)
                        if ($j % 8 == 0) { ?>
                            <section class="side_sok">
                        <?php $antallSider++; } $avsluttTag++; ?>
                            <section class="brukerRes_sok" onClick="location.href='profil.php?bruker=<?php echo($resBr[$j]['idbruker']) ?>'">
                                <figure class="infoBoksBr_sok">
                                    <?php // Henter bilde til bruker
                                    $hentBrBilde = "select hvor from bilder, brukerbilde where brukerbilde.bruker = " . $resBr[$j]['idbruker'] . " and brukerbilde.bilde = bilder.idbilder";
                                    $stmtBrBilde = $db->prepare($hentBrBilde);
                                    $stmtBrBilde->execute();
                                    $resBilde = $stmtBrBilde->fetch(PDO::FETCH_ASSOC);

                                    if (!$resBilde) { ?>
                                        <!-- Standard profilbilde om bruker ikke har lastet opp noe enda -->
                                        <img class="BildeBoksBr_sok" src="bilder/profil.png" alt="Profilbilde for <?php echo($resBr[$j]['brukernavn'])?>">
                                    <?php } else {
                                        // Tester på om filen faktisk finnes
                                        $testPaa = $resBilde['hvor'];
                                        if(file_exists("$lagringsplass/$testPaa")) {  ?> 
                                            <!-- Profilbilde som resultat av spørring -->
                                            <img class="BildeBoksBr_sok" src="bilder/opplastet/<?php echo($resBilde['hvor'])?>" alt="Profilbilde for <?php echo($resBr[$j]['brukernavn'])?>">
                                        <?php } else { ?>
                                            <img class="BildeBoksBr_sok" src="bilder/profil.png" alt="Profilbilde for <?php echo($resBr[$j]['brukernavn'])?>">
                                        <?php }
                                    } ?>
                                    <p class="infoResBr_sok"><?php echo($resBr[$j]['brukernavn'])?></p>
                                </figure>
                            </section>
                            <?php 
                            // Hvis telleren har nådd 8
                            if (($avsluttTag == 8) || $j == (count($resBr) - 1)) { ?>
                                </section>     
                            <?php 
                                // Sett telleren til 0, mulighet for mer enn 2 sider
                                $avsluttTag = 0;
                            } ?>
                    <?php  }
                } ?>
                <section id="sok_bunnSection">
                    <?php if ($antallSider > 1) {?>
                        <p id="sok_antSider">Antall sider: <?php echo($antallSider) ?></p>
                    <?php } ?>
                    <button type="button" id="sok_tilbKnapp" onclick="visForrigeSide('side_sok', 'sok_tilbKnapp', 'sok_nesteKnapp')">Forrige</button>
                    <button type="button" id="sok_nesteKnapp" onclick="visNesteSide('side_sok', 'sok_tilbKnapp', 'sok_nesteKnapp')">Neste</button>
                    <button onclick="location.href='sok.php'" class="lenke_knapp">Tilbake til søk</button>
                </section>
            <?php } else {
                
                /* ------------------------------------------- */
                /* Del for om bruker har oppgitt tomme verdier */
                /* ------------------------------------------- */
                ?>

                <header class="sok_header" onclick="lukkHamburgerMeny()">
                    <h2>Ingen resultater</h2>
                </header>

                <main id="sok_main" onclick="lukkHamburgerMeny()"> 

                <section id="sok_bunnSection">
                    <button onclick="location.href='sok.php'" class="lenke_knapp">Tilbake til søk</button>
                </section>
            <?php } ?>
                
                

        <?php } else if(isset($_GET['artTittel']) || isset($_GET['artForfatter'])) {

            /* ------------------------*/
            /* ----------------------- */
            /* Del for søk på artikkel */
            /* ----------------------- */
            /* ----------------------- */

            // Spørringen som endrer seg utifra brukers valg
            $sokPaaArt = "";
            
            // Visuelt viser hva bruker faktisk søkte på, vises på selve siden
            $infoOmSok = "";

            // Enkel test hvis bruker kun ønsker å søke på tittel (Fra navmeny)
            $sokPaaKunTtl = true;
            if (isset($_GET['artForfatter'])) {
                $sokPaaKunTtl = false;
            }

            if (($_GET['artTittel'] != "") && ($sokPaaKunTtl == true)) {

                /* --------------------------*/
                /* Del for søk på kun tittel */
                /* ------------------------- */

                $sokPaaArt = "select idartikkel, artnavn, artingress, brukernavn, fnavn, enavn from artikkel, bruker where artnavn LIKE '%" . $_GET['artTittel'] . "%' and bruker = idbruker";
                $infoOmSok = "Du har søkt etter: " . $_GET['artTittel'];


            } else if ((($_GET['artTittel'] != "") && $_GET['artForfatter'] != "") && ($sokPaaKunTtl == false)) {

                /* --------------------------------------------------*/
                /* Del for søk på kombinasjon av tittel og forfatter */
                /* ------------------------------------------------- */

                $sokPaaArt = "select idartikkel, artnavn, artingress, brukernavn, fnavn, enavn from artikkel, bruker where artnavn LIKE '%" . $_GET['artTittel'] . "%' and (brukernavn LIKE '%" . $_GET['artForfatter'] . "%' or fnavn LIKE '%" . $_GET['artForfatter'] . "%' or enavn LIKE '%" . $_GET['artForfatter'] . "%') and bruker = idbruker";
                $infoOmSok = "Du har søkt etter: " . $_GET['artTittel'] . ", " . $_GET['artForfatter'];


            } else if ((($_GET['artTittel'] != "") && $_GET['artForfatter'] == "") && ($sokPaaKunTtl == false)) {

                /* ----------------------*/
                /* Del for søk på tittel */
                /* --------------------- */

                $sokPaaArt = "select idartikkel, artnavn, artingress, brukernavn, fnavn, enavn from artikkel, bruker where artnavn LIKE '%" . $_GET['artTittel'] . "%' and bruker = idbruker";
                $infoOmSok = "Du har søkt etter: " . $_GET['artTittel'];


            } else if ((($_GET['artTittel'] == "") && $_GET['artForfatter'] != "") && ($sokPaaKunTtl == false)) {

                /* -------------------------*/
                /* Del for søk på forfatter */
                /* ------------------------ */

                $sokPaaArt = "select idartikkel, artnavn, artingress, brukernavn, fnavn, enavn from artikkel, bruker where (brukernavn LIKE '%" . $_GET['artForfatter'] . "%' or fnavn LIKE '%" . $_GET['artForfatter'] . "%' or enavn LIKE '%" . $_GET['artForfatter'] . "%') and bruker = idbruker";
                $infoOmSok = "Du har søkt etter: " . $_GET['artForfatter'];

                
            }

            
            if ($sokPaaArt != "") {
                $stmtArt = $db->prepare($sokPaaArt);
                $stmtArt->execute();
                $resArt = $stmtArt->fetchAll(PDO::FETCH_ASSOC); 
                
                // Variabel som brukes til å fortelle når vi kan avslutte side_sok
                $avsluttTag = 0;
                $antallSider = 0;

                $resAntall = $stmtArt->rowCount(); 
            ?>
                
                <header class="sok_header" onclick="lukkHamburgerMeny()">
                    <?php if ($resAntall > 1) { ?>
                        <h2><?php echo($resAntall)?> Resultater</h2>
                    <?php } else if ($resAntall == 1) { ?>
                        <h2><?php echo($resAntall)?> Resultat</h2>
                    <?php } else { ?>
                        <h2>Ingen resultater</h2>
                    <?php } ?>
                    <p><?php echo($infoOmSok) ?></p>
                </header>

                <main id="sok_main" onclick="lukkHamburgerMeny()"> 

                <?php if ($resAntall > 0 ) { ?>
                    <?php for ($j = 0; $j < count($resArt); $j++) {
                        // Hvis rest av $j delt på 8 er 0, start section (Ny side)
                        if ($j % 8 == 0) { ?>
                            <section class="side_sok">
                        <?php $antallSider++; } $avsluttTag++; ?>
                        <section class="artRes_sok" onClick="location.href='artikkel.php?artikkel=<?php echo($resArt[$j]['idartikkel']) ?>'">
                            <figure class="infoBoksArt_sok">
                                <h2 class="infoResArt_sok"><?php echo($resArt[$j]['artnavn'])?></h2>
                                <p class="infoResArt_sok"><?php echo($resArt[$j]['artingress'])?></p>
                                <?php 
                                // Hvis bruker ikke har etternavn (Eller har oppgitt et mellomrom eller lignende som navn) hvis brukernavn
                                if (preg_match("/\S/", $resArt[$j]['enavn']) == 0) { ?>
                                    <p class="infoResArt_sok">Skrevet av <?php echo($resArt[$j]['brukernavn'])?></p>
                                <?php } else { ?>
                                    <p class="infoResArt_sok">Skrevet av <?php echo($resArt[$j]['enavn']); if(preg_match("/\S/", $resArt[$j]['fnavn']) == 1) {echo(", "); echo($resArt[$j]['fnavn']); } ?></p>
                                <?php } ?>
                            </figure>
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
                    <button type="button" id="sok_tilbKnapp" onclick="visForrigeSide('side_sok', 'sok_tilbKnapp', 'sok_nesteKnapp')">Forrige</button>
                    <button type="button" id="sok_nesteKnapp" onclick="visNesteSide('side_sok', 'sok_tilbKnapp', 'sok_nesteKnapp')">Neste</button>
                    <button onclick="location.href='sok.php'" class="lenke_knapp">Tilbake til søk</button>
                </section>
            <?php 
            } else {
                
                /* ------------------------------------------- */
                /* Del for om bruker har oppgitt tomme verdier */
                /* ------------------------------------------- */
                ?>

                <header class="sok_header" onclick="lukkHamburgerMeny()">
                    <h2>Ingen resultater</h2>
                </header>

                <main id="sok_main" onclick="lukkHamburgerMeny()"> 

                <section id="sok_bunnSection">
                    <button onclick="location.href='sok.php'" class="lenke_knapp">Tilbake til søk</button>
                </section>
            <?php }

        } else if(isset($_GET['arrTittel']) || isset($_GET['arrDato']) || isset($_GET['fylke'])) {

            /* ---------------------------*/
            /* -------------------------- */
            /* Del for søk på arrangement */
            /* -------------------------- */
            /* -------------------------- */

            // Spørringen som endrer seg utifra brukers valg
            $sokPaaArr ="";
            
            // Visuelt viser hva bruker faktisk søkte på, vises på selve siden
            $infoOmSok = "";

            if (($_GET['arrTittel'] != "") && ($_GET['arrDato'] != "") && ($_GET['fylke'] != "")) {

                /* ----------------------------------------------------*/
                /* Del for søk på kombinasjon av tittel, dato og fylke */
                /* --------------------------------------------------- */

                $sokPaaArr = "select idevent, eventnavn, tidspunkt, veibeskrivelse, brukernavn, fnavn, enavn, fylkenavn from event, bruker, fylke where eventnavn LIKE '%" . $_GET['arrTittel'] . "%' and tidspunkt between NOW() and '" . $_GET['arrDato'] . "' and event.idbruker = bruker.idbruker and event.fylke = fylke.idfylke and fylke.fylkenavn = '" . $_GET['fylke'] . "'";
                $infoOmSok = "Du har søkt etter: " . $_GET['arrTittel'] . ", " . $_GET['arrDato'] . ", " . $_GET['fylke'];

            } else if (($_GET['arrTittel'] != "") && ($_GET['arrDato'] != "") && ($_GET['fylke'] == "")) {
                
                /* ---------------------------------------------*/
                /* Del for søk på kombinasjon av tittel og dato */
                /* -------------------------------------------- */

                $sokPaaArr = "select idevent, eventnavn, tidspunkt, veibeskrivelse, brukernavn, fnavn, enavn, fylkenavn from event, bruker, fylke where eventnavn LIKE '%" . $_GET['arrTittel'] . "%' and tidspunkt between NOW() and '" . $_GET['arrDato'] . "' and event.idbruker = bruker.idbruker and event.fylke = fylke.idfylke";
                $infoOmSok = "Du har søkt etter: " . $_GET['arrTittel'] . ", " . $_GET['arrDato'];

            } else if (($_GET['arrTittel'] != "") && ($_GET['arrDato'] == "") && ($_GET['fylke'] == "")) {

                /* ----------------------*/
                /* Del for søk på tittel */
                /* --------------------- */

                $sokPaaArr = "select idevent, eventnavn, tidspunkt, veibeskrivelse, brukernavn, fnavn, enavn, fylkenavn from event, bruker, fylke where eventnavn LIKE '%" . $_GET['arrTittel'] . "%' and event.idbruker = bruker.idbruker and event.fylke = fylke.idfylke";
                $infoOmSok = "Du har søkt etter: " . $_GET['arrTittel'];

            } else if (($_GET['arrTittel'] == "") && ($_GET['arrDato'] != "") && ($_GET['fylke'] != "")) {

                /* -----------------------------*/
                /* Del for søk på dato og fylke */
                /* -----------------------------*/

                $sokPaaArr = "select idevent, eventnavn, tidspunkt, veibeskrivelse, brukernavn, fnavn, enavn, fylkenavn from event, bruker, fylke where tidspunkt between NOW() and '" . $_GET['arrDato'] . "' and event.idbruker = bruker.idbruker and event.fylke = fylke.idfylke and fylke.fylkenavn = '" . $_GET['fylke'] . "'";
                $infoOmSok = "Du har søkt etter: " . $_GET['arrDato'] . ", " . $_GET['fylke'];

            } else if (($_GET['arrTittel'] == "") && ($_GET['arrDato'] == "") && ($_GET['fylke'] != "")) {

                /* ---------------------*/
                /* Del for søk på fylke */
                /* ---------------------*/

                $sokPaaArr = "select idevent, eventnavn, tidspunkt, veibeskrivelse, brukernavn, fnavn, enavn, fylkenavn from event, bruker, fylke where event.idbruker = bruker.idbruker and event.fylke = fylke.idfylke and fylke.fylkenavn = '" . $_GET['fylke'] . "'";
                $infoOmSok = "Du har søkt etter: " . $_GET['fylke'];

            } else if (($_GET['arrTittel'] == "") && ($_GET['arrDato'] != "") && ($_GET['fylke'] == "")) {

                /* --------------------*/
                /* Del for søk på dato */
                /* --------------------*/

                $sokPaaArr = "select idevent, eventnavn, tidspunkt, veibeskrivelse, brukernavn, fnavn, enavn, fylkenavn from event, bruker, fylke where tidspunkt between NOW() and '" . $_GET['arrDato'] . "' and event.idbruker = bruker.idbruker and event.fylke = fylke.idfylke";
                $infoOmSok = "Du har søkt etter: " . $_GET['arrDato'];

            } else if (($_GET['arrTittel'] != "") && ($_GET['arrDato'] == "") && ($_GET['fylke'] != "")) {

                /* -------------------------------*/
                /* Del for søk på tittel og fylke */
                /* -------------------------------*/

                $sokPaaArr = "select idevent, eventnavn, tidspunkt, veibeskrivelse, brukernavn, fnavn, enavn, fylkenavn from event, bruker, fylke where eventnavn LIKE '%" . $_GET['arrTittel'] . "%' and event.idbruker = bruker.idbruker and event.fylke = fylke.idfylke and fylke.fylkenavn = '" . $_GET['fylke'] . "'";
                $infoOmSok = "Du har søkt etter: " . $_GET['arrTittel'] . ", " . $_GET['fylke'];

            }

            if ($sokPaaArr != "") {

                $stmtArr = $db->prepare($sokPaaArr);
                $stmtArr->execute();
                $resArr = $stmtArr->fetchAll(PDO::FETCH_ASSOC); 
                
                // Variabel som brukes til å fortelle når vi kan avslutte side_sok
                $avsluttTag = 0;
                $antallSider = 0;

                $resAntall = $stmtArr->rowCount(); 
                ?>
                
                <header class="sok_header" onclick="lukkHamburgerMeny()">
                    <?php if ($resAntall > 1) { ?>
                        <h2><?php echo($resAntall)?> Resultater</h2>
                    <?php } else if ($resAntall == 1) { ?>
                        <h2><?php echo($resAntall)?> Resultat</h2>
                    <?php } else { ?>
                        <h2>Ingen resultater</h2>
                    <?php } ?>
                    <p><?php echo($infoOmSok) ?></p>
                </header>

                <main id="sok_main" onclick="lukkHamburgerMeny()"> 

                <?php if ($resAntall > 0 ) { ?>
                    <?php for ($j = 0; $j < count($resArr); $j++) {
                        // Hvis rest av $j delt på 8 er 0, start section (Ny side)
                        if ($j % 8 == 0) { ?>
                            <section class="side_sok">
                        <?php $antallSider++; } $avsluttTag++; ?>
                        <section class="arrRes_sok" onClick="location.href='arrangement.php?arrangement=<?php echo($resArr[$j]['idevent']) ?>'">
                            <figure class="infoBoksArr_sok">

                                <?php // Henter bilde til arrangementet
                                $hentArrBilde = "select hvor from bilder, eventbilde where eventbilde.event = " . $resArr[$j]['idevent'] . " and eventbilde.bilde = bilder.idbilder";
                                $stmtArrBilde = $db->prepare($hentArrBilde);
                                $stmtArrBilde->execute();
                                $resBilde = $stmtArrBilde->fetch(PDO::FETCH_ASSOC);
                                
                                if (!$resBilde) { ?>
                                    <!-- Standard arrangementbilde om arrangør ikke har lastet opp noe enda -->
                                    <img class="BildeBoksArr_sok" src="bilder/stockevent.jpg" alt="Bilde av Oleg Magni fra Pexels">
                                <?php } else { ?>
                                    <!-- Arrangementbilde som resultat av spørring -->
                                    <img class="BildeBoksArr_sok" src="bilder/opplastet/<?php echo($resBilde['hvor'])?>" alt="Profilbilde for <?php echo($resArr[$j]['eventnavn'])?>">
                                <?php } ?>

                                <h2 class="infoResArr_sok"><?php echo($resArr[$j]['eventnavn'])?></h2>
                                <p class="infoResArr_sok"><?php echo($resArr[$j]['tidspunkt'])?></p>
                                <p class="infoResArr_sok"><?php echo($resArr[$j]['veibeskrivelse'] . ", " . $resArr[$j]['fylkenavn'])?></p>
                                <?php 
                                // Hvis bruker ikke har etternavn (Eller har oppgitt et mellomrom eller lignende som navn) hvis brukernavn
                                if (preg_match("/\S/", $resArr[$j]['enavn']) == 0) { ?>
                                    <p class="infoResArr_sok">Arrangert av <?php echo($resArr[$j]['brukernavn'])?></p>
                                <?php } else { ?>
                                    <p class="infoResArr_sok">Arrangert av <?php echo($resArr[$j]['enavn']); if(preg_match("/\S/", $resArr[$j]['fnavn']) == 1) {echo(", "); echo($resArr[$j]['fnavn']); } ?></p>
                                <?php } ?>
                            </figure>
                        </section>
                        <?php 
                        // Hvis telleren har nådd 8
                        if (($avsluttTag == 8) || $j == (count($resArr) - 1)) { ?>
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
                    <button type="button" id="sok_tilbKnapp" onclick="visForrigeSide('side_sok', 'sok_tilbKnapp', 'sok_nesteKnapp')">Forrige</button>
                    <button type="button" id="sok_nesteKnapp" onclick="visNesteSide('side_sok', 'sok_tilbKnapp', 'sok_nesteKnapp')">Neste</button>
                    <button onclick="location.href='sok.php'" class="lenke_knapp">Tilbake til søk</button>
                </section>
            <?php } else {
                
                /* ------------------------------------------- */
                /* Del for om bruker har oppgitt tomme verdier */
                /* ------------------------------------------- */
                ?>

                <header class="sok_header" onclick="lukkHamburgerMeny()">
                    <h2>Ingen resultater</h2>
                </header>

                <main id="sok_main" onclick="lukkHamburgerMeny()"> 

                <section id="sok_bunnSection">
                    <button onclick="location.href='sok.php'" class="lenke_knapp">Tilbake til søk</button>
                </section>
            <?php }
        } else { ?>
            <!-- Del for avansert søk -->
            <header class="sok_header" onclick="lukkHamburgerMeny()">
                <h2>Avansert søk</h2>
            </header>
            <main id="sok_main" onclick="lukkHamburgerMeny()"> 
                <section id="sok_seksjon"> 
                    <!-- Rullegardin for søk på bruker -->
                    <form method="GET">
                            <section class="innholdRullegardin">
                                <section class="sok_inputBoks">
                                    <p class="sokTittel">Brukernavn:</p>
                                    <input type="text" class="sokBrukerFelt" tabindex = "-1" name="brukernavn" placeholder="Skriv inn brukernavn">
                                </section>
                                <section class="sok_inputBoks">
                                    <p class="sokTittel">Epost:</p>
                                    <input type="email" class="sokBrukerFelt" tabindex = "-1" name="epost" placeholder="Skriv inn epost">
                                </section>
                                <section class="sok_inputBoks">
                                    <p class="sokTittel">Interesse:</p>
                                <select name="interesse" class="sokBrukerFelt" tabindex = "-1">
                                    <option value="">Ikke spesifikt</option>
                                    <?php 
                                        // Henter interesser fra database
                                        $hentInt = "select interessenavn from interesse order by interessenavn ASC";
                                        $stmtInt = $db->prepare($hentInt);
                                        $stmtInt->execute();
                                        $interesse = $stmtInt->fetchAll(PDO::FETCH_ASSOC);
                                        foreach ($interesse as $innhold) { ?>
                                            <option value="<?php echo($innhold['interessenavn'])?>"><?php echo($innhold['interessenavn'])?></option>
                                    <?php } ?>
                                </select>
                                </section>
                                <input type="submit" value="Søk på bruker"  class="sokBrukerFelt" tabindex ="-1">
                            </section>
                            <button type="button" id="brukerRullegardin" class="brukerRullegardin" tabindex ="-1">Søk etter bruker</button>
                        
                    </form>
                    <!-- Rullegardin for søk på artikkel -->
                    <form method="GET">
                            <section class="innholdRullegardin">
                                <section class="sok_inputBoks">
                                    <p class="sokTittel">Tittel:</p>
                                    <input type="text" class="sokBrukerFelt" tabindex = "-1" name="artTittel" placeholder="Tittelen på artikkelen">
                                </section>
                                <!--
                                <section class="sok_inputBoks">
                                    <p class="sokTittel">Dato fra:</p>
                                    <input type="date" class="sokBrukerFelt" tabIndex = "-1" name="artDato" title="Alle artikler publisert etter oppgitt dato">
                                </section>
                                -->
                                <section class="sok_inputBoks">
                                    <p class="sokTittel">Skrevet av:</p>
                                    <input type="text" class="sokBrukerFelt" tabindex ="-1" name="artForfatter" placeholder="Forfatter av artikkelen">
                                </section>
                                <input type="submit" value="Søk på artikkel" class="sokBrukerFelt" tabindex ="-1">
                            </section>
                            <button type="button" id="artikkelRullegardin" class="artikkelRullegardin" tabindex ="-1">Søk etter artikkel</button>
                        
                    </form>
                    <!-- Rullegardin for søk på arrangement -->
                    <form method="GET">
                            <section class="innholdRullegardin">
                                <section class="sok_inputBoks">
                                    <p class="sokTittel">Tittel:</p>
                                    <input type="text" class="sokBrukerFelt" tabindex = "-1" name="arrTittel" placeholder="Tittelen på arrangementet">
                                </section>
                                <section class="sok_inputBoks">
                                    <p class="sokTittel">Dato før:</p>
                                    <input type="date" class="sokBrukerFelt" tabindex = "-1" name="arrDato" title="Alle arrangementer før oppgitt dato">
                                </section>
                                <section class="sok_inputBoks">
                                    <p class="sokTittel">Fylke:</p>
                                <select name="fylke" class="sokBrukerFelt" tabindex = "-1">
                                    <option value="">Ikke spesifikt</option>
                                    <?php 
                                        // Henter fylker fra database
                                        $hentFylke = "select fylkenavn from fylke order by fylkenavn ASC";
                                        $stmtFylke = $db->prepare($hentFylke);
                                        $stmtFylke->execute();
                                        $fylke = $stmtFylke->fetchAll(PDO::FETCH_ASSOC);
                                        foreach ($fylke as $innhold) { ?>
                                            <option value="<?php echo($innhold['fylkenavn'])?>"><?php echo($innhold['fylkenavn'])?></option>
                                    <?php } ?>
                                </select>
                                </section>
                                <input type="submit" value="Søk på arrangement" class="sokBrukerFelt" tabindex ="-1">
                            </section>
                            <button type="button" id="arrangementRullegardin" class="arrangementRullegardin" tabindex = "-1">Søk etter arrangement</button>
                       
                    </form>
                </section>
            <?php if(isset($_GET['error']) && $_GET['error'] == 1){ ?>
                <p id="mldFEIL">Vennligst oppgi noen verdier å søke på</p>
            <?php }
        } ?>
        </main>
        
        <!-- Knapp som vises når du har scrollet i vinduet, tar deg tilbake til toppen -->
        <button onclick="tilbakeTilTopp()" id="toppKnapp" title="Toppen"><img src="bilder/pilopp.png" alt="Tilbake til toppen"></button>

        <!-- Footer, epost er for øyeblikket på en catch-all, videresendes til RK -->
        <footer>
            <p class=footer_beskrivelse>&copy; Klimate 2020 | <a tabindex="26" href="mailto:kontakt@klimate.no">Kontakt oss</a>
                <!-- Om brukeren ikke er administrator eller redaktør, vis link for søknad til å bli redaktør -->
                <?php if (isset($_SESSION['idbruker']) and $_SESSION['brukertype'] == "3") { ?> | <a href="soknad.php">Søknad om å bli redaktør</a><?php } ?>
            </p>
        </footer>
    </body>

    <!-- Denne siden er utviklet av Robin Kleppang, siste gang endret xx.xx.xxxx -->
    <!-- Denne siden er kontrollert av , siste gang xx.xx.xxxx -->

</html>