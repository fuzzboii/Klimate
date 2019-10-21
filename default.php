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
            <!-- Legger til knapper for å registrere ny bruker eller innlogging -->
        <button id="logginnKnapp" onClick="location.href='logginn.php'">LOGG INN</button>
        <button onClick="location.href='registrer.php'">REGISTRER</button>
        <!-- Bruker et ikon som skal åpne gardinmenyen, henviser til funksjonen aapneHamburger i javascript.js -->
        <img src="bilder/hamburgerIkon.svg" alt="Hamburger-menyen" class="hamburgerKnapp" onclick="aapneHamburger()">
        <!-- Logoen øverst i venstre hjørne -->
        <img src="bilder/klimateNoText.png" alt="Klimate logo" class="Logo_navmeny">
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
        <img src="bilder/klimate.png" alt="Klimate logo"class="Logo_forside">
        <h1 style="display: none">Bilde av Klimate logoen.</h1>
        <p class="defaultP">Klimate er en nettside hvor du kan diskutere klimasaker med likesinnede personer!</p>
    </header>
    <main id="default_main">   
        
        <article id="artikkel1">
            <h2>Nyeste</h2>
            <p>Regjeringen verner bilister etter økt CO₂-avgift <a href="#">Trykk her for å lese videre</a></p>
        </article>
        <article id="artikkel2">
            <h2>Mest populære</h2>
            <p>Slik ser monsterorkanen ut fra verdensrommet <a href="#">Trykk her for å lese videre</a></p>
        </article>
        <article id="artikkel3">
            <h2>Mest kommentert</h2>
            <p>Svenske Greta Thunberg (16) nominert til Nobels fredspris <a href="#">Trykk her for å lese videre</a></p>
        </article>
        <article id="artikkel4">
            <h2>Tilfeldig utvalgt</h2>
            <p>Hundretusener demonstrerer for klima over hele verden <a href="#">Trykk her for å lese videre</a></p>
        </article>
        
    </main>
</body>

<!-- Denne siden er utviklet av Ajdin Bajrovic & Robin Kleppang, siste gang endret 10.10.2019 -->
<!-- Denne siden er kontrollert av Robin Kleppang & Glenn Petter Pettersen, siste gang 10.10.2019 -->

</html>

<!--Aron Snekkestad -->