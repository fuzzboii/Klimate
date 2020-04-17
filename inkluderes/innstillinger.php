<?php

// Konstruktør for lokal server
/*
class mysqlPDO extends PDO {
    public function __construct() {
        $drv = 'mysql';
        $hst = 'localhost';
        $sch = 'Klimate';
        $usr = 'klimate';
        $pwd = '';
        $dns = $drv . ':host=' . $hst . ';dbname=' . $sch;
        parent::__construct($dns,$usr,$pwd);
    }
}

$host = "localhost";
*/

// Konstruktør for skolens server
class mysqlPDO extends PDO {
    public function __construct() {
        $drv = 'mysql';
        $hst = '128.39.19.159'; // eller 's120.hbv.no'
        $usr = 'usr_klima';
        $pwd = 'pw_klima';
        $sch = 'klima';
        $dsn = $drv . ':host=' . $hst . ';dbname=' . $sch;
        parent::__construct($dsn,$usr,$pwd);
    }
}

$host = "s120.hbv.no";

// Konstruktør for Klimate.no
/*
class mysqlPDO extends PDO {
    public function __construct() {
        $drv = 'mysql';
        $hst = 'localhost';
        $usr = 'klimate_no';
        $pwd = '';
        $sch = 'klimate_no';
        $dsn = $drv . ':host=' . $hst . ';dbname=' . $sch;
        parent::__construct($dsn,$usr,$pwd);
    }
}
*/


// Saltet
$salt = "IT2_2020"; 

// Lagringsplass for bilder opplastet av brukere
$lagringsplass = "bilder/opplastet";

// Listen som brukes som standard personvern, brukes ved innlogging og registrering
// Rekkefølgen: visfnavn, visenavn, visepost, visinteresser, visbeskrivelse, vistelefonnummer
$personvern = array(0, 0, 0, 1, 1, 0);


// Del for å logge ut en bruker etter inaktivitet over lengre tid
if (isset($_SESSION['siste_aktivitet']) && (time() - $_SESSION['siste_aktivitet'] > 7200)) {
    // Siste brukerhandling var mer enn 2 timer siden, logger ut bruker
    session_destroy();   // Sletter session, bruker er nå utlogget.
    // PHP_SELF inneholder relativ plassering av siden scriptet kjører, 'default.php' hvis på default, '/gr8/default.php' ved felles kjøring osv.
    // Laster inn samme side på nytt nå som session er tømt (Siden bruker kanskje er på en side som kan vises av både innloggede og utloggede brukere)
    header('Location: ' . $_SERVER['PHP_SELF']); 
}
$_SESSION['siste_aktivitet'] = time(); // Oppdater session timeout



// Prøver å koble til databasen, passer på å sjekke om siden vi er på er resultat av systemfeil, hvis ikke får vi en uendelig redirect loop
if(!isset($default_melding) || substr($default_melding, 0, 10) != "Systemfeil") {
    try {
        $db = new mysqlPDO();

        // Sjekker om bruker har blitt utestengt, logger da ut
        $hentEksklusjonQ = "select grunnlag, datotil from eksklusjon where bruker = :bruker and (datotil is null or datotil > NOW())";
        $hentEksklusjonSTMT = $db -> prepare($hentEksklusjonQ);
        $hentEksklusjonSTMT -> bindparam(":bruker", $_SESSION['idbruker']);
        $hentEksklusjonSTMT -> execute();

        $eksklusjon = $hentEksklusjonSTMT -> fetch(PDO::FETCH_ASSOC); 

        if($eksklusjon) {
            if($eksklusjon['datotil'] == null) {
                $dato = "er permanent";
            } else {
                $dato = "varer til: " . $eksklusjon['datotil'];
            }
            session_destroy();
            session_start();
            $_SESSION['default_melding'] = "Du har blitt utestengt for '" . $eksklusjon['grunnlag'] . "', utestengelsen " . $dato;
            header("Location: default.php");
        }

    } 
    catch (Exception $ex) {
        // Disse feilmeldingene leder til samme tilbakemelding for bruker
        if ($ex->getCode() == 1049) {
            // 1049, Fikk koblet til men databasen finnes ikke
            $_SESSION['default_melding'] = "Systemfeil, vennligst kontakt administrator om problemet fortsetter";
            header("Location: default.php");
        }
        if ($ex->getCode() == 2002) {
            // 2002, Kunne ikke koble til server
            $_SESSION['default_melding'] = "Systemfeil, vennligst kontakt administrator om problemet fortsetter";
            header("Location: default.php");
        }
        if ($ex->getCode() == 1045) {
            // 1045, Bruker har ikke tilgang
            $_SESSION['default_melding'] = "Systemfeil, vennligst kontakt administrator om problemet fortsetter";
            header("Location: default.php");
        }
        if ($ex->getCode() == 2054) {
            // 2054, "The server requested authentication method unknown to the client"
            $_SESSION['default_melding'] = "Systemfeil, vennligst kontakt administrator om problemet fortsetter";
            header("Location: default.php");
        }
    }
}




// Denne siden er utviklet av Robin Kleppang, siste gang endret 06.03.2020
// Denne siden er kontrollert av Robin Kleppang, siste gang 06.03.2020
?>