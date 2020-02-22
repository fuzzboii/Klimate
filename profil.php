<?php
session_start();

//------------------------------//
// Innstillinger, faste variable //
//------------------------------//
include("innstillinger.php");

//------------------------------//
// Test om man ser egen profil  //
//------------------------------//

if (isset($_SESSION['idbruker']) && $_SESSION['idbruker'] == $_GET['bruker']) {
    $egen = true;
} else {
    $egen = false;
}

if (!isset($_GET['bruker'])) {
    if (!isset($_SESSION['idbruker'])) {
        header("location: sok.php?melding=1");
    } else {
        header("location: profil.php?bruker=" . $_SESSION['idbruker']);
    }
}

// -------------------- //
// Oppdater profilbilde //
// -------------------- //
if (isset($_POST['endreBilde'])) {
    // Del for filopplastning
    if (is_uploaded_file($_FILES['bilde']['tmp_name'])) {
        // Kombinerer bruker med idbruker
        $navn = "bruker" . $_SESSION['idbruker'];
        // Henter filtypen
        $filtype = "." . substr($_FILES['bilde']['type'], 6, 4);
        // Kombinerer navnet med filtypen
        $bildenavn = $navn . $filtype;
        // Opprettet filnavnet for bildet som skal slettes, setter denne til tom streng hvis ikke bruker har profilbilde fra før
        $bildenavnGammelt = "";
        // Selve prosessen som flytter bildet til bestemt lagringsplass
        // Test om det finnes en fil med samme navn
        // Opprett navn med de 3 ulike filtypene
        $navnjpg = $navn . ".jpg";
        $navnjpeg = $navn . ".jpeg";
        $navnpng = $navn . ".png";
        // Test på .jpg
        if(file_exists("$lagringsplass/$navnjpg")) {
            // Dropp i så fall
            unlink("$lagringsplass/$navnjpg");
            $bildenavnGammelt = $navn . "jpg";
        } elseif(file_exists("$lagringsplass/$navnjpeg")) { // ... .jpeg
            // Dropp i så fall
            unlink("$lagringsplass/$navnjpeg");
            $bildenavnGammelt = $navn . "jpeg";
        } elseif (file_exists("$lagringsplass/$navnpng")) { // ... .png
            // Dropp i så fall
            unlink("$lagringsplass/$navnpng");
            $bildenavnGammelt = $navn . "png";
        }
        // Last opp
        move_uploaded_file($_FILES['bilde']['tmp_name'], "$lagringsplass/$bildenavn");

        
        // Test om brukeren har et bilde fra før
        $hentBilde = "select hvor from bilder, brukerbilde where brukerbilde.bruker = " . $_SESSION['idbruker'] . " and brukerbilde.bilde = bilder.idbilder";
        $stmtBilde = $db->prepare($hentBilde);
        $stmtBilde->execute();
        $bilde = $stmtBilde->fetch(PDO::FETCH_ASSOC);
        $antallBilderFunnet = $stmtBilde->rowCount();
        // rowCount() returnerer antall resultater fra database, er dette null finnes det ikke noe bilde i databasen
        if ($antallBilderFunnet != 0) {
            // Hvis brukeren har et bilde fra før:
            if($bildenavnGammelt != "") {
                // Slett det gamle bildet fra databasen først
                $slettBilde = "delete from bilder where hvor='" . $bildenavnGammelt . "'";
                $stmtSlettBilde = $db->prepare($slettBilde);
                $stmtSlettBilde->execute();
            }
            // Legger til bildet i databasen
            $nyttBildeQ = "insert into bilder(hvor) values('" . $bildenavn . "')";
            $nyttBildeSTMT = $db->prepare($nyttBildeQ);
            $nyttBildeSTMT->execute();
            // Returnerer siste bildeid. Last insert id svarer til bildet vi håndterer
            $bildeid = $db->lastInsertId();
            // Brukerbilde må oppdateres, på raden til brukeren
            $nyKoblingQ = "update brukerbilde set bilde='" . $bildeid . "' where bruker='" . $_SESSION['idbruker'] . "'";
            $nyKoblingSTMT = $db->prepare($nyKoblingQ);
            $nyKoblingSTMT->execute();  
        } else {
            // Hvis brukeren ikke har et tilknyttet bilde:
            // Legger til bildet i databasen
            $nyttBildeQ = "insert into bilder(hvor) values('" . $bildenavn . "')";
            $nyttBildeSTMT = $db->prepare($nyttBildeQ);
            $nyttBildeSTMT->execute();
            // Returnerer siste bildeid
            $bildeid = $db->lastInsertId();
            // Lag en kobling mellom bilder og brukerbilde
            $nyKoblingQ = "insert into brukerbilde(bruker, bilde) values('" . $_SESSION['idbruker'] . "', '" . $bildeid . "')";
            $nyKoblingSTMT = $db->prepare($nyKoblingQ);
            $nyKoblingSTMT->execute();
        }
    }
}


 //-----------------------------//
 // Oppdaterer egen beskrivelse //
 //-----------------------------//
 // Litt usikker på om vi trenger den første if-testen (Muligheten skal uansett bare være tilstede for å oppdatere når $egen er sann), 
 // men heller for mye integritet enn for lite
 if ($egen) {
    if (isset($_POST['beskrivelse'])) {
        $oppdaterBeskrivelse = "update bruker set beskrivelse = '" . $_POST['beskrivelse'] . "' where idbruker = " . $_SESSION['idbruker'];
        $stmtOppdaterBeskrivelse = $db->prepare($oppdaterBeskrivelse);
        $stmtOppdaterBeskrivelse->execute();
    }
 }

