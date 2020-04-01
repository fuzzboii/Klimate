<?php
session_start();

//-------------------------------//
// Innstillinger, faste variable //
//-------------------------------//
include("inkluderes/innstillinger.php");


// Browser må validere cache med server før cached kopi kan benyttes
// Dette gjør at man kan gå frem og tilbake i innboksen uten at man får ERR_CACHE_MISS
header("Cache-Control: no cache");


// Forsikrer seg om kun tilgang for administrator
if (!isset($_SESSION['idbruker'])) {
    // En utlogget bruker har forsøkt å nå adminpanelet
    header("Location: default.php?error=1");
} else if ($_SESSION['brukertype'] != '1') {
    // En innlogget bruker som ikke er administrator har forsøkt å åpne adminpanelet, loggfører dette
    $leggTilMisbrukQ = "insert into misbruk(tekst, bruker) values('Oppdaget misbruk, forsøkte nå adminpanel', :bruker)";
    $leggTilMisbrukSTMT = $db -> prepare($leggTilMisbrukQ);
    $leggTilMisbrukSTMT -> bindparam(":bruker", $_SESSION['idbruker']);
    $leggTilMisbrukSTMT -> execute();
    header("Location: default.php?error=6");
}
$input_brukernavn = "";
$input_epost = "";
if (isset($_SESSION['input_brukernavn'])) {
    $input_brukernavn = $_SESSION['input_brukernavn'];
    $input_epost = $_SESSION['input_epost'];
    unset($_SESSION['input_brukernavn']);
    unset($_SESSION['input_epost']);
}

