<?php 
include '../core/init.php';
require_once '../core/classes/validation/Validator.php';
use validation\Validator;

if (isset($_POST['signup'])) {
   
    $email = $_POST['email'];
    $name = $_POST['name'];
    $password = $_POST['password'];
    $username = $_POST['username'];
    $birthdate = isset($_POST['birthdate']) ? $_POST['birthdate'] : '';
    $social_provider = isset($_POST['social_provider']) ? $_POST['social_provider'] : '';

    // Check if it's a social login attempt
    $isSocialLogin = !empty($social_provider);
    
    if(!empty($email) || !empty($password) || !empty($name) || !empty($username)) {
        $email = User::checkInput($email);
        $password = User::checkInput($password); 
        $name = User::checkInput($name); 
        $username = User::checkInput($username); 
        $birthdate = User::checkInput($birthdate);
    } 
   
    $v = new Validator; 
    $v->rules('name' , $name , ['required' , 'string' , 'max:20']);
    $v->rules('username' , $username , ['required' , 'string' , 'max:20']);
    $v->rules('email' , $email , ['required' , 'email']);
    
    // Only validate password for regular signup
    if (!$isSocialLogin) {
        $v->rules('password' , $password , ['required' , 'string' , 'min:5']);
    }
    
    $errors = $v->errors;
    
    // Age validation only for regular signup
    if (!$isSocialLogin && !empty($birthdate)) {
        $today = new DateTime();
        $birthDate = new DateTime($birthdate);
        $age = $today->diff($birthDate)->y;
        
        if ($age < 13) {
            $errors['age'] = "You must be at least 13 years old to create an account.";
        }
    }
    
    if (empty($errors)){
        $username = str_replace(' ', '', $username);
        
        // For social login, generate unique username and email
        if ($isSocialLogin) {
            // Generate unique username and email for social login
            $username = $social_provider . '_user_' . uniqid();
            $email = $social_provider . '_' . uniqid() . '@social.com';
            // For social login, use a default password
            $password = password_hash(uniqid(), PASSWORD_DEFAULT);
        }
        
        if(User::checkEmail($email) === true) {
            $_SESSION['errors_signup'] = ['This email is already in use'];
            header('location: ../index.php');
            exit();
        } else if (!$isSocialLogin && User::checkUserName($username) === true) {
            // Only check username duplicate for regular signup
            $_SESSION['errors_signup'] = ['This username is already in use'];
            header('location: ../index.php');
            exit();
        } else if (!$isSocialLogin && !preg_match("/^[a-zA-Z0-9_]*$/" , $username)) {
            $_SESSION['errors_signup'] = ['Only characters and numbers allowed in username'];
            header('location: ../index.php');
            exit();
        } else {
            // Register user - for social login, store provider info
            if ($isSocialLogin) {
                // Store social provider information
                User::register($email, $password, $name, $username, $social_provider);
            } else {
                User::register($email, $password, $name, $username);
            }
            // Redirect after successful registration
            header('location: ../home.php');
            exit();
        }
    } else { 
        $_SESSION['errors_signup'] = $errors;  
        header('location: ../index.php'); 
        exit();
    }
        
} else {
    header('location: ../index.php');
    exit();
}
?>