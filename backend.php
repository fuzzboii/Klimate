<?php
session_start();

//-------------------------------//
// Innstillinger, faste variable //
//-------------------------------//
include("inkluderes/innstillinger.php");

// Sjekker om bruker har tilgang til å se dette området
if (!isset($_SESSION['idbruker'])) {
    header("Location: default.php?error=1");
} 


//-----------------------------------//
// Henter arrangementer fra database //
//-----------------------------------//

// Denne sorterer og henter ut det førstkommende arrangementet
$hentArrangementQ = "select idevent, eventnavn from event, påmelding
                        where event.idevent = påmelding.event_id
                            and påmelding.bruker_id = :idbruker order by tidspunkt ASC;";
$hentArrangementSTMT = $db->prepare($hentArrangementQ);
$hentArrangementSTMT -> bindParam(":idbruker", $_SESSION['idbruker']);
$hentArrangementSTMT->execute();
$forstkommende = $hentArrangementSTMT->fetch(PDO::FETCH_ASSOC);

// Denne sorterer og henter ut det nyeste arrangementet
$hentKommenterQ = "select artikkel, ingress from kommentar 
                    where bruker = :idbruker order by tid DESC;";
$hentKommenterSTMT = $db->prepare($hentKommenterQ);
$hentKommenterSTMT -> bindParam(":idbruker", $_SESSION['idbruker']);
$hentKommenterSTMT->execute();
$sisteKommentar = $hentKommenterSTMT->fetch(PDO::FETCH_ASSOC);


?>
<!DOCTYPE html>
<html lang="no">

    <head>
        <!-- Setter riktig charset -->
        <meta charset="UTF-8">
        <!-- Legger til viewport -->
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-- Setter tittelen på prosjektet -->
        <title>Oversikt</title>
        <!-- Henter inn ekstern stylesheet -->
        <link rel="stylesheet" type="text/css" href="stylesheet.css">
        <!-- Henter inn favicon, bildet som dukker opp i fanene i nettleseren -->
        <link rel='icon' href='bilder/favicon.png' type='image/x-icon'>
        <!-- Henter inn JavaScript -->
        <script language="JavaScript" src="javascript.js"></script>
    </head>


    <body id="backend_body"> 
        <?php include("inkluderes/navmeny.php") ?>
            
            <!-- Profilbilde med planlagt "Velkommen *Brukernavn hentet fra database*" -->
            <header class="backend_header" onclick="lukkHamburgerMeny()">
                <?php 
                // Del for å vise profilbilde
                // Henter bilde fra database utifra brukerid
                $hentBilde = "select hvor from bilder, brukerbilde where brukerbilde.bruker = " . $_SESSION['idbruker'] . " and brukerbilde.bilde = bilder.idbilder";
                $stmtBilde = $db->prepare($hentBilde);
                $stmtBilde->execute();
                $bilde = $stmtBilde->fetch(PDO::FETCH_ASSOC);
                $antallBilderFunnet = $stmtBilde->rowCount();
                // rowCount() returnerer antall resultater fra database, er dette null finnes det ikke noe bilde i databasen
                if ($antallBilderFunnet != 0) { ?>
                    <?php // Tester på om filen faktisk finnes
                    $testPaa = $bilde['hvor'];
                    if(file_exists("$lagringsplass/$testPaa")) {  ?> 
                        <!-- Hvis vi finner et bilde til bruker viser vi det -->
                        <img src="bilder/opplastet/<?php echo($bilde['hvor'])?>" alt="Profilbilde" class="profil_backend">
                    <?php } else { ?>
                        <img src="bilder/profil.png" alt="Profilbilde" class="profil_backend">
                    <?php } ?>
                <?php } else { ?>
                    <!-- Hvis ikke noe bilde ble funnet benytter vi et standard profilbilde -->
                    <img src="bilder/profil.png" alt="Profilbilde" class="profil_backend">
                <?php } ?>
                <h1 class="velkomst">Velkommen <?php if(preg_match("/\S/", $_SESSION['fornavn']) == 1) { echo($_SESSION['fornavn']); } else { echo($_SESSION['brukernavn']); } ?></h1>
                <?php if($antUlest['antall'] > 0) { ?><p><?php echo("Du har " . $antUlest['antall'] . " uleste meldinger!");?></p><?php } ?></a>
            </header>

            <main id="backend_main" onclick="lukkHamburgerMeny()">
                <section id="backend_section">
                    <!-- Innholdet på siden -->
                    <article>
                        <h2>Førstkommende påmeldt arrangement</h2>
                        <p><?php echo($forstkommende['eventnavn'])?></p>
                        <a href="arrangement.php?arrangement=<?php echo($forstkommende['idevent']) ?>">Trykk her for å lese videre</a>
                    </article>
                    <article>
                        <h2>Din siste kommentar</h2>
                        <p><?php echo($sisteKommentar['ingress'])?></p>
                        <a href="artikkel.php?artikkel=<?php echo($sisteKommentar['artikkel']) ?>">Trykk her for å lese videre</a>
                    </article>
                </section>
            </main>
            <?php include("inkluderes/footer.php") ?>
        </article>   
    </body>

    <!-- Denne siden er utviklet av Glenn Petter Pettersen, Robin Kleppang & Aron Snekkestad, siste gang endret 07.02.2020 -->
    <!-- Denne siden er kontrollert av Robin Kleppang siste gang 07.02.2020 -->

</html>
