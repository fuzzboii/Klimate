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
    <section class="bakgrunnBackend">
        <section class="uklarhetBackend"></section>
    </section>
    
    <!-- Begynnelse på øvre navigasjonsmeny -->
    <nav class="navTop">
            <!-- Legger til en knapp for å logge ut når man er innlogget-->
        <button onClick="location.href='logginn.php'" id="backendLoggUt">LOGG UT</button>
        <!-- Bruker et ikon som skal åpne gardinmenyen, henviser til funksjonen aapneHamburger i javascript.js -->
        <img src="bilder/hamburgerIkon.svg" alt="Hamburger-menyen" class="hamburgerKnappBackend" onclick="aapneHamburger()">
        <!-- Logoen øverst i venstre hjørne, denne leder alltid tilbake til default.php -->
        <img src="bilder/klimateNoText.png" onClick="location.href='default.php'" alt="Klimate logo" class="Logo_navmeny">
        <img src="bilder/profil.png" alt="Profilbilde" class="profil_navmeny">
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
        <img src="bilder/profil.png" alt="Klimate logo"class="profil_backend">
        <h1 class="velkomst">Velkommen Knut-Arne!</h1>
    </header>

    <main id="backend_main">

        <!-- Innholdet på siden -->
            <article class="bgcont">
                <h2>Arrangementer</h2>
                <p>Dugnad hos KlimaVennen <a href="#">Trykk her for å lese videre</a></p>
            </article>
            <article class="bgcont">
                <h2>Diskusjoner</h2>
                <p>Bruk av gressklipper, bensin eller elektrisk? <a href="#">Trykk her for å lese videre</a></p>
            </article>
            <article class="bgcont">
                <h2>Artikler</h2>
                <p>Hundretusener demonstrerer for klima over hele verden <a href="#">Trykk her for å lese videre</a></p>
            </article>
    </main>
</body>

<!-- Denne siden er utviklet av Glenn Petter Pettersen & Aron Snekkestad, siste gang endret 11.10.2019 -->
<!-- Denne siden er kontrollert av Robin Kleppang, siste gang 11.10.2019 -->

</html>
