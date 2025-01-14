<?php

require_once dirname($_SERVER['DOCUMENT_ROOT']) . "/model/facture.php";

class FactureController
{

    private $model;

    function __construct()
    {
        $this->model = 'Facture';
    }

    public function getFacture($numero)
    {
        $facture = $this->model::getFactureById($numero);

        $result = [
            "id_offre" => $facture["id_offre"],
            "numero" => $facture["numero"],
            "date_emission" => $facture["date_emission"],
            "date_echeance" => $facture["date_echeance"],
        ];

        $this->model::log("Les informations de la facture $numero ont été lues.");
        return $result;
    }

    public function getAllFactures()
    {
        $this->model::log("Toutes les factures ont été lues.");
        return $this->model::getAllFactures();
    }

    public function getAllFacturesByIdOffre($id_offre)
    {
        return $this->model::getAllFacturesByIdOffre($id_offre);
    }

    public function createFacture($date_echeance, $date_emission, $id_offre)
    {
        $factureID = $this->model::createFacture($date_echeance, $date_emission, $id_offre);
        $this->model::log("Une facture a été créée.");
        return $factureID;
    }

    public function updateFacture($numero, $date_echeance, $date_emission, $id_offre)
    {
        if ($date_echeance === false && $date_emission === false) {
            $this->model::log("Aucune information n'a été modifiée.");
            return -1;
        } else {
            $facture = $this->model::getFactureById($numero);

            $updatedFacture = $this->model::updateFacture(
                $numero,
                $date_echeance == false ? $facture["date_echeance"] : $date_echeance,
                $date_emission == false ? $facture["date_emission"] : $date_emission,
                $id_offre == false ? $facture["id_offre"] : $id_offre
            );
            $this->model::log("Les informations de la facture $numero ont été modifiées.");
            return $updatedFacture;
        }
    }
}