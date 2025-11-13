<?php 
?>
       <script src="assets/js/jquery-3.4.1.slim.min.js"></script>
<form action="./handle/handleSignUp.php" method="post" id="signupForm">
<?php  if (isset($_SESSION['errors_signup'] )) { ?>
        <script>  
             $(document).ready(function(){
        // Open modal on page load
        $("#exampleModalCenter").modal('show');
       });
      </script>
                <?php foreach ($_SESSION['errors_signup'] as $error) { ?>
                       <div  class="alert alert-danger" role="alert">
                        <p style="font-size: 15px;" class="text-center"> <?php echo $error ; ?> </p></div>  
                <?php } 
                unset($_SESSION['errors_signup']); 
                } ?> 
                
                <div class="form-group">
                    <input type="text" name="name" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Full Name" required>
                </div>
                <div class="form-group">
                    <input type="text" name="username" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Username" required>
                </div>
                <div class="form-group">
                    <input type="email" name="email" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Email Address" required>
                </div>
                <div class="form-group">
                    <input type="password" name="password" class="form-control" id="exampleInputPassword1" placeholder="Password" required>
                </div>
                
                <!-- Age Verification -->
                <div class="form-group">
                    <label for="birthdate">Date of Birth</label>
                    <input type="date" name="birthdate" class="form-control" id="birthdate" required>
                    <small class="form-text text-muted">You must be at least 13 years old to register</small>
                    <div id="ageError" class="alert alert-danger mt-2" style="display: none;">
                        You must be at least 13 years old to create an account.
                    </div>
                </div>
                
                <!-- Social Media Login -->
                <div class="text-center mb-3">
                    <p class="mb-2">Or sign up with social media:</p>
                    <div class="social-login-buttons">
                        <a href="google_login.php" class="btn btn-outline-danger btn-sm mr-2">
                            <i class="fab fa-google"></i> Google
                        </a>
                        <a href="linkedin_login.php" class="btn btn-outline-primary btn-sm mr-2">
                            <i class="fab fa-linkedin"></i> LinkedIn
                        </a>
                        <a href="facebook_login.php" class="btn btn-outline-primary btn-sm">
                            <i class="fab fa-facebook"></i> Facebook
                        </a>
                    </div>
                    <small class="form-text text-muted mt-2">You'll be redirected to the social platform to login</small>
                </div>
                
                <div class="text-center">
                    <button type="submit" name="signup" class="btn btn-primary" id="submitBtn">Sign Up</button>
                </div>
</form>

<script>
// Age validation function
function calculateAge(birthdate) {
    const today = new Date();
    const birthDate = new Date(birthdate);
    let age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    return age;
}

// Form submission validation
document.getElementById('signupForm').addEventListener('submit', function(e) {
    const birthdate = document.getElementById('birthdate').value;
    
    if (birthdate) {
        const age = calculateAge(birthdate);
        if (age < 13) {
            e.preventDefault();
            document.getElementById('ageError').style.display = 'block';
            document.getElementById('birthdate').focus();
        }
    }
});

// Real-time age validation
document.getElementById('birthdate').addEventListener('change', function() {
    const birthdate = this.value;
    const submitBtn = document.getElementById('submitBtn');
    
    if (birthdate) {
        const age = calculateAge(birthdate);
        if (age < 13) {
            document.getElementById('ageError').style.display = 'block';
            submitBtn.disabled = true;
        } else {
            document.getElementById('ageError').style.display = 'none';
            submitBtn.disabled = false;
        }
    }
});
</script>

<style>
.social-login-buttons {
    display: flex;
    justify-content: center;
    gap: 10px;
    flex-wrap: wrap;
}

.btn-sm {
    padding: 8px 15px;
    font-size: 14px;
    text-decoration: none;
    display: inline-block;
}

#ageError {
    font-size: 14px;
    padding: 8px 12px;
}

.btn-outline-danger {
    border-color: #dc3545;
    color: #dc3545;
}

.btn-outline-primary {
    border-color: #007bff;
    color: #007bff;
}

.btn-outline-primary:hover {
    background-color: #007bff;
    color: white;
    text-decoration: none;
}

.btn-outline-danger:hover {
    background-color: #dc3545;
    color: white;
    text-decoration: none;
}
</style>