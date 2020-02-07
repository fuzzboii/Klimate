<?php
session_start();


//------------------------------//
// Innstillinger, faste variable //
//------------------------------//
include("innstillinger.php");


// Utlogging av bruker
if (isset($_POST['loggUt'])) { 
    session_destroy();
    header("Location: default.php?utlogget=1");
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
        <title>Klimate</title>
        <!-- Henter inn ekstern stylesheet -->
        <link rel="stylesheet" type="text/css" href="stylesheet.css">
        <!-- Henter inn favicon, bildet som dukker opp i fanene i nettleseren -->
        <link rel='icon' href='bilder/favicon.png' type='image/x-icon'>
        <!-- Henter inn JavaScript -->
        <script language="JavaScript" src="javascript.js"> </script>
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
                <!-- Legger til knapper for å registrere ny bruker eller innlogging -->
                <!-- Om bruker er innlogget, vis kun en 'Logg ut' knapp -->
                <?php if (isset($_SESSION['idbruker'])) {

                    /* -------------------------------*/
                    /* Del for visning av profilbilde */
                    /* -------------------------------*/

                    // Henter bilde fra database utifra brukerid
                    if(!isset($_GET['systemerror'])) {
                        $hentBilde = "select hvor from bruker, brukerbilde, bilder where idbruker = " . $_SESSION['idbruker'] . " and idbruker = bruker and bilde = idbilder";
                        $stmtBilde = $db->prepare($hentBilde);
                        $stmtBilde->execute();
                        $bilde = $stmtBilde->fetch(PDO::FETCH_ASSOC);
                        $antallBilderFunnet = $stmtBilde->rowCount();
                    } else {$antallBilderFunnet = 0;}
                    
                    // rowCount() returnerer antall resultater fra database, er dette null finnes det ikke noe bilde i databasen
                    if ($antallBilderFunnet != 0) { ?>
                        <!-- Hvis vi finner et bilde til arrangementet viser vi det -->
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

            <!-- For å kunne lukke hamburgermenyen ved å kun trykke på et sted i vinduet må lukkHamburgerMeny() funksjonen ligge i deler av HTML-koden -->
            <!-- Kan ikke legge denne direkte i body -->
            <header onclick="lukkHamburgerMeny()">
                <!-- Logoen midten øverst på siden, med tittel -->
                <img src="bilder/klimate.png" alt="Klimate logo"class="Logo_forside">
                <h1 style="display: none">Bilde av Klimate logoen.</h1>        

                <!-- Meldinger til bruker -->
                <?php if(isset($_GET['utlogget']) && $_GET['utlogget'] == 1){ ?>
                    <p id="mldOK">Du har logget ut</p>    

                <?php } else if(isset($_GET['error']) && $_GET['error'] == 1){ ?>
                    <p id="mldFEIL">Du må logge inn før du kan se dette området</p>  

                <?php } else if(isset($_GET['error']) && $_GET['error'] == 2){ ?>
                    <p id="mldFEIL">Du må logge ut før du kan se dette området</p>   

                <?php } else if(isset($_GET['systemerror'])){ ?>
                    <p id="mldFEIL">Systemfeil, kunne ikke koble til database. Vennligst prøv igjen om kort tid.</p>

                <?php } else if(isset($_GET['error']) && $_GET['error'] == 4){ ?>
                    <p id="mldFEIL">Du kan ikke se dette området</p>  

                <?php } else if(isset($_GET['error']) && $_GET['error'] == 5){ ?>
                    <p id="mldFEIL">Denne brukeren er avregistrert</p>  

                <?php } else if(isset($_GET['avregistrert']) && $_GET['avregistrert'] == "true"){ ?>
                    <p id="mldFEIL">Du har blitt avregistrert</p>  
                <?php } ?>

                <p id="default_beskrivelse">Klimate er en nettside hvor du kan diskutere klimasaker med likesinnede personer!</p>
            </header>
            
            <!-- Funksjon for å lukke hamburgermeny når man trykker på en del i Main -->
            <main id="default_main" onclick="lukkHamburgerMeny()">   
                <?php if(!isset($_GET['systemerror'])){ ?>
                    <!-- IDene brukes til å splitte opp kolonnene i queries -->
                    <article id="artikkel1">
                        <h2>Nyeste</h2>
                        <p><?php 
                            //------------------------------//
                            // Henter artikler fra database //
                            //------------------------------//

                            // Henter artikler fra database, sorterer på høyeste artikkelID og viser denne (Siden vi ikke har dato)
                            $hentNyesteQ = "select idartikkel, artingress from artikkel order by idartikkel DESC limit 1";
                            $hentNyesteSTMT = $db->prepare($hentNyesteQ);
                            $hentNyesteSTMT->execute();
                            $nyesteArtikkel = $hentNyesteSTMT->fetch(PDO::FETCH_ASSOC); 
                        
                        echo($nyesteArtikkel['artingress'])?></p>
                        
                        <a href="artikkel.php?artikkel=<?php echo($nyesteArtikkel['idartikkel'])?>">Trykk her for å lese videre</a>
                    </article>
                    <article id="artikkel2">
                        <h2>Mest populære</h2>
                        <!-- Dette vil da være resultat av en spørring mot database, bruk av echo for å vise -->
                        <p>Slik ser monsterorkanen ut fra verdensrommet</p>
                        <a href="#">Trykk her for å lese videre</a>
                    </article>
                    <article id="artikkel3">
                        <h2>Mest kommentert</h2>
                        <!-- Dette vil da være resultat av en spørring mot database, bruk av echo for å vise -->
                        <p>Svenske Greta Thunberg (16) nominert til Nobels fredspris</p>
                        <a href="#">Trykk her for å lese videre</a>
                    </article>
                    <article id="artikkel4">
                        <h2>Tilfeldig utvalgt</h2>
                        <p><?php 
                            //------------------------------//
                            // Henter artikler fra database //
                            //------------------------------//

                            // Denne sorterer tilfeldig og begrenser resultatet til en artikkel
                            $hentTilfeldig = "select idartikkel, artingress from artikkel order by RAND() limit 1";
                            $stmtTilfeldig = $db->prepare($hentTilfeldig);
                            $stmtTilfeldig->execute();
                            $tilfeldigArtikkel = $stmtTilfeldig->fetch(PDO::FETCH_ASSOC); 
                        
                        echo($tilfeldigArtikkel['artingress'])?></p>
                        
                        <a href="artikkel.php?artikkel=<?php echo($tilfeldigArtikkel['idartikkel'])?>">Trykk her for å lese videre</a>
                    </article>
                <?php } ?>
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
        </article>
    </body>

    <!-- Denne siden er utviklet av Ajdin Bajrovic & Robin Kleppang, siste gang endret 08.12.2019 -->
    <!-- Denne siden er kontrollert av Glenn Petter Pettersen, siste gang 09.12.2019 -->

</html>