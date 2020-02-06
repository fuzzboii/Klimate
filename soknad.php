<?php
session_start();

//------------------------------//
// Innstillinger, faste variable //
//------------------------------//
include("innstillinger.php");



// Brukere skal kunne sende søknad
if (!isset($_SESSION['brukernavn'])) {
    header("Location: default.php?error=1");
} else if ($_SESSION['brukertype'] != '3') {
    header("Location: default.php?error=4");
}

if (isset($_POST['submit'])) {
    $brukernavn = $_POST['brukernavn'];
    $epost = $_POST['epost'];
    $fnavn = $_POST['fnavn'];
    $enavn = $_POST['enavn'];
    $soknaden = $_POST['soknaden'];

    $mailTo = "soknad@klimate.no";
    $headers = "Søknad fra ".$brukernavn;
    $txt = "Søknad om å bli redaktør fra brukeren".$brukernavn.".\n"."Navn:".$fnavn." ".$enavn.".\n"."Epost: ".$epost."\n\n"."Søknad: "."\n".$soknaden;
    mail($mailTo, "Søknad om å bli redaktør fra ".$brukernavn, $txt, $headers);
    header("Location: backend.php?soknadsendt");
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
                        <a class="bildeKontroll" href="javascript:void(0)" onClick="location.href='profil.php?bruker=<?php echo($_SESSION['idbruker']) ?>'" tabindex="3">
                            <img src="bilder/opplastet/<?php echo($bilde['hvor'])?>" alt="Profilbilde" class="profil_navmeny">
                        </a>

                    <?php } else { ?>
                        <!-- Hvis bruker ikke har noe profilbilde, bruk standard profilbilde -->
                        <a class="bildeKontroll" href="javascript:void(0)" onClick="location.href='profil.php?bruker=<?php echo($_SESSION['idbruker']) ?>'" tabindex="3">
                            <img src="bilder/profil.png" alt="Profilbilde" class="profil_navmeny">
                        </a>
                    <?php } ?>
                    <!-- Legger til en knapp for å logge ut når man er innlogget -->
                    <form method="POST" action="default.php">
                        <button name="loggUt" id="registrerKnapp" tabindex="2">LOGG UT</button>
                    </form>
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
                    <a class = "menytab" tabIndex = "-1" href="arrangement.php">Arrangementer</a>
                    <a class = "menytab" tabIndex = "-1" href="artikkel.php">Artikler</a>
                    <a class = "menytab" tabIndex = "-1" href="#">Diskusjoner</a>
                    <a class = "menytab" tabIndex = "-1" href="backend.php">Oversikt</a>
                    <a class = "menytab" tabIndex = "-1" href="konto.php">Konto</a>
                    <a class = "menytab" tabIndex = "-1" href="sok.php">Avansert Søk</a>
                </section>
            </section>

            <!-- For å kunne lukke hamburgermenyen ved å kun trykke på et sted i vinduet må lukkHamburgerMeny() funksjonen ligge i deler av HTML-koden -->
            <!-- Kan ikke legge denne direkte i body -->
            <header onclick="lukkHamburgerMeny()">
                <!-- Logoen midten øverst på siden, med tittel -->
                <img src="bilder/klimate.png" alt="Klimate logo"class="Logo_forside">
                <h1 style="display: none">Bilde av Klimate logoen.</h1>
            </header>

            <!-- Funksjon for å lukke hamburgermeny når man trykker på en del i Main -->
            <main id="soknad_main" onclick="lukkHamburgerMeny()">  
                <form action="soknad.php" method="post">    
                    <section class="inputBoks" style="margin-top: 1em;">
                        <img class="icon" src="bilder/brukerIkon.png" alt="Brukerikon"> <!-- Ikonet for bruker -->
                        <input type="text" class="RegInnFelt" name="brukernavn" value="<?php echo($_SESSION['brukernavn']) ?>" readonly>
                    </section>
                    <section class="inputBoks">
                        <img class="icon" src="bilder/emailIkon.png" alt="Epostikon"> <!-- Ikonet for epostadresse -->
                        <input type="email" class="RegInnFelt" name="epost" value="<?php echo($_SESSION['epost']) ?>" placeholder="Skriv inn e-postadresse" required title="Skriv inn en gyldig epostadresse" autofocus>
                    </section>
                    <section class="inputBoks">
                        <img class="icon" src="bilder/fnenIkon.png" alt="Fornavnikon"> <!-- Ikonet for fornavn -->                            <input type="fnavn" class="RegInnFelt" name="fnavn" value="<?php echo($_SESSION['fornavn']) ?>" placeholder="Skriv inn fornavnet ditt" required title="Skriv inn fornavnet ditt">
                    </section>
                    <section class="inputBoks">
                        <img class="icon" src="bilder/fnenIkon.png" alt="Etternavnikon"> <!-- Ikonet til etternavn -->
                        <input type="enavn" class="RegInnFelt" name="enavn" value="<?php echo($_SESSION['etternavn']) ?>" placeholder="Skriv inn etternavnet ditt" required title="Skriv inn etternavnet ditt">
                    </section>
                    <secton>                        <textarea class="textarea_Soknad" name="soknaden" placeholder="Vennligst fyll ut din søknad..." rows="15" cols="60"></textarea>     
                    </section>
                    <section>
                    <button type="submit" name="submit" class="soknad_knapp" style="margin-top: 2em;">Send søknad</button>
                    </section>
                </form>
            </main>
            
            <!-- Knapp som vises når du har scrollet i vinduet, tar deg tilbake til toppen -->
            <button onclick="tilbakeTilTopp()" id="toppKnapp" title="Toppen"><img src="bilder/pilopp.png" alt="Tilbake til toppen"></button>

            <!-- Footer, epost er for øyeblikket på en catch-all, videresendes til RK -->
            <footer>
                <p class=footer_beskrivelse>&copy; Klimate 2020 | <a href="mailto:kontakt@klimate.no">Kontakt oss</a></p>
            </footer>
        </article>
    </body>

    <!-- Denne siden er utviklet av Robin Kleppang, siste gang endret xx.xx.xxxx -->
    <!-- Denne siden er kontrollert av Glenn Petter Pettersen, siste gang xx.xx.xxxx -->

</html>