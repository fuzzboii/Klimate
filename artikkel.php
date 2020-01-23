<?php
session_start();

//------------------------------//
// Instillinger, faste variable //
//------------------------------//
include("instillinger.php");



$hentTilfeldig = "select * from artikkel";
$stmtTilfeldig = $db->prepare($hentTilfeldig);
$stmtTilfeldig->execute();
$tilfeldigArtikkel = $stmtTilfeldig->fetch(PDO::FETCH_ASSOC);

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

    <body>
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
                            <img src="bilder/<?php echo($bilde['hvor'])?>" alt="Profilbilde" class="profil_navmeny">
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
                <a class="bildeKontroll" href="javascript:void(0)" onClick="location.href='sok.php'">
                    <img src="bilder/sokIkon.png" alt="Søkeikon" class="sok_navmeny">
                </a>

                <!-- Logoen øverst i venstre hjørne -->
                <a class="bildeKontroll" href="default.php" tabindex="1">
                </a>    
                
                <!-- Logoen øverst i venstre hjørne, denne leder alltid tilbake til default.php -->
                <a class="bildeKontroll" href="default.php" tabindex="1">
                    <img src="bilder/klimateNoText.png" alt="Klimate logo" class="Logo_navmeny">
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
                    <?php } else { ?>
                        <!-- Hvis bruker ikke er innlogget -->
                        <a class = "menytab" tabIndex = "-1" href="arrangement.php">Arrangementer</a>
                        <a class = "menytab" tabIndex = "-1" href="artikkel.php">Artikler</a>
                        <a class = "menytab" tabIndex = "-1" href="#">Diskusjoner</a>
                    <?php } ?>
                </section>
            </section>

            <!-- For å kunne lukke hamburgermenyen ved å kun trykke på et sted i vinduet må lukkHamburgerMeny() funksjonen ligge i deler av HTML-koden -->
            <!-- Kan ikke legge denne direkte i body -->
            <header class="artikkel_header" onclick="lukkHamburgerMeny()">
                <h1>Artikler</h1>
            </header>

            <!-- Funksjon for å lukke hamburgermeny når man trykker på en del i Main -->
            <main id="artikkel_main" onclick="lukkHamburgerMeny()">  

                        <!-- Artikkel 1 -->
                        <article id="artikkel_art1">
                            <section class="artikkel_innhold">
                                <!-- her kommer innholder fra databasen -->
                                    <?php
                                        // Henter artikkelen bruker ønsker å se
                                        $hent = "Select idartikkel, artnavn, artingress, arttekst, brukernavn FROM artikkel, bruker WHERE bruker=idbruker order by RAND() LIMIT 1";
                                        $stmt = $db->prepare($hent);
                                        $stmt->execute();
                                        $artikkel = $stmt->fetch(PDO::FETCH_ASSOC);
                                        $antall = $stmt->rowCount();
                                     ?>
                                <!-- a href= -->
                                <figure>

                                </figure>
                                <section class="artikkel_innholdInfo">
                                    <section class="ArtikkelForfatter">
                                        <p id="forfatterOversikt">Forfatter: <?php echo($artikkel["brukernavn"])?></p>
                                    </section>
                                    
                                    <h2 id="artikkelOverskrift"><?php echo($artikkel["artnavn"])?></h2>
                                    <p id="artikkelTekstinnhold"> <?php echo($artikkel["artingress"])?></p>

                                </section>

                            </section>
                        </article>
                                        

                        <!-- Artikkel 2 -->
                        <article id="artikkel_art2">
                            <section class="artikkel_innhold">
                                <!-- her kommer innholder fra databasen -->
                                <!-- a href= -->
                                <figure>

                                </figure>
                                <section class="artikkel_innholdInfo">
                                    <section class="ArtikkelForfatter">
                                        <p id="forfatterOversikt">Forfatter</p>
                                    </section>
                                    
                                    <h2 id="artikkelOverskrift">Artikkel overskrift</h2>
                                    <p id="artikkelTekstinnhold"> Artikkel kort oppsummering</p>

                                </section>

                            </section>
                        </article>
                        
                        <!-- Artikkel 3 -->
                        <article id="artikkel_art3">
                            <section class="artikkel_innhold">
                                <!-- her kommer innholder fra databasen -->
                                <!-- a href= -->
                                <figure>

                                </figure>
                                <section class="artikkel_innholdInfo">
                                    <section class="ArtikkelForfatter">
                                        <p id="forfatterOversikt">Forfatter</p>
                                    </section>
                                    
                                    <h2 id="artikkelOverskrift">Artikkel overskrift</h2>
                                    <p id="artikkelTekstinnhold"> Artikkel kort oppsummering</p>

                                </section>

                            </section>
                        </article>

                        <!-- Artikkel 4 -->
                        <article id="artikkel_art4">
                            <section class="artikkel_innhold">
                                <!-- her kommer innholder fra databasen -->
                                <!-- a href= -->
                                <figure>

                                </figure>
                                <section class="artikkel_innholdInfo">
                                    <section class="ArtikkelForfatter">
                                        <p id="forfatterOversikt">Forfatter</p>
                                    </section>
                                    
                                    <h2 id="artikkelOverskrift">Artikkel overskrift</h2>
                                    <p id="artikkelTekstinnhold"> Artikkel kort oppsummering</p>

                                </section>

                            </section>
                        </article>

                        <!-- Artikkel 5 -->
                        <article id="artikkel_art5">
                            <section class="artikkel_innhold">
                                <!-- her kommer innholder fra databasen -->
                                <!-- a href= -->
                                <figure>

                                </figure>
                                <section class="artikkel_innholdInfo">
                                    <section class="ArtikkelForfatter">
                                        <p id="forfatterOversikt">Forfatter</p>
                                    </section>
                                    
                                    <h2 id="artikkelOverskrift">Artikkel overskrift</h2>
                                    <p id="artikkelTekstinnhold"> Artikkel kort oppsummering</p>

                                </section>

                            </section>
                        </article>

            </main>
            
            <!-- Knapp som vises når du har scrollet i vinduet, tar deg tilbake til toppen -->
            <button onclick="tilbakeTilTopp()" id="toppKnapp" title="Toppen"><img src="bilder/pilopp.png" alt="Tilbake til toppen"></button>

            <!-- Footer, epost er for øyeblikket på en catch-all, videresendes til RK -->
            <footer>
                <p class=footer_beskrivelse>&copy; Klimate 2019 | <a href="mailto:kontakt@klimate.no">Kontakt oss</a>
                    <!-- Om brukeren ikke er administrator eller redaktør, vis link for søknad til å bli redaktør -->
                    <?php if (isset($_SESSION['brukernavn']) and $_SESSION['brukertype'] == "3") { ?> | <a href="soknad.php">Søknad om å bli redaktør</a><?php } ?>
                </p>
            </footer>
        </article>
    </body>

    <!-- Denne siden er utviklet av Robin Kleppang, siste gang endret xx.xx.xxxx -->
    <!-- Denne siden er kontrollert av Glenn Petter Pettersen, siste gang xx.xx.xxxx -->

</html>