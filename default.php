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
        <a class="bildeKontroll" href="#" onclick="hamburgerMeny()" tabindex="10">
            <img src="bilder/hamburgerIkon.svg" alt="Hamburger-menyen" class="hamburgerKnapp">
        </a>
        <!-- Legger til knapper for å registrere ny bruker eller innlogging -->
        <button id="registrerKnapp" onClick="location.href='registrer.php'" tabindex="9">REGISTRER</button>
        <button id="logginnKnapp" onClick="location.href='logginn.php'" tabindex="8">LOGG INN</button>
        <!-- Logoen øverst i venstre hjørne -->
        <a class="bildeKontroll" href="default.php" tabindex="7">
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
    
    <!-- Logoen midten øverst på siden, med tittel -->
    <header onclick="lukkHamburgerMeny()">
        <img src="bilder/klimate.png" alt="Klimate logo"class="Logo_forside">
        <h1 style="display: none">Bilde av Klimate logoen.</h1>
        <p id="default_beskrivelse">Klimate er en nettside hvor du kan diskutere klimasaker med likesinnede personer!</p>
    </header>
    <main id="default_main" onclick="lukkHamburgerMeny()">   
        
        <article id="artikkel1">
            <h2>Nyeste</h2>
            <p>Regjeringen verner bilister etter økt CO₂-avgift</p>
            <a href="#">Trykk her for å lese videre</a>
        </article>
        <article id="artikkel2">
            <h2>Mest populære</h2>
            <p>Slik ser monsterorkanen ut fra verdensrommet</p>
            <a href="#">Trykk her for å lese videre</a>
        </article>
        <article id="artikkel3">
            <h2>Mest kommentert</h2>
            <p>Svenske Greta Thunberg (16) nominert til Nobels fredspris</p>
            <a href="#">Trykk her for å lese videre</a>
        </article>
        <article id="artikkel4">
            <h2>Tilfeldig utvalgt</h2>
            <p>Hundretusener demonstrerer for klima over hele verden</p>
            <a href="#">Trykk her for å lese videre</a>
        </article>
        
    </main>
    <footer>
        <p class=footer_beskrivelse>Denne siden er laget av følgende personer: <br>
        Ajdin Bajrovic, Robin Kleppang, Glenn Pettersen, Aron Snekkestad, Petter Fiskvik</p>
    </footer>
</body>

<!-- Denne siden er utviklet av Ajdin Bajrovic & Robin Kleppang, siste gang endret 10.10.2019 -->
<!-- Denne siden er kontrollert av Robin Kleppang & Glenn Petter Pettersen, siste gang 10.10.2019 -->

</html>