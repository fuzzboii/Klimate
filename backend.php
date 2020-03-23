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

if(isset($forstkommende['idevent'])) {
    $visArr = true;
} else {
    $visArr = false;
}

// Denne sorterer og henter ut det siste kommentaren
$hentKommenterQ = "select artikkel, ingress from kommentar 
                    where bruker = :idbruker order by tid DESC;";
$hentKommenterSTMT = $db->prepare($hentKommenterQ);
$hentKommenterSTMT -> bindParam(":idbruker", $_SESSION['idbruker']);
$hentKommenterSTMT->execute();
$sisteKommentar = $hentKommenterSTMT->fetch(PDO::FETCH_ASSOC);

if(isset($sisteKommentar['artikkel'])) {
    $visKom = true;
} else {
    $visKom = false;
}

// tabindex som skal brukes til å bestemme startpunkt på visningen av arrangementene, denne endres hvis vi legger til flere elementer i navbar eller lignende
$tabindex = 10;
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

            <!-- Del for å vise kommentarer til brukeren -->
            <?php if(isset($_GET['artikler']) && $_GET['artikler'] == $_SESSION['idbruker'] ) { ?>

            <main id="backend_main" onclick="lukkHamburgerMeny()">            
                <section id="backend_section">
                    <ul class="backendNav">
                        <li><a class="aktiv" onClick="location.href='backend.php?artikler=<?php echo($_SESSION['idbruker'])?>'">Artikler</a></li>
                        <li><a onClick="location.href='backend.php?arrangementer=<?php echo($_SESSION['idbruker'])?>'">Arrangementer</a></li>
                        <li><a onClick="location.href='profil.php?bruker=<?php echo($_SESSION['idbruker'])?>'">Profil</a></li>
                    </ul>
                </section>
               
                <!-- Dette vil da være resultat av en spørring mot database, bruk av echo for å vise -->
                
                    <?php 
                         //----------------------------------------------------//
                        // Henter brukerens kommenterte artikler fra database //
                       //----------------------------------------------------//

                        $mestKommenterteQ = "select idartikkel, artnavn, artingress, ingress 
                                            from kommentar, artikkel
                                            where kommentar.artikkel = artikkel.idartikkel and kommentar.bruker = " . $_SESSION['idbruker'] . "
                                            group by idartikkel limit 4";
                        $mestKommenterteSTMT = $db->prepare($mestKommenterteQ);
                        $mestKommenterteSTMT->execute();
                        $mestKommenterte = $mestKommenterteSTMT->fetchAll(PDO::FETCH_ASSOC);
                        ?>

                    <section class="backend_grid">
                        <section>
                        <?php for($i = 0; $i < count($mestKommenterte); $i++) { ?>
                            
                        <section id="backend_innholdSeksjonArtikkel">
                            <?php
                            $hentArtBilde = "select hvor from bilder, artikkelbilde where artikkelbilde.idartikkel = " . $mestKommenterte[$i]['idartikkel'] . " and artikkelbilde.idbilde = bilder.idbilder";
                            $stmtArtBilde = $db->prepare($hentArtBilde);
                            $stmtArtBilde->execute();
                            $resBilde = $stmtArtBilde->fetch(PDO::FETCH_ASSOC);
                            ?>
                            <section id="backend_artikkelBildeFelt">
                                <?php
                                if (!$resBilde) { ?>
                                    <!-- Standard artikkelbilde om arrangør ikke har lastet opp noe enda -->
                                    <img class="default_def_BildeBoks" src="bilder/stockevent.jpg" alt="Bilde av Oleg Magni fra Pexels">
                                <?php } else {
                                    // Tester på om filen faktisk finnes
                                    $testPaa = $resBilde['hvor'];
                                    if(file_exists("$lagringsplass/$testPaa")) {  
                                        //Artikkelbilde som resultat av spørring
                                        if(file_exists("$lagringsplass/" . "thumb_" . $testPaa)) {  ?> 
                                            <!-- Hvis vi finner et miniatyrbilde bruker vi det -->
                                            <img class="default_art_BildeBoks" src="bilder/opplastet/thumb_<?php echo($resBilde['hvor'])?>" alt="Bilde for <?php echo($mestKommenterte[$i]['artnavn'])?>">
                                        <?php } else { ?>
                                            <img class="default_art_BildeBoks" src="bilder/opplastet/<?php echo($resBilde['hvor'])?>" alt="Bilde for <?php echo($mestKommenterte[$i]['artnavn'])?>">
                                        <?php } ?>
                                    <?php } else { ?>
                                        <img class="default_art_BildeBoks" src="bilder/stockevent.jpg" alt="Bilde av Oleg Magni fra Pexels">
                                    <?php }
                                } ?>
                            </section>

                            <?php
                            $hentNyesteKom = "select artikkel, bruker, ingress, tid 
                                             from kommentar 
                                             where kommentar.bruker = " . $_SESSION['idbruker'] . " 
                                            and artikkel = " . $mestKommenterte[$i]['idartikkel'] . "
                                            and tid<current_timestamp()
                                            order by tid desc limit 1";
                            $stmtNyesteKom = $db->prepare($hentNyesteKom);
                            $stmtNyesteKom->execute();
                            $resKommentar = $stmtNyesteKom->fetch(PDO::FETCH_ASSOC);   
                            ?>

                            <section id="backend_artikkelFelt">
                                <h3 class="PopArtiklerOverskrift"><?php echo $mestKommenterte[$i]['artnavn'] ?> </h3>
                                <p class="PopArtiklerIngress"><?php echo $mestKommenterte[$i]['artingress'] ?> </p>
                                
                                <a href="artikkel.php?artikkel=<?php echo($mestKommenterte[$i]['idartikkel'])?>">...Les videre</a>                                      
                            </section>
                                <section class="backendbildeFlex">
                                    <img class="backend_antallKommentarerIkon" src="bilder/meldingIkon.png">
                                </section>
                                <section>
                                    <p class="PopKommentar">Din nyeste kommentar:</p>
                                    <p class="PopArtiklerTekst"><?php echo($resKommentar['tid'])?>: <?php echo $resKommentar['ingress'] ?></p>
                                </section>
                        </section>
                        <?php } ?>
                    </section>

                    <section>
                        <section class="backend_headerIntvindu">
                            <p>Kommenterte artikler</p>
                        </section>

                        <section class="backend_Intvindu">
                            <section>

                                <?php 
                                $mestKommenterteF = "select idartikkel, artnavn, artingress, ingress 
                                from kommentar, artikkel
                                where kommentar.artikkel = artikkel.idartikkel and kommentar.bruker = " . $_SESSION['idbruker'] . "
                                group by idartikkel limit 4, 50";
                                $kommenterteArtSTMT = $db->prepare($mestKommenterteF);
                                $kommenterteArtSTMT->execute();
                                $komentertRes = $kommenterteArtSTMT->fetchAll(PDO::FETCH_ASSOC);
                                ?>

                            <?php for($i = 0; $i < count($komentertRes); $i++) { ?>
                                
                            <section id="backend_artikkelVindu">

                                <?php
                                $hentNyesteKom = "select artikkel, bruker, ingress, tid 
                                                from kommentar 
                                                where kommentar.bruker = " . $_SESSION['idbruker'] . " 
                                                and artikkel = " . $komentertRes[$i]['idartikkel'] . "
                                                and tid<current_timestamp()
                                                order by tid desc limit 1";
                                $stmtNyesteKom = $db->prepare($hentNyesteKom);
                                $stmtNyesteKom->execute();
                                $resKommentar = $stmtNyesteKom->fetch(PDO::FETCH_ASSOC);   
                                ?>

                                <section id="backend_artikkelFelt">
                                    <h3 class="PopArtiklerOverskrift"><?php echo $komentertRes[$i]['artnavn'] ?> </h3>
                                    <p class="PopArtiklerIngress"><?php echo $komentertRes[$i]['artingress'] ?> </p>
                                    
                                    <a href="artikkel.php?artikkel=<?php echo($komentertRes[$i]['idartikkel'])?>">...Les videre</a>                                      
                                </section>
                                    <section>
                                        <p class="PopKommentar">Din nyeste kommentar:</p>
                                        <p class="PopArtiklerTekst"><?php echo($resKommentar['tid'])?>: <?php echo $resKommentar['ingress'] ?></p>
                                    </section>
                                </section>
                                <?php } ?>
                            </section>
                            
                        </section>

                        
                    
                    
                    </section>
                </main>

                <!-- Del for å vise oversikt på arrangementer -->
            <?php } else if(isset($_GET['arrangementer']) && $_GET['arrangementer'] == $_SESSION['idbruker'] ) { ?>

            <main id="backend_main" onclick="lukkHamburgerMeny()">            
                <section id="backend_section">
                    <ul class="backendNav">
                        <li><a onClick="location.href='backend.php?artikler=<?php echo($_SESSION['idbruker'])?>'">Artikler</a></li>
                        <li><a class="aktiv" onClick="location.href='backend.php?arrangementer=<?php echo($_SESSION['idbruker'])?>'">Arrangementer</a></li>
                        <li><a onClick="location.href='profil.php?bruker=<?php echo($_SESSION['idbruker'])?>'">Profil</a></li>
                    </ul>
                </section>

                <?php 
                         //----------------------------------------------------//
                        // Henter brukerens kommenterte artikler fra database //
                       //----------------------------------------------------//

                        $dineArrangementer = "select idevent, eventnavn, tidspunkt, veibeskrivelse, interessert
                                            from event, påmelding
                                            where idevent=event_id and bruker_id= " . $_SESSION['idbruker'] . " and not interessert='Invitert'";
                        $arrangementerSTMT = $db->prepare($dineArrangementer);
                        $arrangementerSTMT->execute();
                        $ArrangRes = $arrangementerSTMT->fetchAll(PDO::FETCH_ASSOC);
                        ?>

                <section class="backend_grid">
                    <section>
                        <?php for($i = 0; $i < count($ArrangRes); $i++) { ?>
                            
                        <section id="backend_innholdSeksjonArg">
                            <?php
                            $hentArgBilde = "select hvor from bilder, eventbilde where eventbilde.event = " . $ArrangRes[$i]['idevent'] . " and eventbilde.bilde = bilder.idbilder";
                            $stmtArgBilde = $db->prepare($hentArgBilde);
                            $stmtArgBilde->execute();
                            $resBilde = $stmtArgBilde->fetch(PDO::FETCH_ASSOC);
                            ?>
                            <section >
                                <?php
                                if (!$resBilde) { ?>
                                    <!-- Standard artikkelbilde om arrangør ikke har lastet opp noe enda -->
                                    <img class="backend_art_BildeBoks" src="bilder/stockevent.jpg" alt="Bilde av Oleg Magni fra Pexels">
                                <?php } else {

                                    // Tester på om filen faktisk finnes
                                    $testPaa = $resBilde['hvor'];
                                    if(file_exists("$lagringsplass/$testPaa")) {  
                                        //Artikkelbilde som resultat av spørring
                                        if(file_exists("$lagringsplass/" . "thumb_" . $testPaa)) {  ?> 
                                            <!-- Hvis vi finner et miniatyrbilde bruker vi det -->
                                            <img class="backend_art_BildeBoks" src="bilder/opplastet/thumb_<?php echo($resBilde['hvor'])?>" alt="Bilde for <?php echo($ArrangRes[$i]['eventnavn'])?>">
                                        <?php } else { ?>
                                            
                                            <img class="backend_art_BildeBoks" src="bilder/opplastet/<?php echo($resBilde['hvor'])?>" alt="Bilde for <?php echo($ArrangRes[$i]['eventnavn'])?>">
                                        <?php } ?>
                                    <?php } else { ?>

                                        <img class="backend_art_BildeBoks" src="bilder/stockevent.jpg" alt="Bilde av Oleg Magni fra Pexels">
                                    <?php }
                                } ?>
                            </section>

                            <section id="backend_argFelt">
                                <section class="backend_seksjon">
                                    <h3 class="PopArgOverskrift"><?php echo $ArrangRes[$i]['eventnavn'] ?> </h3>
                                    <p class="PopArgIngress"><?php echo $ArrangRes[$i]['tidspunkt'] ?> </p>
                                </section>
                                
                            <?php if($ArrangRes[$i]['interessert'] == "Kanskje") {?>
                                <section class="backend_knapper">
                                    <p class="påmeldtTypeOversikt" style="background-color: rgb(255, 191, 0);"><?php echo $ArrangRes[$i]['interessert'] ?> </p>
                                    <a class="OversiktLenke" href="arrangement.php?arrangement=<?php echo($ArrangRes[$i]['idevent'])?>">Gå til arrangement</a>
                                </section>
                            <?php } else if ($ArrangRes[$i]['interessert'] == "Kan ikke") { ?>
                                <section class="backend_knapper">
                                    <p class="påmeldtTypeOversikt" style="background-color: red;"><?php echo $ArrangRes[$i]['interessert'] ?> </p>
                                    <a class="OversiktLenke" href="arrangement.php?arrangement=<?php echo($ArrangRes[$i]['idevent'])?>">Gå til arrangement</a>
                                </section>
                            <?php } else { ?>
                                <section class="backend_knapper">
                                    <p class="påmeldtTypeOversikt"><?php echo $ArrangRes[$i]['interessert'] ?> </p>
                                    <a class="OversiktLenke" href="arrangement.php?arrangement=<?php echo($ArrangRes[$i]['idevent'])?>">Gå til arrangement</a>
                                </section>
                            <?php } ?>
                             
                            </section>
                            
                            

                        
                                
                        </section>
                        <?php } ?>
                    </section>


                </section>
            </main>

            <?php } else { ?>

            <main id="backend_main" onclick="lukkHamburgerMeny()">            
                <section id="backend_section">
                    <ul class="backendNav">
                        <li><a onClick="location.href='backend.php?artikler=<?php echo($_SESSION['idbruker'])?>'">Artikler</a></li>
                        <li><a onClick="location.href='backend.php?arrangementer=<?php echo($_SESSION['idbruker'])?>'">Arrangementer</a></li>
                        <li><a onClick="location.href='profil.php?bruker=<?php echo($_SESSION['idbruker'])?>'">Profil</a></li>
                    </ul>
                </section>
            </main>
            

                <?php } ?>
            <?php include("inkluderes/footer.php") ?>
    </body>

    <!-- Denne siden er utviklet av Glenn Petter Pettersen, Robin Kleppang & Aron Snekkestad, siste gang endret 04.03.2020 -->
    <!-- Denne siden er kontrollert av Aron Snekkestad siste gang 06.03.2020 -->

</html>
