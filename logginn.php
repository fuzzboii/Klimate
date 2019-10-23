<?php

?>

<!DOCTYPE html>
<html lang="no">

<head>
    <!-- Setter riktig charset -->
    <meta charset="UTF-8">
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
    
    <!-- Section til bakgrunnsbildet, denne strekker seg over hele siden, class uklarhet vasker ut bakgrunnen så
        det ikke blir så kraftig overgang mellom fargene -->
    <section class="bakgrunn">
        <section class="uklarhet"></section>
    </section>

    <!-- Begynnelse på øvre navigasjonsmeny -->
    <nav class="navTop">
        <!-- Bruker et ikon som skal åpne gardinmenyen, henviser til funksjonen aapneHamburger i javascript.js -->
        <img src="bilder/hamburgerIkon.svg" alt="Hamburger-menyen" class="hamburgerKnapp" onclick="aapneHamburger()">
        <!-- Legger til en knapp for å gå fra innlogging til registrering -->
        <button class="singelKnapp" onClick="location.href='registrer.php'">REGISTRER</button>
        <!-- Logoen øverst i venstre hjørne, denne leder alltid tilbake til default.php -->
        <img src="bilder/klimateNoText.png" onClick="location.href='default.php'" alt="Klimate logo" class="Logo_navmeny">
    <!-- Slutt på navigasjonsmeny-->
    </nav>
    <!-- Gardinmenyen, denne går over alt annet innhold ved bruk av z-index -->
    <section id="navMeny" class="hamburgerMeny">

        <!-- Knapp som lukker vinduet etter det er åpnet -->
        <button class="lukkHamburger" onclick="lukkHamburger()">&times;</button>
      
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
        <form method="POST" action="backend.php"> <!-- Uten autentisering, for å kunne navigere hele siden uten funksjonalitet -->
            <input type="text" class="RegInnFelt" name="brukernavn" value="" placeholder="Skriv inn brukernavn" autofocus>
            <input type="password" class="RegInnFelt" name="passord" value="" placeholder="Skriv inn passord">
            <input type="submit" class="RegInnFelt_knappLogginn" value="Logg inn">
        </form>
    </main>
</body>

<!-- Denne siden er utviklet av Robin Kleppang, siste gang endret 11.10.2019 -->
<!-- Denne siden er kontrollert av Glenn Petter Pettersen, siste gang 11.10.2019 -->

</html>