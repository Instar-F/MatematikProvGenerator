<?php

class User {
	
	public $username;
	public $role_level;
	public $pdo;
	
	public function __construct($pdo) {
		$this->pdo = $pdo;
    }
	
	public function login($uName, $password) {
    try {
        $stmt = $this->pdo->prepare("
			SELECT u_id, u_uname, u_mail, u_password, r_level
			FROM users
            INNER JOIN roles
            ON users.u_role_fk = roles.r_id
			WHERE u_uname = :uname OR u_mail = :umail
			LIMIT 1
		");
		$stmt->execute([
			'uname' => $uName,
			'umail' => $uName
		]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['u_password'])) {
            // Start session if not already started
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Store user data in session
            $_SESSION['user'] = [
                'id' => $user['u_id'],
                'name' => $user['u_uname'],
                'email' => $user['u_mail'],
                'role' => $user['r_level']
            ];

            return ['success' => true];
        }

        return ['success' => false, 'error' => 'Invalid username/email or password'];

    } catch (PDOException $e) {
        return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
    }
}

    public function logout() {
        session_unset(); 
        session_destroy();
    }

    public function checkLoginStatus($userId = null) {
        if ($userId !== null) {
            return true;
        }
        else {
            return false;
        }
    }

    public function checkUserRole($userRole, $requirement) {
        if ($userRole >= $requirement) {
            return true;
        } else {
            return false;
        }
    }

	
	public function checkUserRegisterInfo($uname, $umail, $upass, $upassrpt, $condition, $currentUserId = null) {
   //Steps 1-3 happens only for user creation, not user edit
	if ($condition === "create") {   
    // Step 1: Username Length Validation
    if (strlen($uname) < 3 || strlen($uname) > 20) {
        return ['success' => false, 'error' => 'Username must be between 3 and 20 characters long.'];
    }

    // Step 2: Check if username already exists (only during create, unless it's the same username)
    
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE LOWER(u_uname) = LOWER(?)");
        $stmt->execute([strtolower($uname)]);
        if ($stmt->rowCount() > 0) {
            return ['success' => false, 'error' => 'Username already exists.'];
        }


    // Step 3: Check if email exists and validate email format
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE LOWER(u_mail) = LOWER(?)");
        $stmt->execute([strtolower($umail)]);
        if ($stmt->rowCount() > 0 && ($currentUserId === null || $stmt->fetch()['u_id'] !== $currentUserId)) {
            return ['success' => false, 'error' => 'Email already exists.'];
        }
    }

    // Step 4: Check if email is valid
    if (!filter_var($umail, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'error' => 'Invalid email format.'];
    }


	if($condition !== "edit" || $upass !== ""){
		// Step 5: Check if passwords match
		if ($upass !== $upassrpt) {
			return ['success' => false, 'error' => 'Passwords do not match.'];
		}

		// Step 6: Validate password strength
	   if (strlen($upass) < 6) {
			return ['success' => false, 'error' => 'Password must be at least 6 characters long.'];
		}
		if (!preg_match('/[A-Z]/', $upass)) {
			return ['success' => false, 'error' => 'Password must contain at least one uppercase letter.'];
		}
		if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $upass)) {
			return ['success' => false, 'error' => 'Password must contain at least one special character.'];
		}
	}

    // ✅ All checks passed
    return ['success' => true];
}
	
	public function createUser($uname, $umail, $upass, $urole){
		try {
			// Hash the password securely
			$hashedPassword = password_hash($upass, PASSWORD_DEFAULT);

			// Begin transaction
			$this->pdo->beginTransaction();

			// Insert user into database
			$stmt = $this->pdo->prepare("INSERT INTO users (u_uname, u_mail, u_password, u_role_fk) 
										 VALUES (?, ?, ?, ?)");
			$stmt->execute([$uname,$umail, $hashedPassword, $urole]);

			// Commit transaction
			$this->pdo->commit();

			return ['success' => true];

		} 
		catch (Exception $e) {
			// Rollback if something goes wrong
			$this->pdo->rollBack();
			return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
		}
	}
	
	public function editUser($userId, $uname, $umail, $upass, $urole) {
    try {
        // Begin transaction
        $this->pdo->beginTransaction();

        // Prepare the base SQL query to update user info (excluding username)
        $query = "UPDATE users SET u_email = ?, u_role_fk = ?";

        // If password is provided (i.e., not empty), hash and update it
        if (!empty($upass)) {
            $hashedPassword = password_hash($upass, PASSWORD_DEFAULT);
            $query .= ", u_password = ?";
            $stmt = $this->pdo->prepare($query . " WHERE u_id = ?");
            $stmt->execute([$umail, $urole, $hashedPassword, $userId]);
        } else {
            // If no password change, exclude the password from the query
            $stmt = $this->pdo->prepare($query . " WHERE u_id = ?");
            $stmt->execute([$umail, $urole, $userId]);
        }

        // Commit transaction
        $this->pdo->commit();

        return ['success' => true];
    } catch (Exception $e) {
        // Rollback if something goes wrong
        $this->pdo->rollBack();
        return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
    }
}
	
	public function selectUserInfo($userId) {
    try {
        // Prepare the SQL statement
        $stmt = $this->pdo->prepare("SELECT u_id, u_name, u_mail, u_role_fk FROM users WHERE u_id = ?");
        $stmt->execute([$userId]);

        // Fetch user data
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            return ['success' => true, 'data' => $user];
        } else {
            return ['success' => false, 'error' => 'User not found.'];
        }
    } catch (Exception $e) {
        return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
    }
}
	
	public function searchUsers($userName){
		
		try {
        // Prepare the SQL statement
        $stmt = $this->pdo->prepare("
			SELECT u_id, u_name, u_fname, u_lname, u_email, r_name 
			FROM users 
			INNER JOIN roles 
			ON users.u_role_fk = roles.r_id
			WHERE u_name LIKE ?");
		$stmt->execute(["%" . $userName . "%"]);

        // Fetch user data
        $userList = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($userList) {
            return ['success' => true, 'data' => $userList];
        } else {
            return ['success' => false, 'error' => 'User not found.'];
        }
    } catch (Exception $e) {
        return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
    }
		
	}
	
public function deleteUser($userId) {
    try {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE u_id = ?");
        $stmt->execute([$userId]);

        if ($stmt->rowCount() > 0) {
            return ['success' => true, 'message' => 'User deleted successfully.'];
        } else {
            return ['success' => false, 'message' => 'No user found with that ID.'];
        }
    } 
	catch (PDOException $e) {
        return ['success' => false, 'message' => 'Database error.', 'error' => $e->getMessage()
        ];
    }
}
	
	
	/*public function displayUser() {
        echo "Username: {$this->username}, Role: {$this->role_level}";
		//print_r $this->pdo;
    }*/
	
}