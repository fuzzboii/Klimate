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
            <a class="bildeKontroll" href="javascript:void(0)" onclick="hamburgerMeny()" tabindex="4">
                <img src="bilder/hamburgerIkon.svg" alt="Hamburger-menyen" class="hamburgerKnapp">
            </a>
            <a class="bildeKontroll" href="javascript:void(0)" onClick="location.href='konto.php'" tabindex="3">
                <img src="bilder/profil.png" alt="Profilbilde" class="profil_navmeny">
            </a>
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
                <a id = "menytab1" tabIndex = "-1" href="#">Arrangementer</a>
                <a id = "menytab2" tabIndex = "-1" href="#">Artikler</a>
                <a id = "menytab3" tabIndex = "-1" href="#">Diskusjoner</a>
                <a id = "menytab4" tabIndex = "-1" href="backend.php">Oversikt</a>
                <a id = "menytab5" tabIndex = "-1" href="konto.php">Konto</a>
            </section>
        </section>

        <header class="konto_rediger_header" onclick="lukkHamburgerMeny()">
            <h1>Konto</h1>
        </header>

        <!-- Konto, rediger. More to come -->
        <main id="konto_rediger_main" onclick="lukkHamburgerMeny()">
            <h2>Endre epost</h2>
            <form method="POST" class="endre_epost">
                <section class="inputBoks">
                    <img class="icon" src="bilder/emailIkon.png" alt="Epostikon"> <!-- epost-ikon -->
                    <input type="text" class="RegInnFelt" name="epost" value="" placeholder="Ny epost" autofocus>
                </section>
                <input type="submit" name="submit" value="Lagre" class="konto_rediger_lagre">
            </form>
            <h2>Endre fornavn</h2>
            <form method="POST" class="endre_fornavn">
                <section class="inputBoks">
                    <img class="icon" src="bilder/brukerIkon.png" alt ="Fornavnikon">
                    <input type="text" class="RegInnFelt" name="fnavn" value="" placeholder="Nytt fornavn">
                </section>
                <input type="submit" name="submit" value="Lagre" class="konto_rediger_lagre">
            </form>
            <h2>Endre etternavn</h2>
            <form method="POST" class="endre_etternavn">
                <section class="inputBoks">
                    <img class="icon" src="bilder/brukerIkon.png" alt="Etternavnikon">
                    <input type="text" class="RegInnFelt" name="enavn" value="" placeholder="Nytt etternavn">
                </section>
                <input type="submit" name="submit" value="Lagre" class="konto_rediger_lagre">
            </form>
            <!-- Endre passord -->
            <!-- Tanken er å ha en button som feller ned en rullgardin -->
            <!-- med feltene 'oppgi passord', 'nytt passord', 'gjenta passord', 'bekreft' -->
            <!-- Opprette og bruke en annen klasse enn lenke_knapp? -->
            <h2 class="lenke_knapp" id="endrePassordKnapp">Endre passord</h2>
            <!-- Selve rullgardinen -->
            <section id="endrePassordMeny" class="endrePassordMeny">
                <!-- Innholdet i rullgardinen -->
                <form method="POST" class="endre_passord_form">
                    <!-- elementer i skjemaet -->
                    <section class="inputBoks">
                        <img class="icon" src="bilder/pwIkon.png" alt="Passordikon">
                        <input type="password" class="RegInnFelt" name="gammeltPassord" placeholder="Gjeldende passord">
                    </section>
                    <section class="inputBoks">
                        <img class="icon" src="bilder/pwIkon.png" alt="Passordikon">
                        <input type="password" class="RegInnFelt" name="nyttPassord" placeholder="Nytt passord">
                    </section>
                    <section class="inputBoks">
                        <img class="icon" src="bilder/pwIkon.png" alt="Passordikon">
                        <input type="password" class="RegInnFelt" name="nyttPassordBekreft" placeholder="Bekreft nytt passord">
                    </section> <!-- slutt på elementer -->
                    <input type="submit" name="bytt_passord" value="Lagre" class="konto_rediger_lagre"> <!-- lagre-knapp -->
                </form> <!-- slutt på innholdet i gardinen -->
            </section> <!-- slutt på gardinen -->
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

<!-- Denne siden er utviklet av Petter Fiskvik, siste gang endret 25.11.2019 -->
<!-- Sist kontrollert av ____ ____, __.__.____ -->