//-----------------------------------------------------//
// Oppdater brukerinteresse fra forhåndsdefinert liste //
//-----------------------------------------------------//
if ($egen) {
    if (isset($_POST['interesse'])) {
        $brukerPlaceholder = $_SESSION['idbruker'];
        $interessePlaceholder = $_POST['interesse'];
        $oppdaterBrukerinteresse = "insert into brukerinteresse(bruker, interesse) values(?, ?)";
        $stmtOppdaterBrukerinteresse = $db->prepare($oppdaterBrukerinteresse);
        $stmtOppdaterBrukerinteresse->execute([$brukerPlaceholder, $interessePlaceholder]);
    }
}
//-----------------------------------------------//
// Oppdater interesse med egendefinert interesse //
//-----------------------------------------------//
if ($egen) {
    if (isset($_POST['interesseEgendefinert'])) {
        // Kontroller at interessen også er unik sammenlignet i lower case
        // Hent alle navnene fra interesse
        $sammenligning = "select lower(interessenavn) as interessenavn from interesse";
        $stmtSammenligning = $db->prepare($sammenligning);
        $stmtSammenligning->execute();
        $interesseSammenlign = $stmtSammenligning->fetchAll(PDO::FETCH_ASSOC);

        // Lower case egendefinert interesse til sammenligning
        $egendefinertLower = strtolower($_POST['interesseEgendefinert']);

        // Sammenlign hvert navn
        foreach($interesseSammenlign as $e) {
            foreach($e as $navn) {
                if($egendefinertLower == $navn) {
                    // Opprett en variabel som tilsiser en match
                    $funnet = true;
                }
            }
        } // Slutt, foreach

        // Oppdater database hvis ingen match ble funnet
        if(!isset($funnet)) {
            // Oppdater interesse
            // ucwords endrer første bokstav (i hvert ord, evt.) til upper case
            $interessePlaceholder = ucwords($egendefinertLower);
            $brukerPlaceholder = $_SESSION['idbruker'];
            $oppdaterInteresse = "insert into interesse(interessenavn) values(?)";
            $stmtOppdaterInteresse = $db->prepare($oppdaterInteresse);
            $stmtOppdaterInteresse->execute([$interessePlaceholder]);

            // Hent id til ny interesse fra interesse
            $hentIdInteresse = "select idinteresse from interesse where interessenavn=?";
            $stmtHentIdInteresse = $db->prepare($hentIdInteresse);
            $stmtHentIdInteresse->execute([$_POST['interesseEgendefinert']]);
            $idInteresse = $stmtHentIdInteresse->fetch(PDO::FETCH_ASSOC);

            // Oppdater så brukerinteresse med denne verdien
            $brukerPlaceholder = $_SESSION['idbruker'];
            $interessePlaceholder = $idInteresse['idinteresse'];
            $oppdaterBrukerinteresse = "insert into brukerinteresse(bruker, interesse) values(?, ?)";
            $stmtOppdaterBrukerinteresse = $db->prepare($oppdaterBrukerinteresse);
            $stmtOppdaterBrukerinteresse->execute([$brukerPlaceholder, $interessePlaceholder]);
        } else {
            // Ellers viser vi en feilmelding
            header('Location: profil.php?bruker=' . $_SESSION['idbruker'] . '&innstillinger&error=1');
        }
    }
}

