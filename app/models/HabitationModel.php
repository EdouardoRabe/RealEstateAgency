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
                    WHERE 1 = 1";
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




    public function generateHabitationsCard($habitations, $linkPath)
    {
        if (empty($habitations)) {
            return '<div class="no-results">Aucune habitation trouvée pour les critères spécifiés.</div>';
        }
        $cards = '';
        foreach ($habitations as $habitation) {
            $image = !empty($habitation['images']) ? htmlspecialchars($habitation['images'][0]) : 'default-image.jpg'; // Image par défaut si aucune image

            $cards .= '
                <a href="' . htmlspecialchars($linkPath) . '?id_habitation=' . urlencode($habitation['id_habitation']) . '" class="card-link">
                    <div class="card">
                        <div class="card-image">
                            <img src="' . $image . '" alt="Habitation Image" class="image">
                        </div>
                        <div class="card-content">
                            <h3 class="card-title">' . htmlspecialchars($habitation['type_name']) . '</h3>
                            <p class="card-info"><strong>Chambres:</strong> ' . htmlspecialchars($habitation['nb_chambres']) . '</p>
                            <p class="card-info"><strong>Loyer:</strong> ' . htmlspecialchars($habitation['loyer']) . ' €</p>
                            <p class="card-info"><strong>Quartier:</strong> ' . htmlspecialchars($habitation['quartier']) . '</p>
                            <p class="card-description">' . htmlspecialchars($habitation['description']) . '</p>
                        </div>
                    </div>
                </a>';
        }

        return $cards;
    }




}
