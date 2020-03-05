<?php
session_start();

//-------------------------------//
// Innstillinger, faste variable //
//-------------------------------//
include("inkluderes/innstillinger.php");

// Brukere skal kunne sende søknad
if (!isset($_SESSION['idbruker'])) {
    header("Location: default.php?error=1");
} else if ($_SESSION['brukertype'] != '3') {
    header("Location: default.php?error=4");
}

// Enkel test som gjør det mulig å beholde brukerinput etter siden er lastet på nytt (Form submit)
$input_soknad = "";
if (isset($_SESSION['input_soknad'])) {
    // Legger innhold i variable som leses senere på siden
    $input_soknad = $_SESSION['input_soknad'];
    // Sletter innholdet så dette ikke eksisterer utenfor denne siden
    unset($_SESSION['input_soknad']);
}

// Funksjonalitet for å sende en epost
if (isset($_POST['submit'])) {
    // Session variabel for å ta vare på søknaden ved evt feil
    $input_soknad = $_POST['soknaden'];

    if($_POST['epost'] != "") {
        $epostValidert = filter_var($_POST["nyepost"], FILTER_VALIDATE_EMAIL);
        if($epostValidert == false) {
            // Error 2, epost ikke gyldig
            header("Location: soknad.php?error=2");
        } else {
            $epost = $epostValidert;
        }
    }

    if(preg_match("/\S/", $_POST['fnavn']) == 1) {
        $fnavn = $_POST['fnavn'];
    } else {
        // Error 3, fornavn ikke gyldig
        header("Location: soknad.php?error=3");
    }

    if(preg_match("/\S/", $_POST['enavn']) == 1) {
        $enavn = $_POST['enavn'];
    } else {
        // Error 4, etternavn ikke gyldig
        header("Location: soknad.php?error=4");
    }

    if(preg_match("/\S/", $_POST['soknaden']) == 1) {
        $soknaden = $_POST['soknaden'];
    } else {
        // Error 5, søknaden ikke gyldig
        header("Location: soknad.php?error=5");
    }

    if(preg_match("/\S/", $_POST['brukernavn']) == 1) {
        $brukernavn = $_POST['brukernavn'];
    } else {
        // Error 6, brukernavn ikke gyldig
        header("Location: soknad.php?error=6");
    }

    // Tester på om telefonnummer i formatet 
    if(preg_match('/^[0-9]{0,12}$/', $_POST['telefon'])) {
        $tlfnr = $_POST['telefon'];
    } else {
        // Error 7, telefonnummer ikke gyldig
        header("Location: soknad.php?error=7");
    }

    $mailTo = "soknad@klimate.no";
    $headers = "From: ". $_POST['epost'];
    $txt = "Søknad om å bli redaktør fra brukeren ".$brukernavn.".\nNavn: ".$fnavn." ".$enavn.".\nEpost: ".$epost."\nTelefonnummer: ".$tlfnr."\n\n"."Søknad: "."\n".$soknaden;
    
    // Hvis eposten ble godkjent til å sendes, send bruker til backend med melding
	// Dette betyr ikke nødvendigvis at mail faktisk når mottaker
    if(mail($mailTo, "Søknad om å bli redaktør fra ".$brukernavn, $txt, $headers)) {
        unset($_SESSION['input_soknad']);
		header("Location: backend.php?soknadsendt");
	} else {
        // Error 1, kunne ikke sendes
		header("Location: soknad.php?error=1");
    }
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
        <title>Søknad</title>
        <!-- Henter inn ekstern stylesheet -->
        <link rel="stylesheet" type="text/css" href="stylesheet.css">
        <!-- Henter inn favicon, bildet som dukker opp i fanene i nettleseren -->
        <link rel='icon' href='bilder/favicon.png' type='image/x-icon'>
        <!-- Henter inn JavaScript -->
        <script language="JavaScript" src="javascript.js"> </script>
    </head>

    <body class="innhold" onload="erTouch()">
        <?php include("inkluderes/navmeny.php") ?>

        <!-- For å kunne lukke hamburgermenyen ved å kun trykke på et sted i vinduet må lukkHamburgerMeny() funksjonen ligge i deler av HTML-koden -->
        <!-- Kan ikke legge denne direkte i body -->
        <header id="soknad_header" onclick="lukkHamburgerMeny()">
            <!-- Logoen midten øverst på siden, med tittel -->
            <h1 id="overskrift">Søknad om å bli redaktør</h1>
            <script language="JavaScript">
                function erTouch() {
                    if (kanTouchBrukes()) {
                        document.getElementById('overskrift').innerHTML = "Touch er støttet";
                    } else {
                        document.getElementById('overskrift').innerHTML = "Touch ikke støttet";
                    }
                }
            </script>
            <?php
            // Feilmeldinger
            if(isset($_GET['error']) && $_GET['error'] == 1) { ?>
                <p id="mldFEIL">Feil oppsto ved sending av søknad</p>

            <?php } else if(isset($_GET['error']) && $_GET['error'] == 2) { ?>
                <p id="mldFEIL">Oppgi en gyldig epost</p>

            <?php } else if(isset($_GET['error']) && $_GET['error'] == 3) { ?>
                <p id="mldFEIL">Oppgi et gyldig fornavn</p>

            <?php } else if(isset($_GET['error']) && $_GET['error'] == 4) { ?>
                <p id="mldFEIL">Oppgi et gyldig etternavn</p>

            <?php } else if(isset($_GET['error']) && $_GET['error'] == 5) { ?>
                <p id="mldFEIL">Oppgi en gyldig søknad</p>

            <?php } else if(isset($_GET['error']) && $_GET['error'] == 6) { ?>
                <p id="mldFEIL">Oppgi et gyldig brukernavn</p>

            <?php } else if(isset($_GET['error']) && $_GET['error'] == 7) { ?>
                <p id="mldFEIL">Oppgi et gyldig telefonnummer</p>

            <?php } ?>
        </header>

        <!-- Funksjon for å lukke hamburgermeny når man trykker på en del i Main -->
        <main id="soknad_main" onclick="lukkHamburgerMeny()">  
            <!-- Form, innholdet i denne formen skal sendes til en epost adresse. -->
            <form action="soknad.php" method="post">    
                <section class="soknad_form">
                    <!-- Input av brukernavn, som beholder siste innskrevne -->    
                    <section class="inputBoksSoknad" style="margin-top: 1em;">
                        <img class="icon" src="bilder/brukerIkon.png" alt="Brukerikon"> <!-- Ikonet for bruker -->
                        <input type="text" class="RegInnFelt" name="brukernavn" value="<?php echo($_SESSION['brukernavn']) ?>" readonly>
                    </section>
                    <!-- Input av brukernavn, som beholder siste innskrevne -->
                    <section class="inputBoksSoknad">
                        <img class="icon" src="bilder/emailIkon.png" alt="Epostikon"> <!-- Ikonet for epostadresse -->
                        <input type="email" class="RegInnFelt" name="epost" value="<?php if(filter_var($_SESSION['epost'], FILTER_VALIDATE_EMAIL) != false) {echo($_SESSION['epost']);} ?>" placeholder="Skriv inn e-postadresse" required title="Skriv inn en gyldig epostadresse">
                    </section>
                    <!-- Input av brukernavn, som beholder siste innskrevne -->
                    <section class="inputBoksSoknad">
                        <img class="icon" src="bilder/fnenIkon.png" alt="Fornavnikon"> <!-- Ikonet for fornavn -->
                        <input type="fnavn" class="RegInnFelt" name="fnavn" value="<?php echo($_SESSION['fornavn']) ?>" placeholder="Skriv inn fornavnet ditt" required title="Skriv inn fornavnet ditt">
                    </section>
                    <!-- Input av brukernavn, som beholder siste innskrevne -->
                    <section class="inputBoksSoknad">
                        <img class="icon" src="bilder/fnenIkon.png" alt="Etternavnikon"> <!-- Ikonet til etternavn -->
                        <input type="enavn" class="RegInnFelt" name="enavn" value="<?php echo($_SESSION['etternavn']) ?>" placeholder="Skriv inn etternavnet ditt" required title="Skriv inn etternavnet ditt">
                    </section>
                    <!-- Input av brukernavn, som beholder siste innskrevne -->
                    <section class="inputBoksSoknad">
                        <img class="icon" src="bilder/telefonIkon.png" alt="telefonikon"> <!-- Ikonet for telefonnummer -->
                        <input type="telefon" class="RegInnFelt" name="telefon" value="<?php if(preg_match('/^[0-9]{0,12}$/', $_SESSION['telefonnummer'])) {echo($_SESSION['telefonnummer']);} ?>" placeholder="Skriv inn telefonnummer" required title="Skriv inn ditt telefonnummer">
                    </section>
                </section>
                <section class="soknad_form">
                    <!-- Tekstfelt for søknad. -->
                    <section>
                        <textarea class="textarea_Soknad" name="soknaden" placeholder="Vennligst fyll ut din søknad..." rows="13    " cols="60" autofocus   ></textarea>     
                    </section>
                    <section>
                        <button type="submit" name="submit" class="soknad_knapp" style="margin-top: 2em;">Send søknad</button>
                    </section>
                </section>
            </form>
        </main>
        <?php include("inkluderes/footer.php") ?>
    </body>

    <!-- Denne siden er utviklet av Robin Kleppang og Glenn Petter Pettersen, siste gang endret 07.02.2020 -->
    <!-- Denne siden er kontrollert av Glenn Petter Pettersen, siste gang 07.02.2020 -->

</html>