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
    <!-- -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
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
        <!-- Legger til en knapp for å gå fra registrering til innlogging -->
        <button class="singelKnapp" onClick="location.href='logginn.php'">LOGG INN</button>
        <!-- Logoen øverst i venstre hjørne, denne leder alltid tilbake til default.php -->
        <img src="bilder/klimateNoText.png" onClick="location.href='default.php'" alt="Klimate logo" class="Logo_navmeny">
    <!-- Slutt på navigasjonsmeny-->
    </nav>
    <!-- Gardinmenyen, denne går over alt annet innhold ved bruk av z-index -->
    <section id="navMeny" class="hamburgerMeny">

        <!-- Knapp som lukker vinduet etter det er åpnet -->
        <button class="lukkHamburger" onclick="lukkHamburger()">&times;</button>
      
        <!-- innholdet i hamburger-menyen -->
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
        <!-- Formen som i senere tid skal brukes til registrering på bruker, bruker type="password" for å ikke vise innholdet brukeren skriver -->
        <!-- Går til logginn.php, tanken var å vise "Registrering lykkes" hvor bruker kan ummiddelbart logge inn -->
        <form method="POST" action="logginn.php" class="innloggForm"> <!-- My byttes ut -->
        <section class="inputBoks">
            <i class="fa fa-user icon"></i>
            <input type="text" class="RegInnFelt" name="brukernavn" value="" placeholder="Skriv inn brukernavn" autofocus>
        </section>
        <section class="inputBoks">
            <i class="fa fa-key icon"></i>
            <input type="password" class="RegInnFelt" name="passord" value="" placeholder="Skriv inn passord">
        </section>
        <section class="inputBoks">
            <i class="fa fa-key icon"></i>
            <input type="password" class="RegInnFelt" name="passord2" value="" placeholder="Bekreft passord">
        </section>

            <input type="submit" class="RegInnFelt_knappRegistrer" value="Registrer ny bruker">
        </form>
    </main>
</body>

<!-- Denne siden er utviklet av Robin Kleppang, siste gang endret 11.10.2019 -->
<!-- Denne siden er kontrollert av Glenn Petter Pettersen, siste gang 11.10.2019 -->

</html>