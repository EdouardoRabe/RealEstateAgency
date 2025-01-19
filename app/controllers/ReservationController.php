<?php

namespace app\controllers;

use Flight;

class ReservationController
{
    public function __construct()
    {
    }

    public function makeReservation()
    {   
        $generaliserModel = Flight::generaliserModel();
        $reservationModel=Flight:: reservationModel();
        $reponse= $generaliserModel -> getFormData("agence_reservation", $omitColumns = ["id_reservation"], $method = 'POST');
        $id=$reponse["id_habitation"];
        $isReserved= $reservationModel-> isHabitationReserved($id, $reponse["arrival"], $reponse["departure"]);
        $message="";$status="";
        if($isReserved){
            $status="error";
            $message="This habitations is currently reserved by someone. Please try to find another in the list. Thank you";
        }else{
            $message="Reservation taken succesfully";
            $status="success";
            $insert=$generaliserModel-> insererDonnee("agence_reservation", $reponse);
        }
        Flight::redirect("detail?id_habitation=".$id."&message=".$message."&status=".$status);
    }

}
