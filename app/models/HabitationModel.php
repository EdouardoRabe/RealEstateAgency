<?php

namespace app\models;

use PDO;
use Exception;

class HabitationModel
{
    private $bdd;

    public function __construct($bdd)
    {
        $this->bdd = $bdd;
    }

    public function getListHabitations($type = null, $minNbChambres = null, $maxNbChambres = null, $minLoyer = null, $maxLoyer = null, $quartier = "")
    {
        try {
            $query = "SELECT h.*, t.name AS type_name, i.image_path
                    FROM agence_habitations h
                    LEFT JOIN agence_habitation_type t ON h.type = t.id_habitation_type
                    LEFT JOIN agence_habitation_images i ON h.id_habitation = i.id_habitation
                    WHERE isDeleted=FALSE";
            $params = [];
            if (!is_null($type) && $type !== "") {
                $query .= " AND h.type = :type";
                $params[':type'] = $type;
            }
            if (!is_null($minNbChambres) && $minNbChambres !== "") {
                $query .= " AND h.nb_chambres >= :minNbChambres";
                $params[':minNbChambres'] = $minNbChambres;
            }
            if (!is_null($maxNbChambres) && $maxNbChambres !== "") {
                $query .= " AND h.nb_chambres <= :maxNbChambres";
                $params[':maxNbChambres'] = $maxNbChambres;
            }
            if (!is_null($minLoyer) && $minLoyer !== "") {
                $query .= " AND h.loyer >= :minLoyer";
                $params[':minLoyer'] = $minLoyer;
            }
            if (!is_null($maxLoyer) && $maxLoyer !== "") {
                $query .= " AND h.loyer <= :maxLoyer";
                $params[':maxLoyer'] = $maxLoyer;
            }
            if (!is_null($quartier) && $quartier !== "") {
                $query .= " AND h.quartier LIKE :quartier";
                $params[':quartier'] = '%' . $quartier . '%';
            }
            $stmt = $this->bdd->prepare($query);
            $stmt->execute($params);
            $habitaciones = $stmt->fetchAll();
            $result = [];
            foreach ($habitaciones as $habitation) {
                $found = false;
                foreach ($result as &$res) {
                    if ($res['id_habitation'] === $habitation['id_habitation']) {
                        $res['images'][] = $habitation['image_path'];
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $habitation['images'] = $habitation['image_path'] ? [$habitation['image_path']] : [];
                    unset($habitation['image_path']); 
                    $result[] = $habitation;
                }
            }
            return $result;
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la récupération des habitations : " . $e->getMessage());
        }
    }

    public function getHabitationById($id_habitation)
    {
        try {
            $query = "SELECT h.*, t.name AS type_name, i.image_path
                    FROM agence_habitations h
                    LEFT JOIN agence_habitation_type t ON h.type = t.id_habitation_type
                    LEFT JOIN agence_habitation_images i ON h.id_habitation = i.id_habitation
                    WHERE h.id_habitation = :id_habitation";
            $params = [':id_habitation' => $id_habitation];
            $stmt = $this->bdd->prepare($query);
            $stmt->execute($params);
            $habitacion = $stmt->fetchAll();
            
            if (empty($habitacion)) {
                return null;
            }
            $result = $habitacion[0];
            $result['images'] = [];
            foreach ($habitacion as $hab) {
                if (!empty($hab['image_path'])) {
                    $result['images'][] = $hab['image_path'];
                }
            }
            return $result;
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la récupération de l'habitation : " . $e->getMessage());
        }
    }



    public function generateHabitationsCard($habitations, $linkPath)
    {
        if (empty($habitations)) {
            return '<div class="no-results">Aucune habitation trouvée pour les critères spécifiés.</div>';
        }
        $cards = '';
        foreach ($habitations as $habitation) {
            $image = !empty($habitation['images']) ? htmlspecialchars($habitation['images'][0]) : 'default-image.jpg';
            $cards .= '
                <a href="' . htmlspecialchars($linkPath) . '?id_habitation=' . urlencode($habitation['id_habitation']) . '" class="card-link">
                    <div class="card">
                        <div class="card-image">
                            <img src="assets/img/' . $image . '" alt="Habitation Image" class="image">
                            <div class="card-overlay">
                                <div class="price-badge">' . htmlspecialchars($habitation['loyer']) . ' €</div>
                                <div class="card-details">
                                    <h3 class="card-title">' . htmlspecialchars($habitation['type_name']) . '</h3>
                                    <div class="card-stats">
                                        <div class="stat">
                                            <i class="fas fa-bed"></i>
                                            <span>' . htmlspecialchars($habitation['nb_chambres']) . ' chambres</span>
                                        </div>
                                        <div class="stat">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <span>' . htmlspecialchars($habitation['quartier']) . '</span>
                                        </div>
                                    </div>
                                    <p class="card-description">' . htmlspecialchars($habitation['description']) . '</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>';
        }
        return $cards;
    }

    public function generateHabitationImagesHtml($habitation, $defaultImagePath)
    {
        try {
            if ($habitation === null) {
                return '<div class="no-results">Habitation non trouvée.</div>';
            }
            $images = $habitation['images'];
            if (empty($images)) {
                $images = [$defaultImagePath]; 
            }
            $html = '<div class="habitation-images">';
            $html .= '<div class="large-image">
                        <img src="assets/img/' . htmlspecialchars($images[0]) . '" alt="Habitation Image" class="image-large">
                    </div>';
            $html .= '<div class="image-grid">';
            for ($i = 1; $i < count($images); $i++) {
                $html .= '<div class="image-item">
                            <img src="assets/img/' . htmlspecialchars($images[$i]) . '" alt="Habitation Image' . ($i + 1) . '" class="image-small">
                        </div>';
            }

            while (count($images) < 5) {
                $html .= '<div class="image-item">
                            <img src="' . htmlspecialchars($defaultImagePath) . '" alt="Teste" class="image-small">
                        </div>';
                $images[] = $defaultImagePath; 
            }

            $html .= '</div>'; 

            $html .= '</div>'; 

            return $html;

        } catch (Exception $e) {
            throw new Exception("Erreur lors de la récupération des images : " . $e->getMessage());
        }
    }





}
