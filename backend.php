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
            <!-- Legger til en knapp for å logge ut når man er innlogget-->
        <!-- Bruker et ikon som skal åpne gardinmenyen, henviser til funksjonen aapneHamburger i javascript.js -->
        <img src="bilder/hamburgerIkon.svg" alt="Hamburger-menyen" class="hamburgerKnapp" onclick="aapneHamburger()">
        <img src="bilder/thjc-goat.jpg" alt="Profilbilde" class="profil_navmeny">
        <button onClick="location.href='logginn.php'" id="backendLoggUt">LOGG UT</button>
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
    
    <!-- Profilbilde med planlagt "Velkommen *Brukernavn hentet fra database*" -->
    <header class="backend_header">
        <img src="bilder/thjc-goat.jpg" alt="Klimate logo"class="profil_backend">
        <h1 class="velkomst">Velkommen Knut-Arne!</h1>
    </header>

    <main id="backend_main">

        <!-- Innholdet på siden -->
            <article id="bgcont1">
                <h2>Arrangementer</h2>
                <p>Dugnad hos KlimaVennen</p>
                <a href="#">Trykk her for å lese videre</a>
            </article>
            <article id="bgcont2">
                <h2>Diskusjoner</h2>
                <p>Bruk av gressklipper, bensin eller elektrisk?</p>
                <a href="#">Trykk her for å lese videre</a>
            </article>
            <article id="bgcont3">
                <h2>Artikler</h2>
                <p>Hundretusener demonstrerer for klima over hele verden</p>
                <a href="#">Trykk her for å lese videre</a>
            </article>
    </main>
    
</body>

<!-- Denne siden er utviklet av Glenn Petter Pettersen & Aron Snekkestad, siste gang endret 11.10.2019 -->
<!-- Denne siden er kontrollert av Robin Kleppang, siste gang 11.10.2019 -->

</html>
