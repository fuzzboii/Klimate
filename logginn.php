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
        <form method="POST" action="backend.php" class="innloggForm"> <!-- Uten autentisering, for å kunne navigere hele siden uten funksjonalitet -->
            <section class="inputBoks">
                <i class="fa fa-user icon"></i> <!-- Laster inn ikonet for bruker fra cdnjs-bibloteket -->
                <input type="text" class="RegInnFelt" name="brukernavn" value="" placeholder="Brukernavn" autofocus>
            </section>
            <section class="inputBoks">
                <i class="fa fa-key icon"></i> <!-- Laster inn ikonet for nøkkel fra cdnjs-bibloteket -->
                <input type="password" class="RegInnFelt" name="passord" value="" placeholder="Passord">
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