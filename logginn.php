<?php

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
        <img src="bilder/hamburgerIkon.svg" alt="Hamburger-menyen" class="hamburgerKnapp" onclick="hamburgerMeny()">
        <!-- Legger til en knapp for å gå fra innlogging til registrering -->
        <button class="singelKnapp" onClick="location.href='registrer.php'">REGISTRER</button>
        <!-- Logoen øverst i venstre hjørne, denne leder alltid tilbake til default.php -->
        <img src="bilder/klimateNoText.png" onClick="location.href='default.php'" alt="Klimate logo" class="Logo_navmeny">
    <!-- Slutt på navigasjonsmeny-->
    </nav>
    <!-- Gardinmenyen, denne går over alt annet innhold ved bruk av z-index -->
    <section id="navMeny" class="hamburgerMeny">

        <!-- innholdet i gardinmenyen -->
        <section class="hamburgerInnhold">
            <a href="#">Diskusjoner</a>
            <a href="#">Arrangementer</a>
            <a href="#">Artikler</a>
            <a href="#">Profil</a>
            <a href="#">Innstillinger</a>
        </section>
    </section>

    <!-- Logoen midten øverst på siden, med tittel -->
    <header>
        <img src="bilder/klimate.png" alt="Klimate logo" class="Logo_forside">
    </header>
    <main>
        <!-- Formen som i senere tid skal brukes til autentisering på bruker, bruker type="password" for å ikke vise innholdet brukeren skriver -->
        <form method="POST" action="backend.php" class="innloggForm"> <!-- Uten autentisering, for å kunne navigere hele siden uten funksjonalitet -->
            <section class="inputBoks">
                <img class="icon" src="bilder/brukerIkon.png" alt="Brukerikon"> <!-- Ikonet for bruker -->
                <input type="text" class="RegInnFelt" name="brukernavn" value="" placeholder="Skriv inn brukernavn" autofocus>
            </section>
            <section class="inputBoks">
                <img class="icon" src="bilder/pwIkon.png" alt="Passordikon"> <!-- Ikonet for passord -->
                <input type="password" class="RegInnFelt" name="passord" value="" placeholder="Skriv inn passord">
            </section>
            <input type="submit" class="RegInnFelt_knappLogginn" value="Logg inn">   
        </form>
        
        <!-- Sender brukeren tilbake til forsiden -->
        <button onClick="location.href='default.php'" id="tilbake_knapp">Tilbake</button>

    </main>
</body>

<!-- Denne siden er utviklet av Robin Kleppang, siste gang endret 11.10.2019 -->
<!-- Denne siden er kontrollert av Glenn Petter Pettersen, siste gang 11.10.2019 -->

</html>