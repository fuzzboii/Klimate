<?php
session_start();

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
        <!-- javascript:void(0) blir her brukt så siden ikke scroller til toppen av seg selv når du trykker på hamburger-ikonet -->
        <a class="bildeKontroll" href="javascript:void(0)" onclick="hamburgerMeny()" tabindex="4">
            <img src="bilder/hamburgerIkon.svg" alt="Hamburger-menyen" class="hamburgerKnapp">
        </a>
        <!-- Legger til knapper for å registrere ny bruker eller innlogging -->
        <?php
            if (isset($_SESSION['brukernavn'])) {
        ?>
        <button id="registrerKnapp" onClick="location.href='backend.php?loggUt=true'" tabindex="3">LOGG UT</button>
        <?php
            } else {
        ?>
        <button id="registrerKnapp" onClick="location.href='registrer.php'" tabindex="3">REGISTRER</button>
        <?php
            }
        ?>
        <?php
            if (isset($_SESSION['brukernavn'])) {
        ?>
        <?php
            } else {
        ?>
        <button id="logginnKnapp" onClick="location.href='logginn.php'" tabindex="2">LOGG INN</button>
        <?php
            }
        ?>
        <!-- Logoen øverst i venstre hjørne -->
        <a class="bildeKontroll" href="default.php" tabindex="1">
            <img src="bilder/klimateNoText.png" alt="Klimate logo" class="Logo_navmeny">
        </a>    
    <!-- Slutt på navigasjonsmeny-->
    </nav>

    <!-- Gardinmenyen, denne går over alt annet innhold ved bruk av z-index -->
    <section id="navMeny" class="hamburgerMeny">
    
        <!-- innholdet i hamburger-menyen -->
        <!-- -1 tabIndex som standard da menyen er lukket -->
        <section class="hamburgerInnhold">
            <a id = "menytab1" tabIndex = "-1" href="#">Diskusjoner</a>
            <a id = "menytab2" tabIndex = "-1" href="#">Arrangementer</a>
            <a id = "menytab3" tabIndex = "-1" href="#">Artikler</a>
            <a id = "menytab4" tabIndex = "-1" href="#">Profil</a>
            <a id = "menytab5" tabIndex = "-1" href="#">Innstillinger</a>
        </section>
    </section>
    
    <!-- Logoen midten øverst på siden, med tittel -->
    <header onclick="lukkHamburgerMeny()">
        <img src="bilder/klimate.png" alt="Klimate logo"class="Logo_forside">
        <h1 style="display: none">Bilde av Klimate logoen.</h1>        
        <?php 
            if(isset($_GET['utlogget']) && $_GET['utlogget'] == 1){ 
        ?>
        <p id="mldOK">Du har logget ut</p>    
        <?php 
            } else if(isset($_GET['error']) && $_GET['error'] == 1){ 
        ?>
        <p id="mldFEIL">Du må logge inn før du kan se dette området</p>    
        <?php 
            } else if(isset($_GET['error']) && $_GET['error'] == 2){ 
        ?>
        <p id="mldFEIL">Du må logge ut før du kan se dette området</p>    
        <?php 
            }
        ?>
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
    
	<button onclick="topFunction()" id="toppKnapp" title="Toppen"><img src="bilder/pilopp.png" alt="Tilbake til toppen"></button>
    <script>
	    var mybutton = document.getElementById("toppKnapp");
	    window.onscroll = function() {scrollFunction()};
    </script>
    <footer>
        <p class=footer_beskrivelse>&copy; Klimate 2019 | <a href="mailto:kontakt@klimate.no">Kontakt oss</a></p>
    </footer>
</body>

<!-- Denne siden er utviklet av Ajdin Bajrovic & Robin Kleppang, siste gang endret 10.10.2019 -->
<!-- Denne siden er kontrollert av Robin Kleppang & Glenn Petter Pettersen, siste gang 10.10.2019 -->

</html>