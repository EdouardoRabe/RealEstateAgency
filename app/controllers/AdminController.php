<?php

namespace app\controllers;

use Flight;

class AdminController {


    public function __construct() {
    }

    

    public function getStart() {
        $generaliserModel=Flight::generaliserModel();
        $form= $generaliserModel->generateInsertFormWithDefaults("agence_user", ["id_user","name", "first_name","role"], "checkLoginAdmin", 'POST', $hidden = [], $conditions = ["id_user"=>2], [], $canNull = false);
        $data = [
            "login"=> $form,
        ];
        Flight::render('loginAdmin', $data);
    }

    public function checkLogin(){
        $generaliserModel=Flight::generaliserModel();
        $data= $generaliserModel-> checkLogin("agence_user",["id_user","name", "first_name","role"],'POST',["id_user"]);
        if($data["success"]==false){
            Flight::redirect('/admin');
        }
        else{
            $_SESSION["id_user"]=$data["data"]["id_user"];
            Flight::redirect('crud');
        }
    }

    public function getCrud(){
        $habitationModel= Flight:: habitationModel();
        $generaliserModel=Flight:: generaliserModel();
        $habitations= $habitationModel-> getListHabitations(null,null,null,null,null,null);
        $table= $generaliserModel-> generateTableau($habitations, $titre = "Liste des habitations", null, $omitColumns = ["id_habitation", "isDeleted"], $crud = true, $redirectUpdate = null, "delete");
        $data=[
            "table"=>$table
        ];
        Flight::render("crud", $data);
    }

    public function delete(){
        $generaliserModel=Flight:: generaliserModel();
        $reponse = Flight::request()->query;
        $delete= $generaliserModel-> updateTableData("agence_habitations", ["isDeleted"=> TRUE],$conditions = ["id_habitation"=>$reponse["id"]]);
        Flight:: redirect("crud");
    }


}
