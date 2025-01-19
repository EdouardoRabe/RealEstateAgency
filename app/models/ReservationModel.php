<?php

namespace app\models;

use PDO;
use Exception;

class ReservationModel
{
    private $bdd;

    public function __construct($bdd)
    {
        $this->bdd = $bdd;
    }

    public function isHabitationReserved($id_habitation, $arrival, $departure)
    {
        try {
            $query = "SELECT COUNT(*) as reservation_count
                    FROM agence_reservation
                    WHERE id_habitation = :id_habitation
                    AND (
                        (:arrival BETWEEN arrival AND departure)
                        OR (:departure BETWEEN arrival AND departure)
                        OR (arrival BETWEEN :arrival AND :departure)
                        OR (departure BETWEEN :arrival AND :departure)
                    )";
            
            $stmt = $this->bdd->prepare($query);
            $stmt->execute([
                ':id_habitation' => $id_habitation,
                ':arrival' => $arrival,
                ':departure' => $departure
            ]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['reservation_count'] > 0;
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la vérification des réservations : " . $e->getMessage());
        }
    }



}
