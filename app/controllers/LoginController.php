<?php

namespace app\controllers;

use Flight;

class LoginController {


    public function __construct() {
    }

    

    public function getStart() {
        $generaliserModel=Flight::generaliserModel();
        $form= $generaliserModel->generateInsertForm("agence_user",["id_user","name", "first_name","role"], "checkLogin");
        $data = [
            "form"=> $form
        ];
        Flight::render('login', $data);
    }

    public function checkLogin(){
        $generaliserModel=Flight::generaliserModel();
        $data= $generaliserModel-> checkLogin("agence_user",["id_user","name", "first_name","role"],'POST',["id_user"]);
        if($data["success"]==false){
            Flight::redirect('/');
        }
        else{
            $_SESSION["id_user"]=$data["data"]["id_user"];
            Flight::redirect('accueil');
        }
    }

    // public function signUp(){
    //     $generaliserModel=Flight::generaliserModel();
    //     $insert= $generaliserModel-> insertData("noel_user", ["id_user"], "POST");
    //     if($insert["success"]==false){
    //         Flight::redirect('/');
    //     }
    //     else{
    //         $data=  $generaliserModel -> getLastInsertedId("noel_user","id_user");
    //         $_SESSION["id_user"]=$data["last_id"];
    //         Flight::redirect('accueil');
    //     }
    // }

    public function getAccueil(){
        $data=[];
        Flight:: render("accueil", $data);
    }

}
