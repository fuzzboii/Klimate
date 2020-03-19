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
                <?php if(!isset($_GET['systemerror'])){ ?>
                    <!-- IDene brukes til å splitte opp kolonnene i queries -->
                    <article>
                        <h2>Nyeste</h2>
                        <p><?php 
                            //------------------------------//
                            // Henter artikler fra database //
                            //------------------------------//

                            // Henter artikler fra database, sorterer på tid og viser denne
                            $hentNyesteQ = "select idartikkel, artnavn, tid from artikkel order by tid DESC limit 1";
                            $hentNyesteSTMT = $db->prepare($hentNyesteQ);
                            $hentNyesteSTMT->execute();
                            $nyesteArtikkel = $hentNyesteSTMT->fetch(PDO::FETCH_ASSOC); 
                        
                        echo($nyesteArtikkel['artnavn'])?></p>
                        
                        <a href="artikkel.php?artikkel=<?php echo($nyesteArtikkel['idartikkel'])?>">Trykk her for å lese videre</a>
                    </article>
                    <article>
                        <h2>Mest kommentert</h2>
                        <!-- Dette vil da være resultat av en spørring mot database, bruk av echo for å vise -->
                        <p><?php 
                            //--------------------------------------//
                            // Henter mest kommenterte fra database //
                            //--------------------------------------//

                            // Henter artikler fra database, sorterer på høyeste antall og viser denne
                            $mestKommenterteQ = "select count(idkommentar) as antall, idartikkel, artnavn from kommentar, artikkel
                                                where kommentar.artikkel = artikkel.idartikkel
                                                group by idartikkel
                                                order by antall DESC limit 1";
                            $mestKommenterteSTMT = $db->prepare($mestKommenterteQ);
                            $mestKommenterteSTMT->execute();
                            $mestKommenterte = $mestKommenterteSTMT->fetch(PDO::FETCH_ASSOC);
                        
                        echo($mestKommenterte['artnavn'])?></p>
                        <a href="artikkel.php?artikkel=<?php echo($mestKommenterte['idartikkel'])?>">Trykk her for å lese videre</a>
                    </article>
                    <article>
                        <h2>Tilfeldig utvalgt</h2>
                        <p><?php 
                            //------------------------------//
                            // Henter artikler fra database //
                            //------------------------------//

                            // Denne sorterer tilfeldig og begrenser resultatet til en artikkel
                            $hentTilfeldig = "select idartikkel, artnavn from artikkel order by RAND() limit 1";
                            $stmtTilfeldig = $db->prepare($hentTilfeldig);
                            $stmtTilfeldig->execute();
                            $tilfeldigArtikkel = $stmtTilfeldig->fetch(PDO::FETCH_ASSOC); 
                        
                        echo($tilfeldigArtikkel['artnavn'])?></p>
                        
                        <a href="artikkel.php?artikkel=<?php echo($tilfeldigArtikkel['idartikkel'])?>">Trykk her for å lese videre</a>
                    </article>
                <?php } ?>
            </section>
        </main>
        <?php include("inkluderes/default_footer.php") ?>
    </body>

    <!-- Denne siden er utviklet av Ajdin Bajrovic & Robin Kleppang, siste gang endret 06.03.2020 -->
    <!-- Denne siden er kontrollert av Aron Snekkestad, siste gang 06.03.2020 -->

</html>