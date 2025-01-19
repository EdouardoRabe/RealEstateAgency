<?php

namespace app\controllers;

use Flight;

class DetailController
{
    public function __construct()
    {
    }

    public function getDetail()
    {
        $generaliserModel = Flight::generaliserModel();
        $reponse = Flight::request()->query;
        $id = !empty($reponse["id_habitation"]) ? $reponse["id_habitation"] : 0;
        $habitationModel = Flight::habitationModel();
        $habitations= $habitationModel-> getHabitationById($id);
        $defaultImagePath="assets/img/defaultImage.jpg";
        $images= $habitationModel-> generateHabitationImagesHtml($habitations, $defaultImagePath);
        $form= $generaliserModel-> generateInsertForm("agence_reservation", $omitColumns = ["id_reservation","id_user"], $redirectPage = 'reservation', $method = 'POST', $hidden = ["id_user"=> $_SESSION["id_user"]], $canNull=false, $numericDouble=[]);
        $data=[
            "images" => $images,
            "form"=>$form,
            "habitation"=>$habitations
        ];
        Flight::render("detail", $data);
    }

}
