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

    public function generateTableau($liste, $titre = "Tableau Dynamique", $colonneMiseEnEvidence = null, $omitColumns = [], $crud = false, $redirectUpdate = null, $redirectDelete = null)
    {
        if (empty($liste)) {
            return "<div class='order'><p>Aucune donnée disponible pour " . htmlspecialchars(ucfirst($titre)) . ".</p></div>";
        }
        $entetes = array_filter(array_keys($liste[0]), function ($key) use ($omitColumns) {
            return is_string($key) && !in_array($key, $omitColumns) && stripos($key, 'id') !== 0;
        });
        if ($crud) {
            $entetes[] = 'Modifier';
            $entetes[] = 'Supprimer';
        }
        $html = "
        <div class='order'>
            <div class='head'>
                <h3>$titre</h3>
                <i class='fas fa-search'></i>
                <i class='fas fa-filter'></i>
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
                if ($entete === 'Modifier') {
                    $updateUrl = $redirectUpdate ? htmlspecialchars($redirectUpdate . "?id=" . $item['id_habitation']) : "#";
                    $html .= "<td><a href='{$updateUrl}' class='crud-icon'><i class='fas fa-edit' style='color:#0c81ee;'></i></a></td>";
                } else if ($entete === 'Supprimer') {
                    $deleteUrl = $redirectDelete ? htmlspecialchars($redirectDelete . "?id=" . $item['id_habitation']) : "#";
                    $html .= "<td><a href='{$deleteUrl}' class='crud-icon'><i class='fas fa-trash-alt' style='color:#ff3d3d;'></i></a></td>";
                } else {
                    $classe = ($entete === $colonneMiseEnEvidence) ? 'status completed' : '';
                    if ($entete == "images") {
                        $html .= "<td><span class='{$classe}'><img src='assets/img/" . $item[$entete][0] . "'></span></td>";
                    } else {
                        $html .= "<td><span class='{$classe}'>" . $item[$entete] . "</span></td>";
                    }
                }
            }
            $html .= "</tr>";
        }
        $html .= "
                </tbody>
            </table>
        </div>";
        return $html;
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
    
    
    
    


    public function generateInputFieldsWithDefaults($table, $omitColumns = [], $hidden = [], $conditions = [], $numericDouble = [], $canNull = false)
    {
        $html = "";
        foreach ($hidden as $hiddenName => $hiddenValue) {
            $html .= "<input type=\"hidden\" name=\"{$hiddenName}\" value=\"{$hiddenValue}\">";
        }
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
                if ($columnName === 'password') {
                    $inputType = 'password';
                } else {
                    foreach ($columnTypes as $dbType => $inputTypeValue) {
                        if (strpos($columnType, $dbType) !== false) {
                            $inputType = $inputTypeValue;
                            break;
                        }
                    }
                }
                $defaultValue = $defaultValues[$columnName] ?? '';
                if (in_array($columnName, $numericDouble)) {
                    $html .= "<div class=\"form-group\">";
                    $html .= "<label for=\"min_{$columnName}\">Min " . ucfirst(str_replace('_', ' ', $columnName)) . "</label>";
                    $html .= "<input type=\"{$inputType}\" name=\"min_{$columnName}\" id=\"min_{$columnName}\" class=\"form-control\" value=\"\" " . ($canNull ? '' : 'required') . " />";
                    $html .= "</div>";
                    $html .= "<div class=\"form-group\">";
                    $html .= "<label for=\"max_{$columnName}\">Max " . ucfirst(str_replace('_', ' ', $columnName)) . "</label>";
                    $html .= "<input type=\"{$inputType}\" name=\"max_{$columnName}\" id=\"max_{$columnName}\" class=\"form-control\" value=\"\" " . ($canNull ? '' : 'required') . " />";
                    $html .= "</div>";
                } else {
                    $required = $canNull ? '' : 'required';
                    $html .= "<div class=\"form-group\">";
                    $html .= "<label for=\"{$columnName}\">" . ucfirst(str_replace('_', ' ', $columnName)) . "</label>";
                    if ($inputType === 'textarea') {
                        $html .= "<textarea name=\"{$columnName}\" id=\"{$columnName}\" class=\"form-control\" $required>{$defaultValue}</textarea>";
                    } else {
                        $html .= "<input type=\"{$inputType}\" name=\"{$columnName}\" id=\"{$columnName}\" class=\"form-control\" value=\"{$defaultValue}\" $required />";
                    }
                    $html .= "</div>";
                }
            }
        } catch (Exception $e) {
            $html .= "Erreur lors de la génération des champs : " . $e->getMessage();
        }

        return $html;
    }



    public function generateInsertFormWithDefaults($table, $omitColumns = [], $redirectPage = '#', $method = 'POST', $hidden = [], $conditions = [], $numericDouble = [], $canNull = false)
    {
        $html= "<form action=\"$redirectPage\" method=\"$method\">";
        $html.=$this->generateInputFieldsWithDefaults($table, $omitColumns , $hidden , $conditions, $numericDouble, $canNull);
        $html.= "<button type=\"submit\" class=\"btn btn-primary login-button\">Submit</button>";
        $html.= "</form>";
        return $html;
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
        return ['status'=>'success'];
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


    public function generateSelectField($table, $value, $column, $label = null, $omitValues = [])
    {
        try {
            $query = "SELECT DISTINCT $value, $column FROM $table";
            $stmt = $this->bdd->prepare($query);
            $stmt->execute();
            $rows = $stmt->fetchAll();
            if ($label === null) {
                $label = ucfirst(str_replace('_', ' ', $column));
            }
            
            $html = "<div class=\"form-group\">";
            $html .= "<label for=\"$value\">$label</label>";
            $html .= "<select name=\"$value\" id=\"$value\" class=\"form-control\">";
            $html .= "<option value=\"\">Choose</option>";
            
            foreach ($rows as $row) {
                if (in_array($row[$value], $omitValues)) {
                    continue; 
                }
                $optionValue = htmlspecialchars($row[$value]); 
                $displayText = htmlspecialchars($row[$column]); 
                $html .= "<option value=\"{$optionValue}\">{$displayText}</option>";
            }
            
            $html .= "</select>";
            $html .= "</div>";
            
            return $html; 
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la génération du champ select : " . $e->getMessage());
        }
    }

   


    public function generateInputFields($table, $omitColumns = [], $hidden = [], $canNull = false, $numericDouble = [])
    {
        $html = "";
        foreach ($hidden as $hiddenName => $hiddenValue) {
            $html .= "<input type=\"hidden\" name=\"{$hiddenName}\" value=\"{$hiddenValue}\">";
        }
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
            if ($columnName === 'password') {
                $inputType = 'password';
            } else {
                foreach ($columnTypes as $dbType => $inputTypeValue) {
                    if (strpos($columnType, $dbType) !== false) {
                        $inputType = $inputTypeValue;
                        break;
                    }
                }
            }
            if (in_array($columnName, $numericDouble)) {
                $html .= "<div class=\"form-group\">";
                $html .= "<label for=\"min_{$columnName}\">Min " . ucfirst(str_replace('_', ' ', $columnName)) . "</label>";
                $html .= "<input type=\"{$inputType}\" name=\"min_{$columnName}\" id=\"min_{$columnName}\" class=\"form-control\" " . ($canNull ? '' : 'required') . " />";
                $html .= "</div>";
                $html .= "<div class=\"form-group\">";
                $html .= "<label for=\"max_{$columnName}\">Max " . ucfirst(str_replace('_', ' ', $columnName)) . "</label>";
                $html .= "<input type=\"{$inputType}\" name=\"max_{$columnName}\" id=\"max_{$columnName}\" class=\"form-control\" " . ($canNull ? '' : 'required') . " />";
                $html .= "</div>";
            } else {
                $required = $canNull ? '' : 'required';
                $html .= "<div class=\"form-group\">";
                $html .= "<label for=\"{$columnName}\">" . ucfirst(str_replace('_', ' ', $columnName)) . "</label>";
                if ($inputType === 'textarea') {
                    $html .= "<textarea name=\"{$columnName}\" id=\"{$columnName}\" class=\"form-control\" $required></textarea>";
                } else {
                    $html .= "<input type=\"{$inputType}\" name=\"{$columnName}\" id=\"{$columnName}\" class=\"form-control\" $required />";
                }
                $html .= "</div>";
            }
        }
        
        return $html;
    }


    public function generateInsertForm($table, $omitColumns = [], $redirectPage = '#', $method = 'POST', $hidden = [], $canNull=false, $numericDouble=[])
    {
        $html= "<form action=\"$redirectPage\" method=\"$method\">";
        $html.= $this-> generateInputFields($table, $omitColumns, $hidden, $canNull, $numericDouble); 
        $html.= "<button type=\"submit\" class=\"btn btn-primary \">Submit</button>";
        $html.="</form>";
        return $html;
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


    public function generateUpload($labelText, $fieldName, $required = true)
    {
        $requiredAttr = $required ? 'required' : '';
        $html = "<div class=\"form-group\">";
        $html .= "<label for=\"{$fieldName}\">" . htmlspecialchars($labelText) . "</label>";
        $html .= "<input type=\"file\" id=\"{$fieldName}\" name=\"{$fieldName}\" class=\"form-control\" {$requiredAttr}>";
        $html .= "</div>";
        return $html;
    }






}
