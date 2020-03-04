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
// Skal få sendt til epost. 
// Må legge til siste del for at eposten skal sendes.
if (isset($_POST['submit'])) {
    $brukernavn = $_POST['brukernavn'];
    $epost = $_POST['epost'];
    $fnavn = $_POST['fnavn'];
    $enavn = $_POST['enavn'];
    $soknaden = $_POST['soknaden'];

    $mailTo = "soknad@klimate.no";
    $headers = "From: ". $_POST['epost'];
    $txt = "Søknad om å bli redaktør fra brukeren ".$brukernavn.".\n"."Navn: ".$fnavn." ".$enavn.".\n"."Epost: ".$epost."\n\n"."Søknad: "."\n".$soknaden;
    
    // Hvis eposten ble godkjent til å sendes, send bruker til backend med melding
	// Dette betyr ikke nødvendigvis at mail faktisk når mottaker
    if(mail($mailTo, "Søknad om å bli redaktør fra ".$brukernavn, $txt, $headers)) {
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

    <body class="innhold">
        <?php include("inkluderes/navmeny.php") ?>

        <!-- For å kunne lukke hamburgermenyen ved å kun trykke på et sted i vinduet må lukkHamburgerMeny() funksjonen ligge i deler av HTML-koden -->
        <!-- Kan ikke legge denne direkte i body -->
        <header id="soknad_header" onclick="lukkHamburgerMeny()">
            <!-- Logoen midten øverst på siden, med tittel -->
            <h1>Søknad om å bli redaktør</h1>
            <?php
            // Feilmeldinger
            if(isset($_GET['error']) && $_GET['error'] == 1) { ?>
                <p id="mldFEIL">Feil oppsto ved sending av søknad</p>
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
                        <input type="email" class="RegInnFelt" name="epost" value="<?php echo($_SESSION['epost']) ?>" placeholder="Skriv inn e-postadresse" required title="Skriv inn en gyldig epostadresse">
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
                        <input type="telefon" class="RegInnFelt" name="telefon" value="<?php echo($_SESSION['telefonnummer']) ?>" placeholder="Skriv inn telefonnummer" required title="Skriv inn ditt telefonnummer">
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