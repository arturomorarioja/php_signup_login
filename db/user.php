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
}