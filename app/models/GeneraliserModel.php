<?php

namespace app\models;

use Ahc\Cli\Exception;
use Flight;

class GeneraliserModel
{
    private $bdd;

    public function __construct($bdd)
    {
        $this->bdd = $bdd;
    }

    public function generateTableau($liste, $titre = "Tableau Dynamique", $colonneMiseEnEvidence = null)
    {
        if (empty($liste)) {
            return "<div class='order'><p>Aucune donnée disponible pour " . htmlspecialchars(ucfirst($titre)) . " .</p></div>";
        }

        $entetes = array_filter(array_keys($liste[0]), function ($key) {
            return is_string($key) && stripos($key, 'id') !== 0;
        });

        $html = "
        <div class='order'>
            <div class='head'>
                <h3>$titre</h3>
                <i class='bx bx-search'></i>
                <i class='bx bx-filter'></i>
            </div>
            <table>
                <thead>
                    <tr>";
        foreach ($entetes as $entete) {
            $html .= "<th>" . htmlspecialchars(ucfirst($entete)) . "</th>";
        }
        $html .= "
                    </tr>
                </thead>
                <tbody>";
        foreach ($liste as $item) {
            $html .= "<tr>";
            foreach ($entetes as $entete) {
                $classe = ($entete === $colonneMiseEnEvidence) ? 'status completed' : '';
                $html .= "<td><span class='{$classe}'>" . htmlspecialchars($item[$entete]) . "</span></td>";
            }
            $html .= "</tr>";
        }

        $html .= "
                </tbody>
            </table>
        </div>";

        return $html;
    }

    public function generateInputFields($table, $omitColumns = [])
    {
        $query = "DESCRIBE $table";
        $stmt = $this->bdd->prepare($query);
        $stmt->execute();
        $columns = $stmt->fetchAll();

        $columnTypes = [
            'int' => 'number',
            'float' => 'number',
            'decimal' => 'number',
            'number' => 'number',
            'varchar' => 'text',
            'char' => 'text',
            'date' => 'date',
            'datetime' => 'datetime-local',
            'text' => 'textarea'
        ];

        foreach ($columns as $column) {
            $columnName = $column['Field'];
            $columnType = strtolower($column['Type']);
            $inputType = 'text';
            if (in_array($columnName, $omitColumns)) {
                continue;
            }
            foreach ($columnTypes as $dbType => $inputTypeValue) {
                if (strpos($columnType, $dbType) !== false) {
                    $inputType = $inputTypeValue;
                    break;
                }
            }
            echo "<div class=\"form-group\">";
            echo "<label for=\"{$columnName}\">" . ucfirst(str_replace('_', ' ', $columnName)) . "</label>";
            if ($inputType === 'textarea') {
                echo "<textarea name=\"{$columnName}\" id=\"{$columnName}\" class=\"form-control\"></textarea>";
            } else {
                echo "<input type=\"{$inputType}\" name=\"{$columnName}\" id=\"{$columnName}\" class=\"form-control\" required />";
            }
            echo "</div>";
        }
    }


    public function generateInsertForm($table, $omitColumns = [], $redirectPage = '#', $method = 'POST')
    {
        echo "<form action=\"$redirectPage\" method=\"$method\">";
        $this->generateInputFields($table, $omitColumns); 
        echo "<button type=\"submit\" class=\"btn btn-primary\">Submit</button>";
        echo "</form>";
    }


    public function getFormData($table, $omitColumns = [], $method = 'POST')
    {
        $query = "DESCRIBE $table";
        $stmt = $this->bdd->prepare($query);
        $stmt->execute();
        $columns = $stmt->fetchAll();
        $formData = [];
        $dataSource = ($method == 'POST') ? Flight::request()->data : Flight::request()->query;
        foreach ($columns as $column) {
            $columnName = $column['Field'];
            if (in_array($columnName, $omitColumns)) {
                continue;
            }
            if (isset($dataSource[$columnName])) {
                $formData[$columnName] = $dataSource[$columnName];
            } else {
                $formData[$columnName] = null;
            }
        }

        return $formData;
    }

