<?php

// Konstruktør for PDO
class mysqlPDO extends PDO {
    public function __construct() {
        $settings = parse_ini_file('klimatetest.ini',TRUE);
        if (!$settings) throw new exception('Får ikke åpnet ini-fil.');
        $drv = $settings['database']['driver'];
        $hst = $settings['database']['host'];
        $sch = $settings['database']['schema'];
        $usr = $settings['database']['username'];
        $pwd = $settings['database']['password'];
        $dns = $drv . ':host=' . $hst . ';dbname=' . $sch;
        parent::__construct($dns,$usr,$pwd);
    }
}


// Saltet
$salt = "IT2_2020"; 


// Prøver å koble til databasen
try {
    $db = new mysqlPDO();
} 
catch (Exception $ex) {
    // Disse feilmeldingene leder til samme tilbakemelding for bruker, dette kan ønskes å utvide i senere tid, så beholder alle for nå.
    if ($ex->getCode() == 1049) {
        // 1049, Fikk koblet til men databasen finnes ikke
        header('location: default.php?error=3');
    }
    if ($ex->getCode() == 2002) {
        // 2002, Kunne ikke koble til server
        header('location: default.php?error=3');
    }
    if ($ex->getCode() == 1045) {
        // 1045, Bruker har ikke tilgang
        header('location: default.php?error=3');
    }
}

?>