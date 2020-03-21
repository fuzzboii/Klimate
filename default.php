<?php
session_start();


//-------------------------------//
// Innstillinger, faste variable //
//-------------------------------//
include("inkluderes/innstillinger.php");


// Utlogging av bruker
if (isset($_POST['loggUt'])) { 
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
        <title>Klimate</title>
        <!-- Henter inn ekstern stylesheet -->
        <link rel="stylesheet" type="text/css" href="stylesheet.css">
        <!-- Henter inn favicon, bildet som dukker opp i fanene i nettleseren -->
        <link rel='icon' href='bilder/favicon.png' type='image/x-icon'>
        <!-- Henter inn JavaScript -->
        <script language="JavaScript" src="javascript.js"> </script>
    </head>

    <body id="default_body" onclick="lukkMelding('mldFEIL_boks')">
        <?php include("inkluderes/default_navmeny.php") ?>

        <!-- For å kunne lukke hamburgermenyen ved å kun trykke på et sted i vinduet må lukkHamburgerMeny() funksjonen ligge i deler av HTML-koden -->
        <!-- Kan ikke legge denne direkte i body -->
        <header onclick="lukkHamburgerMeny()">
            
            <!-- Logoen midten øverst på siden, med tittel -->
            <img src="bilder/klimate.png" alt="Klimate logo"class="Logo_forside">
            <h1 style="display: none">Bilde av Klimate logoen.</h1>    
 
            <!-- Meldinger til bruker -->
            <?php if(isset($_GET['utlogget']) && $_GET['utlogget'] == 1){ ?>
                <p id="mldOK">Du har logget ut</p>    

            <?php } else if(isset($_GET['avregistrert']) && $_GET['avregistrert'] == "true"){ ?>
                <p id="mldFEIL">Du har blitt avregistrert</p>  

            <?php } else if (isset($_GET['error']) && $_GET['error'] >= 1 && $_GET['error'] <= 6) { ?>
                <section id="mldFEIL_boks">
                    <section id="mldFEIL_innhold">
                        <?php if(isset($_GET['error']) && $_GET['error'] == 1){ ?>
                            <p id="mldFEIL">Du må logge inn før du kan se dette området</p>  

                        <?php } else if(isset($_GET['error']) && $_GET['error'] == 2){ ?>
                            <p id="mldFEIL">Du må logge ut før du kan se dette området</p>   

                        <?php } else if(isset($_GET['systemerror'])){ ?>
                            <p id="mldFEIL">Systemfeil, kunne ikke koble til database. Vennligst prøv igjen om kort tid.</p>

                        <?php } else if(isset($_GET['error']) && $_GET['error'] == 4){ ?>
                            <p id="mldFEIL">Du kan ikke se dette området</p>  

                        <?php } else if(isset($_GET['error']) && $_GET['error'] == 5){ ?>
                            <p id="mldFEIL">Denne brukeren er avregistrert</p>  

                        <?php } else if(isset($_GET['error']) && $_GET['error'] == 6){ ?>
                            <p id="mldFEIL">Du har forsøkt å nå et restriktert område, handlingen har blitt loggført</p> 
                        
                        <?php } ?>
                        <!-- Denne gjør ikke noe, men er ikke utelukkende åpenbart at man kan trykke hvor som helst -->
                        <button id="mldFEIL_knapp">Lukk</button>
                    </section>
                </section>
            <?php } ?>

            <p id="default_beskrivelse">Klimate er en nettside hvor du kan diskutere klimasaker med likesinnede personer!</p>
        </header>
        
        <!-- Funksjon for å lukke hamburgermeny når man trykker på en del i Main -->
        <main id="default_main" onclick="lukkHamburgerMeny()">   
            <section id="default_section">
                <?php if(!isset($_GET['systemerror'])) { ?>
                    <!-- IDene brukes til å splitte opp kolonnene i queries -->
                   <article>
                        <section id="default_overskriftSeksjon">
                            <h2>Populære artikler</h2>   
                        </section>
                        <!-- Dette vil da være resultat av en spørring mot database, bruk av echo for å vise -->
                        <section id="default_innholdSeksjonArtikkel">
                            <?php 
                                //-----------------------------------------------//
                                // Henter mest kommenterte/populære fra database //
                                //----------------------------------------------//

                                // Henter artikler fra database, sorterer på høyeste antall og viser denne
                                $mestKommenterteQ = "select count(idkommentar) as antall, idartikkel, artnavn, artingress from kommentar, artikkel
                                                    where kommentar.artikkel = artikkel.idartikkel
                                                    group by idartikkel
                                                    order by antall DESC limit 5";
                                $mestKommenterteSTMT = $db->prepare($mestKommenterteQ);
                                $mestKommenterteSTMT->execute();
                                $mestKommenterte = $mestKommenterteSTMT->fetchAll(PDO::FETCH_ASSOC);
                                ?>

                                <?php for($i = 0; $i < count($mestKommenterte); $i++) { ?>
                                    <?php
                                    $hentArtBilde = "select hvor from bilder, artikkelbilde where artikkelbilde.idartikkel = " . $mestKommenterte[$i]['idartikkel'] . " and artikkelbilde.idbilde = bilder.idbilder";
                                    $stmtArtBilde = $db->prepare($hentArtBilde);
                                    $stmtArtBilde->execute();
                                    $resBilde = $stmtArtBilde->fetch(PDO::FETCH_ASSOC);
                                    ?>
                                    <section id="default_artikkelBildeFelt">
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

                                    <section id="default_artikkelFelt">
                                        <h3 class="PopArtiklerOverskrift"><?php echo $mestKommenterte[$i]['artnavn'] ?> </h3>
                                        <p class="PopArtiklerIngress"><?php echo $mestKommenterte[$i]['artingress'] ?> </p>
                                        
                                        <img class="default_antallKommentarerIkon" src="bilder/meldingIkon.png">
                                        <?php
                                            $hentAntallKommentarer = "select count(idkommentar) as antall from kommentar where kommentar.artikkel = " . $mestKommenterte[$i]['idartikkel'];
                                            $hentAntallKommentarerSTMT = $db -> prepare($hentAntallKommentarer);
                                            $hentAntallKommentarerSTMT->execute();
                                            $antallkommentarer = $hentAntallKommentarerSTMT->fetch(PDO::FETCH_ASSOC);
                                        ?>
                                        <p id="default_antallKommentarer"><?php echo $antallkommentarer['antall'] ?></p>
                                        <a href="artikkel.php?artikkel=<?php echo($mestKommenterte[$i]['idartikkel'])?>">...Les videre</a>
                                                                                  
                                    </section>
                                <?php } ?>
                        </section>
                    </article>
                    <article>
                        <section id="default_overskriftSeksjon">
                            <h2>Kommende arrangementer</h2>  
                        </section>
                        
                        <section id="default_innholdSeksjonArrangement">
                            <?php 
                                //------------------------------------//
                                // Henter arrangementer fra database //
                                //-----------------------------------//

                                // Denne sorterer top 5 nyeste arrangementer
                                $hentArrangement = "select idevent, eventnavn, eventtekst, tidspunkt from event order by tidspunkt ASC LIMIT 5";
                                $hentArrangementSTMT = $db->prepare($hentArrangement);
                                $hentArrangementSTMT->execute();
                                $arrangementer = $hentArrangementSTMT->fetchAll(PDO::FETCH_ASSOC);
                                ?>
                                
                                <?php for($i = 0; $i < count($arrangementer); $i++) { ?>
                                    <section id="default_arrangementFelt">
                                        <h3 class="KommendeArrangementOverskrift"><?php echo $arrangementer[$i]['eventnavn'] ?> </h3>
                                        <img class="KommendeArrangement_datobilde" src="bilder/datoIkon.png">
                                        <p class="KommendeArrangementTidspunkt">
                                            <?php 
                                                $dato = date_create($arrangementer[$i]['tidspunkt']);
                                                echo(date_format($dato,"d/m/Y"));
                                            ?>
                                        </p>
                                        <p class="KommendeArrangementTekst"><?php echo (substr($arrangementer[$i]['eventtekst'],0,150)) ?> </p>                             
                                        
                                        <a href="arrangement.php?arrangement=<?php echo($arrangementer[$i]['idevent'])?>">...Les videre</a>
                                    </section>
                                <?php } ?>
                        </section>
                    </article>
                <?php } ?>
            </section>
        </main>
        <?php include("inkluderes/default_footer.php") ?>
    </body>

    <!-- Denne siden er utviklet av Ajdin Bajrovic & Robin Kleppang, siste gang endret 06.03.2020 -->
    <!-- Denne siden er kontrollert av Aron Snekkestad, siste gang 06.03.2020 -->

</html>