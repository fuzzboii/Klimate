<?php
?> 
<!-- Knapp som vises når du har scrollet i vinduet, tar deg tilbake til toppen -->
        <button onclick="tilbakeTilTopp()" id="toppKnapp" title="Toppen"><img src="bilder/pilopp.png" alt="Tilbake til toppen"></button>

        <!-- Footer, epost er for øyeblikket på en catch-all, videresendes til RK -->
        <footer id="default_footer">
            <p class=footer_beskrivelse>&copy; Klimate <?php echo date("Y");?> | <a href="mailto:kontakt@klimate.no">Kontakt oss</a>
                <!-- Om brukeren ikke er administrator eller redaktør, vis link for søknad til å bli redaktør -->
                <?php if (isset($_SESSION['idbruker']) and $_SESSION['brukertype'] == "3") { ?> | <a href="soknad.php">Søknad om å bli redaktør</a><?php } ?>
            </p>
        </footer>
<?php
// Denne siden er utviklet av Robin Kleppang, siste gang endret 06.03.2020
// Denne siden er kontrollert av Robin Kleppang, siste gang 17.04.2020
?>