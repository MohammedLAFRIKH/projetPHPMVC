<?php

namespace App\Models;

use App\Database;

class UtilisateurModel {
    private $connexion;

    const TABLE_NAME = 'utilisateur';
    const COL_MATRICULE = 'matricule';
    const COL_EMAIL = 'email';
    const COL_MOT_PASSE = 'motPasse';
    const COL_CONFIRMATION_TOKEN = 'confirmation_token';
    const COL_RESET_TOKEN = 'reset_token';

    const COL_EMAIL_CONFIRMED = 'is_confirmed';

    public function __construct() {
        $database = Database::getInstance();
        $this->connexion = $database->getConnection();
    }

    public function getAllUsers() {
        $query = "SELECT * FROM " . self::TABLE_NAME;

        return $this->executeSelectQuery($query);
    }
    
    public function loginUser($cne, $password)
    {
        try {
            $user = $this->getUserByCNE($cne);
    
            if (!$user || $user[self::COL_EMAIL_CONFIRMED] != 1) {
                return false; // User not found or email not confirmed
            }
    
            $hashedPasswordFromDB = $user[self::COL_MOT_PASSE];

            if (password_verify($password, $hashedPasswordFromDB)) {
                return $user;
            } else {
                return false; // Incorrect password
            }
        } catch (\PDOException $e) {
            // Log or handle the database exception with more details
            throw new \Exception("An error occurred while trying to log in: " . $e->getMessage());
        }
    }

    // Inside the UtilisateurModel class

    public function isUserDataComplete($matricule) {
        $query = "SELECT isUserDataComplete FROM " . self::TABLE_NAME . " WHERE " . self::COL_MATRICULE . " = ?";
        $stmt = $this->connexion->prepare($query);
        $stmt->bind_param('s', $matricule);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['isUserDataComplete'] == 1; // Returns true if user data is complete
        } else {
            return false; // User not found or data not complete
        }
    }

    
// Change private to protected or public
    public function getUserByCNE($cne)
    {
        $query = "SELECT * FROM " . self::TABLE_NAME . " WHERE " . self::COL_MATRICULE . " = ?";
        $stmt = $this->connexion->prepare($query);
        $stmt->bind_param('s', $cne);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        } else {
            return null;
        }
    }


    public function enregistrement($userData) {
        try {
            $query = '
                UPDATE ' . self::TABLE_NAME . ' SET
                civility = ?,
                familySituation = ?,
                prenom = ?,
                nom = ?,
                prenom_ar = ?,
                nom_ar = ?,
                cin = ?,
                phone = ?,
                lieuNaiss = ?,
                birthCityArabic = ?,
                birthProvince = ?,
                dateNaiss = ?,
                sexe = ?,
                address = ?,
                codePostal = ?,
                currentCountry = ?,
                nationality = ?,
                etablissement = ?,
                type_de_bac = ?,
                annee_du_bac = ?,
                diplome_bac_plus_2 = ?,
                specialite = ?,
                annee_du_diplome = ?,
                note_s1 = ?,
                note_s3 = ?,
                note_s2 = ?,
                note_s4 = ?,
                isUserDataComplete = ?,
                choix_filiere1 = ?,
                choix_filiere2 = ?,
                piece_jointe = ?
                WHERE matricule = ?';

            $stmt = $this->connexion->prepare($query);

            // Bind parameters
            $stmt->bind_param('ssssssssssssssssssssssssssssssss', 
            $userData['civility'],
            $userData['familySituation'],
            $userData['firstName'],
            $userData['lastName'],
            $userData['firstNameArabic'],
            $userData['lastNameArabic'],
            $userData['cin'],
            $userData['phone'],
            $userData['lieuNaiss'],
            $userData['birthCityArabic'],
            $userData['birthProvince'],
            $userData['dateNaiss'],
            $userData['sexe'],
            $userData['address'],
            $userData['codePostal'],
            $userData['currentCountry'],
            $userData['nationality'],
            $userData['etablissement'],
            $userData['type_de_bac'],
            $userData['annee_du_bac'],
            $userData['diplome_bac_plus_2'],
            $userData['specialite'],
            $userData['annee_du_diplome'],
            $userData['note_s1'],
            $userData['note_s3'],
            $userData['note_s2'],
            $userData['note_s4'],
            $userData['isUserDataComplete'],
            $userData['choix_filiere1'],
            $userData['choix_filiere2'],
            $userData['filePath'],
            $userData['matricule']
            
        );
        

            // Execute the query
            $stmt->execute();

            // Return the number of affected rows
            return $stmt->affected_rows;
        } catch (\PDOException $e) {
            // Log or handle the database exception with more details
            throw new \Exception("An error occurred during update: " . $e->getMessage());
        }
    }


    
    public function getUserByEmail($email) {
        try {
            $query = "SELECT " . self::COL_MATRICULE . " FROM " . self::TABLE_NAME . " WHERE " . self::COL_EMAIL . " = ?";
            $stmt = $this->connexion->prepare($query);
            $stmt->bind_param('s', $email);  // 's' represents a string, adjust if needed

            $stmt->execute();

            // Bind the result
            $stmt->bind_result($matricule);

            // Fetch the user data
            $stmt->fetch();

            $user = [
                self::COL_MATRICULE => $matricule,
            ];

            return $user;
        } catch (\Exception $e) {
            // Log the error or throw a custom exception
            echo "Error: " . $e->getMessage(); // Debugging: Print the error message
            return null;
        }
    }


    public function generatePasswordResetToken($email) {
        try {
            $token = bin2hex(random_bytes(32)); // Generate a random token
    
            $query = "UPDATE " . self::TABLE_NAME . " SET " . self::COL_RESET_TOKEN . " = ? WHERE " . self::COL_EMAIL . " = ?";
            $stmt = $this->connexion->prepare($query);
            $stmt->bind_param('ss', $token, $email);
            $stmt->execute();
    
            return $token;
        } catch (\Exception $e) {
            // Log or handle the exception
            echo "Error: " . $e->getMessage();
            return null;
        }
    }
    
    public function getUserByResetToken($token) {
        try {
            $query = "SELECT * FROM " . self::TABLE_NAME . " WHERE " . self::COL_RESET_TOKEN . " = ? LIMIT 1";
            $stmt = $this->connexion->prepare($query);
            $stmt->bind_param('s', $token);
            $stmt->execute();
    
            $result = $stmt->get_result();
    
            if ($result && $result->num_rows > 0) {
                return $result->fetch_assoc();
            } else {
                return null;
            }
        } catch (\Exception $e) {
            // Log or handle the exception
            echo "Error: " . $e->getMessage();
            return null;
        }
    }
    // UtilisateurModel.php

