<?php
session_start();
// Ved adminside IF ($_SESSION['bruker'] and $_SESSION['brukertype'] == 1) {}
if ($_SESSION['brukernavn']) {
    // OK
} else {
    // Ikke OK, sender tilbake til default med feilmelding
    header("Location: default.php?error=1");
}

?>

<!DOCTYPE html>
<html lang="no">

    <head>
        <!-- Setter riktig charset -->
        <meta charset="UTF-8">
        <!-- Legger til viewport -->
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-- Setter tittelen på prosjektet -->
        <title>Konto</title>
        <!-- Henter inn ekstern stylesheet -->
        <link rel="stylesheet" type="text/css" href="stylesheet.css">
        <!-- Henter inn favicon, bildet som dukker opp i fanene i nettleseren -->
        <link rel='icon' href='bilder/favicon.png' type='image/x-icon'>
        <!-- Henter inn JavaScript -->
        <script language="JavaScript" src="javascript.js"></script>
    </head>

    <body>
        <article class="innhold">
            <!-- Begynnelse på øvre navigasjonsmeny -->
            <nav class="navTop">
                <!-- Bruker et ikon som skal åpne gardinmenyen, henviser til funksjonen hamburgerMeny i javascript.js -->
                <a class="bildeKontroll" href="javascript:void(0)" onclick="hamburgerMeny()" tabindex="4">
                    <img src="bilder/hamburgerIkon.svg" alt="Hamburger-menyen" class="hamburgerKnapp">
                </a>
                <!-- Profilbilde i navmenyen, leder til konto-siden -->
                <a class="bildeKontroll" href="javascript:void(0)" onClick="location.href='konto.php'" tabindex="3">
                    <img src="bilder/profil.png" alt="Profilbilde" class="profil_navmeny">
                </a>
                <!-- Legger til en knapp for å logge ut når man er innlogget -->
                <form method="POST" action="default.php">
                    <button name="loggUt" id="backendLoggUt" tabindex="2">LOGG UT</button>
                </form>

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
                    <a id = "menytab1" tabIndex = "-1" href="#">Arrangementer</a>
                    <a id = "menytab2" tabIndex = "-1" href="#">Artikler</a>
                    <a id = "menytab3" tabIndex = "-1" href="#">Diskusjoner</a>
                    <a id = "menytab4" tabIndex = "-1" href="backend.php">Oversikt</a>
                    <a id = "menytab5" tabIndex = "-1" href="konto.php">Konto</a>
                </section>
            </section>

            <!-- For å kunne lukke hamburgermenyen ved å kun trykke på et sted i vinduet må lukkHamburgerMeny() funksjonen ligge i deler av HTML-koden -->
            <!-- Kan ikke legge denne direkte i body -->
            <header class="konto_header" onclick="lukkHamburgerMeny()">
                <h1>Konto</h1>
            </header>

            <!-- Meldinger til bruker -->
            <?php if(isset($_GET['error']) && $_GET['error'] == 1){ ?>
                <p id="mldFEIL">Systemfeil, kunne ikke koble til database. Vennligst prøv igjen om kort tid.</p>

            <?php } else if(isset($_GET['vellykket']) && $_GET['vellykket'] == 1){ ?>
                <p id="mldOK">Konto oppdatert</p>    

            <?php } else if(isset($_GET['error']) && $_GET['error'] == 2){ ?>
                <p id="mldFEIL">Kunne ikke oppdatere konto, vennligst prøv igjen senere</p>    
            <?php } ?> 

            <!-- Konto brukeropplysninger -->
            <main id="konto_main" onclick="lukkHamburgerMeny()">
                <section class="brukerinformasjon">
                    <table class="brukerinformasjon_tabell">
                        <!-- Brukernavn output -->
                        <tr>
                            <th>Brukernavn:</th>
                                <td><?php echo($_SESSION['brukernavn']) ?></td>
                        <!-- Epost output -->
                        <tr>
                            <th>Epost:</th>
                                <td><?php echo($_SESSION['epost']) ?></td>
                        </tr>  
                        <!-- Fornavn output -->
                        <tr>
                            <th>Fornavn:</th>
                                <td><?php echo($_SESSION['fornavn']) ?></td>
                        </tr>
                        <!-- Etternavn output -->
                        <tr>
                            <th>Etternavn:</th>
                                <td><?php echo($_SESSION['etternavn']) ?></td>
                        </tr>
                    
                    </table>

                    <button onClick="location.href='konto_rediger.php'" name="redigerkonto" class="rediger_konto_knapp">Rediger konto</button>
                </section> 
            </main>

            <!-- Knapp som vises når du har scrollet i vinduet, tar deg tilbake til toppen -->
            <button onclick="topFunction()" id="toppKnapp" title="Toppen"><img src="bilder/pilopp.png" alt="Tilbake til toppen"></button>

            <!-- Footer, epost er for øyeblikket på en catch-all, videresendes til RK -->
            <footer>
                <p class=footer_beskrivelse>&copy; Klimate 2019 | <a href="mailto:kontakt@klimate.no">Kontakt oss</a></p>
            </footer>
        </article>
    </body>

    
<!-- Denne siden er utviklet av Ajdin Bajrovic, siste gang endret 02.12.2019 -->
<!-- Sist kontrollert av Robin Kleppang, siste gang 09.12.2019 -->

</html>
