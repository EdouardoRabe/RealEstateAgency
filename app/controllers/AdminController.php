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
        $table= $generaliserModel-> generateTableau($habitations, $titre = "Liste des habitations", null, $omitColumns = ["id_habitation", "isDeleted"], $crud = true, "update", "delete");
        $data=[
            "table"=>$table
        ];
        Flight::render("crud", $data);
    }

    public function delete(){
        $generaliserModel=Flight:: generaliserModel();
        $reponse = Flight::request()->query;
        $delete= $generaliserModel-> updateTableData("agence_habitations", ["isDeleted"=> TRUE],$conditions = ["id_habitation"=>$reponse["id"]]);
        $deleteImage=$generaliserModel-> deleteData("agence_habitation_images", ["id_habitation"=>$reponse["id"]]);
        Flight:: redirect("crud");
    }

    public function updateForm()
    {
        $generaliserModel = Flight::generaliserModel();
        $id = $_GET['id'];
        $upload = $generaliserModel-> generateUpload("Choisir un image", "file");
        $select = $generaliserModel->generateSelectField("agence_habitation_type", "id_habitation_type", "name", null);
        $input = $generaliserModel->generateInputFieldsWithDefaults("agence_habitations", ["id_habitation","isDeleted","type"],[],["id_habitation"=>$id], [], true);
    
        $data = [
            "upload" => $upload,
            "select" => $select,
            "input" => $input,
            "id" => $id
        ];
        Flight::render('update', $data); 
    }


    public function insertImgBase() {
        $uploadModel = Flight::uploadModel();
        $generaliserModel = Flight::generaliserModel();
        $id_habitation = $_GET['id'];
        $nb_chambres = $_POST['nb_chambres'];
        $loyer = $_POST['loyer'];
        $quartier = $_POST['quartier'];
        $desc = $_POST['description'];
        $files = $_FILES['file'];
        $erreur = $uploadModel->checkError($files);
        if ($erreur != 0) {
            Flight::redirect("update?id=".$id_habitation);
        } else {
            $upload_image = $uploadModel->uploadImg($files); 
            $nomTableHabitations = "agence_habitations";
            $dataHabitations = [
                "type" => $_POST['id_habitation_type'],
                "nb_chambres" => $nb_chambres,
                "loyer" => $loyer,
                "quartier" => $quartier,
                "description" => $desc
            ];
            $updateTable = $generaliserModel->updateTableData($nomTableHabitations, $dataHabitations, $conditions = ["id_habitation"=>$id_habitation]);
            if ($updateTable['status'] === "success") {
                $nomTableImages = "agence_habitation_images";
                $dataImages = [
                    "id_habitation" => $id_habitation,
                    "image_path" => $upload_image
                ];
                $deleteImage=$generaliserModel-> deleteData($nomTableImages, ["id_habitation"=>$id_habitation]);
                $insertBaseImages = $generaliserModel-> insererDonnee($nomTableImages, $dataImages);
                if ($insertBaseImages['status'] === "success") {
                    Flight::redirect("crud");
                } else {
                    die("Erreur lors de l'insertion de l'image : " . $insertBaseImages['message']);
                }
            } else {
                die("Erreur lors de l'insertion de l'habitation : " . $updateTable['message']);
            }
        }
    }


}
