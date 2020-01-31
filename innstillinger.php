<?php

// Konstruktør for lokal server
class mysqlPDO extends PDO {
    public function __construct() {
        $drv = 'mysql';
        $hst = 'localhost';
        $sch = 'Klimate';
        $usr = 'klimate';
        $pwd = 'kskic9866YDtre';
        $dns = $drv . ':host=' . $hst . ';dbname=' . $sch;
        parent::__construct($dns,$usr,$pwd);
    }
}

// Konstruktør for skolens server
/*
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
*/

// Konstruktør for Klimate.no
/*
class mysqlPDO extends PDO {
    public function __construct() {
        $drv = 'mysql';
        $hst = 'localhost';
        $usr = 'klimate_no';
        $pwd = 'bpg5sJNWjhL74Ye85tjr23pB';
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



// Prøver å koble til databasen, passer på å sjekke om siden vi er på er resultat av systemfeil, hvis ikke får vi en uendelig redirect loop
if(!isset($_GET['systemerror'])) {
    try {
        $db = new mysqlPDO();
    } 
    catch (Exception $ex) {
        // Disse feilmeldingene leder til samme tilbakemelding for bruker, dette kan ønskes å utvide i senere tid, så beholder alle for nå.
        if ($ex->getCode() == 1049) {
            // 1049, Fikk koblet til men databasen finnes ikke
            header('Location: default.php?systemerror');
        }
        if ($ex->getCode() == 2002) {
            // 2002, Kunne ikke koble til server
            header('Location: default.php?systemerror');
        }
        if ($ex->getCode() == 1045) {
            // 1045, Bruker har ikke tilgang
            header('Location: default.php?systemerror');
        }
    }
}
?>