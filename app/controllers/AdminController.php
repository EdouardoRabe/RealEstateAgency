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
        $table= $generaliserModel-> generateTableau($habitations, $titre = "Liste des habitations", null, $omitColumns = ["id_habitation", "isDeleted"], $crud = true, "update", "delete","create");
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
        $upload = $generaliserModel-> generateUpload("Choisir un image", "file",false,true);
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

    public function createForm()
    {
        $generaliserModel = Flight::generaliserModel();
        $upload = $generaliserModel-> generateUpload("Choisir un image", "file",false,true);
        $select = $generaliserModel->generateSelectField("agence_habitation_type", "id_habitation_type", "name", null);
        $input = $generaliserModel->generateInputFields("agence_habitations", ["id_habitation","isDeleted","type"],[], false,[]);
        $data = [
            "upload" => $upload,
            "select" => $select,
            "input" => $input,
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
        $type = $_POST['id_habitation_type'];
        $nomTableImages = "agence_habitation_images";
        $nomTableHabitations = "agence_habitations";
        $dataHabitations = [
            "nb_chambres" => $nb_chambres,
            "loyer" => $loyer,
            "quartier" => $quartier,
            "description" => $desc
        ];
        if (!empty($type)) {
            $dataHabitations["type"] = $type;
        }
        $updateTable = $generaliserModel->updateTableData($nomTableHabitations, $dataHabitations, ["id_habitation" => $id_habitation]);
        if ($updateTable['status'] !== "success") {
            Flight::redirect("update?id=$id_habitation&error=" . urlencode("Erreur lors de la mise à jour de l'habitation : " . $updateTable['message']));
            return;
        }
        $isFirstImageInserted = false;
        foreach ($files['name'] as $key => $fileName) {
            $file = [
                'name' => $files['name'][$key],
                'type' => $files['type'][$key],
                'tmp_name' => $files['tmp_name'][$key],
                'error' => $files['error'][$key],
                'size' => $files['size'][$key]
            ];
            $erreur = $uploadModel->checkError($file);
            if ($erreur != 0) {
                Flight::redirect("update?id=$id_habitation&error=" . urlencode("Erreur lors du téléchargement de l'image : " . $fileName));
                return;
            } else {
                $upload_image = $uploadModel->uploadImg($file);
                $dataImages = [
                    "id_habitation" => $id_habitation,
                    "image_path" => $upload_image
                ];
    
                $insertBaseImages = $generaliserModel->insererDonnee($nomTableImages, $dataImages);
                if ($insertBaseImages['status'] !== "success") {
                    Flight::redirect("update?id=$id_habitation&error=" . urlencode("Erreur lors de l'insertion de l'image : " . $insertBaseImages['message']));
                    return;
                }
                if (!$isFirstImageInserted) {
                    $deleteImage = $generaliserModel->deleteData($nomTableImages, ["id_habitation" => $id_habitation]);
                    if ($deleteImage['success'] === false) {
                        Flight::redirect("update?id=$id_habitation&error=" . urlencode("Erreur lors de la suppression des anciennes images : " . $deleteImage['message']));
                        return;
                    }
                    $isFirstImageInserted = true;
                }
            }
        }
    
        Flight::redirect("crud");
    }
    
    

    public function creation() {
        $uploadModel = Flight::uploadModel();
        $generaliserModel = Flight::generaliserModel();
        $nb_chambres = $_POST['nb_chambres'];
        $loyer = $_POST['loyer'];
        $quartier = $_POST['quartier'];
        $desc = $_POST['description'];
        $files = $_FILES['file'];
        $type = $_POST['id_habitation_type'];
        $nomTableImages = "agence_habitation_images";
        $nomTableHabitations = "agence_habitations";
        $dataHabitations = [
            "nb_chambres" => $nb_chambres,
            "loyer" => $loyer,
            "quartier" => $quartier,
            "description" => $desc
        ];
        if (!empty($type)) { 
            $dataHabitations["type"] = $type;
        }
        $updateTable = $generaliserModel->insererDonnee($nomTableHabitations, $dataHabitations);
        if ($updateTable['status'] !== "success") {
            $message = "Erreur lors de l'insertion de l'habitation : " . htmlspecialchars($updateTable['message']);
            Flight::redirect("creation?error=" . urlencode($message));
            exit;
        }
        $lastID = $generaliserModel->getLastInsertedId($nomTableHabitations, "id_habitation");
        if (!isset($lastID['last_id'])) {
            $message = "Erreur : impossible de récupérer l'ID de l'habitation insérée.";
            Flight::redirect("creation?error=" . urlencode($message));
            exit;
        }
        foreach ($files['name'] as $key => $fileName) {
            $file = [
                'name' => $files['name'][$key],
                'type' => $files['type'][$key],
                'tmp_name' => $files['tmp_name'][$key],
                'error' => $files['error'][$key],
                'size' => $files['size'][$key]
            ];
            $erreur = $uploadModel->checkError($file);
            if ($erreur != 0) {
                $message = "Erreur lors de l'upload du fichier : le fichier " . htmlspecialchars($fileName) . " est invalide.";
                Flight::redirect("creation?error=" . urlencode($message));
                exit;
            }
            $upload_image = $uploadModel->uploadImg($file);
            if (!$upload_image) {
                $message = "Erreur lors de l'upload de l'image : " . htmlspecialchars($fileName);
                Flight::redirect("creation?error=" . urlencode($message));
                exit;
            }
            $dataImages = [
                "id_habitation" => $lastID["last_id"],
                "image_path" => $upload_image
            ];
            $insertBaseImages = $generaliserModel->insererDonnee($nomTableImages, $dataImages);
            if ($insertBaseImages['status'] !== "success") {
                $message = "Erreur lors de l'insertion de l'image dans la base : " . htmlspecialchars($insertBaseImages['message']);
                Flight::redirect("creation?error=" . urlencode($message));
                exit;
            }
        }
        Flight::redirect("crud");
    }
    
    


}
