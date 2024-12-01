<?php

session_start();

?>

<?php

include dirname($_SERVER['DOCUMENT_ROOT']) . '/controller/avis_controller.php';
include dirname($_SERVER['DOCUMENT_ROOT']) . '/php_files/get_details_offre.php';

$avisController = new AvisController;



$titre = isset($_POST['titre']) ? $_POST['titre'] : (isset($_SESSION['titre']) ? $_SESSION['titre'] : '');
$description = isset($_POST['description']) ? $_POST['description'] : (isset($_SESSION['description']) ? $_SESSION['description'] : '');
// $note = $_POST['note'];
$date_experience = isset($_POST['date_experience']) ? $_POST['date_experience'] : (isset($_SESSION['date_experience']) ? $_SESSION['date_experience'] : '');
$date_experience = date('Y-m-d H:i:s', strtotime($date_experience));
$id_compte = isset($_POST['id_compte']) ? $_POST['id_compte'] : (isset($_SESSION['id_compte']) ? $_SESSION['id_compte'] : '');
$id_membre = isset($_POST['id_membre']) ? $_POST['id_membre'] : (isset($_SESSION['id_membre']) ? $_SESSION['id_membre'] : '');
$id_offre = isset($_POST['id_offre']) ? $_POST['id_offre'] : (isset($_SESSION['id_offre']) ? $_SESSION['id_offre'] : '');
$note = isset($_POST['note']) ? $_POST['note'] : (isset($_SESSION['note']) ? $_SESSION['note'] : '');+
$note_globale = 0;
$note_cuisine = isset($_POST['note_cuisine']) ? $_POST['note_cuisine'] : (isset($_SESSION['note_cuisine']) ? $_SESSION['note_cuisine'] : '');
$note_ambiance = isset($_POST['note_ambiance']) ? $_POST['note_ambiance'] : (isset($_SESSION['note_ambiance']) ? $_SESSION['note_ambiance'] : '');
$note_service = isset($_POST['note_service']) ? $_POST['note_service'] : (isset($_SESSION['note_service']) ? $_SESSION['note_service'] : '');
$note_rapport_qualite_prix = isset($_POST['note_rapport_qualite_prix']) ? $_POST['note_rapport_qualite_prix'] : (isset($_SESSION['note_rapport_qualite_prix']) ? $_SESSION['note_rapport_qualite_prix'] : '');

// print_r("La note de la cuisine : " . $note_cuisine);
// print_r("La note globale : " . $note_globale);


// print_r("L'id du membre : " . $id_membre);

if ($categorie_offre == "restauration") {
    $note_globale = ($note_cuisine + $note_ambiance + $note_service + $note_rapport_qualite_prix) / 4;
} else {
    $note_globale = $note;
}

if($avisController->createAvis($titre, $description, $date_experience, $id_membre, $id_offre)){
    echo "Test d'insertion d'un avis (OK)";
    // header('Location: /offre/index.php');
} else {
    echo "ERREUR: Impossible de créer l'avis";
}