    public function insertData($table, $omitColumns = [], $method = 'POST')
    {
        try {
            $formData = $this->getFormData($table, $omitColumns, $method);
            foreach ($formData as $key => $value) {
                if ($value === null) {
                    $formDataStr = print_r($formData, true); 
                    return [
                        'success' => false,
                        'message' => "Le champ `$key` est obligatoire mais n'a pas été fourni. Contenu complet de \$formData : " . $formDataStr
                    ];
                }
            }
            $columns = array_keys($formData);
            $values = array_values($formData);
            $columnNames = implode(", ", $columns);
            $placeholders = implode(", ", array_fill(0, count($columns), '?'));
            $query = "INSERT INTO $table ($columnNames) VALUES ($placeholders)";
            $stmt = $this->bdd->prepare($query);
            if ($stmt->execute($values)) {
                return [
                    'success' => true,
                    'message' => "Les données ont été insérées avec succès dans la table `$table`."
                ];
            } else {
                return [
                    'success' => false,
                    'message' => "Échec de l'insertion des données dans la table `$table`."
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => "Erreur lors de l'insertion : " . $e->getMessage()
            ];
        }
    }

    public function checkLogin($table, $omitColumns = [], $method = 'POST', $return = [])
    {
        try {
            $formData = $this->getFormData($table, $omitColumns, $method);
            $requiredColumns = array_diff(array_keys($formData), $omitColumns);
            
            foreach ($requiredColumns as $column) {
                if (!isset($formData[$column]) || $formData[$column] === null) {
                    return [
                        'success' => false,
                        'message' => "Le champ `$column` est obligatoire mais n'a pas été fourni. Contenu complet : " . json_encode($formData)
                    ];
                }
            }
            $conditions = [];
            $values = [];
            foreach ($formData as $key => $value) {
                if (!in_array($key, $omitColumns)) {
                    $conditions[] = "$key = ?";
                    $values[] = $value;
                }
            }
            $whereClause = implode(' AND ', $conditions);
            $query = "SELECT * FROM $table WHERE $whereClause";
            $stmt = $this->bdd->prepare($query);
            $stmt->execute($values);

            if ($stmt->rowCount() > 0) {
                $data = $stmt->fetch();
                if (!empty($return)) {
                    $filteredData = [];
                    foreach ($return as $column) {
                        if (array_key_exists($column, $data)) {
                            $filteredData[$column] = $data[$column];
                        }
                    }
                    return [
                        'success' => true,
                        'message' => "Connexion réussie.",
                        'data' => $filteredData
                    ];
                }
                return [
                    'success' => true,
                    'message' => "Connexion réussie.",
                    'data' => $data
                ];
            } else {
                return [
                    'success' => false,
                    'message' => "Nom d'utilisateur ou mot de passe incorrect.",
                    'data' => $formData
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => "Erreur lors de la vérification des identifiants : " . $e->getMessage()
            ];
        }
    }


    public function insererDonnee($nomTable, $donnee)
    {
        try {
            if (empty($donnee)) {
                return ["message" => "Les données sont vides.", "status" => "error"];
            }
            $colonnes = array_keys($donnee);
            $colonnesListe = implode(", ", $colonnes);
            $placeholders = implode(", ", array_map(function ($col) {
                return ":$col";
            }, $colonnes));
            $query = "INSERT INTO $nomTable ($colonnesListe) VALUES ($placeholders)";
            $stmt = $this->bdd->prepare($query);
            $stmt->execute($donnee);
            return ["message" => "Insertion avec succès", "status" => "success"];
        } catch (Exception $e) {
            return ["message" => "Erreur lors de l'insertion: " . $e->getMessage(), "status" => "error"];
        }
    }


    public function insererDonnees($nomTable, $donnees)
    {
        try {
            if (empty($donnees)) {
                return ["message" => "Le tableau de données est vide.", "status" => "error"];
            }
            
            $colonnes = array_keys($donnees[0]);
            $colonnesListe = implode(", ", $colonnes);

            $placeholders = implode(", ", array_map(function ($col) {
                return ":$col";
            }, $colonnes));
            
            $query = "INSERT INTO $nomTable ($colonnesListe) VALUES ($placeholders)";
            $stmt = $this->bdd->prepare($query);
            
            foreach ($donnees as $ligne) {
                $stmt->execute($ligne);
            }
            
            return ["message" => "Insertion avec succès", "status" => "success"];
        } catch (Exception $e) {
            return ["message" => "Erreur lors de l'insertion: " . $e->getMessage(), "status" => "error"];
        }
    }

    function getTableData($tableName, $conditions = [], $omitColumns = [], $join = null) {
        if (empty($tableName)) {
            echo 'Le nom de la table ne peut pas être vide.';
            return;
        }
        $sql = "SELECT * FROM $tableName";
        if ($join !== null && is_array($join)) {
            foreach ($join as $joinInfo) {
                if (isset($joinInfo[0], $joinInfo[1]) && is_array($joinInfo[1])) {
                    $table2 = $joinInfo[0];       
                    $joinColumns = $joinInfo[1];  
                    $onClauses = [];
                    foreach ($joinColumns as $columnPair) {
                        if (count($columnPair) === 2) {
                            $onClauses[] = "$columnPair[0] = $columnPair[1]";
                        }
                    }
                    if (!empty($onClauses)) {
                        $sql .= " INNER JOIN $table2 ON " . implode(' AND ', $onClauses);
                    }
                } else {
                    echo 'Les informations de jointure sont incorrectes.';
                    return;
                }
            }
        }
        if (!empty($conditions)) {
            $whereClauses = [];
            foreach ($conditions as $column => $value) {
                $escapedValue = Flight::bdd()->quote($value);
                $whereClauses[] = "$column = $escapedValue";
            }
            if (!empty($whereClauses)) {
                $sql .= " WHERE " . implode(' AND ', $whereClauses);
            }
        }
        $data = Flight::bdd()->query($sql)->fetchAll();
        if (!empty($omitColumns)) {
            foreach ($data as &$row) {
                foreach ($omitColumns as $omit) {
                    if (array_key_exists($omit, $row)) {
                        unset($row[$omit]);
                    }
                }
            }
        }
        return $data;
    }
    
    
    
    


    public function generateInputFieldsWithDefaults($table, $omitColumns = [], $conditions = [])
    {
        try {
            $query = "DESCRIBE $table";
            $stmt = $this->bdd->prepare($query);
            $stmt->execute();
            $columns = $stmt->fetchAll();
            $defaultValues = [];
            if (!empty($conditions)) {
                $conditionClauses = [];
                $params = [];
                foreach ($conditions as $column => $value) {
                    $conditionClauses[] = "$column = ?";
                    $params[] = $value;
                }
                $whereClause = implode(' AND ', $conditionClauses);
                $query = "SELECT * FROM $table WHERE $whereClause LIMIT 1";
                $stmt = $this->bdd->prepare($query);
                $stmt->execute($params);
                $defaultValues = $stmt->fetch();
            }
            $columnTypes = [
                'int' => 'number',
                'float' => 'number',
                'decimal' => 'number',
                'number' => 'number',
                'varchar' => 'text',
                'char' => 'text',
                'date' => 'date',
                'datetime' => 'datetime-local',
                'text' => 'textarea'
            ];

            foreach ($columns as $column) {
                $columnName = $column['Field'];
                $columnType = strtolower($column['Type']);
                if (in_array($columnName, $omitColumns)) {
                    continue;
                }

                $inputType = 'text';
                foreach ($columnTypes as $dbType => $inputTypeValue) {
                    if (strpos($columnType, $dbType) !== false) {
                        $inputType = $inputTypeValue;
                        break;
                    }
                }

                $defaultValue = $defaultValues[$columnName] ?? '';
                echo "<div class=\"form-group\">";
                echo "<label for=\"$columnName\">" . ucfirst(str_replace('_', ' ', $columnName)) . "</label>";
                if ($inputType == 'textarea') {
                    echo "<textarea name=\"$columnName\" id=\"$columnName\" class=\"form-control\">$defaultValue</textarea>";
                } else {
                    echo "<input type=\"$inputType\" name=\"$columnName\" id=\"$columnName\" class=\"form-control\" value=\"$defaultValue\" required />";
                }
                echo "</div>";
            }
        } catch (Exception $e) {
            echo "Erreur lors de la génération des champs : " . $e->getMessage();
        }
    }

    public function generateInsertFormWithDefaults($table, $omitColumns = [], $redirectPage = '#', $method = 'POST', $conditions = [])
    {
        echo "<form action=\"$redirectPage\" method=\"$method\">";
        $this->generateInputFieldsWithDefaults($table, $omitColumns, $conditions);
        echo "<button type=\"submit\" class=\"btn btn-primary\">Submit</button>";
        echo "</form>";
    }

    public function getLastInsertedId($table, $idColumn)
    {
        try {
            $query = "SELECT MAX($idColumn) AS last_id FROM $table";
            $stmt = $this->bdd->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();

            if ($result && isset($result['last_id'])) {
                return [
                    'success' => true,
                    'last_id' => $result['last_id'],
                    'message' => "Dernier ID récupéré avec succès."
                ];
            } else {
                return [
                    'success' => false,
                    'message' => "Aucun ID trouvé dans la table `$table`."
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => "Erreur lors de la récupération du dernier ID : " . $e->getMessage()
            ];
        }
    }




    public function updateData($table, $omitColumns = [], $method = 'POST', $conditions = [])
    {
        try {
            $formData = $this->getFormData($table, $omitColumns, $method);
            foreach ($formData as $key => $value) {
                if ($value === null) {
                    $formDataStr = print_r($formData, true);
                    return [
                        'success' => false,
                        'message' => "Le champ `$key` est obligatoire mais n'a pas été fourni. Contenu complet de \$formData : " . $formDataStr
                    ];
                }
            }
            $setClauses = [];
            $values = [];
            foreach ($formData as $column => $value) {
                $setClauses[] = "$column = ?";
                $values[] = $value;
            }
            $setClause = implode(", ", $setClauses);
            if (empty($conditions)) {
                return [
                    'success' => false,
                    'message' => "Aucune condition fournie pour la mise à jour. Cela empêcherait une mise à jour accidentelle de toutes les lignes."
                ];
            }
            $whereClauses = [];
            foreach ($conditions as $column => $value) {
                $whereClauses[] = "$column = ?";
                $values[] = $value;
            }
            $whereClause = implode(" AND ", $whereClauses);
            $query = "UPDATE $table SET $setClause WHERE $whereClause";
            $stmt = $this->bdd->prepare($query);
            if ($stmt->execute($values)) {
                return [
                    'success' => true,
                    'message' => "Les données ont été mises à jour avec succès dans la table `$table`."
                ];
            } else {
                return [
                    'success' => false,
                    'message' => "Échec de la mise à jour des données dans la table `$table`."
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => "Erreur lors de la mise à jour : " . $e->getMessage()
            ];
        }
    }

    function updateTableData($tableName, $data, $conditions = []) {
        if (empty($tableName)) {
            echo 'Le nom de la table ne peut pas être vide.';
            return;
        }
        if (empty($data)) {
            echo 'Les données à mettre à jour ne peuvent pas être vides.';
            return;
        }
        $setClauses = [];
        foreach ($data as $column => $value) {
            $escapedValue = Flight::bdd()->quote($value);
            $setClauses[] = "$column = $escapedValue";
        }
        $sql = "UPDATE $tableName SET " . implode(', ', $setClauses);
        if (!empty($conditions)) {
            $whereClauses = [];
            foreach ($conditions as $column => $value) {
                $escapedValue = Flight::bdd()->quote($value);
                $whereClauses[] = "$column = $escapedValue";
            }
            if (!empty($whereClauses)) {
                $sql .= " WHERE " . implode(' AND ', $whereClauses);
            }
        }
        $result = Flight::bdd()->exec($sql);
        return $result;
    }
    

    public function deleteData($table, $conditions = [])
    {
        try {
            if (empty($conditions)) {
                return [
                    'success' => false,
                    'message' => "Aucune condition fournie pour la suppression. Cela empêcherait une suppression accidentelle de toutes les lignes."
                ];
            }
            $whereClauses = [];
            $values = [];
            foreach ($conditions as $column => $value) {
                $whereClauses[] = "$column = ?";
                $values[] = $value;
            }
            $whereClause = implode(" AND ", $whereClauses);
            $query = "DELETE FROM $table WHERE $whereClause";
            $stmt = $this->bdd->prepare($query);
            if ($stmt->execute($values)) {
                return [
                    'success' => true,
                    'message' => "Les données ont été supprimées avec succès de la table `$table`."
                ];
            } else {
                return [
                    'success' => false,
                    'message' => "Échec de la suppression des données de la table `$table`."
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => "Erreur lors de la suppression : " . $e->getMessage()
            ];
        }
    }


    public function generateSelectField($table, $column, $label = null, $omitValues = [])
    {
        $query = "SELECT DISTINCT $column FROM $table";
        $stmt = $this->bdd->prepare($query);
        $stmt->execute();
        $rows = $stmt->fetchAll(); 
        $values = array_column($rows, $column);
        if ($label === null) {
            $label = ucfirst(str_replace('_', ' ', $column));
        }
        echo "<div class=\"form-group\">";
        echo "<label for=\"$column\">$label</label>";
        echo "<select name=\"$column\" id=\"$column\" class=\"form-control\">";
        echo "<option value=\"\">-- Sélectionnez --</option>";
        foreach ($values as $value) {
            if (in_array($value, $omitValues)) {
                continue;
            }
            if (is_scalar($value)) {
                echo "<option value=\"{$value}\">{$value}</option>";
            }
        }
        echo "</select>";
        echo "</div>";
    }


    public function generateLoginSignupForms($table, $omitColumnsSignup = [], $omitColumnsLogin = [], $redirectPageSignup = '#', $redirectPageLogin = '#', $method = 'POST')
    {
        $query = "DESCRIBE $table";
        $stmt = $this->bdd->prepare($query);
        $stmt->execute();
        $columns = $stmt->fetchAll();
        $columnTypes = [
            'int' => 'number',
            'float' => 'number',
            'decimal' => 'number',
            'number' => 'number',
            'varchar' => 'text',
            'char' => 'text',
            'date' => 'date',
            'datetime' => 'datetime-local',
            'text' => 'textarea'
        ];
        $generateFields = function ($columns, $omitColumns) use ($columnTypes) {
            $fields = '';
            foreach ($columns as $column) {
                $columnName = $column['Field'];
                $columnType = strtolower($column['Type']);
                $inputType = 'text';
                if (in_array($columnName, $omitColumns)) {
                    continue;
                }
                foreach ($columnTypes as $dbType => $inputTypeValue) {
                    if (strpos($columnType, $dbType) !== false) {
                        $inputType = $inputTypeValue;
                        break;
                    }
                }
                $fields .= "<div class=\"input-field\">";
                $fields .= "<i class=\"fas fa-user\"></i>"; 
                $fields .= $inputType === 'textarea' 
                    ? "<textarea name=\"{$columnName}\" placeholder=\"" . ucfirst(str_replace('_', ' ', $columnName)) . "\" class=\"form-control\"></textarea>" 
                    : "<input type=\"{$inputType}\" name=\"{$columnName}\" placeholder=\"" . ucfirst(str_replace('_', ' ', $columnName)) . "\" class=\"form-control\" required />";
                $fields .= "</div>";
            }
            return $fields;
        };
        $signupFields = $generateFields($columns, $omitColumnsSignup);
        $signupForm = "
            <form action=\"$redirectPageSignup\" method=\"$method\" class=\"sign-up-form\">
                <h2 class=\"title\">Sign up</h2>
                $signupFields
                <input type=\"submit\" class=\"btn\" value=\"Sign up\" />
                <p class=\"social-text\">Or Sign up with social platforms</p>
                <div class=\"social-media\">
                    <a href=\"#\" class=\"social-icon\"><i class=\"fab fa-facebook-f\"></i></a>
                    <a href=\"#\" class=\"social-icon\"><i class=\"fab fa-twitter\"></i></a>
                    <a href=\"#\" class=\"social-icon\"><i class=\"fab fa-google\"></i></a>
                    <a href=\"#\" class=\"social-icon\"><i class=\"fab fa-linkedin-in\"></i></a>
                </div>
            </form>";
        $loginFields = $generateFields($columns, $omitColumnsLogin);
        $loginForm = "
            <form action=\"$redirectPageLogin\" method=\"$method\" class=\"sign-in-form\">
                <h2 class=\"title\">Sign in</h2>
                $loginFields
                <input type=\"submit\" value=\"Login\" class=\"btn solid\" />
                <p class=\"social-text\">Or Sign in with social platforms</p>
                <div class=\"social-media\">
                    <a href=\"#\" class=\"social-icon\"><i class=\"fab fa-facebook-f\"></i></a>
                    <a href=\"#\" class=\"social-icon\"><i class=\"fab fa-twitter\"></i></a>
                    <a href=\"#\" class=\"social-icon\"><i class=\"fab fa-google\"></i></a>
                    <a href=\"#\" class=\"social-icon\"><i class=\"fab fa-linkedin-in\"></i></a>
                </div>
            </form>";
        return "
                <div class=\"forms-container\">
                    <div class=\"signin-signup\">
                        $loginForm
                        $signupForm
                    </div>
                </div>";
    }

    public function generateAdminForms($table, $omitColumnsLogin = [], $redirectPageLogin = '#', $method = 'POST', $defaultValues = [])
    {
        $query = "DESCRIBE $table";
        $stmt = $this->bdd->prepare($query);
        $stmt->execute();
        $columns = $stmt->fetchAll();
        $columnTypes = [
            'int' => 'number',
            'float' => 'number',
            'decimal' => 'number',
            'number' => 'number',
            'varchar' => 'text',
            'char' => 'text',
            'date' => 'date',
            'datetime' => 'datetime-local',
            'text' => 'textarea'
        ];

        $generateFields = function ($columns, $omitColumns) use ($columnTypes, $defaultValues) {
            $fields = '';
            foreach ($columns as $column) {
                $columnName = $column['Field'];
                $columnType = strtolower($column['Type']);
                $inputType = 'text';
                if (in_array($columnName, $omitColumns)) {
                    continue;
                }
                foreach ($columnTypes as $dbType => $inputTypeValue) {
                    if (strpos($columnType, $dbType) !== false) {
                        $inputType = $inputTypeValue;
                        break;
                    }
                }
                $defaultValue = isset($defaultValues[$columnName]) ? htmlspecialchars($defaultValues[$columnName]) : '';
                $fields .= "<div class=\"input-field\">";
                $fields .= "<i class=\"fas fa-user\"></i>";
                $fields .= $inputType === 'textarea' 
                    ? "<textarea name=\"{$columnName}\" placeholder=\"" . ucfirst(str_replace('_', ' ', $columnName)) . "\" class=\"form-control\">" . $defaultValue . "</textarea>"
                    : "<input type=\"{$inputType}\" name=\"{$columnName}\" placeholder=\"" . ucfirst(str_replace('_', ' ', $columnName)) . "\" value=\"{$defaultValue}\" class=\"form-control\" required />";
                $fields .= "</div>";
            }
            return $fields;
        };

        $loginFields = $generateFields($columns, $omitColumnsLogin);
        $loginForm = "
            <form action=\"$redirectPageLogin\" method=\"$method\" class=\"sign-in-form\">
                <h2 class=\"title\">Sign in</h2>
                $loginFields
                <input type=\"submit\" value=\"Login\" class=\"btn solid\" />
                <p class=\"social-text\">Or Sign in with social platforms</p>
                <div class=\"social-media\">
                    <a href=\"#\" class=\"social-icon\"><i class=\"fab fa-facebook-f\"></i></a>
                    <a href=\"#\" class=\"social-icon\"><i class=\"fab fa-twitter\"></i></a>
                    <a href=\"#\" class=\"social-icon\"><i class=\"fab fa-google\"></i></a>
                    <a href=\"#\" class=\"social-icon\"><i class=\"fab fa-linkedin-in\"></i></a>
                </div>
            </form>";
        return "
                <div class=\"forms-container\">
                    <div class=\"signin-signup\">
                        $loginForm
                    </div>
                </div>";
    }


    






}
