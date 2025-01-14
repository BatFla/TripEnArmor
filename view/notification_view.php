<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/controller/avis_controller.php';
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/controller/membre_controller.php';

$avisController = new avisController();
$membreController = new MembreController();

$avis = $avisController->getAvisByIdPro($_SESSION['id_pro']);

if ($avis && count($avis) !== 0) {
    foreach($avis as $avi) {
        // print_r($avi);
?>
<div class="h-full p-2">
    <!-- lien vers l'offre -->
    <div class="w-full flex justify-between items-center">
        <h3 class="text-gray-600"><span class="text-black"><?php echo $avi['titre']; ?></span> posté par
            <span class="text-black"><?php echo $membreController->getInfosMembre($avi['id_membre'])['pseudo']; ?></span> Il y a <span class="text-black"><?php echo $avi['date_publication']; ?></span>
        </h3>
        <div class="h-6 w-24 bg-primary">
            Note
        </div>
    </div>
    <p>Vécu le <?php echo $avi['date_experience']; ?></p>
    <p>
        <?php
            echo $avi['commentaire']
        ?>
    </p>
    <hr />
</div>
<?php
}} else {
?>
<div class="h-full p-2">
    <p class="text-xl text-center">
        Vous n'avez aucune notification.
    </p>
</div>
<?php
}?>