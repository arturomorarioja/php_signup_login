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
     * @return string The new user's activation hash, or 0 if there is an error,
     *         whose message will be logged into $this->lastErrorMessage
     */
    public function add(string $name, string $email, string $password): string|int
    {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $accountActivationHash = $this->generateToken();
        
        $sql =<<<'SQL'
            INSERT INTO user
                (cName, cEmail, cPasswordHash, cAccountActivationHash)
            VALUES
                (:name, :email, :passwordHash, :accountActivationHash);
        SQL;
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'name'                  => $name,
                'email'                 => $email,
                'passwordHash'          => $passwordHash,
                'accountActivationHash' => $accountActivationHash
            ]);
            if ($stmt->rowcount() === 0) {
                $this->lastErrorMessage = 'The user could not be added to the database';
                return 0;
            } else {
                return $accountActivationHash;
            }
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
     * @return The user ID, or 0 if there is an error,
     *         whose message will be logged into $this->lastErrorMessage
     */
    public function validateLogin(string $email, string $password): array|int
    {
        $sql =<<<'SQL'
            SELECT nUserID, cName, cPasswordHash, cAccountActivationHash
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
            // Account not activated
            if ($result['cAccountActivationHash'] !== null) {
                $this->lastErrorMessage = 'Account not yet activated. Please check your email';
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
     * It resets a user token to give them the ability 
     * to reset their password within the next half hour
     * 
     * @param $email The email of the user whose token is reset
     * @return string The hashed token or 0 if there was an error,
     *         whose message will be logged into $this->lastErrorMessage
     */
    public function resetPasswordResetToken(string $email): string|int
    {
        // User existence is checked
        $sql =<<<'SQL'
            SELECT COUNT(*) AS Total
            FROM user
            WHERE cEmail = :email;
        SQL;
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(['email' => $email]);
            if ($stmt->fetch()['Total'] === 0) {
                $this->lastErrorMessage = 'There is no user with this email address';
                return 0;
            }
        } catch (PDOException $e) {
            $this->lastErrorMessage = 'Database error: ' . $e->getMessage();
            return 0;
        }

        // A unique user token is generated to guarantee the user's legitimacy
        $tokenHash = $this->generateToken();
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
     * Validates an account activation token against the database
     * 
     * @param $tokenHash The token to validate
     * @return The user ID or 0 if the token does not exist, has expired or there is an error,
     *         whose message will be logged into $this->lastErrorMessage
     */
    public function validateAccountActivationToken(string $tokenHash): int
    {
        $sql =<<<'SQL'
            SELECT nUserID
            FROM user
            WHERE cAccountActivationHash = :accountActivationHash;
        SQL;
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(['accountActivationHash' => $tokenHash]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            // The token does not exist
            if (gettype($result) !== 'array') {
                $this->lastErrorMessage = 'Nonexisting token';
                return 0;
            }

            // Account activation is successful.
            // The activation token can be deleted.
            $sql =<<<'SQL'
                UPDATE user
                SET cAccountActivationHash = NULL
                WHERE nUserID = :userID;
            SQL;
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(['userID' => $result['nUserID']]);

            return $result['nUserID'];
        } catch (PDOException $e) {
            $this->lastErrorMessage = 'Database error: ' . $e->getMessage();
            return 0;
        }
    }

    /**
     * Validates a password reset token against the database
     * 
     * @param $tokenHash The token to validate
     * @return string The user ID or 0 if the token does not exist, has expired or there is an error,
     *         whose message will be logged into $this->lastErrorMessage
     */
    public function validatePasswordsToken(string $tokenHash): int 
    {
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
     * It validates user passwords
     * 
     * @param $password       The password
     * @param $repeatPassword The password repeated (both must match)
     * @return array Error messages
     */
    public static function validatePasswords(string $password, string $repeatPassword): array
    {
        $errorMessages = [];

        if (strlen($password) < 8) {
            $errorMessages[] = 'Password must be at least 8 characters';
        }
        
        if (!preg_match('/[a-z]/i', $password)) {
            $errorMessages[] = 'Password must contain at least one letter';
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errorMessages[] = 'Password must contain at least one number';
        }
        
        if ($password !== $repeatPassword) {
            $errorMessages[] = 'Passwords must have the same value';
        }    

        return $errorMessages;
    }

    /**
     * It validates user information upon sign up
     * 
     * @param $name           Username
     * @param $email          User email address
     * @param $password       User password
     * @param $repeatPassword Repeated user password (both must match)
     * @return array Error messages
     */
    public static function validateInformation(string $name, string $email, string $password, string $repeatPassword): array
    {
        $errorMessages = [];

        if (empty($name)) { 
            $errorMessages[] = 'Name is required'; 
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMessages[] = 'Valid email is required';
        }
    
        return array_merge($errorMessages, self::validatePasswords($password, $repeatPassword));
    }

    /**
     * It resets a user password
     * 
     * @param $userID   The ID of the user whose password is reset
     * @param $password The new password
     * @return 1 if the password reset is successful, 0 if there is an error,
     *         whose message will be logged into $this->lastErrorMessage
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

    /**
     * Generates a hashed token for use when either 
     * activating an account or resetting a password
     * 
     * @return string The hashed token
     */
    private function generateToken(): string
    {
        // random_bytes() generates a cryptographically secure sequence 
        //      of bytes with the length it receives as a parameter
        // bin2hex() converts binary data into its hexadecimal representation
        $token = bin2hex(random_bytes(16));
        // hash() generates a hash value, in this case using the sha256 algorithm
        return hash('sha256', $token);
    }
}