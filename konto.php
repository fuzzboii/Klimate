<?php
session_start();
// Ved adminside IF ($_SESSION['bruker'] and $_SESSION['brukertype'] == 1) {}
if ($_SESSION['brukernavn']) {
    // OK
} else {
    header("Location: default.php?error=1");
}

// Utlogging av bruker
if(isset($_GET['loggUt']) && $_GET['loggUt'] == "true"){ 
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
        <title>Konto</title>
        <!-- Henter inn ekstern stylesheet -->
        <link rel="stylesheet" type="text/css" href="stylesheet.css">
        <!-- Henter inn favicon, bildet som dukker opp i fanene i nettleseren -->
        <link rel='icon' href='bilder/favicon.png' type='image/x-icon'>
        <!-- Henter inn JavaScript -->
        <script language="JavaScript" src="javascript.js"></script>
    </head>

    <body>
        <!-- Begynnelse på øvre navigasjonsmeny -->
        <nav class="navTop">
            <!-- Legger til en knapp for å logge ut når man er innlogget-->
            <!-- Bruker et ikon som skal åpne gardinmenyen, henviser til funksjonen hamburgerMeny i javascript.js -->
            <a class="bildeKontroll" href="javascript:void(0)" onclick="hamburgerMeny()" tabindex="3">
                <img src="bilder/hamburgerIkon.svg" alt="Hamburger-menyen" class="hamburgerKnapp">
            </a>
            <img src="bilder/profil.png" alt="Profilbilde" class="profil_navmeny">
            <!-- Legger til en knapp for å logge ut når man er innlogget -->
            <form method="GET" action="backend.php"> 
                <button name="loggUt" id="backendLoggUt" formmethod="GET" tabindex="2" value="true">LOGG UT</button>
            </form>
            <!-- Logoen øverst i venstre hjørne, denne leder alltid tilbake til default.php -->
            <a class="bildeKontroll" href="default.php" tabindex="1">
                <img src="bilder/klimateNoText.png" alt="Klimate logo" class="Logo_navmeny">
            </a> 
        <!-- Slutt på navigasjonsmeny-->
        </nav>

        <!-- Gardinmenyen, denne går over alt annet innhold ved bruk av z-index -->
        <section id="navMeny" class="hamburgerMeny">

            <!-- innholdet i hamburger-menyen -->
            <section class="hamburgerInnhold">
                <a href="#">Diskusjoner</a>
                <a href="#">Arrangementer</a>
                <a href="#">Artikler</a>
                <a href="#">Profil</a>
                <a href="#">Innstillinger</a>
            </section>
        </section>

        <header class="konto_header" onclick="lukkHamburgerMeny()">
            <h1>Konto</h1>
        </header>

        <!-- Konto brukeropplysninger -->
        <main id="konto_main" onclick="lukkHamburgerMeny()">
            <section class="brukerinformasjon">
                <h3 class="brukeropplysninger_overskirft">Brukeropplysninger</h3>
                
                <h4 class="kontoinfo_brukernavn">Brukernavn: </h4>
                <h4 class="kontoinfo_fornavn">Fornavn: </h4>
                <h4 class="kontoinfo_etternavn">Etternavn: </h4>
                <h4 class="kontoinfo_epost">Epost: </h4>

                <button onClick="location.href='konto_rediger.php'" name="redigerkonto" class="rediger_konto_knapp">Rediger konto</button>
            </section> 
        </main>

        <button onclick="topFunction()" id="toppKnapp" title="Toppen">Tilbake til toppen</button>
        <script>
	        var mybutton = document.getElementById("toppKnapp");
	        window.onscroll = function() {scrollFunction()};
        </script>
        <footer>
            <p class=footer_beskrivelse>&copy; Klimate 2019 | <a href="mailto:kontakt@klimate.no">Kontakt oss</a></p>
        </footer>
    </body>

</html>

<!-- Denne siden er utviklet av Ajdin Bajrovic, siste gang endret 13.11.2019 -->
<!-- Sist kontrollert av ____ ____, __.__.____ -->