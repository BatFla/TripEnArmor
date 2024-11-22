<?php

require_once "../model/horaire.php";

class HoraireController {

    private $model;

    function __construct() {
        $this->model = 'Horaire';
    }
    
    public function createHoraire($ouverture, $fermeture, $pause_debut, $pause_fin, $id_offre) {
        $horaireID = $this->model::createCompte($ouverture, $fermeture, $pause_debut, $pause_fin, $id_offre);
        return $horaireID;
    }
    public function getInfosHoraire($id_horaire){
        $horaire = $this->model::getHoraireById($id_horaire);

        $result = [
            "id_horaire" => $horaire["id_horaire"],
            "ouverture" => $horaire["ouverture"],
            "fermeture" => $horaire["fermeture"],
            "pause_debut" => $horaire["num_pause_debut"],
            "pause_fin" => $horaire["pause_fin"],
            "id_offre" => $horaire["id_offre"]
        ];

        return $result;
    }

    public function updateHoraire($id_horaire, $ouverture = false, $fermeture =false, $pause_debut = false, $pause_fin = false, $id_offre = false) {  
        if ($ouverture === false && $fermeture === false && $pause_debut === false && $pause_fin === false && $id_offre === false) {
            echo "ERREUR: Aucun champ à modifier";
            return -1;
        } else {
            $horaire = $this->model::getHoraireById($id_horaire);
            
            $updatedHoraireId = $this->model::updateHoraire(
                $id_horaire, 
                $ouverture !== false ? $ouverture : $horaire["ouverture"], 
                $fermeture !== false ? $fermeture : $horaire["fermeture"], 
                $pause_debut !== false ? $pause_debut : $horaire["num_pause_debut"], 
                $pause_fin !== false ? $pause_fin : $horaire["pause_fin"],
                $id_offre
            );
            return $updatedHoraireId;
        }
    }
}