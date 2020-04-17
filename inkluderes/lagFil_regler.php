<?php
if(!file_exists("./generert/regler.html") && isset($_SESSION['idbruker'])) {
    $hentRegler = "select regeltekst from Regel";
    $stmtRegler = $db->prepare($hentRegler);
    $stmtRegler->execute();
    $tellingRegler = $stmtRegler->rowcount();
    $regler = $stmtRegler->fetchAll(PDO::FETCH_ASSOC);

    $handle = fopen("./generert/regler.html",'w');

    //Start-tag'er for html-dokumentet og head
    fwrite($handle,"<!DOCTYPE html>\n<html>\n\t<head>" .
                        "\n\t\t<meta charset='UTF-8'>" .
                        "\n\t\t<meta name='viewport' content='width=device-width, initial-scale=1.0'>" .
                        "\n\t\t<link rel='icon' href='../bilder/favicon.png' type='image/x-icon'>" . 
                        "\n\t\t<script language='JavaScript' src='../javascript.js'> </script>" .
                        "\n\t\t<link rel='stylesheet' type='text/css' href='../stylesheet.css'>" .
                        "\n\t\t<title>Regler</title>" .
                    "\n\t</head>");
    //Body og main start-tag
    fwrite($handle,"\n\t<body id='regler_body'>");

    //Navmeny-bar og hamburgermeny
    fwrite($handle,"\n\t\t<nav class='navTop'>\n\t\t\t<a class='bildeKontroll' href='javascript:void(0)' onclick='hamburgerMeny()' tabindex='6'>" .
                    "\n\t\t\t\t<img src='../bilder/hamburgerIkon.svg' alt='Hamburger-menyen' class='hamburgerKnapp'>\n\t\t\t</a>");

    // Henter bilde fra database utifra brukerid
    $hentBildeQ = "select hvor from bilder, brukerbilde where brukerbilde.bruker = " . $_SESSION['idbruker'] . " and brukerbilde.bilde = bilder.idbilder";
    $hentBildeSTMT = $db->prepare($hentBildeQ);
    $hentBildeSTMT->execute();
    $bilde = $hentBildeSTMT->fetch(PDO::FETCH_ASSOC);
    $antallBilderFunnet = $hentBildeSTMT->rowCount();
    // Test'er på ulike brukere
    if ($antallBilderFunnet != 0) {
        // Hvis vi finner et bilde til bruker viser vi det 
        fwrite($handle, "\n\t\t\t<a class='bildeKontroll' href='javascript:void(0)' onClick=location.href='../profil.php?bruker=" . $_SESSION['idbruker'] . "' tabindex='5'>");
        $testPaa = $bilde['hvor'];
        // Tester på om filen faktisk finnes
        if(file_exists("./" . "$lagringsplass/$testPaa")) {   
            if(file_exists("./" . "$lagringsplass/" . "thumb_" . $testPaa)) {
                if ($_SESSION['brukertype'] == 2) {
                    // Setter redaktør border "Grønn" 
                fwrite($handle,"\n\t\t\t\t<img src='../bilder/opplastet/thumb_" . $bilde['hvor'] . "' alt='Profilbilde'  class='profil_navmeny' style='border: 2px solid green;'>");
                } else if ($_SESSION['brukertype'] == 1) {
                    // Setter administrator border "Rød" 
                fwrite($handle,"\n\t\t\t\t<img src='../bilder/opplastet/thumb_" . $bilde['hvor'] . "' alt='Profilbilde'  class='profil_navmeny' style='border: 2px solid red;'>");
                } else if ($_SESSION['brukertype'] == 3) {
                    // Setter vanlig profil bilde  
                fwrite($handle,"\n\t\t\t\t<img src='../bilder/opplastet/thumb_" . $bilde['hvor'] . "' alt='Profilbilde'  class='profil_navmeny'>"); 
                }
            } else { 
                if ($_SESSION['brukertype'] == 2) {
                    // Setter redaktør border "Grønn" 
                    fwrite($handle,"\n\t\t\t\t<img src='../bilder/opplastet/" . $bilde['hvor'] . "' alt='Profilbilde'  class='profil_navmeny' style='border: 2px solid green;'>");
                } else if ($_SESSION['brukertype'] == 1) {
                    // Setter administrator border "Rød" 
                    fwrite($handle,"\n\t\t\t\t<img src='../bilder/opplastet/" . $bilde['hvor'] . "' alt='Profilbilde'  class='profil_navmeny' style='border: 2px solid red;'>");
                } else if ($_SESSION['brukertype'] == 3) {
                    // Setter vanlig profil bilde  
                    fwrite($handle,"\n\t\t\t\t<img src='../bilder/opplastet/" . $bilde['hvor'] . "' alt='Profilbilde'  class='profil_navmeny'>"); 
                }
            }
        } else { 
            // Om filen ikke ble funnet, vis standard profilbilde
            if ($_SESSION['brukertype'] == 2) {
                // Setter redaktør border "Grønn" 
                fwrite($handle,"\n\t\t\t\t<img src='../bilder/profil.png' alt='Profilbilde' class='profil_navmeny' style='border: 2px solid green;'>");
            } else if ($_SESSION['brukertype'] == 1) {
                // Setter administrator border "Rød" 
                fwrite($handle,"\n\t\t\t\t<img src='../bilder/profil.png' alt='Profilbilde' class='profil_navmeny' style='border: 2px solid red;'>"); 
            } else if ($_SESSION['brukertype'] != 1 || 2) {
                // Setter vanlig profil bilde 
                fwrite($handle,"\n\t\t\t\t<img src='../bilder/profil.png' alt='Profilbilde' class='profil_navmeny'>");}
        } 
        fwrite($handle,"\n\t\t\t</a>");
    } else { 
        fwrite($handle, "\n\t\t\t<a class='bildeKontroll' href='javascript:void(0)' onClick=location.href='../profil.php?bruker=" . $_SESSION['idbruker'] . "' tabindex='5'>");
        if ($_SESSION['brukertype'] == 2) {
            // Setter redaktør border "Grønn"
            fwrite($handle,"\n\t\t\t\t<img src='../bilder/profil.png' alt='Profilbilde' class='profil_navmeny' style='border: 2px solid green;'>");

        } else if ($_SESSION['brukertype'] == 1) {
            // Setter administrator border "Rød" 
            fwrite($handle,"\n\t\t\t\t<img src='../bilder/profil.png' alt='Profilbilde' class='profil_navmeny' style='border: 2px solid red;'>");

        } else if ($_SESSION['brukertype'] != 1 || 2) {
            // Setter vanlig profil bilde
            fwrite($handle,"\n\t\t\t\t<img src='../bilder/profil.png' alt='Profilbilde' class='profil_navmeny'>");
        } 
        fwrite($handle,"\n\t\t\t</a>");
    }
    //Logg ut knapp
    fwrite($handle,"\n\t\t\t<form id='loggUtKnappForm' method='POST' action='../default.php'>" . 
                        "\n\t\t\t\t<button name='loggUt' id='registrerKnapp' tabindex='4'>LOGG UT</button>" . 
                    "\n\t\t\t</form>");

    fwrite($handle, "\n\t\t\t<form id='sokForm_regler' action='../sok.php'>" . 
                        "\n\t\t\t\t<input id='sokBtn_navmeny' type='submit' value='' tabindex='3'>" .
                        "\n\t\t\t\t<input id='sokInp_navmeny' type='text' name='artTittel' placeholder='Søk på artikkel' tabindex='2'>" . 
                    "\n\t\t\t</form>" . 

                    "\n\t\t\t<a href='javascript:void(0)' onClick=location.href='sok.php' tabindex='-1'>" . 
                        "\n\t\t\t\t<img src='../bilder/sokIkon.png' alt='Søkeikon' class='sok_navmeny' tabindex='2'>" . 
                    "\n\t\t\t</a>");

    //Klimate-logo'en
    fwrite($handle,"\n\t\t\t<a href='../default.php' tabindex='1'>" . 
                        "\n\t\t\t\t<img class='Logo_navmeny' src='../bilder/klimateNoText.png' alt='Klimate logo'>" . 
                    "\n\t\t\t</a>" .
                    "\n\t\t</nav>\n\t\t<main onclick='lukkHamburgerMeny()'>");

                    
    //Rullegardin, tester på om brukeren er admin
    if ($_SESSION['brukertype'] == 1) { 
    fwrite($handle,"\n\t\t\t<section id='navMeny' class='hamburgerMeny' onclick='lukkHamburgerMeny()'>" .
                        "\n\t\t\t\t<section class='avbrySeksjon'>" .
                            "\n\t\t\t\t\t<img class='xikon' src='../bilder/xikon.png'>" . 
                        "\n\t\t\t\t</section>" . 
                        "\n\t\t\t\t<section class='hamburgerInnhold'>" .
                            "\n\t\t\t\t\t<a class = 'menytab' tabIndex = '-1' href='../administrator.php' style='margin-bottom: 0.2em; font-weight: bold;'>" . $brukertypenavn['brukertypenavn'] . " innstillinger</a>" . 
                            "\n\t\t\t\t\t<a class = 'menytab' tabIndex = '-1' href='../backend.php'>Min oversikt</a>" .
                            "\n\t\t\t\t\t<a class = 'menytab' tabIndex = '-1' href='../arrangement.php'>Arrangementer</a>" .
                            "\n\t\t\t\t\t<a class = 'menytab' tabIndex = '-1' href='../artikkel.php'>Artikler</a>" .
                            "\n\t\t\t\t\t<a class = 'menytab' tabIndex = '-1' href='../meldinger.php'>Innboks</a>" .
                            "\n\t\t\t\t\t<a class = 'menytab' tabIndex = '-1' href='../profil.php?bruker=" . $_SESSION['idbruker'] . "'>Profil</a>" .
                            "\n\t\t\t\t\t<a class = 'menytab' tabIndex = '-1' href='../konto.php'>Konto</a>" .
                            "\n\t\t\t\t\t<a class = 'menytab' tabIndex = '-1' href='../sok.php'>Avansert Søk</a>" .
                            "\n\t\t\t\t\t<a class = 'menytab' tabIndex = '-1' href='regler.html'>Regler</a>" .
                        "\n\t\t\t\t</section>" .
                    "\n\t\t\t</section>");

    //Rullegardin
    } else {
    fwrite($handle,"\n\t\t\t<section id='navMeny' class='hamburgerMeny' onclick='lukkHamburgerMeny()'>" .
                        "\n\t\t\t\t<section class='avbrySeksjon'>" .
                            "\n\t\t\t\t\t<img class='xikon' src='../bilder/xikon.png'>" .
                        "\n\t\t\t\t</section>" .
                        "\n\t\t\t\t<section class='hamburgerInnhold'>" .
                            "\n\t\t\t\t\t<a class = 'menytab' tabIndex = '-1' href='../backend.php'>Min oversikt</a>" .
                            "\n\t\t\t\t\t<a class = 'menytab' tabIndex = '-1' href='../arrangement.php'>Arrangementer</a>" .
                            "\n\t\t\t\t\t<a class = 'menytab' tabIndex = '-1' href='../artikkel.php'>Artikler</a>" .
                            "\n\t\t\t\t\t<a class = 'menytab' tabIndex = '-1' href='../meldinger.php'>Innboks</a>" .
                            "\n\t\t\t\t\t<a class = 'menytab' tabIndex = '-1' href='../profil.php?bruker=" . $_SESSION['idbruker'] . "'>Profil</a>" .
                            "\n\t\t\t\t\t<a class = 'menytab' tabIndex = '-1' href='../konto.php'>Konto</a>" .
                            "\n\t\t\t\t\t<a class = 'menytab' tabIndex = '-1' href='../sok.php'>Avansert Søk</a>" .
                        "\n\t\t\t\t</section>" .
                    "\n\t\t\t</section>");
    }

    //listedata med regler og innholdsboksen 
    fwrite($handle,"\n\t\t\t<header class='backend_header' onclick='lukkHamburgerMeny()'>" .
                        "\n\t\t\t\t<h1 class='velkomst'>Regler</h1>" .
                    "\n\t\t\t</header>" .
                    "\n\t\t\t<article id='regler_article'>" .
                        "\n\t\t\t\t<p>Som bruker hos Klimate må du følge reglene spesifisert under.</p>" .
                        "\n\t\t\t\t<ol class='reglerdata'>");

    foreach($regler as $rad) {           
        fwrite($handle,"\n\t\t\t\t\t<li>" . $rad['regeltekst'] . "</li>");
    }   

    //Footer og slutt-taggger
    fwrite($handle,"\n\t\t\t\t</ol>\n\t\t\t</article>\n\t\t</main>" .
                    "\n\t\t<button onclick='tilbakeTilTopp()' id='toppKnapp' title='Toppen'><img src='../bilder/pilopp.png' alt='Tilbake til toppen'></button>" .
                    "\n\t\t<footer>" .
                        "\n\t\t\t<p class=footer_beskrivelse>&copy; Klimate " . date("Y") . " | <a href='mailto:kontakt@klimate.no'>Kontakt oss</a>");
    if (isset($_SESSION['idbruker']) and $_SESSION['brukertype'] == "3") {  
        fwrite($handle,"| <a href='../soknad.php'>Søknad om å bli redaktør</a>");
    }

    fwrite($handle,"</p>" .
                    "\n\t\t</footer>" .
                    "\n\t</body>" .
                    "\n</html>");
    fclose($handle);
}
?>