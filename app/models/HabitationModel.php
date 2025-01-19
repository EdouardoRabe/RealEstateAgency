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

    public function getListHabitations($type = null, $nbChambres = null, $loyer = null, $quartier = null)
    {
        try {
            $query = "SELECT h.*, t.name AS type_name
                    FROM agence_habitations h
                    LEFT JOIN agence_habitation_type t ON h.type = t.id_habitation_type
                    WHERE 1 = 1";
            $params = [];

            if (!is_null($type)) {
                $query .= " AND h.type = :type";
                $params[':type'] = $type;
            }
            if (!is_null($nbChambres)) {
                $query .= " AND h.nb_chambres = :nbChambres";
                $params[':nbChambres'] = $nbChambres;
            }
            if (!is_null($loyer)) {
                $query .= " AND h.loyer <= :loyer";
                $params[':loyer'] = $loyer;
            }
            if (!is_null($quartier)) {
                $query .= " AND h.quartier LIKE :quartier";
                $params[':quartier'] = '%' . $quartier . '%';
            }

            $stmt = $this->bdd->prepare($query);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la récupération des habitations : " . $e->getMessage());
        }
    }


    public function generateHabitationsCard($habitations)
    {
        if (empty($habitations)) {
            return '<div class="no-results">Aucune habitation trouvée pour les critères spécifiés.</div>';
        }

        $cards = '';
        foreach ($habitations as $habitation) {

            $cards .= '
                <div class="card">
                    <div class="card-image">
                        <img src="' . $habitation['image'] . '" alt="Habitation Image" class="image">
                    </div>
                    <div class="card-content">
                        <h3 class="card-title">' . htmlspecialchars($habitation['type_name']) . '</h3>
                        <p class="card-info"><strong>Chambres:</strong> ' . htmlspecialchars($habitation['nb_chambres']) . '</p>
                        <p class="card-info"><strong>Loyer:</strong> ' . htmlspecialchars($habitation['loyer']) . ' €</p>
                        <p class="card-info"><strong>Quartier:</strong> ' . htmlspecialchars($habitation['quartier']) . '</p>
                        <p class="card-description">' . htmlspecialchars($habitation['description']) . '</p>
                    </div>
                </div>';
        }

        return $cards;
    }


}