if (isset($_POST['subRegistrering'])) {
    $_SESSION['input_brukernavn'] = $_POST['brukernavn'];
    $_SESSION['input_epost'] = $_POST['epost'];
    // Tester på om passordene er like
    if ($_POST['passord'] == $_POST['passord2']) {
        // Tester på om bruker har fyllt ut alle de obligatoriske feltene
        if ($_POST['brukernavn'] != "" && $_POST['epost'] != "") {
            // Tester på om en gyldig epost ("NAVN@NAVN.DOMENE") er oppgitt
            if (filter_var($_POST["epost"], FILTER_VALIDATE_EMAIL)) {
                try {

                    $br = $_POST['brukernavn'];
                    $pw = $_POST['passord'];
                    if($_POST['brukertype'] != "4") {
                        $btype = $_POST['brukertype'];
                    } else {
                        $btype = "3";
                    }

                    // Validering av passordstyrke, server validering
                    $storebokstaver = preg_match('@[A-Z]@', $pw);
                    $smaabokstaver = preg_match('@[a-z]@', $pw);
                    $nummer = preg_match('@[0-9]@', $pw);
                    // Denne er for spesielle symboler, ikke i bruk for øyeblikket
                    // $spesielleB = preg_match('@[^\w]@', $pw);

                    if ($pw == "") {
                        // Ikke noe passord skrevet
                        header("Location: administrator.php?error=3");
                    } else if (!$storebokstaver || !$smaabokstaver || !$nummer /*|| !$spesielleB*/ || strlen($pw) < 8) {
                        // Ikke tilstrekkelig passord skrevet
                        header("Location: administrator.php?error=4");
                    } else {
                        // Sjekker om brukernavn er opptatt (Brukes så lenge brukernavn ikke er satt til UNIQUE i db)
                        $lbr = strtolower($_POST['brukernavn']);
                        $sjekkbnavn = "select lower(brukernavn) as brukernavn from bruker where lower(brukernavn)='" . $lbr . "'";
                        $sjekket = $db->prepare($sjekkbnavn);
                        $sjekket->execute();
                        $testbnavn = $sjekket->fetch(PDO::FETCH_ASSOC);

                        // Hvis resultatet over er likt det bruker har oppgitt som brukernavn stopper vi og advarer bruker om at brukernavnet er allerede tatt
                        if (!isset($testbnavn['brukernavn']) || $testbnavn['brukernavn'] != $lbr) {
                            // OK, vi forsøker å registrere bruker
                            $epost = $_POST['epost'];

                            // Salter passorder
                            $kombinert = $salt . $pw;
                            // Krypterer saltet passord
                            $spw = sha1($kombinert);
                            $sql = "insert into bruker(brukernavn, passord, epost, brukertype) VALUES('" . $br . "', '" . $spw . "', '" . $epost . "', $btype)";


                            // Prepared statement for å beskytte mot SQL injection
                            $stmt = $db->prepare($sql);

                            $vellykket = $stmt->execute(); 
                            
                            // Alt gikk OK, sender til logginn med melding til bruker
                            if ($vellykket) {
                                // Fjerner session variable for brukerinput om ingen feil oppstår
                                unset($_SESSION['input_brukernavn']);
                                unset($_SESSION['input_epost']);

                                // Sjekker på om bruker har registrert preferanser
                                $sjekkPrefQ = "select idpreferanse from preferanse where bruker = " . $_SESSION['idbruker'];
                                $sjekkPrefSTMT = $db->prepare($sjekkPrefQ);
                                $sjekkPrefSTMT->execute();
                                $resPref = $sjekkPrefSTMT->fetch(PDO::FETCH_ASSOC); 

                                // Bruker har ikke preferanser, oppretter de
                                // Variabelen $personvern kommer fra innstillinger
                                if(!$resPref) {
                                    $opprettPrefQ = "insert into preferanse(visfnavn, visenavn, visepost, visinteresser, visbeskrivelse, vistelefonnummer, bruker) values('" . 
                                                        $personvern[0] . "', '" . $personvern[1] . "', '" . $personvern[2] . "', '" . $personvern[3] . "', '" . $personvern[4] . "', '" . $personvern[5] . "', " .
                                                            $_SESSION['idbruker'] . ")";

                                    $opprettPrefSTMT = $db->prepare($opprettPrefQ);
                                    $opprettPrefSTMT->execute();
                                }

                                header("location: administrator.php?vellykket=1");
                            }
                        } else {
                            // Brukernavnet er tatt
                            header("location: administrator.php?error=1");
                        }
                    }
                }
                catch (PDOException $ex) {
                    if ($ex->getCode() == 23000) {
                        // 23000, Duplikat, tenkes brukt til brukernavn da det ønskes å være satt UNIQUE i db
                        header("location: administrator.php?error=1");
                    } else if ($ex->getCode() == '42S22') {
                        // 42S22, Kolonne eksisterer ikke
                        header("location: administrator.php?error=5");
                    }
                } 
            } else {
                // Feilmelding 7, bruker har oppgitt en ugyldig epost
                header("location: administrator.php?error=7");
            }
        } else {
            // Feilmelding 6, bruker har ikke skrevet noe i ett av de obligatoriske feltene
            header("location: administrator.php?error=6");
        }
    } else {
        // Feilmelding 2 = passord ikke like
        header("location: administrator.php?error=2");
    }
}

if(isset($_POST['slettregel'])) {
    $slettregelQ = "delete from regel where idregel = :regelen";
    $slettregelSTMT = $db -> prepare($slettregelQ);
    $slettregelSTMT->bindParam(':regelen', $_POST['slettregel']);
    $slettregelSTMT->execute();

    $slettet = $slettregelSTMT->rowCount();

    if($slettet == 0) {
        header("location: administrator.php?error=8");
    } else {
        header("location: administrator.php");
    }
}

