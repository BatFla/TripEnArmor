<?php
session_start();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image" href="/public/images/favicon.png">

    <link rel="stylesheet" href="/styles/input.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <script
        src="https://cdn.tailwindcss.com?plugins=forms,typography,aspect-ratio,line-clamp,container-queries"></script>
    <script src="/styles/config.js"></script>

    <script type="module" src="/scripts/main.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
    <script src="/scripts/loadCaroussel.js" type="module"></script>

    <!-- Pour les requêtes AJAX -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <title>Détails d'une offre - PACT</title>
</head>

<body class="flex flex-col">

    <!-- Inclusion du header -->
    <?php
    require_once dirname(path: $_SERVER['DOCUMENT_ROOT']) . '/html/public/components/header.php';
    ?>

    <?php
    $id_offre = $_SESSION['id_offre'];
    if (isset($_SESSION['id_membre'])) {
        $id_membre = $_SESSION['id_membre'];
    }

    // Connexion avec la bdd
    require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/php_files/connect_to_bdd.php';

    // Avoir une variable $pro qui contient les informations du pro actuel.
    $stmt = $dbh->prepare("SELECT id_pro FROM sae_db._offre WHERE id_offre = :id_offre");
    $stmt->bindParam(':id_offre', $id_offre);
    $stmt->execute();
    $id_pro = $stmt->fetch(PDO::FETCH_ASSOC)['id_pro'];

    $stmt = $dbh->prepare("SELECT * FROM sae_db._professionnel WHERE id_compte = :id_pro");
    $stmt->bindParam(':id_pro', $id_pro);
    $stmt->execute();
    $pro = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($pro) {
        $nom_pro = $pro['nom_pro'];
    }

    // Obtenir l'ensemble des informations de l'offre
    $stmt = $dbh->prepare("SELECT * FROM sae_db._offre WHERE id_offre = :id_offre");
    $stmt->bindParam(':id_offre', $id_offre);
    $stmt->execute();
    $offre = $stmt->fetch(PDO::FETCH_ASSOC);
    require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/php_files/get_details_offre.php';
    switch ($categorie_offre) {
        case 'restauration':
            require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/controller/restauration_controller.php';
            $controllerRestauration = new RestaurationController();
            $parc_attraction = $controllerRestauration->getInfosRestauration($id_offre);
            break;

        case 'activite':
            require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/controller/activite_controller.php';
            $controllerActivite = new ActiviteController();
            $activite = $controllerActivite->getInfosActivite($id_offre);

            // Durée de l'activité
            $duree_act = $activite['duree'];
            $duree_act = substr($duree_act, 0, -3);
            $duree_act = str_replace(':', 'h', $duree_act);

            // Prestations de l'activité
            $prestation = $activite['prestations'];

            // Âge requis pour l'activité
            $age_requis_act = $activite['age_requis'];
            break;

        case 'parc_attraction':
            require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/controller/parc_attraction_controller.php';
            $controllerParcAttraction = new ParcAttractionController();
            $parc_attraction = $controllerParcAttraction->getInfosParcAttraction($id_offre);

            // Âge requis pour le parc d'attraction
            $age_requis_pa = $parc_attraction['age_requis'];

            // Nombre d'attractions du parc d'attraction
            $nb_attractions = $parc_attraction['nb_attractions'];
            break;

        case 'visite':
            require_once dirname(path: $_SERVER['DOCUMENT_ROOT']) . '/controller/visite_controller.php';
            $controllerVisite = new VisiteController();
            $visite = $controllerVisite->getInfosVisite($id_offre);

            // Durée de la visite
            $duree_vis = $visite['duree'];
            $duree_vis = substr($duree_vis, 0, -3);
            $duree_vis = str_replace(':', 'h', $duree_vis);

            // Visite guidée ou non
            $guideBool = $visite['avec_guide'];
            if ($guideBool == true) {
                $guide = 'oui';
                require_once dirname(path: $_SERVER['DOCUMENT_ROOT']) . '/controller/visite_langue_controller.php';
                $controllerLangue = new VisiteLangueController();
                $tabLangues = $controllerLangue->getLanguesByIdVisite($id_offre);
                $langues = '';
                foreach ($tabLangues as $langue) {
                    // Ajout des langues parlées lors de la visite
                    $langues .= $langue['nom'] . ', ';
                }
                $langues = rtrim($langues, ', ');
            } else {
                $guide = 'non';
            }

            break;

        case 'spectacle':
            require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/controller/spectacle_controller.php';
            $controllerSpectacle = new SpectacleController();
            $spectacle = $controllerSpectacle->getInfosSpectacle($id_offre);

            // Durée du spectacle
            $duree_spec = $spectacle['duree'];
            $duree_spec = substr($duree_spec, 0, -3);
            $duree_spec = str_replace(':', 'h', $duree_spec);

            // Capacité du spectacle
            $capacite = $spectacle['capacite'];
            break;
        default:
            break;
    }

    require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/controller/horaire_controller.php';
    $controllerHoraire = new HoraireController();
    $horaires = $controllerHoraire->getHorairesOfOffre($id_offre);

    foreach ($horairesV1 as $jour => $horaire) {
        $horaires['ouverture'][$jour] = $horaire['ouverture'];
        $horaires['pause_debut'][$jour] = $horaire['pause_debut'];
        $horaires['pause_fin'][$jour] = $horaire['pause_fin'];
        $horaires['fermeture'][$jour] = $horaire['fermeture'];
    }
    $jour_semaine = date('l');
    $jours_semaine_fr = [
        'Monday' => 'lundi',
        'Tuesday' => 'mardi',
        'Wednesday' => 'mercredi',
        'Thursday' => 'jeudi',
        'Friday' => 'vendredi',
        'Saturday' => 'samedi',
        'Sunday' => 'dimanche'
    ];

    $jour_semaine = $jours_semaine_fr[$jour_semaine];
    date_default_timezone_set('Europe/Paris');
    $heure_actuelle = date('H:i');
    $ouvert = false;

    // $time1 = "14:30";
    // $time2 = "16:45";
    
    // $timestamp1 = strtotime($time1);
    // $timestamp2 = strtotime($time2);
    
    // if ($timestamp1 < $timestamp2) {
    //     echo "$time1 est avant $time2";
    // } elseif ($timestamp1 > $timestamp2) {
    //     echo "$time1 est après $time2";
    // } else {
    //     echo "$time1 est égal à $time2";
    // }
    
    foreach ($horaires as $jour => $horaire) {
        if ($jour == $jour_semaine) {
            $ouverture = $horaire['ouverture'];
            $fermeture = $horaire['fermeture'];
            if ($ouverture !== null && $fermeture !== null) {
                $fermeture_T = explode(':', $fermeture);
                $fermeture_T[0] = $fermeture_T[0] + 24;
                $fermeture_T = implode(':', $fermeture_T);
                if ($heure_actuelle >= $ouverture && $heure_actuelle <= $fermeture_T) {
                    if ($horaire['pause_debut'] !== null && $horaire['pause_fin'] !== null) {
                        $pause_debut = $horaire['pause_debut'];
                        $pause_fin = $horaire['pause_fin'];
                        if ($heure_actuelle >= $pause_debut && $heure_actuelle <= $pause_fin) {
                            $ouvert = false;
                        } else {
                            $ouvert = true;
                        }
                    } else {
                        $ouvert = true;
                    }
                }
            }
        }
    }

    if ($categorie_offre !== 'restauration') {
        require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/controller/tarif_public_controller.php';
        $controllerGrilleTarifaire = new TarifPublicController();
        // VALEUR TEST CAR PAS DANS LA BDD
        // $tarifs = [
        //     [
        //         "titre_tarif" => "Tarif adulte",
        //         "prix" => 10
        //     ],
        //     [
        //         "titre_tarif" => "Tarif enfant",
        //         "prix" => 5
        //     ]
        // ];
    }

    if ($categorie_offre == 'parc_attraction') {
        // require dirname($_SERVER['DOCUMENT_ROOT']) . '/controller/t_image_img_controller.php';
        // $controllerImage = new TImageImgController();
        // $path_plan = $controllerImage->getPathToPlan($id_offre);
    }
    ?>

    <main class="flex flex-col md:block md:mx-10 self-center md:p-2 max-w-[1280px] overflow-auto grow">
        <div class="flex md:gap-3">

            <!-- PARTIE GAUCHE (menu) -->
            <div id="menu">
                <?php
                require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/html/public/components/menu.php';
                ?>
            </div>

            <!-- PARTIE DROITE (offre & détails) -->
            <div class="grow md:p-4 flex flex-col items-center md:gap-4">

                <!-- CAROUSSEL -->
                <div
                    class="w-full h-80 md:h-[400px] overflow-hidden relative swiper default-carousel swiper-container md:border md:border-black md:rounded-lg">
                    <!-- Wrapper -->
                    <?php
                    require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/controller/image_controller.php';
                    $controllerImage = new ImageController();
                    $images = $controllerImage->getImagesOfOffre($id_offre);
                    ?>
                    <div class="swiper-wrapper">
                        <div class="swiper-slide !w-full">
                            <img class="object-cover w-full h-full" src='/public/images/<?php if ($images['carte']) {
                                echo "offres/" . $images['carte'];
                            } else {
                                echo $categorie_offre . '.jpg';
                            } ?>' alt="image de slider">
                        </div>
                        <?php
                        if ($images['details']) {
                            foreach ($images['details'] as $image) {
                                ?>
                                <div class="swiper-slide !w-full">
                                    <img class="object-cover w-full h-full"
                                        src='/public/images/<?php echo "offres/" . $image; ?>' alt="image de slider">
                                </div>
                                <?php
                            }
                        }
                        ?>
                    </div>

                    <!-- Boutons de navigation sur la slider -->
                    <?php if ($images['details']) { ?>
                        <div class="flex items-center gap-8 justify-center">
                            <a
                                class="swiper-button-prev group flex justify-center items-center border border-solid rounded-full !top-1/2 !left-5 !bg-primary !text-white after:!text-base">
                                ‹</a>
                            <a
                                class="swiper-button-next group flex justify-center items-center border border-solid rounded-full !top-1/2 !right-5 !bg-primary !text-white after:!text-base">
                                ›</a>
                        </div>
                        <?php
                    }
                    ?>
                </div>

                <!-- RESTE DES INFORMATIONS SUR L'OFFRE -->
                <div class="space-y-4 px-2 md:px-0 w-full">
                    <div class="flex flex-col md:flex-row md:items-center w-full md:justify-between">
                        <div class="flex flex-col md:flex-row md:items-center">
                            <h1 class="text-h1 font-bold"><?php echo $offre['titre'] ?></h1>
                            <p class="hidden text-h1 md:flex md:pt-1">&nbsp;-&nbsp;</p>
                            <p class="professionnel text-h1 md:pt-1"><?php echo $nom_pro ?></p>
                        </div>
                    </div>
                    <?php if ($ouvert == true) {
                        ?>
                        <p class="text-h2 font-bold text-green-500">Ouvert</p>
                        <?php
                    } else {
                        ?>
                        <p class="text-h1 font-bold text-red-500">Fermé</p>
                        <?php
                    }
                    ?>
                    <div class="w-full">
                        <p class="text-small">
                            <?php echo $resume ?>
                        </p>
                    </div>

                    <!-- Afficher les tags de l'offre -->
                    <?php
                    require_once dirname(path: $_SERVER['DOCUMENT_ROOT']) . '/controller/tag_offre_controller.php';
                    $controllerTagOffre = new TagOffreController();
                    $tags_offre = $controllerTagOffre->getTagsByIdOffre($id_offre);

                    require_once dirname(path: $_SERVER['DOCUMENT_ROOT']) . '/controller/tag_controller.php';
                    $controllerTag = new TagController();
                    $tagsAffiche = "";
                    foreach ($tags_offre as $tag) {
                        $tagsListe[] = $controllerTag->getInfosTag($tag['id_tag']);
                    }
                    foreach ($tagsListe as $tag) {
                        $tagsAffiche .= $tag['nom'] . ', ';
                    }

                    $tagsAffiche = rtrim($tagsAffiche, ', ');
                    if ($tags_offre) {
                        ?>
                        <div class="p-1 rounded-lg bg-secondary self-center w-full">
                            <?php
                            echo ("<p class='text-white text-center truncate'>$tagsAffiche</p>");
                            ?>
                        </div>
                        <?php
                    } else {
                        ?>
                        <div class="p-1 rounded-lg bg-secondary self-center w-full">
                            <?php
                            echo ("<p class='text-white text-center'>Aucun tag à afficher</p>");
                            ?>
                        </div>
                        <?php
                    }
                    ?>


                    <!-- Partie du bas de la page (toutes les infos pratiques) -->
                    <div class="flex flex-col md:flex-row w-full">
                        <!-- Partie description -->
                        <div class="partie-description flex flex-col basis-1/2 pr-2">
                            <!-- Prix + localisation -->
                            <div class="flex flex-col space-y-2 md:gap-4">
                                <p class="text-h4 font-bold">À propos</p>
                                <div class="flex items-center gap-4 px-2">
                                    <i class="w-6 text-center fa-solid fa-location-dot"></i>
                                    <div class="text-small">
                                        <p><?php echo $ville . ', ' . $code_postal ?></p>
                                        <p><?php echo $adresse['numero'] . ' ' . $adresse['odonyme'] . ' ' . $adresse['complement'] ?>
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center px-2 gap-4">
                                    <i class="w-6 text-center fa-solid fa-money-bill"></i>
                                    <p class="prix text-small mt-1"><?php echo $prix_a_afficher ?></p>
                                </div>
                            </div>
                            <!-- Description détaillée -->
                            <div class="description flex flex-col space-y-2 my-4">
                                <p class="text-h4 font-bold">Description</p>
                                <p class="text-justify text-small px-2 prose">
                                    <?php echo $description ?>
                                </p>
                            </div>
                        </div>

                        <!-- Partie avis & Infos en fonction du type offre -->
                        <div class="basis-1/2">
                            <!-- Infos en fonction du type de l'offre -->
                            <a href="" class="">
                                <div class="flex flex-row justify-between" id="horaire-button">
                                    <div class="flex font-bold">
                                        <p class="text-h4 font-bold">Horaires&nbsp;</p>
                                    </div>
                                    <p id="horaire-arrow">></p>
                                </div>
                                <div class="hidden text-small py-3" id="horaire-info">
                                    <?php
                                    foreach ($horaires as $jour => $horaire) {
                                        echo "$jour : ";
                                        foreach ($horaire as $key => $value) {
                                            if ($value !== null) {
                                                $horaire[$key] = substr($value, 0, -3);
                                            }
                                        }
                                        if ($horaire['ouverture'] == null) {
                                            echo "Fermé <br>";
                                        } else {
                                            if ($horaire['pause_debut'] == null) {
                                                echo $horaire['ouverture'] . ' - ' . $horaire['fermeture'];
                                            } else {
                                                echo $horaire['ouverture'] . ' - ' . $horaire['pause_debut'] . ' ' . $horaire['pause_fin'] . ' - ' . $horaire['fermeture'];
                                            }
                                            echo "<br>";
                                        }
                                    }
                                    ?>
                                </div>
                            </a>
                            <a href="" class="">
                                <div class="flex flex-row justify-between pt-3" id="compl-button">
                                    <p class="text-h4 font-bold ">Informations complémentaires</p>
                                    <p id="compl-arrow">></p>
                                </div>
                                <div class="flex flex-col py-3 hidden" id="compl-info">
                                    <?php
                                    switch ($categorie_offre) {
                                        case 'restauration':
                                            // VALEUR TEST CAR PAS DANS LA BDD
                                            $tags_type_repas = 'Petit-dej, Brunch, Déjeuner, Dîner, Goûter';
                                            ?>
                                            <div class="text-small flex flex-row">
                                                <p class="text-small">Repas servis&nbsp:&nbsp</p>
                                                <p><?php echo $tags_type_repas ?></p>
                                            </div>
                                            <?php
                                            break;

                                        case 'activite':
                                            ?>
                                            <div class="text-small flex flex-row">
                                                <p>Durée&nbsp:&nbsp</p>
                                                <p><?php echo $duree_act ?></p>
                                            </div>
                                            <p class="text-small">Âge requis&nbsp;:&nbsp;<?php echo $age_requis_act ?> ans</p>
                                            <div class="text-small">
                                                <?php echo $prestation ?>
                                            </div>

                                            <?php
                                            break;

                                        case 'parc_attraction':
                                            ?>
                                            <div class="text-small flex flex-row">
                                                <p>Âge requis&nbsp:&nbsp</p>
                                                <p><?php echo $age_requis_pa ?></p>
                                                <p>&nbspans</p>
                                            </div>
                                            <div class="text-small flex flex-row">
                                                <p>Nombre d'attraction&nbsp:&nbsp</p>
                                                <p><?php echo $nb_attractions ?></p>
                                            </div>
                                            <?php
                                            if ($images) {
                                                ?>
                                                <img src="/public/images/offres/<?php echo $images['plan']; ?>" alt="">
                                                <?php
                                            } else {
                                                ?>
                                                <p class="text-small">Aucun plan</p>
                                                <?php
                                            } ?>
                                            <?php
                                            break;

                                        case 'visite':
                                            ?>
                                            <div class="text-small flex flex-row">
                                                <p>Durée&nbsp:&nbsp</p>
                                                <p><?php echo $duree_vis ?></p>
                                            </div>
                                            <div class="text-small flex flex-row">
                                                <p>Visite guidée :&nbsp</p>
                                                <p><?php echo $guide ?></p>
                                            </div>
                                            <?php if ($guideBool == true) { ?>
                                                <div class="text-small">
                                                    <p>Langue(s) parlée(s) lors de la visite guidée :&nbsp <?php echo $langues ?>
                                                    </p>
                                                </div>
                                            <?php } ?>
                                            <?php
                                            break;

                                        case 'spectacle':
                                            ?>
                                            <div class="text-small flex flex-row">
                                                <p>Durée&nbsp:&nbsp</p>
                                                <p><?php echo $duree_spec ?></p>
                                            </div>
                                            <div class="text-small flex flex-row">
                                                <p>Capacité :&nbsp</p>
                                                <p><?php echo $capacite ?></p>
                                                <p>&nbsppersonnes</p>
                                            </div>
                                            <?php
                                            break;

                                        default:
                                            ?>
                                            <p class="text-small">Aucune informations complémentaires à afficher.</p>
                                            <?php
                                            break;
                                    }
                                    ?>
                                </div>
                            </a>
                            <?php
                            if ($categorie_offre != 'restauration') {
                                ?>
                                <a href="" class="">
                                    <div class="flex flex-row justify-between pt-3" id="grille-button">
                                        <p class="text-h4 font-bold">Grille tarifaire</p>
                                        <p id="grille-arrow">></p>
                                    </div>
                                    <div class="hidden text-small py-3" id="grille-info">
                                        <?php
                                        require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/controller/tarif_public_controller.php';
                                        $controllerTarifPublic = new TarifPublicController();
                                        $tarifs = $controllerTarifPublic->getTarifsByIdOffre($id_offre);
                                        foreach ($tarifs as $tarif) {
                                            ?>

                                            <?php echo $tarif['titre'] ?> :&nbsp;
                                            <?php echo $tarif['prix'] ?> € <br>
                                            <?php
                                        }
                                        ?>
                                    </div>
                                </a>
                                <?php
                            }
                            ?>

                            <!-- Partie avis -->
                            <div class="mt-5 flex flex-col gap-2">

                                <div class="w-full flex justify-between">
                                    <h3 class="text-h4 font-bold">Avis</h3>
                                    <?php
                                    // Moyenne des notes quand il y en a une
                                    if ($moyenne) { ?>
                                        <div class="flex gap-1">
                                            <div class="flex gap-1 shrink-0">
                                                <?php for ($i = 0; $i < 5; $i++) {
                                                    if ($moyenne > 1) {
                                                        ?>
                                                        <img class="w-3" src="/public/images/oeuf_plein.svg" alt="1 point de note">
                                                        <?php
                                                    } else if ($moyenne > 0) {
                                                        ?>
                                                            <img class="w-3" src="/public/images/oeuf_moitie.svg"
                                                                alt="0.5 point de note">
                                                        <?php
                                                    } else {
                                                        ?>
                                                            <img class="w-3" src="/public/images/oeuf_vide.svg" alt="0 point de note">
                                                        <?php
                                                    }
                                                    $moyenne--;
                                                }
                                                ?>
                                            </div>
                                            <p class='text-small italic flex items-center'>(<?php echo $nb_avis ?>)</p>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>

                                <?php
                                if (isset($_SESSION['id_membre'])) {
                                    // UTILISATEUR CONNECTÉ, 2 cas :
                                    // - a déjà écrit un avis, auquel cas on le voit en premier et on peut le modifier
                                    // - n'a pas déjà écrit d'avis, auquel cas un formulaire de création d'avis apparaît
                                
                                    // vérifier si l'utilisateur a écrit un avis
                                    include_once dirname($_SERVER['DOCUMENT_ROOT']) . '/controller/avis_controller.php';
                                    $avisController = new AvisController;
                                    $mon_avis = $avisController->getAvisByIdMembreEtOffre($_SESSION['id_membre'], $id_offre);
                                    if ($mon_avis) { ?>
                                        <!-- AFFICHER SON AVIS ET POUVOIR LE MODIFIER -->
                                        <?php
                                        $id_avis = $mon_avis['id_avis'];
                                        $id_membre = $_SESSION['id_membre'];
                                        include dirname($_SERVER['DOCUMENT_ROOT']) . '/view/mon_avis_view.php';
                                        ?>
                                    <?php } else {
                                        ?>
                                        <!-- FORMULAIRE DE CRÉATION D'AVIS -->
                                        <div class="flex flex-col gap-2">
                                            <button
                                                onclick="document.getElementById('avis_formulaire').classList.toggle('hidden');"
                                                class="bg-secondary font-bold text-white rounded-lg p-2 self-end flex items-center gap-2">
                                                <i class="fa-solid fa-pen"></i>
                                                <p>Rédiger un avis</p>
                                            </button>

                                            <form id="avis_formulaire" action="/scripts/creation_avis.php" method="POST"
                                                class="hidden flex flex-col gap-4">

                                                <!-- Titre de l'avis -->
                                                <div>
                                                    <label for="titre">Titre</label>
                                                    <input type="text" name="titre" id="titre" placeholder="Titre de l'avis"
                                                        class="w-full border border-black rounded-lg p-1" required>
                                                </div>

                                                <!-- Commentaire de l'avis -->
                                                <textarea type="commentaire" name="commentaire" id="commentaire"
                                                    placeholder="Votre commentaire"
                                                    class="w-full border border-black rounded-lg p-1"></textarea>

                                                <!-- Note globale donnée (pour toutes les offres) -->
                                                <div>
                                                    <label for="note_globale">Note globale</label>
                                                    <select name="note_globale" id="note_globale" class="p-1 rounded-lg"
                                                        required>
                                                        <option selected disabled>...</option>
                                                        <option value="0">0</option>
                                                        <option value="0.5">0,5</option>
                                                        <option value="1">1</option>
                                                        <option value="1.5">1,5</option>
                                                        <option value="2">2</option>
                                                        <option value="2.5">2,5</option>
                                                        <option value="3">3</option>
                                                        <option value="3.5">3,5</option>
                                                        <option value="4">4</option>
                                                        <option value="4.5">4,5</option>
                                                        <option value="5">5</option>
                                                    </select>
                                                </div>

                                                <?php
                                                // Notes additionnelles pour les restaurants
                                                if ($categorie_offre == 'restauration') { ?>
                                                    <div>
                                                        <label for="note_ambiance">Ambiance</label>
                                                        <select name="note_ambiance" id="note_ambiance" class="p-1 rounded-lg"
                                                            required>
                                                            <option selected disabled>...</option>
                                                            <option value="0">0</option>
                                                            <option value="0.5">0,5</option>
                                                            <option value="1">1</option>
                                                            <option value="1.5">1,5</option>
                                                            <option value="2">2</option>
                                                            <option value="2.5">2,5</option>
                                                            <option value="3">3</option>
                                                            <option value="3.5">3,5</option>
                                                            <option value="4">4</option>
                                                            <option value="4.5">4,5</option>
                                                            <option value="5">5</option>
                                                        </select>
                                                    </div>

                                                    <div>
                                                        <label for="note_service">Service</label>
                                                        <select name="note_service" id="note_service" class="p-1 rounded-lg"
                                                            required>
                                                            <option selected disabled>...</option>
                                                            <option value="0">0</option>
                                                            <option value="0.5">0,5</option>
                                                            <option value="1">1</option>
                                                            <option value="1.5">1,5</option>
                                                            <option value="2">2</option>
                                                            <option value="2.5">2,5</option>
                                                            <option value="3">3</option>
                                                            <option value="3.5">3,5</option>
                                                            <option value="4">4</option>
                                                            <option value="4.5">4,5</option>
                                                            <option value="5">5</option>
                                                        </select>
                                                    </div>

                                                    <div>
                                                        <label for="note_cuisine">Cuisine</label>
                                                        <select name="note_cuisine" id="note_cuisine" class="p-1 rounded-lg"
                                                            required>
                                                            <option selected disabled>...</option>
                                                            <option value="0">0</option>
                                                            <option value="0.5">0,5</option>
                                                            <option value="1">1</option>
                                                            <option value="1.5">1,5</option>
                                                            <option value="2">2</option>
                                                            <option value="2.5">2,5</option>
                                                            <option value="3">3</option>
                                                            <option value="3.5">3,5</option>
                                                            <option value="4">4</option>
                                                            <option value="4.5">4,5</option>
                                                            <option value="5">5</option>
                                                        </select>
                                                    </div>

                                                    <div>
                                                        <label for="note_rapport">Rapport qualité / prix</label>
                                                        <select name="note_rapport" id="note_rapport" class="p-1 rounded-lg"
                                                            required>
                                                            <option selected disabled>...</option>
                                                            <option value="0">0</option>
                                                            <option value="0.5">0,5</option>
                                                            <option value="1">1</option>
                                                            <option value="1.5">1,5</option>
                                                            <option value="2">2</option>
                                                            <option value="2.5">2,5</option>
                                                            <option value="3">3</option>
                                                            <option value="3.5">3,5</option>
                                                            <option value="4">4</option>
                                                            <option value="4.5">4,5</option>
                                                            <option value="5">5</option>
                                                        </select>
                                                    </div>

                                                    <?php
                                                }
                                                ?>

                                                <!-- Date de l'expérience -->
                                                <div>
                                                    <label for="date_experience">Date de l'expérience</label>
                                                    <input type="date" name="date_experience" id="date_experience"
                                                        value="Date de votre expérience" required>
                                                </div>

                                                <!-- Contexte de passage -->
                                                <div>
                                                    <label for="contexte_passage">Contexte de passage</label>
                                                    <select name="contexte_passage" id="contexte_passage" class="p-1 rounded-lg"
                                                        required>
                                                        <option selected disabled>...</option>
                                                        <option value="en solo">en solo</option>
                                                        <option value="en couple">en couple</option>
                                                        <option value="entre amis">entre amis</option>
                                                        <option value="pour le travail">pour le travail</option>
                                                        <option value="en famille">en famille</option>
                                                    </select>
                                                </div>

                                                <!-- Publier l'avis ou annuler l'écriture -->
                                                <div class="flex justify-end gap-3 items-center">
                                                    <div onclick="document.getElementById('avis_formulaire').classList.toggle('hidden');"
                                                        class="font-bold text-secondarygit rounded-lg p-2 self-end flex items-center gap-2 border border-secondary">
                                                        <p>- Annuler</p>
                                                    </div>

                                                    <input type="submit" value="+ Publier"
                                                        class="bg-secondary text-white font-bold rounded-lg p-2 self-end">
                                                </div>

                                                <hr class="w-1/2 border border-black self-end my-2 rounded-lg bg-black">

                                                <!-- Champs cachés pour transmettre des donées à la création de l'offre -->
                                                <input type="text" id='id_offre' name='id_offre' hidden
                                                    value="<?php echo $_SESSION['id_offre'] ?>">
                                                <input type="text" id='id_membre' name='id_membre' hidden
                                                    value="<?php echo $_SESSION['id_membre'] ?>">
                                            </form>

                                            <script>
                                                // Eviter de pouvoir sélectionner un date ultérieure au jour actuel
                                                function setMaxDate() {
                                                    const today = new Date();
                                                    const year = today.getFullYear();
                                                    const month = String(today.getMonth() + 1).padStart(2, '0');
                                                    const day = String(today.getDate()).padStart(2, '0');
                                                    const maxDate = `${year}-${month}-${day}`;

                                                    document.getElementById("date_experience").setAttribute("max", maxDate);
                                                }

                                                // Call the function when the page loads
                                                window.onload = setMaxDate;
                                            </script>

                                        </div>
                                        <?php
                                    }
                                    ?>

                                    <?php
                                    // UTILISATEUR PAS CONNECTÉ
                                } else if (!isset($_SESSION['id_pro'])) { ?>
                                        <p class="text-small italic"><a href='/connexion' class="underline">Connectez-vous</a>
                                            pour rédiger un
                                            avis</p>
                                    <?php
                                }
                                ?>

                                <!-- Conteneur pour tous les avis -->
                                <div id="avis-container" class="flex flex-col gap-2 items-center"></div>

                                <!-- Bouton pour charger plus d'avis -->
                                <div class="flex gap-2 items-center justify-center self-end">
                                    <!-- Symbole de chargement quand les avis chargent -->
                                    <img id="loading-indicator" class="w-8 h-6" style="display: none;"
                                        src="/public/images/loading.gif" alt="Loading...">
                                    <button class="text-small font-bold" id="load-more-btn">
                                        Afficher plus...
                                    </button>
                                </div>

                            </div>

                            <!-- A garder ici car il y a du PHP -->
                            <script>
                                $(document).ready(function () {
                                    // Paramètres à passer au fichier PHP de chargement des avis
                                    let idx_avis = 0;
                                    const id_offre = <?php echo $_SESSION['id_offre'] ?>;
                                    const id_membre = <?php if (isset($_SESSION['id_membre'])) {
                                        echo $_SESSION['id_membre'];
                                    } else {
                                        echo '-1';
                                    } ?>;

                                    // Charger les X premiers avis
                                    loadAvis();

                                    // Ajouter des avis quand le bouton est cliqué
                                    $('#load-more-btn').click(function () {
                                        loadAvis();
                                    });

                                    // Fonction pour charger X avis (en PHP), puis les ajouter à la page via AJAX JS
                                    function loadAvis() {
                                        // Afficher le loader pendant le chargement
                                        $('#loading-indicator').show();

                                        // Désactiver le bouton pendant le chargement
                                        $('#load-more-btn').prop('disabled', true);

                                        $.ajax({
                                            url: '/scripts/load_avis.php',
                                            type: 'GET',
                                            data: {
                                                id_offre: id_offre,
                                                idx_avis: idx_avis,
                                                id_membre: id_membre
                                            },

                                            // Durant l'exécution de la requête
                                            success: function (response) {
                                                const lesAvisCharges = response;
                                                if (lesAvisCharges.length > 0) {
                                                    // Ajouter le contenu HTML généré par loaded avis.
                                                    $('#avis-container').append(lesAvisCharges);

                                                    // Pour l'éventuel prochain chargement, incrémenter le curseur
                                                    idx_avis += 3;
                                                } else {
                                                    // Ne plus pouvoir cliquer sur le bouton quand il n'y a plus d'avis
                                                    $('#load-more-btn').prop('disabled', true).text('');
                                                }
                                            },

                                            // A la fin, chacher le logo de chargement
                                            complete: function () {
                                                // Masquer le loader après la requête
                                                $('#loading-indicator').hide();
                                                // Réactiver le bouton après la requête (que ce soit réussi ou non)
                                                $('#load-more-btn').prop('disabled', false);
                                            }
                                        });
                                    }
                                });
                            </script>
                        </div>
                    </div>
                </div>
    </main>

    <!-- FOOTER -->
    <?php
    include_once dirname($_SERVER['DOCUMENT_ROOT']) . '/html/public/components/footer.php';
    ?>

    <script>
        // Configurer les flèches pour faire des dropdown menu stylés
        function setupToggle(arrowID, buttonID, infoID) {
            const button = document.getElementById(buttonID);
            const arrow = document.getElementById(arrowID);
            const info = document.getElementById(infoID);

            if (button) {
                button.addEventListener('click', function (event) {
                    event.preventDefault();
                    arrow.classList.toggle('rotate-90');
                    info.classList.toggle('hidden');
                });
            }
        }
        setupToggle('horaire-arrow', 'horaire-button', 'horaire-info');
        setupToggle('compl-arrow', 'compl-button', 'compl-info');
        setupToggle('grille-arrow', 'grille-button', 'grille-info');
    </script>
</body>

</html>