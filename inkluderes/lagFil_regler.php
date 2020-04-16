<!-- -->
<?php
if(!file_exists("../regler.html")) {
    $hentRegler = "select regeltekst from Regel";
    $stmtRegler = $db->prepare($hentRegler);
    $stmtRegler->execute();
    $tellingRegler = $stmtRegler->rowcount();
    $regler = $stmtRegler->fetchAll(PDO::FETCH_ASSOC);

    $handle = fopen("regler.html",'w');

    //Start-tag'er for html-dokumentet og head
    fwrite($handle,"<!DOCTYPE html>
                    <html>
                        <head>
                            <meta charset='UTF-8'>
                            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                            <link rel='icon' href='bilder/favicon.png' type='image/x-icon'>
                            <script language='JavaScript' src='javascript.js'> </script>
                            <link rel='stylesheet' type='text/css' href='stylesheet.css'>
                            
                            <title>Tatt imot!</title>
                        </head>");
    //Body og main start-tag
    fwrite($handle,"<body>
                    <main class='regler_innhold'>");

    //Navmeny-bar og hamburgermeny
    fwrite($handle,"<nav class='navTop'>
                        <a class='bildeKontroll' href='javascript:void(0)' onclick='hamburgerMeny()' tabindex='6'>
                            <img src='bilder/hamburgerIkon.svg' alt='Hamburger-menyen' class='hamburgerKnapp'>
                        </a>");

    //profilbilde i navmenyen
    // Test'er på ulike brukere
    if ($antallBilderFunnet != 0) {
    // Hvis vi finner et bilde til bruker viser vi det 
    fwrite($handle, "<a class='bildeKontroll' href='javascript:void(0)' onClick=location.href='profil.php?bruker=" . $_SESSION['idbruker'] . "' tabindex='5'>");
            
            $testPaa = $bilde['hvor'];
            // Tester på om filen faktisk finnes
            if(file_exists("$lagringsplass/$testPaa")) {   
                if(file_exists("$lagringsplass/" . "thumb_" . $testPaa)) {
                    if ($_SESSION['brukertype'] == 2) {
                        // Setter redaktør border "Grønn" 
                    fwrite($handle,"<img src='bilder/opplastet/thumb_" . $bilde['hvor'] . "' alt='Profilbilde'  class='profil_navmeny' style='border: 2px solid green;'>");
                    } else if ($_SESSION['brukertype'] == 1) {
                        // Setter administrator border "Rød" 
                    fwrite($handle,"<img src='bilder/opplastet/thumb_" . $bilde['hvor'] . "' alt='Profilbilde'  class='profil_navmeny' style='border: 2px solid red;'>");
                    } else if ($_SESSION['brukertype'] == 3) {
                        // Setter vanlig profil bilde  
                    fwrite($handle,"<img src='bilder/opplastet/thumb_" . $bilde['hvor'] . "' alt='Profilbilde'  class='profil_navmeny'>"); 
                    }
                } else { 
                    if ($_SESSION['brukertype'] == 2) {
                        // Setter redaktør border "Grønn" 
                        fwrite($handle,"<img src='bilder/opplastet/" . $bilde['hvor'] . "' alt='Profilbilde'  class='profil_navmeny' style='border: 2px solid green;'>");
                    } else if ($_SESSION['brukertype'] == 1) {
                        // Setter administrator border "Rød" 
                        fwrite($handle,"<img src='bilder/opplastet/" . $bilde['hvor'] . "' alt='Profilbilde'  class='profil_navmeny' style='border: 2px solid red;'>");
                    } else if ($_SESSION['brukertype'] == 3) {
                        // Setter vanlig profil bilde  
                        fwrite($handle,"<img src='bilder/opplastet/" . $bilde['hvor'] . "' alt='Profilbilde'  class='profil_navmeny'>"); 
                    }
                }
            } else { 
                // Om filen ikke ble funnet, vis standard profilbilde
                if ($_SESSION['brukertype'] == 2) {
                    // Setter redaktør border "Grønn" 
                    fwrite($handle,"<img src='bilder/profil.png' alt='Profilbilde' class='profil_navmeny' style='border: 2px solid green;'>");
                } else if ($_SESSION['brukertype'] == 1) {
                    // Setter administrator border "Rød" 
                    fwrite($handle,"<img src='bilder/profil.png' alt='Profilbilde' class='profil_navmeny' style='border: 2px solid red;'>"); 
                } else if ($_SESSION['brukertype'] != 1 || 2) {
                    // Setter vanlig profil bilde 
                    fwrite($handle,"<img src='bilder/profil.png' alt='Profilbilde' class='profil_navmeny'>");}
            } 
    fwrite($handle,"</a>");
    } else { 
        fwrite($handle, "<a class='bildeKontroll' href='javascript:void(0)' onClick=location.href='profil.php?bruker=" . $_SESSION['idbruker'] . "' tabindex='5'>");
        if ($_SESSION['brukertype'] == 2) {
                // Setter redaktør border "Grønn"
                fwrite($handle,"<img src='bilder/profil.png' alt='Profilbilde' class='profil_navmeny' style='border: 2px solid green;'>");

            } else if ($_SESSION['brukertype'] == 1) {
                // Setter administrator border "Rød" 
                fwrite($handle,"<img src='bilder/profil.png' alt='Profilbilde' class='profil_navmeny' style='border: 2px solid red;'>");

            } else if ($_SESSION['brukertype'] != 1 || 2) {
                // Setter vanlig profil bilde
                fwrite($handle,"<img src='bilder/profil.png' alt='Profilbilde' class='profil_navmeny'>");
            } 
    fwrite($handle,"</a>");                    
    }
    //Logg ut knapp
    fwrite($handle,"<form id='loggUtKnappForm' method='POST' action='default.php'>
                        <button name='loggUt' id='registrerKnapp' tabindex='4'>LOGG UT</button>
                    </form>");

    fwrite($handle, "<form id='sokForm_regler' action='sok.php'>
                        <input id='sokBtn_navmeny' type='submit' value='' tabindex='3'>
                        <input id='sokInp_navmeny' type='text' name='artTittel' placeholder='Søk på artikkel' tabindex='2'>
                    </form>

                    <a href='javascript:void(0)' onClick=location.href='sok.php' tabindex='-1'>
                        <img src='bilder/sokIkon.png' alt='Søkeikon' class='sok_navmeny' tabindex='2'>
                    </a>");

    //Klimate-logo'en
    fwrite($handle," <a href='default.php' tabindex='1'>
                            <img class='Logo_navmeny' src='bilder/klimateNoText.png' alt='Klimate logo'>
                        </a>
                    </nav>");

    //Rullegardin, tester på om brukeren er admin
    if ($_SESSION['brukertype'] == 1) { 
    fwrite($handle,"<section id='navMeny' class='hamburgerMeny' onclick='lukkHamburgerMeny()'>
                        <section class='avbrySeksjon'>
                            <img class='xikon' src='bilder/xikon.png'>   
                        </section>
                            <section class='hamburgerInnhold'>
                            <a class = 'menytab' tabIndex = '-1' href='administrator.php' style='margin-bottom: 0.2em; font-weight: bold;'>" . $brukertypenavn['brukertypenavn'] . " innstillinger</a>
                            <a class = 'menytab' tabIndex = '-1' href='backend.php'>Min oversikt</a>
                            <a class = 'menytab' tabIndex = '-1' href='arrangement.php'>Arrangementer</a>
                            <a class = 'menytab' tabIndex = '-1' href='artikkel.php'>Artikler</a>
                            <a class = 'menytab' tabIndex = '-1' href='meldinger.php'>Innboks</a>
                            <a class = 'menytab' tabIndex = '-1' href='profil.php?bruker=" . $_SESSION['idbruker'] . "'>Profil</a>
                            <a class = 'menytab' tabIndex = '-1' href='konto.php'>Konto</a>
                            <a class = 'menytab' tabIndex = '-1' href='sok.php'>Avansert Søk</a>
                            <a class = 'menytab' tabIndex = '-1' href='regler.html'>Regler</a>
                            </section>
                    </section>");

    //Rullegardin
    } else {
    fwrite($handle,"<section id='navMeny' class='hamburgerMeny' onclick='lukkHamburgerMeny()'>
                        <section class='avbrySeksjon'>
                            <img class='xikon' src='bilder/xikon.png'>   
                        </section>
                            <section class='hamburgerInnhold'>
                            <a class = 'menytab' tabIndex = '-1' href='backend.php'>Min oversikt</a>
                            <a class = 'menytab' tabIndex = '-1' href='arrangement.php'>Arrangementer</a>
                            <a class = 'menytab' tabIndex = '-1' href='artikkel.php'>Artikler</a>
                            <a class = 'menytab' tabIndex = '-1' href='meldinger.php'>Innboks</a>
                            <a class = 'menytab' tabIndex = '-1' href='profil.php?bruker=" . $_SESSION['idbruker'] . "'>Profil</a>
                            <a class = 'menytab' tabIndex = '-1' href='konto.php'>Konto</a>
                            <a class = 'menytab' tabIndex = '-1' href='sok.php'>Avansert Søk</a>
                            </section>
                    </section>");
    }

    //listedata med regler og innholdsboksen 
    fwrite($handle,"<header class='backend_header' onclick='lukkHamburgerMeny()'>
                        <h1 class='velkomst'>Regler</h1>
                    </header>

                    <article id='regler_main'>
                    <ol class='reglerdata'>");

    foreach($regler as $rad) {           
        fwrite($handle,"
                    <li>" . $rad['regeltekst'] . "</li>");
    }   

    //Footer og slutt-taggger
    fwrite($handle,"</ol>
                    </article>
                    </main>
                
                    <footer>
                        <p class=footer_beskrivelse>&copy; Klimate " . date("Y") . " | <a href='mailto:kontakt@klimate.no'>Kontakt oss</a>");
    if (isset($_SESSION['idbruker']) and $_SESSION['brukertype'] == "3") {  
        fwrite($handle,"| <a href='soknad.php'>Søknad om å bli redaktør</a>");
    }

    fwrite($handle,"</p>
                    </footer>
                    </body>
                    </html>");
    fclose($handle);
}
?>