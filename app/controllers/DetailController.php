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
        $message = !empty($reponse["message"]) ? $reponse["message"] : "";
        $status = !empty($reponse["status"]) ? $reponse["status"] : "";
        $habitationModel = Flight::habitationModel();
        $habitations= $habitationModel-> getHabitationById($id);
        $defaultImagePath="assets/img/defaultImage.jpg";
        $images= $habitationModel-> generateHabitationImagesHtml($habitations, $defaultImagePath);
        $form= $generaliserModel-> generateInsertForm("agence_reservation", $omitColumns = ["id_reservation","id_user", "id_habitation"], $redirectPage = 'reservation', $method = 'POST', $hidden = ["id_user"=> $_SESSION["id_user"], "id_habitation"=>$id], $canNull=false, $numericDouble=[]);
        $data=[
            "images" => $images,
            "form"=>$form,
            "habitation"=>$habitations,
            "message"=> $message,
            "status"=> $status
        ];
        Flight::render("detail", $data);
    }

}
