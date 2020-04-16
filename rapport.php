<?php
session_start();

//-------------------------------//
// Innstillinger, faste variable //
//-------------------------------//
include("inkluderes/innstillinger.php");

// Forsikrer seg om kun tilgang for administrator
if (!isset($_SESSION['idbruker'])) {
    // En utlogget bruker har forsøkt å nå rapport-siden
    $_SESSION['default_melding'] = "Du kan ikke se denne siden";
    header("Location: default.php");
} else if ($_SESSION['brukertype'] != '1') {
    // En innlogget bruker som ikke er administrator har forsøkt å åpne rapport-siden, loggfører dette
    $leggTilMisbrukQ = "insert into misbruk(tekst, bruker) values('Oppdaget misbruk, forsøkte nå rapport-siden', :bruker)";
    $leggTilMisbrukSTMT = $db -> prepare($leggTilMisbrukQ);
    $leggTilMisbrukSTMT -> bindparam(":bruker", $_SESSION['idbruker']);
    $leggTilMisbrukSTMT -> execute();

    // Sender melding til alle administratorere 

    $hentAdminQ = "select idbruker from bruker where brukertype = 1";
    $hentAdminSTMT = $db -> prepare($hentAdminQ);
    $hentAdminSTMT -> execute();
    $administratorer = $hentAdminSTMT -> fetchAll(PDO::FETCH_ASSOC);

    foreach ($administratorer as $admin) {
        $nyMeldingQ = "insert into melding(tittel, tekst, tid, lest, sender, mottaker) values('Oppdaget misbruk', 'Automatisk misbruk oppdaget, bruker forsøkte nå Rapportsiden.', NOW(), 0, :sender, :mottaker)";
        $nyMeldingSTMT = $db->prepare($nyMeldingQ);
        $nyMeldingSTMT -> bindparam(":sender", $_SESSION['idbruker']);
        $nyMeldingSTMT -> bindparam(":mottaker", $admin['idbruker']);
        $nyMeldingSTMT->execute();
    }

    session_destroy();
    session_start();
    $_SESSION['default_melding'] = "Du kan ikke se denne siden";
    header("Location: default.php");
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
    <!-- IF-testing på hva bruker ønsker å vise -->
        <!-- Alle brukere -->
        <?php if($_POST['rapport'] == "Alle brukere") { ?>
            <h2>Alle brukere</h2>
        
        <!-- Spesifikk bruker -->
        <?php } elseif($_POST['rapport'] == "Spesifikk bruker") { ?>
            <h2>Spesifikk bruker</h2>
        
        <!-- Eksklusjoner -->
        <?php } elseif($_POST['rapport'] == "Eksklusjoner") { ?>
            <h2>Eksklusjoner</h2>

        <!-- Advarsler -->
        <?php } elseif($_POST['rapport'] == "Advarsler") { ?>
            <h2>Advarsler</h2>
        <?php } ?>
    </main>
    <?php include("inkluderes/footer.php") ?>
</body>

<!-- Denne siden er utviklet av Robin Kleppang, siste gang endret 06.03.2020 -->
<!-- Denne siden er kontrollert av , siste gang 06.03.2020 -->

</html>