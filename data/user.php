<?php

require_once 'database.php';

class User extends Database
{
    /**
     * Adds a new user
     * 
     * @param $name     The username
     * @param $email    The user's email address
     * @param $password The user's password
     * @return The new user's ID, or 0 if there is an error,
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
            return 0;
        }
    }

    /**
     * Validates a user login
     * 
     * @param $email    The user's email address
     * @param $password The user's password
     * @return The user ID, or 0 if the validation is unsuccessful
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
                return 0;
            }
            // Incorrect password
            if (!password_verify($password, $result['cPasswordHash'])) {
                $this->lastErrorMessage = 'Incorrect credentials';
                return 0;
            }
            return [
                'user_id' => $result['nUserID'],
                'name'    => $result['cName']
            ];

        } catch (PDOException $e) {
            $this->lastErrorMessage = 'Database error: ' . $e->getMessage();
            return 0;
        }
    }

    /**
     * It resets a user token
     * 
     * @param $email The email of the user whose token is reset
     * @return The hashed token or 0 if there was an error
     */
    public function resetToken(string $email): string|int
    {
        // random_bytes() generates a cryptographically secure sequence 
        //      of bytes with the length it receives as a parameter
        // bin2hex() converts binary data into its hexadecimal representation
        $token = bin2hex(random_bytes(16));
        // hash() generates a hash value, in this case using the sha256 algorithm
        $tokenHash = hash('sha256', $token);
        // As the token could be figured out via a brute force attack,
        //      it is set to expire in 30 minutes
        $expiry = date('Y-m-d H:i:s', time() + (60 * 30));

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
            if ($stmt->rowCount() > 0) {
                return $tokenHash;
            } else {
                $this->lastErrorMessage = 'The user token hash was not updated';
                return 0;
            }
        } catch (PDOException $e) {
            $this->lastErrorMessage = 'Database error: ' . $e->getMessage();
            return 0;
        }
    }

    /**
     * Validates a user token against the database
     * 
     * @param $token The token to validate
     * @return The user ID or 0 if the token does not exist or has expired
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
                return 0;
            }
            // The token exists, but it has expired
            if (strtotime($result['dResetTokenExpiresAt']) <= time()) {
                $this->lastErrorMessage = 'The token has expired';
                return 0;
            }
            // The token exists and is valid
            return $result['nUserID'];
        } catch (PDOException $e) {
            $this->lastErrorMessage = 'Database error: ' . $e->getMessage();
            return 0;
        }
    }

    /**
     * It resets a user password
     * 
     * @param $userID   The ID of the user whose password is reset
     * @param $password The new password
     * @return 1 if the password reset is successful, 0 otherwose
     */
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
            if ($stmt->rowCount() > 0) {
                return 1;
            } else {
                $this->lastErrorMessage = 'The password update was unsuccessful';
                return 0;
            }
        } catch (PDOException $e) {
            $this->lastErrorMessage = 'Database error: ' . $e->getMessage();
            return 0;
        }
    }
}