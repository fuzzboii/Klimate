<?php
session_start();

//------------------------------//
// Innstillinger, faste variable //
//------------------------------//
include("innstillinger.php");

// Sjekker om bruker har tilgang til å se dette området
if (!isset($_SESSION['idbruker'])) {
    header("Location: default.php?error=1");
} 


//-----------------------------------//
// Henter arrangementer fra database //
//-----------------------------------//

// Denne sorterer og henter ut det nyeste arrangementet
$hentArrangement = "select * from event order by tidspunkt DESC limit 1";
$stmtArrangement = $db->prepare($hentArrangement);
$stmtArrangement->execute();
$sisteArrangement = $stmtArrangement->fetch(PDO::FETCH_ASSOC);


?>
<!DOCTYPE html>
<html lang="no">

    <head>
        <!-- Setter riktig charset -->
        <meta charset="UTF-8">
        <!-- Legger til viewport -->
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-- Setter tittelen på prosjektet -->
        <title>Klimate</title>
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
                <!-- javascript:void(0) blir her brukt så siden ikke scroller til toppen av seg selv når du trykker på hamburger-ikonet -->
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
                                <!-- Setter redaktør border "Oransje" -->
                                <img src="bilder/profil.png" alt="Profilbilde" class="profil_navmeny" style="border: 2px solid green;">
                            <?php } else if ($_SESSION['brukertype'] == 1) { ?>
                                <!-- Setter administrator border "Rød" -->
                                <img src="bilder/profil.png" alt="Profilbilde" class="profil_navmeny" style="border: 2px solid red;"> 
                            <?php } else if ($_SESSION['brukertype'] != 1 || 2) { ?>
                                <!-- Setter vanlig profil bilde -->
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
                    <button name="loggUt" id="backendLoggUt" tabindex="4" value="true">LOGG UT</button>
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
            
            <!-- Profilbilde med planlagt "Velkommen *Brukernavn hentet fra database*" -->
            <header class="backend_header" onclick="lukkHamburgerMeny()">
                <?php 
                // Del for å vise profilbilde
                // Henter bilde fra database utifra brukerid
                $hentBilde = "select hvor from bilder, brukerbilde where brukerbilde.bruker = " . $_SESSION['idbruker'] . " and brukerbilde.bilde = bilder.idbilder";
                $stmtBilde = $db->prepare($hentBilde);
                $stmtBilde->execute();
                $bilde = $stmtBilde->fetch(PDO::FETCH_ASSOC);
                $antallBilderFunnet = $stmtBilde->rowCount();
                // rowCount() returnerer antall resultater fra database, er dette null finnes det ikke noe bilde i databasen
                if ($antallBilderFunnet != 0) { ?>
                    <?php // Tester på om filen faktisk finnes
                    $testPaa = $bilde['hvor'];
                    if(file_exists("$lagringsplass/$testPaa")) {  ?> 
                        <!-- Hvis vi finner et bilde til bruker viser vi det -->
                        <img src="bilder/opplastet/<?php echo($bilde['hvor'])?>" alt="Profilbilde" class="profil_backend">
                    <?php } else { ?>
                        <img src="bilder/profil.png" alt="Profilbilde" class="profil_backend">
                    <?php } ?>
                <?php } else { ?>
                    <!-- Hvis ikke noe bilde ble funnet benytter vi et standard profilbilde -->
                    <img src="bilder/profil.png" alt="Profilbilde" class="profil_backend">
                <?php } ?>
                <h1 class="velkomst">Velkommen <?php if(preg_match("/\S/", $_SESSION['fornavn']) == 1) { echo($_SESSION['fornavn']); } else { echo($_SESSION['brukernavn']); } ?></h1>
            </header>

            <main id="backend_main" onclick="lukkHamburgerMeny()">
                <!-- Innholdet på siden -->
                <!-- IDene brukes til å splitte opp kolonnene i queries -->
                <article id="bgcont1">
                    <h2>Siste arrangement</h2>
                    <p><?php echo($sisteArrangement['eventnavn'])?></p>
                    <a href="arrangement.php?arrangement=<?php echo($sisteArrangement['idevent']) ?>">Trykk her for å se dette arrangementet</a>
                </article>
                <article id="bgcont2">
                    <h2>Diskusjoner</h2>
                    <!-- Dette vil da være resultat av en spørring mot database, bruk av echo for å vise -->
                    <p>Bruk av gressklipper, bensin eller elektrisk?</p>
                    <a href="#">Trykk her for å lese videre</a>
                </article>
                <article id="bgcont3">
                    <h2>Artikler</h2>
                    <!-- Dette vil da være resultat av en spørring mot database, bruk av echo for å vise -->
                    <p>Hundretusener demonstrerer for klima over hele verden</p>
                    <a href="#">Trykk her for å lese videre</a>
                </article>
            </main>

            <!-- Knapp som vises når du har scrollet i vinduet, tar deg tilbake til toppen -->
            <button onclick="tilbakeTilTopp()" id="toppKnapp" title="Toppen"><img src="bilder/pilopp.png" alt="Tilbake til toppen"></button>

            <!-- Footer, epost er for øyeblikket på en catch-all, videresendes til RK -->
            <footer>
                <p class=footer_beskrivelse>&copy; Klimate <?php echo date("Y");?> | <a href="mailto:kontakt@klimate.no">Kontakt oss</a>
                    <!-- Om brukeren ikke er administrator eller redaktør, vis link for søknad til å bli redaktør -->
                    <?php if ($_SESSION['brukertype'] == "3") { ?> | <a href="soknad.php">Søknad om å bli redaktør</a><?php } ?>
                </p>
            </footer>
        </article>   
    </body>

    <!-- Denne siden er utviklet av Glenn Petter Pettersen, Robin Kleppang & Aron Snekkestad, siste gang endret 07.02.2020 -->
    <!-- Denne siden er kontrollert av Robin Kleppang siste gang 07.02.2020 -->

</html>
