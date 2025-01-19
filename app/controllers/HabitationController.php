<?php

namespace app\controllers;

use Flight;

class HabitationController
{
    public function __construct()
    {
    }

    public function getHome()
    {
        $generaliserModel = Flight::generaliserModel();
        $reponse = Flight::request()->query;
        $type = !empty($reponse["id_habitation_type"]) ? $reponse["id_habitation_type"] : null;
        $minNbChambres = !empty($reponse["min_nb_chambres"]) ? $reponse["min_nb_chambres"] : null;
        $maxNbChambres = !empty($reponse["max_nb_chambres"]) ? $reponse["max_nb_chambres"] : null;
        $minLoyer = !empty($reponse["min_loyer"]) ? $reponse["min_loyer"] : null;
        $maxLoyer = !empty($reponse["max_loyer"]) ? $reponse["max_loyer"] : null;
        $quartier = !empty($reponse["quartier"]) ? $reponse["quartier"] : null;
        

        $habitationModel = Flight::habitationModel();
        $habitations = $habitationModel->getListHabitations($type, $minNbChambres , $maxNbChambres , $minLoyer , $maxLoyer, $quartier ); 
        $path="detail";
        $cards = $habitationModel->generateHabitationsCard($habitations,$path); 

        $select = $generaliserModel->generateSelectField("agence_habitation_type", "id_habitation_type", "name", null);
        $input = $generaliserModel->generateInputFields("agence_habitations", ["type", "description", "id_habitation", "image"],[],true,["nb_chambres","loyer"]);

        $data = [
            "habitations" => $cards,
            "select" => $select,
            "input" => $input,
        ];

        Flight::render("home", $data);
    }

}
