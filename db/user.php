<?php

require_once 'database.php';

class User extends Database
{
    /**
     * Adds a new user
     * 
     * @return The new user's ID, or -1 if there is an error,
     *         whose message will be logged into $this->lastErrorMessage
     */
    public function add(string $name, string $email, string $password): int
    {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        $sql =<<<'SQL'
            INSERT INTO user
                (cName, cEmail, cPasswordHash)
            VALUES
                (:name, :email, :passwordHash);
        SQL;
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'name'          => $name,
                'email'         => $email,
                'passwordHash'  => $passwordHash
            ]);
            return $this->conn->lastInsertId();
        } catch(PDOException $e) {
            if ($e->getCode() === '23000') {
                $this->lastErrorMessage = "A user with the email address $email already exists.";
            } else {
                $this->lastErrorMessage = 'Database error: ' . $e->getMessage();
            }
            return -1;
        }
    }

    /**
     * Validates a user login
     * 
     * @return The user ID, or -1 if the validation is unsuccessful
     */
    public function validateLogin(string $email, string $password): array|int
    {
        $sql =<<<'SQL'
            SELECT nUserID, cName, cPasswordHash
            FROM user
            WHERE cEmail = :email;
        SQL;
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(['email' => $email]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            // Email not found
            if (gettype($result) !== 'array') {
                $this->lastErrorMessage = 'Incorrect credentials';
                return -1;
            }
            // Incorrect password
            if (!password_verify($password, $result['cPasswordHash'])) {
                $this->lastErrorMessage = 'Incorrect credentials';
                return -1;
            }
            return [
                'user_id' => $result['nUserID'],
                'name'    => $result['cName']
            ];

        } catch (PDOException $e) {
            $this->lastErrorMessage = 'Database error: ' . $e->getMessage();
            return -1;
        }
    }

    public function resetToken(string $email, string $tokenHash, string $expiry): int
    {
        $sql =<<<'SQL'
            UPDATE user
            SET cResetTokenHash = :resetTokenHash,
                dResetTokenExpiresAt = :resetTokenExpiresAt
            WHERE cEmail = :email;
        SQL;
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'resetTokenHash'        => $tokenHash,
                'resetTokenExpiresAt'   => $expiry,
                'email'                 => $email
            ]);
            return $stmt->rowCount() > 0 ? 1 : -1;
        } catch (PDOException $e) {
            $this->lastErrorMessage = 'Database error: ' . $e->getMessage();
            return -1;
        }
    }

    /**
     * Validates a user token against the database
     * 
     * @return The user ID or -1 if the token does not exist or has expired
     */
    public function validateToken(string $token): int 
    {
        $tokenHash = hash('sha256', $token);

        $sql =<<<'SQL'
            SELECT nUserID, dResetTokenExpiresAt
            FROM user
            WHERE cResetTokenHash = :resetTokenHash;
        SQL;
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(['resetTokenHash' => $tokenHash]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            // The token does not exist
            if (gettype($result) !== 'array') {
                $this->lastErrorMessage = 'Nonexisting token';
                return -1;
            }
            // The token exists, but it has expired
            if (strtotime($result['dResetTokenExpiresAt']) <= time()) {
                $this->lastErrorMessage = 'The token has expired';
                return -1;
            }
            // The token exists and is valid
            return $result['nUserID'];
        } catch (PDOException $e) {
            $this->lastErrorMessage = 'Database error: ' . $e->getMessage();
            return -1;
        }
    }

    public function resetPassword(int $userID, string $password): int
    {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $sql =<<<'SQL'
            UPDATE user
            SET cPasswordHash = :passwordHash,
                cResetTokenHash = NULL,
                dResetTokenExpiresAt = NULL
            WHERE nUserID = :userID;
        SQL;
        try {

            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'passwordHash'  => $passwordHash,
                'userID'        => $userID
            ]);
            return ($stmt->rowCount() > 0);
        } catch (PDOException $e) {
            $this->lastErrorMessage = 'Database error: ' . $e->getMessage();
            return -1;
        }
    }
}