//-----------------//
// Slett Interesse //
//-----------------//
if ($egen) {
    if (isset($_POST['interesseTilSletting'])) {
        $hentetInteresse = substr($_POST['interesseTilSletting'], 6);
        // Hent tilsvarende ID
        $hentIdInteresse = "select idinteresse from interesse where interessenavn= '" . $hentetInteresse . "'";
        $stmtHentIdInteresse = $db->prepare($hentIdInteresse);
        $stmtHentIdInteresse->execute();
        $idInteresse = $stmtHentIdInteresse->fetch(PDO::FETCH_ASSOC);

        // Slett interessen
        $slettInteresse = "delete from brukerinteresse 
                           where bruker=?
                           and interesse=?";
        $stmtSlettInteresse = $db->prepare($slettInteresse);
        $stmtSlettInteresse->execute([$_SESSION['idbruker'], $idInteresse['idinteresse']]);

    }
}

//------------------------------//
//------------------------------//
// Henting av data på bruker    //
//------------------------------//
//------------------------------//
//-----------------------//
// Henting av brukernavn //
//-----------------------//
$hentBrukernavnProfil = "select brukernavn from bruker where idbruker = " . $_GET['bruker'];
$stmtBrukernavnProfil = $db->prepare($hentBrukernavnProfil);
$stmtBrukernavnProfil->execute();
$brukernavnProfil = $stmtBrukernavnProfil->fetch(PDO::FETCH_ASSOC);

//---------------------------------------------------------------//
// Henting av navn/tlf/mail, avhengig av brukerens innstillinger //
//---------------------------------------------------------------//
$hentPersonaliaProfil = "Select fnavn, enavn, epost, telefonnummer from bruker where idbruker = " . $_GET['bruker'];
$stmtPersonaliaProfil = $db->prepare($hentPersonaliaProfil);
$stmtPersonaliaProfil->execute();
$personaliaProfil = $stmtPersonaliaProfil->fetch(PDO::FETCH_ASSOC);

//-----------------------//
// Henting av interesser //
//-----------------------//
$hentInteresseProfil = "select interessenavn from interesse, brukerinteresse where brukerinteresse.bruker = "
                        . $_GET['bruker'] . " and brukerinteresse.interesse=interesse.idinteresse;";
$stmtInteresseProfil = $db->prepare($hentInteresseProfil);
$stmtInteresseProfil->execute();
$tellingInteresse = $stmtInteresseProfil->rowcount();

// Test på resultat
if ($tellingInteresse > 0) {
    // Hvis sant, har vi bekreftet at vi har noe å vise med echo
    // Resulterer i et 2D array
    $interesseProfil = $stmtInteresseProfil->fetchAll(PDO::FETCH_ASSOC);
// settes ellers til null, for øyeblikket
} else $interesseProfil = "Brukeren har ikke oppgitt noen interesser";

//----------------------------------------------//
// Hent alle interesser fra db, til en <select> //
//----------------------------------------------//
$hentInteresse = "select idinteresse, interessenavn from interesse";
$stmtHentInteresse = $db->prepare($hentInteresse);
$stmtHentInteresse->execute();
$interesse = $stmtHentInteresse->fetchAll(PDO::FETCH_ASSOC);

//------------------------//
// Henting av beskrivelse //
//------------------------//
$hentBeskrivelseProfil = "select beskrivelse from bruker where idbruker = " . $_GET['bruker'];
$stmtBeskrivelseProfil = $db->prepare($hentBeskrivelseProfil);
$stmtBeskrivelseProfil->execute();
$tellingBeskrivelse = $stmtBeskrivelseProfil->rowcount();

// Test på resultatet
if ($tellingBeskrivelse > 0) {
    $beskrivelseProfil = $stmtBeskrivelseProfil->fetch(PDO::FETCH_ASSOC);
} else $beskrivelseProfil = null;

//---------------------//
// Henting av artikler //
//---------------------//
$hentArtikkelProfil = "select artnavn from artikkel where bruker = " . $_GET['bruker'];
$stmtArtikkelProfil = $db->prepare($hentArtikkelProfil);
$stmtArtikkelProfil->execute();
$tellingArtikkel = $stmtArtikkelProfil->rowcount();

// Test på resultat
if ($tellingArtikkel > 0) {
    $artikkelProfil = $stmtArtikkelProfil->fetchAll(PDO::FETCH_ASSOC);
} else $artikkelProfil = null;

//--------------------------//
// Henting av arrangementer //
//--------------------------//
$hentArrangementProfil = "select eventnavn from event where idbruker = " . $_GET['bruker'];
$stmtArrangementProfil = $db->prepare($hentArrangementProfil);
$stmtArrangementProfil->execute();
$tellingArrangement = $stmtArrangementProfil->rowcount();

