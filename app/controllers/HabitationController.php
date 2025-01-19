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
        $habitationModel = Flight::habitationModel();
        $habitations = $habitationModel->getListHabitations(); 
        $cards = $habitationModel->generateHabitationsCard($habitations); 

        $data = [
            "habitations" => $cards,
        ];

        Flight::render("home", $data);
    }
}
