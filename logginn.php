<?php
session_start();

//-------------------------------//
// Innstillinger, faste variable //
//-------------------------------//
include("inkluderes/innstillinger.php");


// Sjekker om bruker er i en gyldig session, sender tilbake til hovedsiden hvis så
if (isset($_SESSION['idbruker'])) {
    header("Location: default.php?error=2");
}

// Setter tidssonen, dette er for at One.com domenet skal fungere, brukes i sjekk mot innloggingsforsøk
date_default_timezone_set("Europe/Oslo");

// Enkel test som gjør det mulig å beholde brukerinput etter siden er lastet på nytt (Form submit)
$input_brukernavn = "";
if (isset($_SESSION['input_brukernavn'])) {
    $input_brukernavn = $_SESSION['input_brukernavn'];
    unset($_SESSION['input_brukernavn']);
}


if (isset($_POST['submit'])) {
    $_SESSION['input_brukernavn'] = $_POST['brukernavn'];
    // Ventetiden når en bruker er lukket ute
    $ventetid = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s") . "- 180 seconds"));

    // Hvis feiltelleren ikke eksisterer har ikke bruker forsøkt å logge inn hittils, oppretter da disse med standardverdier
    if (!isset($_SESSION['feilteller'])) {
        $_SESSION['feilteller'] = 0;
        $_SESSION['sistFeilet'] = date("Y-m-d H:i:s");
    }

    // Hvis det har gått 3 minutter mellom siste gang bruker feiler innlogging, tøm telleren
    if ($_SESSION['sistFeilet'] <= $ventetid) {
        $_SESSION['feilteller'] = 0;
    }

    // Sjekker først om bruker har feilet innlogging for mange ganger
    if ($_SESSION['feilteller'] < 5) {

        $br = $_POST['brukernavn'];
        $lbr = strtolower($_POST['brukernavn']);
        $pw = $_POST['passord'];
        $kombinert = $salt . $pw;
        // Krypterer passorder med salting
        $spw = sha1($kombinert);

        $sql = "select * from bruker where lower(brukernavn)='" . $lbr . "' and passord='" . $spw . "'";
        // Prepared statement for å beskytte mot SQL injection
        $stmt = $db->prepare($sql);

        $stmt->execute();

        $resultat = $stmt->fetch(PDO::FETCH_ASSOC);

        if (strtolower($resultat['brukernavn']) == $lbr and $resultat['passord'] == $spw) {
            if ($resultat['brukertype'] == 4) {
                $oppdaterBrukertypeQ = "update bruker set brukertype = 3 where idbruker = " . $resultat['idbruker'];
                $oppdaterBrukertypeSTMT = $db->prepare($oppdaterBrukertypeQ);
                $oppdaterBrukertypeSTMT->execute();
                
                $antallEndret = $oppdaterBrukertypeSTMT->rowCount();

                if ($antallEndret == 0) {
                    header("Location: logginn.php?error=1");
                } else {
                    $resultat['brukertype'] = 3;
                }
            } 

            $_SESSION['idbruker'] = $resultat['idbruker'];
            $_SESSION['brukernavn'] = $resultat['brukernavn'];
            $_SESSION['fornavn'] = $resultat['fnavn'];
            $_SESSION['etternavn'] = $resultat['enavn'];
            $_SESSION['epost'] = $resultat['epost'];
            $_SESSION['telefonnummer'] = $resultat['telefonnummer'];
            $_SESSION['brukertype'] = $resultat['brukertype'];
            
            $_SESSION['feilteller'] = 0;

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

            // Fjerner session variable for brukerinput om ingen feil oppstår
            unset($_SESSION['input_brukernavn']);

            header("Location: backend.php");
        } else {    
            // Øker teller for feilet innlogging med 1
            $_SESSION['feilteller']++;
            $_SESSION['sistFeilet'] = date("Y-m-d H:i:s");
            
            header("Location: logginn.php?error=1");
        }
    } else {
        // Bruker har feilet for mange ganger, gir tilbakemelding til bruker
        header("Location: logginn.php?error=2");
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
        <title>Innlogging</title>
        <!-- Henter inn ekstern stylesheet -->
        <link rel="stylesheet" type="text/css" href="stylesheet.css">
        <!-- Henter inn favicon, bildet som dukker opp i fanene i nettleseren -->
        <link rel='icon' href='bilder/favicon.png' type='image/x-icon'>
        <!-- Henter inn JavaScript -->
        <script language="JavaScript" src="javascript.js"> </script>
    </head>

    <body>
        <article class="innhold">
            <?php include("inkluderes/navmeny.php") ?>
            
            <main id="toppMain" onclick="lukkHamburgerMeny()">
                <!-- Form brukes til autentisering av bruker, bruker type="password" for å ikke vise innholdet brukeren skriver -->
                <form method="POST" action="logginn.php" class="innloggForm">
                    <section class="inputBoks">
                        <img class="icon" src="bilder/brukerIkon.png" alt="Brukerikon"> <!-- Ikonet for bruker -->
                        <input type="text" class="RegInnFelt" name="brukernavn" value="<?php echo($input_brukernavn) ?>" placeholder="Skriv inn brukernavn" required autofocus>
                    </section>
                    <section class="inputBoks">
                        <img class="icon" src="bilder/pwIkon.png" alt="Passordikon"> <!-- Ikonet for passord -->
                        <input type="password" class="RegInnFeltPW" name="passord" value="" placeholder="Skriv inn passord" required>
                    </section>
                    <input style="margin-bottom: 1em;" type="checkbox" onclick="visPassordReg()">Vis passord</input>
                    <!-- Meldinger til bruker -->
                    <?php if(isset($_GET['error']) && $_GET['error'] == 1){ ?>
                        <p id="mldFEIL">Sjekk brukernavn og passord</p>    
                    
                    <?php } else if(isset($_GET['error']) && $_GET['error'] == 2){ ?>
                        <p id="mldFEIL">Du har feilet innlogging for mange ganger, vennligst vent</p>
                        
                    <?php } else if(isset($_GET['error']) && $_GET['error'] == 3){ ?>
                        <p id="mldFEIL">Kunne ikke registrere bruker, vennligst kontakt administrator om dette problemet fortsetter</p>
                    
                    <?php } else if(isset($_GET['vellykket']) && $_GET['vellykket'] == 1){ ?>
                        <p id="mldOK">Bruker opprettet, vennligst logg inn</p>    
                    
                    <?php } else if(isset($_GET['vellykket']) && $_GET['vellykket'] == 2){ ?>
                        <p id="mldOK">Passord endret</p>
                    <?php } ?>

                    <input type="submit" name="submit" class="RegInnFelt_knappLogginn" value="Logg inn">   
                </form>

                <!-- Sender brukeren tilbake til forsiden -->
                <button onClick="location.href='glemt_passord.php'" class="lenke_knapp">Glemt passord?</button>
                <button onClick="location.href='default.php'" class="lenke_knapp">Tilbake til forside</button>

            </main>
            <?php include("inkluderes/footer.php") ?>
        </article>
    </body>

    <!-- Denne siden er utviklet av Aron Snekkestad, Robin Kleppang, siste gang endret 21.02.2020 -->
    <!-- Denne siden er kontrollert av Aron Snekkestad siste gang 06.03.2020 -->

</html>