// test på resultatet
if ($tellingArrangement > 0) {
    $arrangementProfil = $stmtArrangementProfil->fetchAll(PDO::FETCH_ASSOC);
} else $arrangementProfil = null;


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
        <title>
            <?php if ($egen && !isset($_GET['innstillinger'])) {
                    echo($_SESSION['brukernavn']);
                  } else if(isset($_GET['innstillinger'])) { ?>
                Rediger profil
            <?php } else { ?>
                Profil
            <?php } ?>
        </title>
        <!-- Henter inn ekstern stylesheet -->
        <link rel="stylesheet" type="text/css" href="stylesheet.css">
        <!-- Henter inn favicon, bildet som dukker opp i fanene i nettleseren -->
        <link rel='icon' href='bilder/favicon.png' type='image/x-icon'>
        <!-- Henter inn JavaScript -->
        <script language="JavaScript" src="javascript.js"> </script>
    </head>

    <body onload="profilTabbing()"> 
        <article class="innhold">
            <!-- Begynnelse på øvre navigasjonsmeny -->
            <nav class="navTop"> 
                <!-- Bruker et ikon som skal åpne gardinmenyen, henviser til funksjonen hamburgerMeny i javascript.js -->
                <!-- javascript:void(0) blir her brukt så siden ikke scroller til toppen av seg selv når du trykker på hamburger-ikonet -->
                <a class="bildeKontroll" href="javascript:void(0)" onclick="hamburgerMeny()" tabindex="6">
                    <img src="bilder/hamburgerIkon.svg" alt="Hamburger-menyen" class="hamburgerKnapp">
                </a>
                <!-- Legger til knapper for å registrere ny bruker eller innlogging -->
                <!-- Om bruker er innlogget, vis kun en 'Logg ut' knapp -->
                <?php if (isset($_SESSION['idbruker'])) {
                    // Vises når bruker er innlogget

                    /* -------------------------------*/
                    /* Del for visning av profilbilde */
                    /* -------------------------------*/

                    // Henter bilde fra database utifra brukerid
                    
                    $hentBilde = "select hvor from bilder, brukerbilde where brukerbilde.bruker = " . $_SESSION['idbruker'] . " and brukerbilde.bilde = bilder.idbilder";
                    $stmtBilde = $db->prepare($hentBilde);
                    $stmtBilde->execute();
                    $bilde = $stmtBilde->fetch(PDO::FETCH_ASSOC);
                    $antallBilderFunnet = $stmtBilde->rowCount();
                    // rowCount() returnerer antall resultater fra database, er dette null finnes det ikke noe bilde i databasen
                    if ($antallBilderFunnet != 0) { ?>
                        <!-- Hvis vi finner et bilde til bruker viser vi det -->
                        <a class="bildeKontroll" href="javascript:void(0)" onClick="location.href='profil.php?bruker=<?php echo($_SESSION['idbruker']) ?>'" tabindex="5">
                            <?php
                            $testPaa = $bilde['hvor'];
                            // Tester på om filen faktisk finnes
                            if(file_exists("$lagringsplass/$testPaa")) {   
                                if ($_SESSION['brukertype'] == 2) { ?>
                                    <!-- Setter redaktør border "Oransje" -->
                                    <img src="bilder/opplastet/<?php echo($bilde['hvor'])?>" alt="Profilbilde"  class="profil_navmeny" style="border: 2px solid green;">
                                
                                <?php 
                                }
                                if ($_SESSION['brukertype'] == 1) { ?>
                                    <!-- Setter administrator border "Rød" -->
                                    <img src="bilder/opplastet/<?php echo($bilde['hvor'])?>" alt="Profilbilde"  class="profil_navmeny" style="border: 2px solid red;"> 
                                <?php 
                                }
                                if ($_SESSION['brukertype'] == 3) { ?> 
                                    <!-- Setter vanlig profil bilde -->
                                    <img src="bilder/opplastet/<?php echo($bilde['hvor'])?>" alt="Profilbilde"  class="profil_navmeny"> 
                                <?php 
                                }
                            } else { 
                                // Om filen ikke ble funnet, vis standard profilbilde
                                if ($_SESSION['brukertype'] == 2) { ?>
                                    <img src="bilder/profil.png" alt="Profilbilde" class="profil_navmeny" style="border: 2px solid green;">
                                <!-- Setter administrator border "Rød" -->
                                <?php } else if ($_SESSION['brukertype'] == 1) { ?>
                                    <img src="bilder/profil.png" alt="Profilbilde" class="profil_navmeny" style="border: 2px solid red;"> 
                                <!-- Setter vanlig profil bilde -->
                                <?php } else if ($_SESSION['brukertype'] != 1 || 2) { ?>
                                    <img src="bilder/profil.png" alt="Profilbilde" class="profil_navmeny"> 
                                <?php
                                }
                            } ?>
                        </a>

                    <?php } else { ?>
                        <a class="bildeKontroll" href="javascript:void(0)" onClick="location.href='profil.php?bruker=<?php echo($_SESSION['idbruker']) ?>'" tabindex="5">
                            <!-- Setter redaktør border "Oransje" -->
                            <?php if ($_SESSION['brukertype'] == 2) { ?>
                                <img src="bilder/profil.png" alt="Profilbilde" class="profil_navmeny" style="border: 2px solid green;">
                            <!-- Setter administrator border "Rød" -->
                            <?php } else if ($_SESSION['brukertype'] == 1) { ?>
                                <img src="bilder/profil.png" alt="Profilbilde" class="profil_navmeny" style="border: 2px solid red;"> 
                            <!-- Setter vanlig profil bilde -->
                            <?php } else if ($_SESSION['brukertype'] != 1 || 2) { ?>
                                <img src="bilder/profil.png" alt="Profilbilde" class="profil_navmeny"> 
                            <?php } ?>
                        </a>

                    <?php } ?>
                    <!-- Legger til en knapp for å logge ut når man er innlogget -->
                    <form method="POST" action="default.php">
                        <button name="loggUt" id="registrerKnapp" tabindex="4">LOGG UT</button>
                    </form>
                <?php } else { ?>
                    <!-- Vises når bruker ikke er innlogget -->
                    <button id="registrerKnapp" onClick="location.href='registrer.php'" tabindex="5">REGISTRER</button>
                    <button id="logginnKnapp" onClick="location.href='logginn.php'" tabindex="4">LOGG INN</button>
                <?php } ?>

                <form id="sokForm_navmeny" action="sok.php">
                    <input id="sokBtn_navmeny" type="submit" value="Søk" tabindex="3">
                    <input id="sokInp_navmeny" type="text" name="artTittel" placeholder="Søk på artikkel" tabindex="2">
                </form>
                <a href="javascript:void(0)" onClick="location.href='sok.php'" tabindex="-1">
                    <img src="bilder/sokIkon.png" alt="Søkeikon" class="sok_navmeny" tabindex="2">
                </a>
                <!-- Logoen øverst i venstre hjørne -->
                <a href="default.php" tabindex="1">
                    <img class="Logo_navmeny" src="bilder/klimateNoText.png" alt="Klimate logo">
                </a>  
            <!-- Slutt på navigasjonsmeny-->
            </nav>

            <!-- Gardinmenyen, denne går over alt annet innhold ved bruk av z-index -->
            <section id="navMeny" class="hamburgerMeny">
            
                <!-- innholdet i hamburger-menyen -->
                <!-- -1 tabIndex som standard da menyen er lukket -->
                <section class="hamburgerInnhold">
                    <?php if (isset($_SESSION['idbruker'])) { ?>
                        <!-- Hva som vises om bruker er innlogget -->
                        
                        <!-- Redaktør meny "Oransje" -->
                        <?php if ($_SESSION['brukertype'] == 2) { ?>
                            <p style="color: green"> Innlogget som Redaktør </p>
                        <!-- Administrator meny "Rød" -->
                        <?php } else if ($_SESSION['brukertype'] == 1) { ?>
                            <p style="color: red"> Innlogget som Administrator </p>
                        <?php } ?>

                        <a class = "menytab" tabIndex = "-1" href="arrangement.php">Arrangementer</a>
                        <a class = "menytab" tabIndex = "-1" href="artikkel.php">Artikler</a>
                        <a class = "menytab" tabIndex = "-1" href="meldinger.php">Innboks</a>
                        <a class = "menytab" tabIndex = "-1" href="backend.php">Oversikt</a>
                        <a class = "menytab" tabIndex = "-1" href="konto.php">Konto</a>
                        <a class = "menytab" tabIndex = "-1" href="sok.php">Avansert Søk</a>
                    <?php } else { ?>
                        <!-- Hvis bruker ikke er innlogget -->
                        <a class = "menytab" tabIndex = "-1" href="arrangement.php">Arrangementer</a>
                        <a class = "menytab" tabIndex = "-1" href="artikkel.php">Artikler</a>
                        <a class = "menytab" tabIndex = "-1" href="sok.php">Avansert Søk</a>
                    <?php } ?>
                </section>
            </section>

            <!-----------------------
            Del for brukerinformasjon
            ------------------------>
            <!-- ---------------------------------------------------- -->
            <!-- Tester på om rediger brukerinnstilinger er påklikket -->
            <!-- ---------------------------------------------------- -->
            <?php if(isset($_GET['innstillinger'])) { ?>
                <header class="profil_header" onclick="lukkHamburgerMeny()">
                    
                </header>
                
                <main class="profil_main">
                <h2>Rediger informasjon</h2>
                    <h3>Endre profilbilde</h3>
                    <form class="profil_bilde" method="POST" enctype="multipart/form-data" action="profil.php?bruker=<?php echo $_SESSION['idbruker'] ?>">
                        <h4>Velg et bilde</h4>
                        <input type="file" name="bilde" id="bildeK" accept=".jpg, .jpeg, .png" tabindex="7">
                        <input class="profil_knapp" type="submit" name="endreBilde" value="Last opp" tabindex="8">
                    </form>
                    <!-- -------------------------------------------------------------------------------------------------------------- -->
                    <!-- <h3>Vis eller skjul personalia</h3> -->
                    <!-- <section class="profil_persInf">     -->
                        <!-- Test på $egen, Edit: if-testen med $egen og foreach-løkken ble fjernet --> 
                        <!-- Ikke egen profil -->
                        <!-- Funksjonaliteter for egen profil må nesten kreve en ny tabell for privacy settings? -->
                        <!-- Ser ingen gode løsninger for ellers å kunne skjule informasjon uten å endre på de relevante feltene (NO NO)-->
                            <!-- <p><strong>Fornavn:</strong></p> <p><?php echo($personaliaProfil["fnavn"])?></p> -->
                            <!-- <p><strong>Etternavn:</strong> </p> <p><?php echo($personaliaProfil["enavn"])?></p> -->
                            <!-- <p><strong>E-post Adresse:</strong></p> <p> <?php echo($personaliaProfil["epost"])?></p> -->
                            <!-- <p><strong>Telefonnummer:</strong></p> <p> <?php echo($personaliaProfil["telefonnummer"])?></p> -->
                        <!-- </section> -->
                    <!-- -------------------------------------------------------------------------------------------------------------- -->
                    <!-- Del for å oppdatere brukerbeskrivelse -->
                <?php if($egen) { ?>
                        <h3>Endre beskrivelse</h3>
                        <form class="profil_beskrivelse" method="POST" action="profil.php?bruker=<?php echo $_SESSION['idbruker'] ?>&innstillinger">
                            <textarea name="beskrivelse" placeholder="Skriv litt om deg selv" tabindex="9"><?php echo $beskrivelseProfil['beskrivelse'] ?></textarea>
                            <input class="profil_knapp" type="submit" value="Oppdater" tabindex="9"/>
                        </form>
                    <?php } ?>
                    <!-- Viser interesser -->
                    <h3 class="OverskriftInter">Interesser</h3>
                    <!-- Nøstet foreach -->
                    <!-- Ytre løkke -->
                    <section class="interesserSection">
                        <section class="interesserTags">
                            <?php if ($tellingInteresse != null) {
                                foreach ($interesseProfil as $rad) {    
                                    foreach ($rad as $kolonne) { ?> 
                                        <!-- Test om bruker er i slettemodus -->
                                        <?php if (isset($_POST['slettemodus'])) { ?> 
                                            <input id="innholdAaSlette<?php echo($kolonne)?>" class="slett" form="slettemodus" name="interesseTilSletting" type="submit" onmouseenter="visSlett('innholdAaSlette<?php echo($kolonne)?>')" onmouseout="visSlett('innholdAaSlette<?php echo($kolonne)?>')" value="<?php echo($kolonne) ?>" tabindex = <?php echo($tabindex); $tabindex++; ?>></input>
                                            <!-- Ellers normal visning (som tydeligvis kjører åkke som) -->
                                        <?php } else { ?> 
                                            <p class="proInt"onClick="location.href='sok.php?brukernavn=&epost=&interesse=<?php echo($kolonne) ?>'" tabindex = <?php echo($tabindex); $tabindex++;?>><?php echo($kolonne); ?></p>
                                        <?php } // Slutt, else løkke    
                                    } // Slutt, indre løkke
                                } // Slutt, ytre løkke
                            } ?> <!-- Slutt, IF-test -->
                        </section>
                        <!-- Del for å legge til interesser -->
                        <!-- dropdown med forhåndsdefinerte interesser, for egen profil -->

                        <!-- Slettemodus -->
                        <?php if ($egen) { ?>
                        <form id="slettemodus" class="slett_interesse" method="POST" action="profil.php?bruker=<?php echo $_SESSION['idbruker'] ?>&innstillinger">
                            <?php if(!isset($_POST['slettemodus'])) { ?>
                                <input class="profil_knapp3" type="submit" name="slettemodus" value="Slett interesse" tabindex="100">
                            <?php } else { ?> 
                                <input class="profil_knapp2" type="submit" name="avbryt" value="Avbryt" tabindex="100"> 
                            <?php } ?>
                        </form>
                        <?php } ?>
                        
                        <?php if($egen) { ?>
                            <form class="profil_interesse" method="POST" action="profil.php?bruker=<?php echo $_SESSION['idbruker'] ?>&innstillinger">
                                <select class="profil_input" name="interesse" tabindex="101">
                                    <?php $index=1 ?>
                                    <?php foreach($interesse as $rad) { ?>
                                        <option value="<?php echo($rad['idinteresse']) ?>"> <?php echo($rad['interessenavn']) ?> </option>
                                    <?php } ?> <!-- Slutt, ytre løkke -->
                                    
                                </select>
                                <input class="profil_knapp" type="submit" value="Legg til" tabindex="102"></input>
                            </form>

                            <!-- Egendefinert interesse -->
                            <form class="profil_interesse_egendefinert" method ="POST" action="profil.php?bruker=<?php echo $_SESSION['idbruker'] ?>&innstillinger">
                                <input class="profil_inputTekst" name="interesseEgendefinert" type="text" placeholder="Egendefinert" tabindex="103"></input>
                                <input class="profil_knapp" type="submit" value="Legg til" tabindex="104"></input>
                            </form>
                        <?php } ?> <!-- Slutt, IF-test -->                
                    </section> 
                    
                    <!-- tilbake knapp -->
                    <?php if($egen) {?>
                            <button onClick="location.href='profil.php?bruker=<?php echo $_SESSION['idbruker'] ?>'" name="redigerkonto" class="rediger_profil_knapp" tabindex="105">Tilbake</button>
                    <?php }?>

                </main>
            <?php } else { ?>
                <!-- ---------- -->
                <!-- profilside -->
                <!-- ---------- -->
                <!-- For å kunne lukke hamburgermenyen ved å kun trykke på et sted i vinduet må lukkHamburgerMeny() funksjonen ligge i deler av HTML-koden -->
                <!-- Kan ikke legge denne direkte i body -->
                <header class="profil_header" onclick="lukkHamburgerMeny()">
                    
                </header>

                <!-- Funksjon for å lukke hamburgermeny når man trykker på en del i Main -->
                <main class="profil_main" onclick="lukkHamburgerMeny()">  
                    <section class="profil_pb">
                        <!-- Bilde av brukeren -->
                        <!-- FLYTT SØK-DELEN AV DENNE BITEN OPP TIL FØR HTML-ERKLÆRING? -->
                        <?php
                        $hentProfilbilde = "select hvor from bilder, brukerbilde where brukerbilde.bruker = " . $_GET['bruker'] . " and brukerbilde.bilde = bilder.idbilder";
                        $stmtProfilbilde = $db->prepare($hentProfilbilde);
                        $stmtProfilbilde->execute();
                        $profilbilde = $stmtProfilbilde->fetch(PDO::FETCH_ASSOC);
                        $antallProfilbilderFunnet = $stmtProfilbilde->rowCount();
                        // rowCount() returnerer antall resultater fra database, er dette null finnes det ikke noe bilde i databasen
                        if ($antallProfilbilderFunnet != 0) { ?>
                            <!-- Hvis vi finner et bilde til brukeren viser vi det -->
                            <section class="bildeKontroll" tabindex="-1">
                                <?php // Tester på om filen faktisk finnes
                                $testPaa = $profilbilde['hvor'];
                                if(file_exists("$lagringsplass/$testPaa")) {  ?> 
                                    <img src="bilder/opplastet/<?php echo($profilbilde['hvor'])?>" alt="Profilbilde" class="profil_bilde">
                                <?php } else { ?>
                                    <img src="bilder/profil.png" alt="Profilbilde" class="profil_bilde">
                                <?php } ?>
                                <h1 class="velkomst"> <?php echo $brukernavnProfil['brukernavn'] ?> </h1>
                            </section>
            
                        <?php } else { ?>
                            <!-- Hvis brukeren ikke har noe profilbilde, bruk standard profilbilde -->
                            <section class="bildeKontroll" tabindex="3">
                                <img src="bilder/profil.png" alt="Profilbilde" class="profil_bilde">
                                <!-- Vis brukernavn -->
                                <h1 class="velkomst"> <?php echo $brukernavnProfil['brukernavn'] ?> </h1>
                            </section>
                        <?php } ?>
                        
                        
                        
                        <!-- --------------- -->
                        <!-- BRUKERINFO ---- -->
                        <!-- --------------- -->
                        <h2>Om</h2>
                        <h3>Oversikt</h3>
                        <section class="profil_persInf">
                        
                        <!-- Test på $egen, Edit: if-testen med $egen og foreach-løkken ble fjernet --> 
                        <!-- Ikke egen profil -->
                        <!-- Funksjonaliteter for egen profil må nesten kreve en ny tabell for privacy settings? -->
                        <!-- Ser ingen gode løsninger for ellers å kunne skjule informasjon uten å endre på de relevante feltene (NO NO)-->
                            
                            <p><strong>Fornavn:</strong></p> <p><?php echo($personaliaProfil["fnavn"])?></p>
                            <p><strong>Etternavn:</strong> </p> <p><?php echo($personaliaProfil["enavn"])?></p>
                            <p><strong>E-post Adresse:</strong></p> <p> <?php echo($personaliaProfil["epost"])?></p>
                            <p><strong>Telefonnummer:</strong></p> <p> <?php echo($personaliaProfil["telefonnummer"])?></p>
                        </section>
                    </section>    
                    
                    <!-- BESKRIVELSE -->
                    <section class="brukerBeskrivelse">
                    <h3>Beskrivelse</h3>
                        <?php ?>
                            <p><?php if(preg_match("/\S/", $beskrivelseProfil['beskrivelse']) == 1) {echo($beskrivelseProfil['beskrivelse']);} else {echo("Bruker har ikke oppgitt en beskrivelse");} ?></p>
                        <?php  ?>
                    </section>
                    <!-- INTERESSER -->
                    <h2>Interesser</h2>
                    <!-- Nøstet foreach -->
                    <!-- Ytre løkke -->
                    <section class="interesserTags">
                    <?php if ($tellingInteresse != null) {
                        foreach ($interesseProfil as $rad) {    
                            foreach ($rad as $kolonne) { ?> 
                                <!-- Test om bruker er i slettemodus -->
                                <?php if (isset($_POST['slettemodus'])) { ?> 
                                    <input class="slett" form="slettemodus" name="interesseTilSletting" type="submit" value="<?php echo($kolonne) ?>" tabindex = <?php echo($tabindex); $tabindex++; ?>></input>
                                <!-- Ellers normal visning (som tydeligvis kjører åkke som) -->
                                <?php } else { ?> 
                                    <p class="proInt" onClick="location.href='sok.php?brukernavn=&epost=&interesse=<?php echo($kolonne) ?>'" tabindex = <?php echo($tabindex); $tabindex++;?>> <?php echo($kolonne); ?> </p>
                                <?php } // Slutt, else løkke    
                            } // Slutt, indre løkke
                        } // Slutt, ytre løkke
                    } ?> <!-- Slutt, IF-test --> 
                    </section>
                    <?php if($egen) {?>
                        <button onClick="location.href='profil.php?bruker=<?php echo $_SESSION['idbruker'] ?>&innstillinger'" name="redigerkonto" class="rediger_profil_knapp" tabindex=30>Rediger informasjon</button>
                    <?php } ?>
            </main>

             <?php } ?> <!-- Test på om brukeren har klikket på rediger -->
            <!-- Knapp som vises når du har scrollet i vinduet, tar deg tilbake til toppen -->
            <button onclick="tilbakeTilTopp()" id="toppKnapp" title="Toppen"><img src="bilder/pilopp.png" alt="Tilbake til toppen"></button>

            <!-- Footer, epost er for øyeblikket på en catch-all, videresendes til RK -->
            <footer>
                <p class=footer_beskrivelse>&copy; Klimate <?php echo date("Y");?> | <a href="mailto:kontakt@klimate.no">Kontakt oss</a>
                    <!-- Om brukeren ikke er administrator eller redaktør, vis link for søknad til å bli redaktør -->
                    <?php if (isset($_SESSION['idbruker']) and $_SESSION['brukertype'] == "3") { ?> | <a href="soknad.php">Søknad om å bli redaktør</a><?php } ?>
                </p>
            </footer>
        </article>
    </body>

    <!-- Denne siden er utviklet av Robin Kleppang, Petter Fiskvik, Aron Snekkestad, Ajdin Bajorvic siste gang endret 07.02.2020 -->
    <!-- Denne siden er kontrollert av Aron Snekkestad, siste gang 07.02.2020 -->

</html>