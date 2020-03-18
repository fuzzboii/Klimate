<?php
session_start();

//-------------------------------//
// Innstillinger, faste variable //
//-------------------------------//
include("inkluderes/innstillinger.php");

// Forsikrer seg om kun tilgang for administrator
if (!isset($_SESSION['idbruker'])) {
    // En utlogget bruker har forsøkt å nå rapport-siden
    header("Location: default.php?error=1");
} else if ($_SESSION['brukertype'] != '1') {
    // En innlogget bruker som ikke er administrator har forsøkt å åpne rapport-siden, loggfører dette
    $leggTilMisbrukQ = "insert into misbruk(tekst, bruker) values('Oppdaget misbruk, forsøkte nå rapport-siden', :bruker)";
    $leggTilMisbrukSTMT = $db -> prepare($leggTilMisbrukQ);
    $leggTilMisbrukSTMT -> bindparam(":bruker", $_SESSION['idbruker']);
    $leggTilMisbrukSTMT -> execute();
    header("Location: default.php?error=6");
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
        <title>Rapport</title>
        <!-- Henter inn ekstern stylesheet -->
        <link rel="stylesheet" type="text/css" href="stylesheet.css">
        <!-- Henter inn favicon, bildet som dukker opp i fanene i nettleseren -->
        <link rel='icon' href='bilder/favicon.png' type='image/x-icon'>
        <!-- Henter inn JavaScript -->
        <script language="JavaScript" src="javascript.js"> </script>
    </head>

    <body id="rapport_body" onclick="lukkMelding('mldFEIL_boks')">
        <?php include("inkluderes/navmeny.php") ?>

        <!-- For å kunne lukke hamburgermenyen ved å kun trykke på et sted i vinduet må lukkHamburgerMeny() funksjonen ligge i deler av HTML-koden -->
        <!-- Kan ikke legge denne direkte i body -->
        <header onclick="lukkHamburgerMeny()">
            <!-- Overskrift på siden -->
            <h1>Rapport</h1>
        </header>
        <main onclick="lukkHamburgerMeny()">
            <?php echo($_POST['rapport']) ?>
        </main>
        <?php include("inkluderes/footer.php") ?>
    </body>
</html>

    <!-- Denne siden er utviklet av Robin Kleppang, siste gang endret 06.03.2020 -->
    <!-- Denne siden er kontrollert av , siste gang 06.03.2020 -->