public function updatePasswordWithToken($token, $password) {
    try {
        $query = "UPDATE " . self::TABLE_NAME . " SET " . self::COL_MOT_PASSE . " = ? WHERE " . self::COL_RESET_TOKEN . " = ?";
        $stmt = $this->connexion->prepare($query);
        

        $stmt->bind_param('ss', $password, $token);
        $stmt->execute();

        return $stmt->affected_rows > 0;
    } catch (\PDOException $e) {
        // Log or handle the database exception with more details
        throw new \Exception("An error occurred during password update: " . $e->getMessage());
    }
}

    public function clearResetToken($email) {
        try {
            $query = "UPDATE " . self::TABLE_NAME . " SET " . self::COL_RESET_TOKEN . " = NULL WHERE " . self::COL_EMAIL . " = ?";
            $stmt = $this->connexion->prepare($query);
            $stmt->bind_param('s', $email);
            $stmt->execute();
    
            return $stmt->affected_rows > 0;
        } catch (\Exception $e) {
            // Log or handle the exception
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    

    public function registerUser($userData)
    {
        $confirmationToken = $userData['confirmationToken'];
    
        $stmt = $this->connexion->prepare("INSERT INTO " . self::TABLE_NAME . " (" . self::COL_MATRICULE . ", " . self::COL_EMAIL . ", " . self::COL_MOT_PASSE . ", " . self::COL_CONFIRMATION_TOKEN . ") VALUES (?, ?, ?, ?)");
    
        $stmt->bind_param('ssss', $userData['CNE'], $userData['email'], $userData['password'], $confirmationToken);
    
        $result = $stmt->execute();
    
        if ($result) {
            return ['userId' => true, 'confirmationToken' => $confirmationToken];
        } else {
            return false; // Registration failed
        }
    }
    
    public function confirmUser($userId) {
        $query = "UPDATE " . self::TABLE_NAME . " SET is_confirmed = true WHERE " . self::COL_MATRICULE . " = ?";
        return $this->executeUpdateQuery($query, 'i', $userId);
    }

    public function verifyToken($CNE, $token) {
        $storedToken = $this->getTokenFromDatabase($CNE);

        return $token === $storedToken;
    }

    public function getTokenFromDatabase($CNE) {
        $query = "SELECT " . self::COL_CONFIRMATION_TOKEN . " FROM " . self::TABLE_NAME . " WHERE " . self::COL_MATRICULE . " = ?";
        $result = $this->executeSelectQuery($query, 's', $CNE);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row[self::COL_CONFIRMATION_TOKEN];
        }

        return null;
    }
    // Dans la classe UtilisateurModel

public function isUserConfirmed($CNE) {
    $query = "SELECT is_confirmed FROM " . self::TABLE_NAME . " WHERE " . self::COL_MATRICULE . " = ?";
    $stmt = $this->connexion->prepare($query);
    $stmt->bind_param('s', $CNE);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['is_confirmed'] == 1; // Retourne vrai si l'utilisateur est confirmé
    } else {
        return false; // L'utilisateur n'est pas trouvé ou n'est pas confirmé
    }
}


public function checkEmailExists($email) {
    $query = "SELECT " . self::COL_EMAIL . " FROM " . self::TABLE_NAME . " WHERE " . self::COL_EMAIL . " = ?";
    $result = $this->executeSelectQuery($query, 's', $email);

    if ($result && $result->num_rows > 0) {
        return true; // L'email existe
    } else {
        return false; // L'email n'existe pas
    }
}
    private function executeSelectQuery($query, $bindType = null, ...$bindParams) {
        $stmt = $this->connexion->prepare($query);

        if ($bindType && $bindParams) {
            array_unshift($bindParams, $bindType);
            $this->bindParams($stmt, $bindParams);
        }

        $stmt->execute();

        $result = $stmt->get_result();

        return $result;
    }

    private function executeUpdateQuery($query, $bindType = null, ...$bindParams) {
        $stmt = $this->connexion->prepare($query);

        if ($bindType && $bindParams) {
            array_unshift($bindParams, $bindType);
            $this->bindParams($stmt, $bindParams);
        }

        return $stmt->execute();
    }

    private function bindParams($stmt, $params) {
        $bindParams = [];
        foreach ($params as $key => $value) {
            $bindParams[$key] = &$params[$key];
        }

        call_user_func_array([$stmt, 'bind_param'], $bindParams);
    }

}
