<?php
session_start();
// Ved adminside IF ($_SESSION['bruker'] and $_SESSION['brukertype'] == 1) {}
// Sjekker om bruker er i en gyldig session, sender tilbake til hovedsiden hvis så
if (isset($_SESSION['brukernavn'])) {
    header("Location: default.php?error=2");
}

try {
    include("klimate_pdo_prod.php");
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

// Setter så PDO kaster ut feilmelding og stopper funksjonen ved database-feil (PDOException)
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sql = "select * from bruker";

$stmt = $db->prepare($sql);
    
$stmt->execute();

$resultat = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($resultat as $res) {
    echo "<br>";
    echo "<br>";
    echo "<br>";
    echo($res['idbruker']);
    echo "<br>";
    echo($res['brukernavn']);
    echo "<br>";
    echo($res['passord']);
    echo "<br>";
    echo($res['fnavn']);
    echo "<br>";
    echo($res['enavn']);
    echo "<br>";
    echo($res['epost']);
    echo "<br>";
}

?>