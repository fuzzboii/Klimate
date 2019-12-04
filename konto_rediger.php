<?php
session_start();
// Ved adminside IF ($_SESSION['bruker'] and $_SESSION['brukertype'] == 1) {}
if ($_SESSION['brukernavn']) {
    // OK
} else {
    header("Location: default.php?error=1");
}

try {
    include("klimate_pdo.php");
    $db = new myPDO();
} 
catch (PDOException $ex) {
    if ($ex->getCode() == 1049){
        // 1049, Databasen finnes ikke
        header("location: konto.php?error=1");
    }
} 

// Setter så PDO kaster ut feilmelding og stopper funksjonen ved database-feil (PDOException)
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Utlogging av bruker
if(isset($_GET['loggUt']) && $_GET['loggUt'] == "true"){ 
    session_destroy();
    header("Location: default.php?utlogget=1");
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

    <body onload="kontoRullegardin()">
        <!-- Begynnelse på øvre navigasjonsmeny -->
        <nav class="navTop">
            <!-- Legger til en knapp for å logge ut når man er innlogget-->
            <!-- Bruker et ikon som skal åpne gardinmenyen, henviser til funksjonen hamburgerMeny i javascript.js -->
            <a class="bildeKontroll" href="javascript:void(0)" onclick="hamburgerMeny()" tabindex="4">
                <img src="bilder/hamburgerIkon.svg" alt="Hamburger-menyen" class="hamburgerKnapp">
            </a>
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

        <header class="konto_rediger_header" onclick="lukkHamburgerMeny()">
            <h1>Konto</h1>
        </header>


        <main id="konto_rediger_main" onclick="lukkHamburgerMeny()">
            <section class="brukerinformasjon_rediger"> 
                <!-- Underoverskrift -->
                <h2 class="redigerbruker_overskrift">Rediger brukeropplysninger</h2>
                
                <!-- Felt for brukeropplysning endringer -->
                <form method="POST" action="konto_rediger.php" class="konto_rediger_Form">
                    <!-- Brukernavn -->
                    <section class="konto_rediger_inputBoks">
                        <h3 class="endre_brukernavn_overskrift">Endre brukernavn</h3>
                        <input type="text" class="KontoredigeringFelt" name="brukernavn" value="" placeholder="Nytt brukernavn" autofocus>
                    </section>
                    <!-- Epost -->
                    <section class="konto_rediger_inputBoks">
                        <h3 class="endre_epost_overskrift">Endre epost</h3>
                        <input type="email" class="KontoredigeringFelt" name="nyepost" value="" placeholder="Ny epost">
                    </section>     
                    <!-- Passord (Rullegardin) -->
                    <button type="button" id="kontoRullegardin" class="kontoRullegardin">Endre passord</button>
                    <section class="innholdRullegardin">
                        <section class="konto_rediger_inputBoks">
                            <h3 class="endre_gammeltpassord_overskrift">Gammelt passord</h3>
                            <input type="password" class="KontoredigeringFelt" name="gammeltpassord" value="" placeholder="Gammelt passord" autofocus>
                        </section>
                        <section class="konto_rediger_inputBoks">
                            <h3 class="endre_nyttpassord_overskrift">Nytt passord</h3>
                            <input type="password" class="KontoredigeringFelt" name="nyttpassord" value="" placeholder="Nytt passord">
                        </section>
                        <section class="konto_rediger_inputBoks">
                            <h3 class="endre_nyttpassordbekreft_overskrift">Bekreft nytt passord</h3>
                            <input type="password" class="KontoredigeringFelt" name="bekreftnyttpassord" value="" placeholder="Bekreft nytt passord">
                        </section>
                    </section>
                    <!-- Fornavn -->
                    <section class="konto_rediger_inputBoks">
                        <h3 class="endre_fornavn_overskrift">Endre fornavn</h3>
                        <input type="fornavn" class="KontoredigeringFelt" name="nyttfornavn" value="" placeholder="Nytt fornavn">
                    </section>
                    <!-- Etternavn -->
                    <section class="konto_rediger_inputBoks">
                        <h3 class="endre_etternavn_overskrift">Endre etternavn</h3>
                        <input type="etternavn" class="KontoredigeringFelt" name="nyttetternavn" value="" placeholder="Nytt etternavn">
                    </section>
                    
                    <!-- Knapp for å lagre endringer -->
                    <input type="submit" name="subEndring" class="KontoredigeringFelt_knappLagre" value="Lagre endringer">
                </form>
                <!-- Sender brukeren tilbake til forsiden -->
                <button onClick="location.href='konto.php'" name="submit" class="lenke_knapp">Avbryt</button>
            </section>
        </main>


        <button onclick="topFunction()" id="toppKnapp" title="Toppen">Tilbake til toppen</button>

        <footer>
            <p class=footer_beskrivelse>&copy; Klimate 2019 | <a href="mailto:kontakt@klimate.no">Kontakt oss</a></p>
        </footer>
    </body>

</html>

<!-- Denne siden er utviklet av Petter Fiskvik og Ajdin Bajrovic, siste gang endret 03.12.2019 -->
<!-- Sist kontrollert av ____ ____, __.__.____ -->