<?php
session_start();

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
        <!-- Begynnelse på øvre navigasjonsmeny -->
        <nav class="navTop"> 
            <!-- Bruker et ikon som skal åpne gardinmenyen, henviser til funksjonen hamburgerMeny i javascript.js -->
            <!-- javascript:void(0) blir her brukt så siden ikke scroller til toppen av seg selv når du trykker på hamburger-ikonet -->
            <a class="bildeKontroll" href="javascript:void(0)" onclick="hamburgerMeny()" tabindex="4">
                <img src="bilder/hamburgerIkon.svg" alt="Hamburger-menyen" class="hamburgerKnapp">
            </a>
            <!-- Legger til knapper for å registrere ny bruker eller innlogging -->
            <!-- Om bruker er innlogget, vis kun en 'Logg ut' knapp -->
            <?php if (isset($_SESSION['brukernavn'])) { ?>
                <!-- Vises når bruker er innlogget -->
                <!-- Mulig løsning på bruk av profilbilde <img src="bilder/</?php echo($_SESSION['profilbilde']) ?>" -->
                <a class="bildeKontroll" href="javascript:void(0)" onClick="location.href='konto.php'" tabindex="3">
                    <img src="bilder/profil.png" alt="Profilbilde" class="profil_navmeny">
                </a>
                <!-- Legger til en knapp for å logge ut når man er innlogget -->
                <form method="POST" action="default.php">
                    <button name="loggUt" id="registrerKnapp" tabindex="2">LOGG UT</button>
                </form>
            <?php } else { ?>
                <!-- Vises når bruker ikke er innlogget -->
                <button id="registrerKnapp" onClick="location.href='registrer.php'" tabindex="3">REGISTRER</button>
                <button id="logginnKnapp" onClick="location.href='logginn.php'" tabindex="2">LOGG INN</button>
            <?php } ?>
            <!-- Logoen øverst i venstre hjørne -->
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
                    <a id = "menytab1" tabIndex = "-1" href="#">Arrangementer</a>
                    <a id = "menytab2" tabIndex = "-1" href="#">Artikler</a>
                    <a id = "menytab3" tabIndex = "-1" href="#">Diskusjoner</a>
                    <a id = "menytab4" tabIndex = "-1" href="backend.php">Oversikt</a>
                    <a id = "menytab5" tabIndex = "-1" href="konto.php">Konto</a>
                <?php } else { ?>
                    <!-- Hvis bruker ikke er innlogget -->
                    <a id = "menytab1" tabIndex = "-1" href="#">Arrangementer</a>
                    <a id = "menytab2" tabIndex = "-1" href="#">Artikler</a>
                    <a id = "menytab3" tabIndex = "-1" href="#">Diskusjoner</a>
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

            <?php } else if(isset($_GET['error']) && $_GET['error'] == 3){ ?>
                <p id="mldFEIL">Systemfeil, kunne ikke koble til database. Vennligst prøv igjen om kort tid.</p>
            <?php } ?>

            <p id="default_beskrivelse">Klimate er en nettside hvor du kan diskutere klimasaker med likesinnede personer!</p>
        </header>

        <!-- Funksjon for å lukke hamburgermeny når man trykker på en del i Main -->
        <main id="default_main" onclick="lukkHamburgerMeny()">   
            
            <!-- IDene brukes til å splitte opp kolonnene i queries -->
            <article id="artikkel1">
                <h2>Nyeste</h2>
                <!-- Dette vil da være resultat av en spørring mot database, bruk av echo for å vise -->
                <p>Regjeringen verner bilister etter økt CO₂-avgift</p>
                <a href="#">Trykk her for å lese videre</a>
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
                <!-- Dette vil da være resultat av en spørring mot database, bruk av echo for å vise -->
                <p>Hundretusener demonstrerer for klima over hele verden</p>
                <a href="#">Trykk her for å lese videre</a>
            </article>
            
        </main>
        
        <!-- Knapp som vises når du har scrollet i vinduet, tar deg tilbake til toppen -->
        <button onclick="topFunction()" id="toppKnapp" title="Toppen"><img src="bilder/pilopp.png" alt="Tilbake til toppen"></button>

        <!-- Footer, epost er for øyeblikket på en catch-all, videresendes til RK -->
        <footer>
            <p class=footer_beskrivelse>&copy; Klimate 2019 | <a href="mailto:kontakt@klimate.no">Kontakt oss</a></p>
        </footer>
    </body>

    <!-- Denne siden er utviklet av Ajdin Bajrovic & Robin Kleppang, siste gang endret 10.10.2019 -->
    <!-- Denne siden er kontrollert av Robin Kleppang & Glenn Petter Pettersen, siste gang 10.10.2019 -->

</html>