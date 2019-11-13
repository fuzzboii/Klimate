<?php

?>

<html>

    <head>
        <!-- Setter riktig charset -->
        <meta charset="UTF-8">
        <!-- Legger til viewport -->
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-- Setter tittelen på prosjektet -->
        <title>Innstillinger</title>
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
            <img src="bilder/thjc-goat.jpg" alt="Profilbilde" class="profil_navmeny">
            <button onClick="loggUt()" id="backendLoggUt" tabindex="2">LOGG UT</button>
            <script>
                function loggUt() {
                    sessionStorage.clear(); // Fungerer ikke
                    window.location.replace("default.php");
                }
            </script>
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

        <header class="innstillinger_header" onclick="lukkHamburgerMeny()">
            <h1>Innstillinger</h1>
        </header>

        <!-- Innstillinger. More to come -->
        <main id="innstillinger_main" onclick="lukkHamburgerMeny()">
            <!-- Endre passord -->
            <!-- Tanken er å ha en button som feller ned en rullgardin -->
            <!-- med feltene 'oppgi passord', 'nytt passord', 'gjenta passord', 'bekreft' -->
            <!-- Opprette og bruke en annen klasse enn lenke_knapp? -->
            <a class="lenke_knapp" id="endrePassordKnapp" href="javascript:void(0)" onclick="endrePassordMeny()">Endre passord</a> <!-- kjører ikke scriptet? -->
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
                </form> <!-- slutt på innholdet i gardinen -->
            </section> <!-- slutt på gardinen -->
            <!-- Endre profilbilde -->
            <!-- Tør ikke uttale meg om hvordan dette vil fungere... -->
            <button class="lenke_knapp">Endre profilbilde</button>
            <!-- Hva mer? -->
            <button class="lenke_knapp">Hva mer?</button>
            <button class="lenke_knapp">Hva mer?</button>
            <button class="lenke_knapp">Hva mer?</button>
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

<!-- Denne siden er utviklet av Petter Fiskvik, siste gang endret 13.11.2019 -->
<!-- Sist kontrollert av ____ ____, siste gang kontrollert __.__.____ -->