if(isset($_POST['advaring'])) {
    if($_POST['advaring'] != "") {
        if($_POST['advartbruker'] != "") {
            $_POST['bruker'] = $_POST['advartbruker'];

            // Sjekker om brukeren er av type administrator, tillater ikke administratorer å utføre handling på en administrator
            $sjekkAdminQ = "select idbruker, brukertype from bruker where idbruker = :bruker and brukertype = 3";
            $sjekkAdminSTMT = $db -> prepare($sjekkAdminQ);
            $sjekkAdminSTMT -> bindparam(":bruker", $_POST['advartbruker']);
            $sjekkAdminSTMT -> execute();

            if(!$sjekkAdminSTMT) {
                // Bruker er ikke administrator
                $advarBrukerQ = "insert into advarsel(advarseltekst, bruker, administrator) values(:tekst, :bruker, :admin)";
                $advarBrukerSTMT = $db -> prepare($advarBrukerQ);
                $advarBrukerSTMT -> bindparam(":tekst", $_POST['advaring']);
                $advarBrukerSTMT -> bindparam(":bruker", $_POST['advartbruker']);
                $advarBrukerSTMT -> bindparam(":admin", $_SESSION['idbruker']);
                $advarBrukerSTMT -> execute();

                if($advarBrukerSTMT) {
                    $admin_melding = "Bruker advart";
                } else {
                    $admin_melding = "Feil oppsto ved advaring av bruker";
                }
            } else {
                $admin_melding = "Du kan ikke advare en administrator";
            }
        }
    }
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
        <title>Adminpanel</title>
        <!-- Henter inn ekstern stylesheet -->
        <link rel="stylesheet" type="text/css" href="stylesheet.css">
        <!-- Henter inn favicon, bildet som dukker opp i fanene i nettleseren -->
        <link rel='icon' href='bilder/favicon.png' type='image/x-icon'>
        <!-- Henter inn JavaScript -->
        <script language="JavaScript" src="javascript.js"> </script>
    </head>

    <body id="admin_body">
        <?php include("inkluderes/navmeny.php") ?>

        <!-- For å kunne lukke hamburgermenyen ved å kun trykke på et sted i vinduet må lukkHamburgerMeny() funksjonen ligge i deler av HTML-koden -->
        <!-- Kan ikke legge denne direkte i body -->
        <header id="admin_header" onclick="lukkHamburgerMeny()">
            <!-- Overskrift på siden -->
            <h1 id="admin_overskrift">Adminpanel</h1>
            <img id="admin_hovedmeny_ikon" src="bilder/pilnIkon.png" onclick="admHovedmeny()">
        </header>
        <main id="admin_main" onclick="lukkHamburgerMeny()">

            <form method="POST" id="admin_form" action="administrator.php">
            </form>

            <form method="POST" id="rapport_form" action="rapport.php">
            </form>

            <section id="admin_hovedmeny">
                <button name="oversikt" form="admin_form">Oversikt</button>
                <button id="admin_adm_knapp" onclick="admMeny()">Administrering</button>
                <section id="admin_adm_delmeny" style="display: none;">
                    <button name="administrering" form="admin_form" value="Alle brukere">Alle brukere</button>
                    <button name="administrering" form="admin_form" value="Advarsler">Advarsler</button>
                    <button name="administrering" form="admin_form" value="Eksklusjoner">Eksklusjoner</button>
                    <button name="administrering" form="admin_form" value="Misbruk">Misbruk</button>
                    <button name="administrering" form="admin_form" value="Administratorer">Administratorer</button>
                </section>
                <img src="bilder/rapportIkon.png" id="admin_rap_ikon">
                <button id="admin_rap_knapp" onclick="rapMeny()">Rapporter</button>
                <section id="admin_rap_delmeny" style="display: none;">
                    <button name="rapport" form="rapport_form" value="Alle brukere">Alle brukere</button>
                    <button name="rapport" form="rapport_form" value="Spesifikk bruker">Spesifikk bruker</button>
                    <button name="rapport" form="rapport_form" value="Eksklusjoner">Eksklusjoner</button>
                    <button name="rapport" form="rapport_form" value="Advarsler">Advarsler</button>
                </section>
                <button name="nybruker" form="admin_form">Opprett ny bruker</button>
                <button name="nyregel" form="admin_form">Opprett ny regel</button>
            </section>

            <?php 
            if(isset($_POST['administrering'])) { 
                // Administrering ?>
                <h2 id="admin_underskrift"><?php echo($_POST['administrering']); ?></h2>
            
                <form method="POST" id="bruker_form" action="administrator.php">
                    <input type="hidden" id="bruker_form_verdi" name="bruker" value="">
                </form>

                <input type="text" id="admin_sok" onkeyup="adminpanelSok()" placeholder="Søk etter navn..">

                <?php if($_POST['administrering'] == "Alle brukere") {
                    $hentBrukereQ = "select idbruker, brukernavn, fnavn, enavn, epost, brukertype.brukertypenavn as brukertypenavn from bruker, brukertype where bruker.brukertype = brukertype.idbrukertype order by brukernavn";
                    $hentBrukereSTMT = $db->prepare($hentBrukereQ);
                    $hentBrukereSTMT -> execute();
                    $brukere = $hentBrukereSTMT -> fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <table id="admin_allebrukere_table">
                        <thead>
                            <tr>
                                <th id="admin_allebrukere_bruker">BRUKERNAVN</th>
                                <th id="admin_allebrukere_info">INFO</th>
                                <th id="admin_allebrukere_type">TYPE</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for($i = 0; $i < count($brukere); $i++) { 
                                if($i < 8) { ?>
                                    <tr class="admin_allebrukere_rad" title="Vis denne brukeren" onclick="aapneBruker(<?php echo($brukere[$i]['idbruker']) ?>)">
                                        <td class="admin_allebrukere_allebruker"><?php echo($brukere[$i]['brukernavn'])?></td>
                                        <td class="admin_allebrukere_allenavn">Navn: <?php if(isset($brukere[$i]['fnavn'])) {echo($brukere[$i]['fnavn'] . " "); if(isset($brukere[$i]['enavn'])) {echo($brukere[$i]['enavn']);}} else {echo("Ikke oppgitt");} ?></td>
                                        <td class="admin_allebrukere_alleepost">Epost: <?php if(isset($brukere[$i]['epost'])) {echo($brukere[$i]['epost']);} else {echo("Ikke oppgitt");}?></td>
                                        <td class="admin_allebrukere_alletype"><?php if(isset($brukere[$i]['brukertypenavn'])) {echo($brukere[$i]['brukertypenavn']);}?></td>
                                    </tr>
                                <?php } else { ?>
                                    <tr class="admin_allebrukere_rad" style="display: none" title="Vis denne brukeren" onclick="aapneBruker(<?php echo($brukere[$i]['idbruker']) ?>)">
                                        <td class="admin_allebrukere_allebruker"><?php echo($brukere[$i]['brukernavn'])?></td>
                                        <td class="admin_allebrukere_allenavn">Navn: <?php if(isset($brukere[$i]['fnavn'])) {echo($brukere[$i]['fnavn'] . " "); if(isset($brukere[$i]['enavn'])) {echo($brukere[$i]['enavn']);}} else {echo("Ikke oppgitt");} ?></td>
                                        <td class="admin_allebrukere_alleepost">Epost: <?php if(isset($brukere[$i]['epost'])) {echo($brukere[$i]['epost']);} else {echo("Ikke oppgitt");}?></td>
                                        <td class="admin_allebrukere_alletype"><?php if(isset($brukere[$i]['brukertypenavn'])) {echo($brukere[$i]['brukertypenavn']);}?></td>
                                    </tr>
                                <?php }
                            } 
                            if($i > 8) { ?>
                                <button id="admin_allebrukere_knapp" onclick="visFlereBrukere()">Vis flere</button>
                            <?php } ?>
                        </tbody>
                    </table>
                <?php } ?>
                <button id="admin_administrering_tiloversikt" name="oversikt" form="admin_form">Til oversikten</button>
            <?php } else if(isset($_POST['nybruker'])) { 
                // Ny bruker (Evt endring?) ?>
                <h2 id="admin_underskrift">Opprett en bruker</h2>
                <form method="POST" action="administrator.php" class="innloggForm">
                    <section class="inputBoks">
                        <img class="icon" src="bilder/brukerIkon.png" alt="Brukerikon">
                        <input type="text" class="RegInnFelt" name="brukernavn" value="<?php echo($input_brukernavn) ?>" placeholder="Skriv inn brukernavn" required title="Skriv inn ett brukernavn" autofocus>
                    </section>
                    <section class="inputBoks">
                        <img class="icon" src="bilder/emailIkon.png" alt="Epostikon">
                        <input type="email" class="RegInnFelt" name="epost" value="<?php echo($input_epost) ?>" placeholder="Skriv inn e-postadresse" required title="Skriv inn en gyldig epostadresse">
                    </section>
                    <section class="inputBoks">
                        <img class="icon" src="bilder/pwIkon.png" alt="Passordikon">
                        <input type="password" class="RegInnFeltPW" name="passord" value="" placeholder="Skriv inn passord" required pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" title="Minimum 8 tegn, 1 liten og 1 stor bokstav">
                    </section>
                    <section class="inputBoks">
                        <img class="icon" src="bilder/pwIkon.png" alt="Passordikon">
                        <input type="password" class="RegInnFeltPW" name="passord2" value="" placeholder="Bekreft passord" required pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" title="Minimum 8 tegn, 1 liten og 1 stor bokstav">
                    </section>
                    <section>
                        <select id="brukertypeValg" name="brukertype">
                            <option value="3">Ordinær bruker</option>
                            <option value="2">Redaktør</option>
                            <option value="1">Administrator</option>
                        </select>
                    </section>
                    <input style="margin-bottom: 1em; margin-top: 1em;" type="checkbox" onclick="visPassordReg()">Vis passord</input>
                <input type="submit" name="subRegistrering" class="RegInnFelt_knappRegistrer" value="Legg til brukeren">
            </form>
            <button id="admin_tiloversikt" name="oversikt" form="admin_form">Til oversikten</button>
            <?php } else if(isset($_POST['nyregel'])) {
                // Ny regel ?>
                <button id="admin_tiloversikt" name="oversikt" form="admin_form">Til oversikten</button>
            <?php } else if(isset($_POST['bruker'])) {
                // Visning av bruker 
                $hentBrukerinfoQ = "select brukernavn, fnavn, enavn, epost, telefonnummer from bruker where idbruker = :bruker";
                $hentBrukerinfoSTMT = $db -> prepare($hentBrukerinfoQ);
                $hentBrukerinfoSTMT -> bindparam(":bruker", $_POST['bruker']);
                $hentBrukerinfoSTMT -> execute();
                $brukerinfo = $hentBrukerinfoSTMT -> fetch(PDO::FETCH_ASSOC); 

                $harFornavn = false;
                $harEtternavn = false;
                $harEpost = false;
                $harTlf = false;

                if(isset($brukerinfo['fnavn']) && preg_match("/\S/", $brukerinfo['fnavn']) == 1) {
                    $harFornavn = true;
                }
                if(isset($brukerinfo['enavn']) && preg_match("/\S/", $brukerinfo['enavn']) == 1) {
                    $harEtternavn = true;
                }
                if(isset($brukerinfo['epost']) && preg_match("/\S/", $brukerinfo['epost']) == 1) {
                    $harEpost = true;
                }
                if(isset($brukerinfo['telefonnummer']) && preg_match("/\S/", $brukerinfo['telefonnummer']) == 1) {
                    $harTlf = true;
                }

                // Henter misbruk
                $hentMisbrukQ = "select tekst from misbruk where bruker = :bruker";
                $hentMisbrukSTMT = $db -> prepare($hentMisbrukQ);
                $hentMisbrukSTMT -> bindparam(":bruker", $_POST['bruker']);
                $hentMisbrukSTMT -> execute();
                $misbruk = $hentMisbrukSTMT -> fetchAll(PDO::FETCH_ASSOC);

                // Henter advarsler
                $hentAdvarslerQ = "select advarseltekst, brukernavn from advarsel, bruker where bruker = :bruker and advarsel.administrator = bruker.idbruker";
                $hentAdvarslerSTMT = $db -> prepare($hentAdvarslerQ);
                $hentAdvarslerSTMT -> bindparam(":bruker", $_POST['bruker']);
                $hentAdvarslerSTMT -> execute();
                $advarsler = $hentAdvarslerSTMT -> fetchAll(PDO::FETCH_ASSOC);

                // Henter eksklusjoner
                $hentEksklusjonerQ = "select grunnlag, brukernavn, datofra, datotil from eksklusjon, bruker where bruker = :bruker and eksklusjon.administrator = bruker.idbruker";
                $hentEksklusjonerSTMT = $db -> prepare($hentEksklusjonerQ);
                $hentEksklusjonerSTMT -> bindparam(":bruker", $_POST['bruker']);
                $hentEksklusjonerSTMT -> execute();
                $eksklusjoner = $hentEksklusjonerSTMT -> fetchAll(PDO::FETCH_ASSOC);

                if(isset($brukerinfo)) { ?>
                    <section id="admin_brukerinfo">
                        <figure>
                            <?php 
                            $hentBrukerbildeQ = "select hvor from bilder, brukerbilde where bilder.idbilder = brukerbilde.bilde and brukerbilde.bruker = :bruker";
                            $hentBrukerbildeSTMT = $db -> prepare($hentBrukerbildeQ);
                            $hentBrukerbildeSTMT -> bindparam(":bruker", $_POST['bruker']);
                            $hentBrukerbildeSTMT -> execute();
                            $brukerbilde = $hentBrukerbildeSTMT -> fetch(PDO::FETCH_ASSOC);

                            if ($brukerbilde) {
                                $testPaa = $brukerbilde['hvor'];
                                // Tester på om filen faktisk finnes
                                if(file_exists("$lagringsplass/$testPaa")) {
                                    // Profilbilde som resultat av spørring
                                    if(file_exists("$lagringsplass/" . "thumb_" . $testPaa)) {
                                        // Hvis vi finner et miniatyrbilde bruker vi det ?>
                                        <img id="admin_brukerbilde" src="bilder/opplastet/thumb_<?php echo($brukerbilde['hvor']) ?>" alt="Profilbilde til <?php echo($brukerinfo['brukernavn']) ?>">
                                    <?php } else { ?>
                                        <img id="admin_brukerbilde" src="bilder/opplastet/<?php echo($brukerbilde['hvor']) ?>" alt="Profilbilde til <?php echo($brukerinfo['brukernavn']) ?>">
                                    <?php } ?>
                                <?php } else { ?>
                                    <img id="admin_brukerbilde" src="bilder/profil.png" alt="Standard profilbilde">
                                <?php } ?>
                            <?php } else { ?>
                                <img id="admin_brukerbilde" src="bilder/profil.png" alt="Standard profilbilde">
                            <?php } ?>
                        </figure>
                        <p id="admin_brukernavn"><?php echo($brukerinfo['brukernavn']) ?></p>
                        <?php if($harFornavn) {echo("<p>Navn: " . $brukerinfo['fnavn']);} if($harEtternavn) {echo(" " . $brukerinfo['enavn']);} if(!$harFornavn && !$harEtternavn) {echo("<p id='admin_ikkeoppgitt'>Navn: Ikke oppgitt");} ?></p>
                        <?php if($harEpost) {echo("<p>Epost: " . $brukerinfo['epost']);} else {echo("<p id='admin_ikkeoppgitt'>Epost: Ikke oppgitt");} ?></p>
                        <?php if($harTlf) {echo("<p>Telefon: " . $brukerinfo['telefonnummer']);} else {echo("<p id='admin_ikkeoppgitt'>Telefon: Ikke oppgitt");} ?></p>
                    </section>
                    <section id="admin_handlinger">
                        <p class="admin_handlingvalg" id="admin_aktivhandling" onclick="byttHandling('Advar')">Advar</p>
                        <p class="admin_handlingvalg" onclick="byttHandling('Ekskluder')">Ekskluder</p>
                        <form id="admin_handling_form" method="POST" action="administrator.php">
                            <p id="admin_handling">Advar bruker</p>
                            <input id="admin_handling_bruker" type="hidden" name="advartbruker" value="<?php echo($_POST['bruker']) ?>">
                            <textarea id="admin_handling_tekst" name="advaring" placeholder="Skriv inn grunnlaget" title="Hva brukeren har gjort feil" required></textarea>
                            <p id="admin_handling_lengde">Lengde, la være for permanent</p>
                            <input id="admin_handling_dato" type="date" name="datotil">
                            <input onclick="sjekkAdminHandling()" id="admin_handling_submit" type="button" value="Advar bruker">
                        </form>
                    </section>
                    <section id="admin_allemisbruk">
                        <p id="admin_allemisbruk_tittel">Misbruk<p>
                        <table id="admin_misbruk_table">
                            <?php if(count($misbruk) != 0) { ?>
                                <thead>
                                    <tr>
                                        <th id="admin_misbruk_grunnlag">GRUNNLAG</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php for($i = 0; $i < count($misbruk); $i++) { ?>
                                        <tr class="admin_misbruk_rad">
                                            <td class="admin_misbruk_allegrunnlag"><?php echo($misbruk[$i]['tekst'])?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            <?php } else { ?>
                                <p id="admin_misbruk_ikkeregistrert">Ikke noe misbruk registrert</p>
                            <?php } ?>
                        </table>
                    </section>
                    <section id="admin_alleadvarsler">
                        <p id="admin_alleadvarsler_tittel">Advarsler<p>
                        <table id="admin_alleadvarsler_table">
                            <?php if(count($advarsler) != 0) { ?>
                                <thead>
                                    <tr>
                                        <th id="admin_alleadvarsler_grunnlag">GRUNNLAG</th>
                                        <th id="admin_alleadvarsler_administrator">ADVART AV</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php for($i = 0; $i < count($advarsler); $i++) { ?>
                                        <tr class="admin_alleadvarsler_rad">
                                            <td class="admin_alleadvarsler_allegrunnlag"><?php echo($advarsler[$i]['advarseltekst'])?></td>
                                            <td class="admin_alleadvarsler_alleadmin"><?php echo($advarsler[$i]['brukernavn'])?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            <?php } else { ?>
                                <p id="admin_alleadvarsler_ikkeregistrert">Ikke noen advarsler registrert</p>
                            <?php } ?>
                        </table>
                    </section>
                    <section id="admin_alleeksklusjoner">
                        <p id="admin_alleeksklusjoner_tittel">Eksklusjoner<p>
                        <table id="admin_alleeksklusjoner_table">
                            <?php if(count($eksklusjoner) != 0) { ?>
                                <thead>
                                    <tr>
                                        <th id="admin_alleeksklusjoner_grunnlag">GRUNNLAG</th>
                                        <th id="admin_alleeksklusjoner_administrator">EKSKLUDERT AV</th>
                                        <th id="admin_alleeksklusjoner_datofra">DATO FRA</th>
                                        <th id="admin_alleeksklusjoner_datotil">DATO TIL</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php for($i = 0; $i < count($eksklusjoner); $i++) { ?>
                                        <tr class="admin_alleeksklusjoner_rad">
                                            <td class="admin_alleeksklusjoner_allegrunnlag"><?php echo($eksklusjoner[$i]['grunnlag'])?></td>
                                            <td class="admin_alleeksklusjoner_alleadmin"><?php echo($eksklusjoner[$i]['brukernavn'])?></td>
                                            <td class="admin_alleeksklusjoner_alledatofra"><?php echo(date_format(date_create($eksklusjoner[$i]['datofra']), "j M H:i")) ?></td>
                                            <td class="admin_alleeksklusjoner_alledatotil"><?php echo(date_format(date_create($eksklusjoner[$i]['datotil']), "j M H:i")) ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            <?php } else { ?>
                                <p id="admin_alleeksklusjoner_ikkeregistrert">Ikke noen eksklusjoner registrert</p>
                            <?php } ?>
                        </table>
                    </section>
                <?php } else { ?>
                    <h2 id="admin_underskrift">Kunne ikke vise brukeren</h2>
                <?php } ?>
            <?php } else {
                // Selve oversikten, default view ?>
                <h2 id="admin_underskrift">Oversikten</h2>

                <form method="POST" id="admin_form_advarsel" action="administrator.php">
                    <input type="hidden"  name="administrering" value="Advarsler">
                    <section onclick="aapneAdmin('admin_form_advarsel')" id="admin_advarsler">
                        <p id="admin_advarsler_tittel">Advarsler</p>
                        <?php 
                        $hentAntallQ = "select count(idadvarsel) as antall from advarsel";
                        $hentAntallSTMT = $db -> prepare($hentAntallQ);
                        $hentAntallSTMT->execute();
                        $antalladvarsler = $hentAntallSTMT->fetch(PDO::FETCH_ASSOC); 
                        ?>
                        <p id="admin_advarsler_antall"><?php echo($antalladvarsler['antall']) ?></p>
                    </section>
                </form>
                <form method="POST" id="admin_form_misbruk" action="administrator.php">
                    <input type="hidden"  name="administrering" value="Misbruk">
                    <section onclick="aapneAdmin('admin_form_misbruk')" id="admin_misbruk">
                        <p id="admin_misbruk_tittel">Misbruk</p>
                        <?php 
                        $hentAntallQ = "select count(idmisbruk) as antall from misbruk";
                        $hentAntallSTMT = $db -> prepare($hentAntallQ);
                        $hentAntallSTMT->execute();
                        $antallmisbruk = $hentAntallSTMT->fetch(PDO::FETCH_ASSOC); 
                        ?>
                        <p id="admin_misbruk_antall"><?php echo($antallmisbruk['antall']) ?></p>
                    </section>
                </form>
                <form method="POST" id="admin_form_eksklusjoner" action="administrator.php">
                    <input type="hidden"  name="administrering" value="Eksklusjoner">
                    <section onclick="aapneAdmin('admin_form_eksklusjoner')" id="admin_eksklusjoner">
                        <p id="admin_eksklusjoner_tittel">Eksklusjoner</p>
                        <?php 
                        $hentAntallQ = "select count(ideksklusjon) as antall from eksklusjon";
                        $hentAntallSTMT = $db -> prepare($hentAntallQ);
                        $hentAntallSTMT->execute();
                        $antalleksklusjoner = $hentAntallSTMT->fetch(PDO::FETCH_ASSOC); 
                        ?>
                        <p id="admin_eksklusjoner_antall"><?php echo($antalleksklusjoner['antall']) ?></p>
                    </section>
                </form>
                <section id="admin_brukere">
                    <p id="admin_brukere_tittel">Antall brukere</p>
                    <?php 
                    $hentAntallQ = "select count(idbruker) as antall from bruker";
                    $hentAntallSTMT = $db -> prepare($hentAntallQ);
                    $hentAntallSTMT->execute();
                    $antallbrukere = $hentAntallSTMT->fetch(PDO::FETCH_ASSOC); 
                    ?>
                    <p id="admin_brukere_antall"><?php echo($antallbrukere['antall'])?></p>
                </section>
                <button id="admin_regler_knapp" onclick="regMeny()">Reglement</button>
                <?php 
                $hentReglerQ = "select idregel, regeltekst, brukernavn from regel, bruker where regel.idbruker = bruker.idbruker";
                $hentReglerSTMT = $db->prepare($hentReglerQ);
                $hentReglerSTMT -> execute();
                $regler = $hentReglerSTMT -> fetchAll(PDO::FETCH_ASSOC);
                ?>
                <table id="admin_regler_table">
                    <thead>
                        <tr>
                            <th>Regel</th>
                            <th id="admin_regler_oppr">Opprettet av</th>
                            <th id="admin_regler_slett"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for($i = 0; $i < count($regler); $i++) {
                            if(isset($regler[$i]['regeltekst'])) { ?>
                                <tr>
                                    <td><?php echo($regler[$i]['regeltekst'])?></td>
                                    <td><?php echo($regler[$i]['brukernavn'])?></td>
                                    <td><button class="admin_regler_slett_knapp" name="slettregel" form="admin_form" value="<?php echo($regler[$i]['idregel'])?>">Slett</button></td>
                                </tr>
                            <?php }
                        } ?>
                    </tbody>
                </table>
            <?php } ?>

            <!-- Håndtering av feilmeldinger -->

            <section id="mldFEIL_boks" onclick="lukkMelding('mldFEIL_boks')" <?php if(isset($admin_melding)) { ?> style="display: block" <?php } ?>>
                <section id="mldFEIL_innhold">
                    <p id="mldFEIL"><?php if(isset($admin_melding)) { echo($admin_melding); unset($admin_melding); } ?></p>  
                    <!-- Denne gjør ikke noe, men er ikke utelukkende åpenbart at man kan trykke hvor som helst -->
                    <button id="mldFEIL_knapp">Lukk</button>
                </section>  
            </section>
            <?php 
            if(isset($_GET['vellykket']) && $_GET['vellykket'] == 1) { ?>
                <p id="mldOK">Brukeren er opprettet</p>
            <?php } ?>

            <!-- Sender brukeren tilbake til forsiden
            <button onClick="location.href='default.php'" name="submit" class="lenke_knapp">Tilbake til forside</button> -->
        </main>
        <?php include("inkluderes/footer.php") ?>
    </body>

<!-- Denne siden er utviklet av Glenn Petter Pettersen og Robin Kleppang, siste gang endret 06.03.2020 -->
<!-- Denne siden er kontrollert av , siste gang 06.03.2020 -->
</html>