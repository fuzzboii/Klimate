<?php
session_start();

//------------------------------//
// Instillinger, faste variable //
//------------------------------//
include("instillinger.php");



?>

<!DOCTYPE html>
<html lang="no">

    <head>
        <!-- Setter riktig charset -->
        <meta charset="UTF-8">
        <!-- Legger til viewport -->
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-- Setter tittelen på prosjektet -->
        <title>Søk</title>
        <!-- Henter inn ekstern stylesheet -->
        <link rel="stylesheet" type="text/css" href="stylesheet.css">
        <!-- Henter inn favicon, bildet som dukker opp i fanene i nettleseren -->
        <link rel='icon' href='bilder/favicon.png' type='image/x-icon'>
        <!-- Henter inn JavaScript -->
        <script language="JavaScript" src="javascript.js"> </script>
    </head>

    <body onload="sokRullegardin()">
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
                        <a class="bildeKontroll" href="javascript:void(0)" onClick="location.href='profil.php'" tabindex="5">
                            <img src="bilder/<?php echo($bilde['hvor'])?>" alt="Profilbilde" class="profil_navmeny">
                        </a>
                    <?php } else { ?>
                        <!-- Hvis bruker ikke har noe profilbilde, bruk standard profilbilde -->
                        <a class="bildeKontroll" href="javascript:void(0)" onClick="location.href='profil.php'" tabindex="5">
                            <img src="bilder/profil.png" alt="Profilbilde" class="profil_navmeny">
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
                <a class="bildeKontroll" href="javascript:void(0)" onClick="location.href='sok.php'">
                    <img src="bilder/sokIkon.png" alt="Søkeikon" class="sok_navmeny">
                </a>
                <!-- Logoen øverst i venstre hjørne -->
                <a class="bildeKontroll" href="default.php" tabindex="1">
                    <img src="bilder/klimateNoText.png" alt="Klimate logo" class="Logo_navmeny">
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
                        <a class = "menytab" tabindex = "-1" href="arrangement.php">Arrangementer</a>
                        <a class = "menytab" tabindex = "-1" href="artikkel.php">Artikler</a>
                        <a class = "menytab" tabindex = "-1" href="#">Diskusjoner</a>
                        <a class = "menytab" tabindex = "-1" href="backend.php">Oversikt</a>
                        <a class = "menytab" tabindex = "-1" href="konto.php">Konto</a>
                        <a class = "menytab" tabindex = "-1" href="sok.php">Avansert Søk</a>
                    <?php } else { ?>
                        <!-- Hvis bruker ikke er innlogget -->
                        <a class = "menytab" tabindex = "-1" href="arrangement.php">Arrangementer</a>
                        <a class = "menytab" tabindex = "-1" href="artikkel.php">Artikler</a>
                        <a class = "menytab" tabindex = "-1" href="#">Diskusjoner</a>
                        <a class = "menytab" tabindex = "-1" href="sok.php">Avansert Søk</a>
                    <?php } ?>
                </section>
            </section>
            
            <!-- Funksjon for å lukke hamburgermeny når man trykker på en del i Main -->
            <!-- For å kunne lukke hamburgermenyen ved å kun trykke på et sted i vinduet må lukkHamburgerMeny() funksjonen ligge i deler av HTML-koden -->
            <!-- Kan ikke legge denne direkte i body -->
            <?php if(isset($_GET['brukernavn']) || isset($_GET['epost'])) { ?>
                <!-- Del for søk på bruker -->
                <header class="headerSok" onclick="lukkHamburgerMeny()">
                    <h1>x Resultater (Bruker)</h1>
                </header>

                <!-- TODO -->
                <?php if (($_GET['brukernavn'] != "") && ($_GET['epost'] != "")) {
                    // Del for søk på kombinasjon av brukernavn og epost
                    $sokPaaKomb = "select idbruker, brukernavn, epost, hvor from bruker, brukerbilde, bilder where brukernavn = '" . $_GET['brukernavn'] . "' and epost = '" . $_GET['epost'] . "' and bruker.idbruker = brukerbilde.bruker and brukerbilde.bilde = bilder.idbilder";
                    $stmtKomb = $db->prepare($sokPaaKomb);
                    $stmtKomb->execute();
                    $resKomb = $stmtKomb->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($resKomb as $brukeropplysninger) { ?>
                        <section class="brukerRes_sok" onClick="location.href='profil.php?bruker=<?php echo($brukeropplysninger['idbruker']) ?>'">
                            <figure class="bildeRes_sok">
                                <?php if ($brukeropplysninger['hvor'] == "") { ?>
                                    <!-- Standard profilbilde om bruker ikke har lastet opp noe enda -->
                                    <img src="bilder/profil.png" alt="Profilbilde for <?php echo($brukeropplysninger['brukernavn'])?>">
                                <?php } else { ?>
                                    <!-- Profilbilde som resultat av spørring -->
                                    <img src="bilder/<?php echo($brukeropplysninger['hvor'])?>" alt="Profilbilde for <?php echo($brukeropplysninger['brukernavn'])?>">
                                <?php } ?>
                            </figure>
                            <section class="infoRes_sok">
                                <p class="infoResBr_sok"><?php echo($brukeropplysninger['brukernavn'])?></p>
                                <p class="infoResEp_sok"><?php echo($brukeropplysninger['epost'])?></p>
                            </section>
                        </section>
                    <?php } ?>

                <?php } else if (($_GET['brukernavn'] != "") && ($_GET['epost'] == "")) { ?>
                    <!-- Del for søk på kun brukernavn -->
                    <p>Søk på brukernavn</p>
                <?php } else if (($_GET['brukernavn'] == "") && ($_GET['epost'] != "")) { ?>
                    <!-- Del for søk på kun epost -->
                    <p>Søk på epost</p>
                <?php } else { /* Bruker har forsøkt å søke på tomme verdier, sender tilbake */ header("Location: sok.php?error=1"); } ?>
                    
                    
                    

            <?php } else if(isset($_GET['artTittel']) || isset($_GET['artDato'])) { ?>
                <!-- Del for søk på artikkel -->
                <header class="headerSok" onclick="lukkHamburgerMeny()">
                    <h1>x Resultater (Artikkel)</h1>
                </header>
                <p>!Tester ikke på data enda, ignorer variabel feil!</p>
                <p>Skal nå søke med tittel: <?php echo($_GET['artTittel']); ?></p>
                <p>Og / eller dato: <?php echo($_GET['artDato']); ?></p>

            <?php } else if(isset($_GET['arrTittel']) || isset($_GET['arrDato'])) { ?>
                <!-- Del for søk på arrangement -->
                <header class="headerSok" onclick="lukkHamburgerMeny()">
                    <h1>x Resultater (Arrangement)</h1>
                </header>
                <p>!Tester ikke på data enda, ignorer variabel feil!</p>
                <p>Skal nå søke med tittel: <?php echo($_GET['arrTittel']); ?></p>
                <p>Og / eller dato: <?php echo($_GET['arrDato']); ?></p>
                <p>Og / eller fylke: <?php echo($_GET['fylke']); ?></p>

            <?php } else { ?>
                <!-- Del for avansert søk -->
                <header class="headerSok" onclick="lukkHamburgerMeny()">
                    <h1>Avansert søk</h1>
                </header>
                <main id="sok_main" onclick="lukkHamburgerMeny()"> 
                    <section id="sok_seksjon"> 
                        <!-- Rullegardin for søk på bruker -->
                        <form method="GET">
                            <a class="bildeKontroll" tabindex="15">
                                <section class="innholdRullegardin">
                                    <section class="sok_inputBoks">
                                        <p class="sokTittel">Brukernavn:</p>
                                        <input type="text" class="sokBrukerFelt" tabindex = "-1" name="brukernavn" placeholder="Skriv inn brukernavn">
                                    </section>
                                    <section class="sok_inputBoks">
                                        <p class="sokTittel">Epost:</p>
                                        <input type="email" class="sokBrukerFelt" tabindex = "-1" name="epost" placeholder="Skriv inn epost">
                                    </section>
                                    <input type="submit" class="sokKnapp" value="Søk">
                                </section>
                                <button type="button" id="brukerRullegardin" class="brukerRullegardin">Søk etter bruker</button>
                            </a>   
                        </form>
                        <!-- Rullegardin for søk på artikkel -->
                        <form method="GET">
                            <a class="bildeKontroll" tabindex="16">
                                <section class="innholdRullegardin">
                                    <section class="sok_inputBoks">
                                        <p class="sokTittel">Tittel:</p>
                                        <input type="text" class="sokBrukerFelt" tabindex = "-1" name="artTittel" placeholder="Tittelen på artikkelen">
                                    </section>
                                    <!--
                                    <section class="sok_inputBoks">
                                        <p class="sokTittel">Dato fra:</p>
                                        <input type="date" class="sokBrukerFelt" tabIndex = "-1" name="artDato" title="Alle artikler publisert etter oppgitt dato">
                                    </section>
                                    -->
                                    <section class="sok_inputBoks">
                                        <p class="sokTittel">Skrevet av:</p>
                                        <input type="text" class="sokBrukerFelt" tabindex ="-1" name="artForfatter" placeholder="Forfatter av artikkelen">
                                    </section>
                                    <input type="submit" class="sokKnapp" value="Søk">
                                </section>
                                <button type="button" id="artikkelRullegardin" class="artikkelRullegardin">Søk etter artikkel</button>
                            </a>
                        </form>
                        <!-- Rullegardin for søk på arrangement -->
                        <form method="GET">
                            <a class="bildeKontroll" class="innholdRullegardin" tabindex="17">
                                <section class="innholdRullegardin">
                                    <section class="sok_inputBoks">
                                        <p class="sokTittel">Tittel:</p>
                                        <input type="text" class="sokBrukerFelt" tabindex = "-1" name="arrTittel" placeholder="Tittelen på arrangementet">
                                    </section>
                                    <section class="sok_inputBoks">
                                        <p class="sokTittel">Dato før:</p>
                                        <input type="date" class="sokBrukerFelt" tabindex = "-1" name="arrDato" title="Alle arrangementer før oppgitt dato">
                                    </section>
                                    <section class="sok_inputBoks">
                                        <p class="sokTittel">Fylke:</p>
                                    <select name="fylke">
                                        <option value="">Ikke spesifikt</option>
                                        <?php 
                                            // Henter fylker fra database
                                            $hentFylke = "select fylkenavn from fylke order by fylkenavn ASC";
                                            $stmtFylke = $db->prepare($hentFylke);
                                            $stmtFylke->execute();
                                            $fylke = $stmtFylke->fetchAll(PDO::FETCH_ASSOC);
                                            foreach ($fylke as $innhold) { ?>
                                                <option value="<?php echo($innhold['fylkenavn'])?>"><?php echo($innhold['fylkenavn'])?></option>
                                        <?php } ?>
                                    </select>
                                    </section>
                                    <input type="submit" class="sokKnapp" value="Søk">
                                </section>
                                <button type="button" id="arrangementRullegardin" class="arrangementRullegardin">Søk etter arrangement</button>
                            </a>
                        </form>
                    </section>
                <?php if(isset($_GET['error']) && $_GET['error'] == 1){ ?>
                    <p id="mldFEIL">Vennligst oppgi noen verdier å søke på</p>
                <?php } ?>
                    
                </main>
            <?php } ?>





            
            <!-- Knapp som vises når du har scrollet i vinduet, tar deg tilbake til toppen -->
            <button onclick="tilbakeTilTopp()" id="toppKnapp" title="Toppen"><img src="bilder/pilopp.png" alt="Tilbake til toppen"></button>

            <!-- Footer, epost er for øyeblikket på en catch-all, videresendes til RK -->
            <footer>
                <p class=footer_beskrivelse>&copy; Klimate 2019 | <a href="mailto:kontakt@klimate.no">Kontakt oss</a>
                    <!-- Om brukeren ikke er administrator eller redaktør, vis link for søknad til å bli redaktør -->
                    <?php if (isset($_SESSION['brukernavn']) and $_SESSION['brukertype'] == "3") { ?> | <a href="soknad.php">Søknad om å bli redaktør</a><?php } ?>
                </p>
            </footer>
        </article>
    </body>

    <!-- Denne siden er utviklet av Robin Kleppang, siste gang endret xx.xx.xxxx -->
    <!-- Denne siden er kontrollert av Glenn Petter Pettersen, siste gang xx.xx.xxxx -->

</html>