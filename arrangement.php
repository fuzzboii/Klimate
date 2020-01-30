<?php
session_start();

//------------------------------//
// Innstillinger, faste variable //
//------------------------------//
include("innstillinger.php");

// Enkel test som gjør det mulig å beholde brukerinput etter siden er lastet på nytt (Form submit)
$input_tittel = "";
$input_innhold = "";
$input_tidspunkt = "";
$input_adresse = "";
$input_fylke = "";
if (isset($_SESSION['input_tittel'])) {
    // Legger innhold i variable som leses senere på siden
    $input_tittel = $_SESSION['input_tittel'];
    $input_innhold = $_SESSION['input_innhold'];
    $input_tidspunkt = $_SESSION['input_tidspunkt'];
    $input_adresse = $_SESSION['input_adresse'];
    $input_fylke = $_SESSION['input_fylke'];
    // Sletter innholdet så dette ikke eksisterer utenfor denne siden
    unset($_SESSION['input_tittel']);
    unset($_SESSION['input_innhold']);
    unset($_SESSION['input_tidspunkt']);
    unset($_SESSION['input_adresse']);
    unset($_SESSION['input_fylke']);
}

if (isset($_POST['publiserArrangement'])) {
    $_SESSION['input_tittel'] = $_POST['tittel'];
    $_SESSION['input_innhold'] = $_POST['innhold'];
    $_SESSION['input_tidspunkt'] = $_POST['tidspunkt'];
    $_SESSION['input_adresse'] = $_POST['adresse'];
    $_SESSION['input_fylke'] = $_POST['fylke'];

    if (strlen($_POST['tittel']) <= 45 && strlen($_POST['tittel']) > 0) {
        if (strlen($_POST['innhold'] <= 1000) && strlen($_POST['innhold']) > 0) {
            if ($_POST['tidspunkt'] != "") {
                if (strtotime($_POST['tidspunkt']) > strtotime(date("Y-m-d H:i:s"))) {
                    if(strlen($_POST['adresse']) <= 250 && strlen($_POST['adresse']) > 0) {
                        if($_POST['fylke'] != "") {
                        
                            // Tar utgangspunkt i at bruker ikke har lastet opp bilde
                            $harBilde = false;

                            // Sanitiserer innholdet før det blir lagt i databasen
                            $tittel = filter_var($_POST['tittel'], FILTER_SANITIZE_STRING);
                            $innhold = filter_var($_POST['innhold'], FILTER_SANITIZE_STRING);
                            $tidspunkt = $_POST['tidspunkt'];
                            $adresse = filter_var($_POST['adresse'], FILTER_SANITIZE_STRING);

                            // Henter IDen til fylket som ble valgt
                            $hentFylkeQ = "select idfylke from fylke where fylkenavn = '" . $_POST['fylke'] . "'";                        
                            $hentFylkeSTMT = $db->prepare($hentFylkeQ);
                            $hentFylkeSTMT->execute();
                            $idfylke = $hentFylkeSTMT->fetch(PDO::FETCH_ASSOC); 
                            
                            // Spørringen som oppretter arrangementet
                            $nyttArrangementQ = "insert into event(eventnavn, eventtekst, tidspunkt, veibeskrivelse, idbruker, fylke) values('" . $tittel . "', '" . $innhold . "', '" . $tidspunkt . "', '" . $adresse . "', '" . $_SESSION['idbruker'] . "', '" . $idfylke['idfylke'] . "')";
                            $nyttArrangementSTMT = $db->prepare($nyttArrangementQ);
                            $nyttArrangementSTMT->execute();
                            $idevent = $db->lastInsertId();
                            
                            // Del for filopplastning
                            if (is_uploaded_file($_FILES['bilde']['tmp_name'])) {
                                // Kombinerer artikkel med den siste idevent'en
                                $navn = "event" . $idevent;
                                // Henter filtypen
                                $filtype = "." . substr($_FILES['bilde']['type'], 6, 4);
                                // Kombinerer navnet med filtypen
                                $bildenavn = $navn . $filtype;
                                // Selve prosessen som flytter bildet til bestemt lagringsplass
                                if (move_uploaded_file($_FILES['bilde']['tmp_name'], "$lagringsplass/$bildenavn")) {
                                    $harbilde = true;
                                } else {
                                    // Feilmelding her
                                }
                            }
                            if ($harbilde == true) {
                                // Legger til bildet i databasen, dette kan være sin egne spørring
                                $nyttBildeQ = "insert into bilder(hvor) values('" . $bildenavn . "')";
                                $nyttBildeSTMT = $db->prepare($nyttBildeQ);
                                $nyttBildeSTMT->execute();
                                // Returnerer siste bildeid'en
                                $bildeid = $db->lastInsertId();

                                // Spørringen som lager koblingen mellom bilder og arrangement
                                $nyKoblingQ = "insert into eventbilde(event, bilde) values('" . $idevent . "', '" . $bildeid . "')";
                                $nyKoblingSTMT = $db->prepare($nyKoblingQ);
                                $nyKoblingSTMT->execute();
                            }

                            header('Location: arrangement.php?arrangement=' . $idevent);
                        } else { header('Location: arrangement.php?nyarrangement=error6'); } // Fylke ikke oppgitt
                    } else { header('Location: arrangement.php?nyarrangement=error5'); } // Adresse tomt / for langt
                } else { header('Location: arrangement.php?nyarrangement=error4'); } // Dato tilbake i tid
            } else { header('Location: arrangement.php?nyarrangement=error3'); } // Tidspunkt ikke oppgitt
        } else { header('Location: arrangement.php?nyarrangement=error2'); } // Innholdt tomt / for langt
    } else { header('Location: arrangement.php?nyarrangement=error1'); } // Tittel tomt / for langt
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
        <title>Arrangementer</title>
        <!-- Henter inn ekstern stylesheet -->
        <link rel="stylesheet" type="text/css" href="stylesheet.css">
        <!-- Henter inn favicon, bildet som dukker opp i fanene i nettleseren -->
        <link rel='icon' href='bilder/favicon.png' type='image/x-icon'>
        <!-- Henter inn JavaScript -->
        <script language="JavaScript" src="javascript.js"> </script>
    </head>

    <body id="arrangement_body" onload="hentSide('arrangement_hovedsection', 'arrangement_tilbKnapp', 'arrangement_nesteKnapp')" onresize="hentSide('side_arrangement', 'arrangement_tilbKnapp', 'arrangement_nesteKnapp')">
        <!-- Begynnelse på øvre navigasjonsmeny -->
        <nav class="navTop"> 
            <!-- Bruker et ikon som skal åpne gardinmenyen, henviser til funksjonen hamburgerMeny i javascript.js -->
            <!-- javascript:void(0) blir her brukt så siden ikke scroller til toppen av seg selv når du trykker på hamburger-ikonet -->
            <a class="bildeKontroll" href="javascript:void(0)" onclick="hamburgerMeny()" tabindex="4">
                <img src="bilder/hamburgerIkon.svg" alt="Hamburger-menyen" class="hamburgerKnapp">
            </a>
            <!-- Legger til knapper for å registrere ny bruker eller innlogging -->
            <!-- Om bruker er innlogget, vis kun en 'Logg ut' knapp -->
            <?php if (isset($_SESSION['brukernavn'])) {
                // Vises når bruker er innlogget

                /* -------------------------------*/
                /* Del for visning av profilbilde */
                /* -------------------------------*/

                // Henter bilde fra database utifra brukerid

                $hentBilde = "select hvor from bruker, brukerbilde, bilder where idbruker = " . $_SESSION['idbruker'] . " and idbruker = bruker and bilde = idbilder";
                $stmtBilde = $db->prepare($hentBilde);
                $stmtBilde->execute();
                $bilde = $stmtBilde->fetch(PDO::FETCH_ASSOC);
                $antallBilderFunnet = $stmtBilde->rowCount();
                
                // rowCount() returnerer antall resultater fra database, er dette null finnes det ikke noe bilde i databasen
                if ($antallBilderFunnet != 0) { ?>
                    <!-- Hvis vi finner et bilde til bruker viser vi det -->
                    <a class="bildeKontroll" href="javascript:void(0)" onClick="location.href='profil.php'" tabindex="3">
                        <img src="bilder/opplastet/<?php echo($bilde['hvor'])?>" alt="Profilbilde" class="profil_navmeny">
                    </a>

                <?php } else { ?>
                    <!-- Hvis bruker ikke har noe profilbilde, bruk standard profilbilde -->
                    <a class="bildeKontroll" href="javascript:void(0)" onClick="location.href='profil.php'" tabindex="3">
                        <img src="bilder/profil.png" alt="Profilbilde" class="profil_navmeny">
                    </a>
                <?php } ?>

                <!-- Legger til en knapp for å logge ut når man er innlogget -->
                <form method="POST" action="default.php">
                    <button name="loggUt" id="registrerKnapp" tabindex="2">LOGG UT</button>
                </form>
            <?php } else { ?>
                <!-- Vises når bruker ikke er innlogget -->
                <button id="registrerKnapp" onClick="location.href='registrer.php'" tabindex="3">REGISTRER</button>
                <button id="logginnKnapp" onClick="location.href='logginn.php'" tabindex="2">LOGG INN</button>
            <?php } ?>
            <form id="sokForm_navmeny" action="sok.php">
                <input id="sokBtn_navmeny" type="submit" value="Søk" tabindex="3">
                <input id="sokInp_navmeny" type="text" name="artTittel" placeholder="Søk på artikkel" tabindex="2">
            </form>
            <a href="javascript:void(0)" onClick="location.href='sok.php'">
                <img src="bilder/sokIkon.png" alt="Søkeikon" class="sok_navmeny">
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
                <?php if (isset($_SESSION['brukernavn'])) { ?>
                    <!-- Hva som vises om bruker er innlogget -->
                    <a class = "menytab" tabIndex = "-1" href="arrangement.php">Arrangementer</a>
                    <a class = "menytab" tabIndex = "-1" href="artikkel.php">Artikler</a>
                    <a class = "menytab" tabIndex = "-1" href="#">Diskusjoner</a>
                    <a class = "menytab" tabIndex = "-1" href="backend.php">Oversikt</a>
                    <a class = "menytab" tabIndex = "-1" href="konto.php">Konto</a>
                    <a class = "menytab" tabIndex = "-1" href="sok.php">Avansert Søk</a>
                <?php } else { ?>
                    <!-- Hvis bruker ikke er innlogget -->
                    <a class = "menytab" tabIndex = "-1" href="arrangement.php">Arrangementer</a>
                    <a class = "menytab" tabIndex = "-1" href="artikkel.php">Artikler</a>
                    <a class = "menytab" tabIndex = "-1" href="#">Diskusjoner</a>
                    <a class = "menytab" tabIndex = "-1" href="sok.php">Avansert Søk</a>
                <?php } ?>
            </section>
        </section>

        <!-- Funksjon for å lukke hamburgermeny når man trykker på en del i Main -->
       
            <?php if(isset($_GET['arrangement'])){
                // Henter arrangementet bruker ønsker å se
                $hent = "select idevent, eventnavn, eventtekst, tidspunkt, veibeskrivelse, brukernavn, fnavn, enavn, epost, telefonnummer, fylkenavn from event, bruker, fylke where idevent = '" . $_GET['arrangement'] . "' and event.idbruker = bruker.idbruker and event.fylke = fylke.idfylke";
                $stmt = $db->prepare($hent);
                $stmt->execute();
                $arrangement = $stmt->fetch(PDO::FETCH_ASSOC);
                $antallArrangement = $stmt->rowCount();
                // rowCount() returnerer antall resultater fra database, er dette null finnes det ikke noe arrangement med denne eventid'en i databasen
                if ($antallArrangement == 0) { ?>
                    <!-- Del for å vise feilmelding til bruker om at arrangementet ikke eksisterer -->
                    <header class="arrangement_header" onclick="lukkHamburgerMeny()">
                        <h1>Arrangement ikke funnet</h1>
                    </header>
                    <main id="arrangement_main" onclick="lukkHamburgerMeny()"> 
                    <section id="arrangement_bunnSection">
                        <button onclick="location.href='arrangement.php'" class="lenke_knapp">Tilbake til arrangementer</button>  
                    </section>
                <?php } else { 
                    // Del for å vise et spesifikt arrangement
                    // Henter bilde fra database utifra eventid
                    $hentBilde = "select hvor from event, eventbilde, bilder where idevent = " . $_GET['arrangement'] . " and idevent = event and bilde = idbilder";
                    $stmtBilde = $db->prepare($hentBilde);
                    $stmtBilde->execute();
                    $bilde = $stmtBilde->fetch(PDO::FETCH_ASSOC);
                    $antallBilderFunnet = $stmtBilde->rowCount();
                    // rowCount() returnerer antall resultater fra database, er dette null finnes det ikke noe bilde i databasen
                    ?> 
                    <section id="arrangement_spes"> 
                            <header id="arrangement_headerMBilde" onclick="lukkHamburgerMeny()">
                                <h1><?php echo($arrangement['eventnavn'])?></h1>
                            </header>
                            <main id="arrangement_main" style="margin-top: 6em;" onclick="lukkHamburgerMeny()"> 
                                <?php if ($antallBilderFunnet != 0) { ?>
                                <!-- Hvis vi finner et bilde til arrangementet viser vi det -->
                                <img id="arrangement_fullSizeBilde" src="bilder/opplastet/<?php echo($bilde["hvor"]) ?>" alt="Bilde av arrangementet">
                        <?php } else { ?>
                            <header class="arrangement_header" onclick="lukkHamburgerMeny()">
                                <h1><?php echo($arrangement['eventnavn'])?></h1>
                            </header>
                            <main id="arrangement_main" onclick="lukkHamburgerMeny()"> 
                        <?php } ?>
                        <!-- -----------------påklikket arrangement---------------------  -->
                        <section id="arrangement_omEvent">
                            <h3>Om arrangementet</h3>
                            <p id="arrangement_tekst"><?php echo($arrangement['eventtekst'])?></p>

                             

                            </section>
                            <section class="arg_informasjon">
                                
                                <section class="argInf_dato">
                                    <h3>Dato</h3>
                                    <p id="arrangement_dato">Når: <?php echo(substr($arrangement['tidspunkt'], 0, 10) . " kl: "); echo(substr($arrangement['tidspunkt'], 11, 5)) ?></p>
                                </section>
                                <section class="argInf_sted">
                                    <h3>Sted</h3>
                                    <?php 
                                        $dato = date_create($arrangement['tidspunkt']);
                                    ?>
                                    <!-- Lenke som leder til Google Maps med adresse -->
                                    <p class="arrangement_adresse">Adresse: <a href="http://maps.google.com/maps?q=<?php echo($arrangement['veibeskrivelse'] . ", " . $arrangement['fylkenavn']);?>"><?php echo($arrangement['veibeskrivelse']) ?></a></p>
                                    <p class="arrangement_adresse"><?php echo($arrangement['fylkenavn']) ?> fylke</p>
                                </section>
                                <section class="argInf_om">
                                    <h3>Om arrangør</h3>
                                    <?php 
                                    // Hvis bruker ikke har etternavn (Eller har oppgitt et mellomrom eller lignende som navn) hvis brukernavn
                                    if (preg_match("/\S/", $arrangement['enavn']) == 0) { ?>
                                        <p id="arrangement_navn"><?php echo($arrangement['brukernavn'])?></p>
                                    <?php } else { ?>
                                        <p id="arrangement_navn"><?php if(preg_match("/\S/", $arrangement['fnavn']) == 1) {echo($arrangement['fnavn'] . " "); echo($arrangement['enavn']);  } ?></p>
                                    <?php } ?>
                                    <p id="arrangement_mail">Epost: <a href="mailto:<?php echo($arrangement['epost'])?>"><?php echo($arrangement['epost'])?></a></p>
                                </section>
                            
                        </section>
                    <?php } ?>
                </section>
            <?php  } else if (isset($_GET['nyarrangement'])) { ?>      
            
                <header class="arrangement_header" onclick="lukkHamburgerMeny()">
                    <h1>Nytt arrangement</h1>
                </header>

                <main id="arrangement_main" onclick="lukkHamburgerMeny()">

                <article id="arrangement_arrangementNy">
                    <form method="POST" action="arrangement.php" enctype="multipart/form-data">
                        <h2>Tittel</h2>
                        <input type="text" maxlength="45" name="tittel" value="<?php echo($input_tittel) ?>" placeholder="Skriv inn tittel" autofocus required>
                        <h2>Innhold</h2>
                        <textarea maxlength="1000" name="innhold" rows="5" cols="35" placeholder="Skriv litt hva arrangementet handler om" required><?php echo($input_innhold) ?></textarea>
                        <h2>Dato</h2>
                        <input type="datetime-local" name="tidspunkt" value="<?php echo($input_tidspunkt) ?>" required>
                        <h2>Adresse</h2>
                        <input type="text" maxlength="250" name="adresse" value="<?php echo($input_adresse) ?>" placeholder="Oppgi adresse" required>
                        <select name="fylke" required>
                            <?php if($input_fylke != "") { ?><option value="<?php echo($input_fylke) ?>"><?php echo($input_fylke) ?></option>
                            <?php } else { ?>
                                <option value="">Velg fylke</option>
                            <?php }
                                // Henter fylker fra database
                                $hentFylke = "select fylkenavn from fylke order by fylkenavn ASC";
                                $stmtFylke = $db->prepare($hentFylke);
                                $stmtFylke->execute();
                                $fylke = $stmtFylke->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($fylke as $innhold) { ?>
                                    <option value="<?php echo($innhold['fylkenavn'])?>"><?php echo($innhold['fylkenavn'])?></option>
                            <?php } ?>
                        </select>
                        <h2>Bilde</h2>
                        <input type="file" name="bilde" id="bilde" accept=".jpg, .jpeg, .png">

                        <?php if($_GET['nyarrangement'] == "error1"){ ?>
                            <p id="mldFEIL">Tittel for lang eller ikke oppgitt</p>
                    
                        <?php } else if($_GET['nyarrangement'] == "error2"){ ?>
                            <p id="mldFEIL">Innhold for lang eller ikke oppgitt</p>
                        
                        <?php } else if($_GET['nyarrangement'] == "error3") { ?>
                            <p id="mldFEIL">Oppgi en dato</p>

                        <?php } else if($_GET['nyarrangement'] == "error4"){ ?>
                            <p id="mldFEIL">Datoen må være forover i tid</p>    

                        <?php } else if($_GET['nyarrangement'] == "error5"){ ?>
                            <p id="mldFEIL">Adresse for lang eller ikke oppgitt</p>   

                        <?php } else if($_GET['nyarrangement'] == "error6"){ ?>
                            <p id="mldFEIL">Fylke ikke oppgitt</p>    
                        <?php } ?>

                        <input id="arrangement_submitNy" type="submit" name="publiserArrangement" value="Opprett Arrangement">
                    </form>
                </article>

            <?php } else {

                // Del for å vise alle arrangement 
                $hentAlleArr = "select idevent, eventnavn, tidspunkt, veibeskrivelse, brukernavn, fnavn, enavn, fylkenavn from event, bruker, fylke where tidspunkt >= NOW() and event.idbruker = bruker.idbruker and event.fylke = fylke.idfylke order by tidspunkt asc";
            
                $stmtArr = $db->prepare($hentAlleArr);
                $stmtArr->execute();
                $resArr = $stmtArr->fetchAll(PDO::FETCH_ASSOC); 
                
                // Variabel som brukes til å fortelle når vi kan avslutte side_sok
                $avsluttTag = 0;
                $antallSider = 0;

                $resAntall = $stmtArr->rowCount(); 
                ?>
                
                <header class="arrangement_header" onclick="lukkHamburgerMeny()">
                    <h1>Arrangementer</h1>
                    <a href="arrangement.php?nyarrangement" tabindex="-1"> <!-- VIKTIG, tabindex -->
                        <img id="arrangement_plussikon" src="bilder/plussIkon.png" alt="Plussikon for å opprette nytt arrangement">
                    </a>
                </header>
                <main id="arrangement_main" onclick="lukkHamburgerMeny()">
                <?php if ($resAntall > 0 ) { ?>
                    <?php for ($j = 0; $j < count($resArr); $j++) {
                        // Hvis rest av $j delt på 8 er 0, start section (Ny side)
                        if ($j % 8 == 0) { ?>
                            <section class="arrangement_hovedsection">
                        <?php $antallSider++; } $avsluttTag++; ?>
                        <section class="arrangement_ressection" onClick="location.href='arrangement.php?arrangement=<?php echo($resArr[$j]['idevent']) ?>'">
                            <figure class="arrangement_infoBoks">

                                <?php // Henter bilde til arrangementet
                                $hentArrBilde = "select hvor from bilder, eventbilde where eventbilde.event = " . $resArr[$j]['idevent'] . " and eventbilde.bilde = bilder.idbilder";
                                $stmtArrBilde = $db->prepare($hentArrBilde);
                                $stmtArrBilde->execute();
                                $resBilde = $stmtArrBilde->fetch(PDO::FETCH_ASSOC);
                                
                                if (!$resBilde) { ?>
                                    <!-- Standard arrangementbilde om arrangør ikke har lastet opp noe enda -->
                                    <img class="arrangement_BildeBoks" src="bilder/stockevent.jpg" alt="Bilde av Oleg Magni fra Pexels">
                                <?php } else { ?>
                                    <!-- Arrangementbilde som resultat av spørring -->
                                    <img class="arrangement_BildeBoks" src="bilder/opplastet/<?php echo($resBilde['hvor'])?>" alt="Profilbilde for <?php echo($resArr[$j]['eventnavn'])?>">
                                <?php } ?>
                            </figure>

                            <p class="arrangement_tidspunkt">
                                <?php 
                                    $dato = date_create($resArr[$j]['tidspunkt']);
                                    echo(date_format($dato,"d/m/Y"));
                                ?>
                            </p>
                            <img class="arrangement_rFloatBilde" src="bilder/datoIkon.png">
                            <p class="arrangement_fylke"><?php echo($resArr[$j]['fylkenavn'])?></p>
                            <img class="arrangement_rFloatBilde" src="bilder/stedIkon.png">
                            <img class="arrangement_navn" src="bilder/brukerIkonS.png">
                            <?php 
                            // Hvis bruker ikke har etternavn (Eller har oppgitt et mellomrom eller lignende som navn) hvis brukernavn
                            if (preg_match("/\S/", $resArr[$j]['enavn']) == 0) { ?>
                                <p class="arrangement_navn"><?php echo($resArr[$j]['brukernavn'])?></p>
                            <?php } else { ?>
                                <p class="arrangement_navn"><?php echo($resArr[$j]['enavn']) ?></p>
                            <?php } ?>
                            <h2><?php echo($resArr[$j]['eventnavn'])?></h2>
                        </section>
                        <?php 
                        // Hvis telleren har nådd 8
                        if (($avsluttTag == 8) || $j == (count($resArr) - 1)) { ?>
                            </section>     
                        <?php 
                            // Sett telleren til 0, mulighet for mer enn 2 sider
                            $avsluttTag = 0;
                        }
                    }
                } ?>

                <section id="arrangement_bunnSection">
                    <?php if ($antallSider > 1) {?>
                        <p id="sok_antSider">Antall sider: <?php echo($antallSider) ?></p>
                    <?php } ?>
                    <button type="button" id="arrangement_tilbKnapp" onclick="visForrigeSide('arrangement_hovedsection', 'arrangement_tilbKnapp', 'arrangement_nesteKnapp')">Forrige</button>
                    <button type="button" id="arrangement_nesteKnapp" onclick="visNesteSide('arrangement_hovedsection', 'arrangement_tilbKnapp', 'arrangement_nesteKnapp')">Neste</button>
                </section>
            <?php } ?>
        </main>
        
        <!-- Knapp som vises når du har scrollet i vinduet, tar deg tilbake til toppen -->
        <button onclick="tilbakeTilTopp()" id="toppKnapp" title="Toppen"><img src="bilder/pilopp.png" alt="Tilbake til toppen"></button>

        <!-- Footer, epost er for øyeblikket på en catch-all, videresendes til RK -->
        <footer>
            <p class=footer_beskrivelse>&copy; Klimate 2020 | <a href="mailto:kontakt@klimate.no">Kontakt oss</a>
                <!-- Om brukeren ikke er administrator eller redaktør, vis link for søknad til å bli redaktør -->
                <?php if (isset($_SESSION['brukernavn']) and $_SESSION['brukertype'] == "3") { ?> | <a href="soknad.php">Søknad om å bli redaktør</a><?php } ?>
            </p>
        </footer>
    </body>

    <!-- Denne siden er utviklet av Robin Kleppang, siste gang endret xx.xx.xxxx -->
    <!-- Denne siden er kontrollert av Glenn Petter Pettersen, siste gang xx.xx.xxxx -->

</html>