<?php 
include 'core/init.php';
  
$user_id = $_SESSION['user_id'];

$user = User::getData($user_id);
$who_users = Follow::whoToFollow($user_id);
$notify_count = User::CountNotification($user_id);

if (User::checkLogIn() === false) 
header('location: index.php');

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings | Kabi</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/all.min.css">

    <!-- time function to force css file to reload -->
    
    <link rel="stylesheet" href="assets/css/profile_style.css?v=<?php echo time(); ?>">
    <link rel="shortcut icon" type="image/png" href="assets/images/kabi.png"> 
   
</head>
<body>
     
    <!-- Modern Background Design -->
    <div class="modern-bg">
        <div class="bg-gradient"></div>
        <div class="bg-pattern"></div>
    </div>

    <div id="mine" class="modern-layout">
 
    <div class="wrapper-left modern-sidebar">
        <div class="sidebar-left">
          <div class="grid-sidebar" style="margin-top: 12px">
            <div class="icon-sidebar-align">
              <img src="assets/images/kabi.png" alt="" height="30px" width="30px" />
            </div>
          </div>

          <a href="home.php">
          <div class="grid-sidebar" style="margin-top: 12px">
            <div class="icon-sidebar-align">
              <img src="includes/icons/tweethome.png" alt="" height="26.25px" width="26.25px" />
            </div>
            <div class="wrapper-left-elements">
              <a href="home.php" style="margin-top: 4px;"><strong>Home</strong></a>
            </div>
          </div>
          </a>
  
           <a href="notification.php">
          <div class="grid-sidebar">
            <div class="icon-sidebar-align position-relative">
                <?php if ($notify_count > 0) { ?>
              <i class="notify-count"><?php echo $notify_count; ?></i> 
              <?php } ?>
              <img
                src="assets/images/icons/tweetnotif.png"
                alt=""
                height="26.25px"
                width="26.25px"
              />
            </div>
  
            <div class="wrapper-left-elements">
              <a href="notification.php" style="margin-top: 4px"><strong>Notifications</strong></a>
            </div>
          </div>
          </a>
        
            <a href="<?php echo BASE_URL . $user->username; ?>">
          <div class="grid-sidebar">
            <div class="icon-sidebar-align">
              <img src="assets/images/icons/tweetprof.png" alt="" height="26.25px" width="26.25px" />
            </div>
  
            <div class="wrapper-left-elements">
              <a href="<?php echo BASE_URL . $user->username; ?>" style="margin-top: 4px"><strong>Profile</strong></a>
            </div>
          </div>
          </a>
          
          <a href="account.php">
          <div class="grid-sidebar bg-active modern-active">
            <div class="icon-sidebar-align">
              <img src="assets/images/icons/tweetsetting.png" alt="" height="26.25px" width="26.25px" />
            </div>
  
            <div class="wrapper-left-elements">
              <a href="account.php" style="margin-top: 4px"><strong>Settings</strong></a>
            </div>
          </div>
          </a>
          
          <a href="includes/logout.php">
          <div class="grid-sidebar">
            <div class="icon-sidebar-align">
            <i style="font-size: 26px;" class="fas fa-sign-out-alt"></i>
            </div>
  
            <div class="wrapper-left-elements">
              <a href="includes/logout.php" style="margin-top: 4px"><strong>Logout</strong></a>
            </div>
          </div>
          </a>
  
          <div class="box-user modern-user-card">
            <div class="grid-user">
              <div>
                <img
                  src="assets/images/users/<?php echo $user->img ?>"
                  alt="user"
                  class="img-user"
                />
              </div>
              <div>
                <p class="name"><strong><?php if($user->name !== null) {
                echo $user->name; } ?></strong></p>
                <p class="username">@<?php echo $user->username; ?></p>
              </div>
              <div class="mt-arrow">
                <img
                  src="https://i.ibb.co/mRLLwdW/arrow-down.png"
                  alt=""
                  height="18.75px"
                  width="18.75px"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
          
  

      <div class="grid-posts modern-main">
        <div class="border-right modern-border">
          <div class="grid-toolbar-center modern-header">
            <div class="center-input-search">
              <h2 class="modern-title"><i class="fas fa-cog modern-icon"></i> Account Settings</h2>
              <p class="modern-subtitle">Manage your account preferences and security</p>
            </div>
          </div>

          <div class="box-fixed" id="box-fixed"></div>
  
          <div class="box-home feed modern-content">
               <div class="container modern-container">
                <div class="nav flex-column nav-pills modern-tabs" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                  <a style="color:black !important;" class="nav-link active text-center modern-tab-item" id="v-pills-home-tab" data-toggle="pill" href="#v-pills-home" role="tab" aria-controls="v-pills-home" aria-selected="true">
                    <i class="fas fa-user-edit tab-icon"></i>
                    <span>Profile</span>
                  </a>
                  <a style="color:black !important;" class="nav-link text-center modern-tab-item" id="v-pills-profile-tab" data-toggle="pill" href="#v-pills-profile" role="tab" aria-controls="v-pills-profile" aria-selected="false">
                    <i class="fas fa-lock tab-icon"></i>
                    <span>Security</span>
                  </a>
                  <a style="color:black !important;" class="nav-link text-center modern-tab-item modern-tab-danger" id="v-pills-delete-tab" data-toggle="pill" href="#v-pills-delete" role="tab" aria-controls="v-pills-delete" aria-selected="false">
                    <i class="fas fa-exclamation-triangle tab-icon"></i>
                    <span>Danger Zone</span>
                  </a>
                </div>
                <div class="tab-content modern-tab-content" id="v-pills-tabContent">
                  <div class="tab-pane fade show active" id="v-pills-home" role="tabpanel" aria-labelledby="v-pills-home-tab">
                        <!-- Change EMAIL and USAERNAME Form -->

                    <form method="POST" action="handle/handleAccountSetting.php" class="py-4 modern-form" >
                      
                    <?php  if (isset($_SESSION['errors_account'] )) {
                        
                        ?>
                              
                        <?php foreach ($_SESSION['errors_account'] as $error) { ?>

                            <div  class="alert alert-danger modern-alert" role="alert">
                                <i class="fas fa-exclamation-circle alert-icon"></i>
                                <p style="font-size: 15px;" class="text-center"> <?php echo $error ; ?> </p>  
                            </div> 
                                    <?php }   ?> 

                        <?php }  unset($_SESSION['errors_account'])  ?>
                      <div class="form-group modern-form-group">
                        <label for="exampleInputEmail1" class="modern-label">Email address</label>
                        <div class="input-container">
                          <i class="fas fa-envelope input-icon"></i>
                          <input type="email" name="email" value="<?php echo $user->email; ?>" class="form-control modern-input" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Enter email">
                        </div>
                      </div>
                      <div class="form-group modern-form-group">
                        <label for="exampleInputPassword1" class="modern-label">Username</label>
                        <div class="input-container">
                          <i class="fas fa-user input-icon"></i>
                          <input type="text" name="username" value="<?php echo $user->username; ?>" class="form-control modern-input" id="exampleInputPassword1" placeholder="Username">
                        </div>
                      </div>
                      
                      <div class="text-center modern-btn-container">

                        <button type="submit" name="submit" class="btn modern-btn-primary">
                          <i class="fas fa-save btn-icon"></i>
                          Save Changes
                        </button>
                      </div>

                    </form>

                  </div>
                  <div class="tab-pane fade" id="v-pills-profile" role="tabpanel" aria-labelledby="v-pills-profile-tab">
                    

                    <!-- Change Password Form -->

                    <form method="POST" action="handle/handleChangePassword.php" class="py-4 modern-form" >
                    <script src="assets/js/jquery-3.5.1.min.js"></script>
                    <?php  if (isset($_SESSION['errors_password'] )) {
                        
                        ?>
                        

                         <script>  
                                $(document).ready(function(){
                            // Open modal on page load
                            $("#v-pills-profile-tab").click();
                    
                          });
                          </script>

                        <?php foreach ($_SESSION['errors_password'] as $error) { ?>

                            <div  class="alert alert-danger modern-alert" role="alert">
                                <i class="fas fa-exclamation-circle alert-icon"></i>
                                <p style="font-size: 15px;" class="text-center"> <?php echo $error ; ?> </p>  
                            </div> 
                                    <?php }   ?> 

                        <?php }  unset($_SESSION['errors_password'])  ?>

                      <div class="form-group modern-form-group">
                        <label for="exampleInputEmail1" class="modern-label">Current Password</label>
                        <div class="input-container">
                          <i class="fas fa-lock input-icon"></i>
                          <input type="password" name="old_password" class="form-control modern-input" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Current Password">
                        </div>
                      </div>
                      <div class="form-group modern-form-group">
                        <label for="exampleInputPassword1" class="modern-label">New Password</label>
                        <div class="input-container">
                          <i class="fas fa-key input-icon"></i>
                          <input type="password" name="new_password" class="form-control modern-input" id="exampleInputPassword1" placeholder="New Password">
                        </div>
                      </div>

                      <div class="form-group modern-form-group">
                        <label for="exampleInputPassword1" class="modern-label">Confirm New Password</label>
                        <div class="input-container">
                          <i class="fas fa-check-circle input-icon"></i>
                          <input type="password" name="ver_password" class="form-control modern-input" id="exampleInputPassword1" placeholder="Confirm New Password">
                        </div>
                      </div>
                      
                      <div class="text-center modern-btn-container">

                        <button type="submit" name="submit" class="btn modern-btn-primary">
                          <i class="fas fa-shield-alt btn-icon"></i>
                          Update Password
                        </button>
                      </div>

                    </form>

                  </div>
                  
                  <div class="tab-pane fade" id="v-pills-delete" role="tabpanel" aria-labelledby="v-pills-delete-tab">
                    

                    <!-- Delete Account Form -->

                    <div class="py-4 modern-danger-zone">
                      <div class="alert alert-danger modern-danger-alert">
                        <i class="fas fa-radiation-alt danger-icon"></i>
                        <h4 class="alert-heading">Warning: Account Deletion</h4>
                        <p>Deleting your account is a permanent action and cannot be undone. All your data, including posts, followers, and settings will be permanently removed from our system.</p>
                        <hr>
                        <p class="mb-0">If you're sure you want to proceed, please enter your password below to confirm account deletion.</p>
                      </div>

                      <form method="POST" action="handle/handleDeleteAccount.php" class="modern-form">
                        <?php if (isset($_SESSION['errors_delete'])) { ?>
                          <?php foreach ($_SESSION['errors_delete'] as $error) { ?>
                            <div class="alert alert-danger modern-alert" role="alert">
                              <i class="fas fa-exclamation-circle alert-icon"></i>
                              <p style="font-size: 15px;" class="text-center"><?php echo $error; ?></p>  
                            </div> 
                          <?php } ?>
                        <?php } unset($_SESSION['errors_delete']) ?>

                        <div class="form-group modern-form-group">
                          <label for="deletePassword" class="modern-label text-danger">Confirm Password</label>
                          <div class="input-container">
                            <i class="fas fa-lock input-icon text-danger"></i>
                            <input type="password" name="password" class="form-control modern-input modern-input-danger" id="deletePassword" placeholder="Enter your password" required>
                          </div>
                        </div>
                        
                        <div class="form-group form-check modern-checkbox">
                          <input type="checkbox" name="confirm_delete" class="form-check-input modern-checkbox-input" id="confirmDelete" required>
                          <label class="form-check-label modern-checkbox-label text-danger" for="confirmDelete">
                            I understand that this action cannot be undone and all my data will be permanently deleted.
                          </label>
                        </div>
                        
                        <div class="text-center modern-btn-container">

                          <button type="submit" name="delete_account" class="btn modern-btn-danger">
                            <i class="fas fa-trash-alt btn-icon"></i>
                            Delete My Account
                          </button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
                   
               </div>
           
          </div>
        </div>
        <div class="modern-right-sidebar"> 
            
        <div style="width: 90%;" class="container modern-search-container">

            <div class="input-group py-2 m-auto pr-5 position-relative modern-search">

            <i id="icon-search" class="fas fa-search tryy modern-search-icon"></i>
            <input type="text" class="form-control search-input modern-search-input"  placeholder="Search Kabi...">
            <div class="search-result modern-search-result">

        
            </div>
            </div>
       </div>


      
            

                
       <div class="box-share modern-who-to-follow">
            <p class="txt-share modern-section-title"><strong>Who to follow</strong></p>
            <?php 
            foreach($who_users as $user) { 
              //  $u = User::getData($user->user_id);
               $user_follow = Follow::isUserFollow($user_id , $user->id) ;
               ?>
          <div class="grid-share modern-user-card">
          <a style="position: relative; z-index:5; color:black" href="<?php echo $user->username;  ?>">
                      <img
                        src="assets/images/users/<?php echo $user->img; ?>"
                        alt=""
                        class="img-share modern-user-avatar"
                      />
                    </a>
                    <div class="modern-user-info">
                      <p class="modern-user-name">
                      <a style="position: relative; z-index:5; color:black" href="<?php echo $user->username;  ?>">  
                      <strong><?php echo $user->name; ?></strong>
                      </a>
                    </p>
                      <p class="username modern-user-handle">@<?php echo $user->username; ?>
                      <?php if (Follow::FollowsYou($user->id , $user_id)) { ?>
                  <span class="ml-1 follows-you modern-follows-you">Follows You</span></p>
                  <?php } ?></p></p>
                    </div>
                    <div>
                      <button class="follow-btn follow-btn-m modern-follow-btn
                      <?= $user_follow ? 'following' : 'follow' ?>"
                      data-follow="<?php echo $user->id; ?>"
                      data-user="<?php echo $user_id; ?>"
                      data-profile="<?php echo $u_id; ?>"
                      style="font-weight: 700;">
                      <?php if($user_follow) { ?>
                        <i class="fas fa-check follow-icon"></i>
                        Following 
                      <?php } else {  ?>  
                          <i class="fas fa-plus follow-icon"></i>
                          Follow
                        <?php }  ?> 
                      </button>
                    </div>
                  </div>

                  <?php }?>
         
          
          </div>
  
  
  
        </div>
      </div> </div>
      <script src="assets/js/search.js"></script>    
       <script src="assets/js/follow.js"></script>
      <script src="https://kit.fontawesome.com/38e12cc51b.js" crossorigin="anonymous"></script>
      <!-- <script src="assets/js/jquery-3.4.1.slim.min.js"></script> -->
        <script src="assets/js/popper.min.js"></script>
        <script src="assets/js/bootstrap.min.js"></script>
        
        <!-- Additional JavaScript for delete confirmation -->
        <script>
          document.addEventListener('DOMContentLoaded', function() {
            const deleteForm = document.querySelector('form[action="handle/handleDeleteAccount.php"]');
            if (deleteForm) {
              deleteForm.addEventListener('submit', function(e) {
                const confirmDelete = document.getElementById('confirmDelete');
                if (!confirmDelete.checked) {
                  e.preventDefault();
                  alert('Please confirm that you understand the consequences of deleting your account.');
                  return false;
                }
                
                if (!confirm('Are you absolutely sure you want to delete your account? This action cannot be undone.')) {
                  e.preventDefault();
                  return false;
                }
              });
            }
          });
        </script>
</body>

<style>
  /* Modern Background */
  .modern-bg {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: -1;
    overflow: hidden;
  }
  
  .bg-gradient {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    opacity: 0.03;
  }
  
  .bg-pattern {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: 
      radial-gradient(circle at 25% 25%, rgba(255,255,255,0.1) 2px, transparent 0),
      radial-gradient(circle at 75% 75%, rgba(255,255,255,0.1) 1px, transparent 0);
    background-size: 50px 50px, 30px 30px;
  }
  
  /* Modern Layout */
  .modern-layout {
    position: relative;
    z-index: 1;
  }
  
  .modern-sidebar {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-right: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
  }
  
  .modern-main {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
  }
  
  .modern-border {
    border-right: 1px solid rgba(0, 0, 0, 0.08) !important;
  }
  
  .modern-right-sidebar {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
  }
  
  /* Modern Header */
  .modern-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px 20px !important;
    border-bottom: none;
  }
  
  .modern-title {
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 5px;
    color: white;
  }
  
  .modern-subtitle {
    color: rgba(255, 255, 255, 0.8);
    font-size: 16px;
    margin: 0;
  }
  
  .modern-icon {
    margin-right: 15px;
    font-size: 24px;
  }
  
  /* Modern Container */
  .modern-container {
    padding: 40px 20px;
    max-width: 800px;
  }
  
  /* Modern Tabs */
  .modern-tabs {
    background: white;
    border-radius: 20px;
    padding: 10px;
    box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1);
    margin-bottom: 40px;
    border: 1px solid rgba(0, 0, 0, 0.05);
  }
  
  .modern-tab-item {
    padding: 20px 15px !important;
    border-radius: 15px !important;
    margin: 5px 0;
    border: none !important;
    transition: all 0.3s ease;
    color: #6c757d !important;
    font-weight: 500;
  }
  
  .modern-tab-item:hover {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white !important;
    transform: translateY(-2px);
  }
  
  .modern-tab-item.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    color: white !important;
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
  }
  
  .modern-tab-danger {
    color: #dc3545 !important;
  }
  
  .modern-tab-danger:hover,
  .modern-tab-danger.active {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%) !important;
    color: white !important;
  }
  
  .tab-icon {
    font-size: 20px;
    margin-bottom: 8px;
    display: block;
  }
  
  /* Modern Tab Content */
  .modern-tab-content {
    background: white;
    border-radius: 20px;
    padding: 40px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(0, 0, 0, 0.05);
  }
  
  /* Modern Forms */
  .modern-form {
    max-width: 500px;
    margin: 0 auto;
  }
  
  .modern-form-group {
    margin-bottom: 25px;
  }
  
  .modern-label {
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 10px;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  
  .input-container {
    position: relative;
  }
  
  .input-icon {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #667eea;
    z-index: 2;
  }
  
  .modern-input {
    padding: 15px 15px 15px 45px !important;
    border: 2px solid #e2e8f0 !important;
    border-radius: 12px !important;
    font-size: 16px;
    transition: all 0.3s ease;
    background: #f8fafc;
  }
  
  .modern-input:focus {
    border-color: #667eea !important;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1) !important;
    background: white;
    transform: translateY(-1px);
  }
  
  .modern-input-danger {
    border-color: #fc8181 !important;
  }
  
  .modern-input-danger:focus {
    border-color: #e53e3e !important;
    box-shadow: 0 0 0 3px rgba(229, 62, 62, 0.1) !important;
  }
  
  /* Modern Buttons */
  .modern-btn-container {
    margin-top: 30px;
  }
  
  .modern-btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    padding: 15px 40px;
    border-radius: 50px;
    font-weight: 600;
    font-size: 16px;
    color: white;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
  }
  
  .modern-btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6);
    color: white;
  }
  
  .modern-btn-danger {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    border: none;
    padding: 15px 40px;
    border-radius: 50px;
    font-weight: 600;
    font-size: 16px;
    color: white;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(245, 87, 108, 0.4);
  }
  
  .modern-btn-danger:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(245, 87, 108, 0.6);
    color: white;
  }
  
  .btn-icon {
    margin-right: 8px;
  }
  
  /* Modern Alerts */
  .modern-alert {
    border-radius: 12px;
    border: none;
    padding: 15px 20px;
    background: #fed7d7;
    color: #c53030;
    margin-bottom: 20px;
  }
  
  .alert-icon {
    margin-right: 10px;
  }
  
  .modern-danger-alert {
    border-radius: 15px;
    border: none;
    padding: 25px;
    background: linear-gradient(135deg, #fed7d7 0%, #feb2b2 100%);
    color: #c53030;
    border-left: 5px solid #e53e3e;
  }
  
  .danger-icon {
    font-size: 24px;
    margin-bottom: 15px;
    display: block;
  }
  
  /* Modern Checkbox */
  .modern-checkbox {
    margin: 25px 0;
  }
  
  .modern-checkbox-input {
    width: 20px;
    height: 20px;
    margin-right: 10px;
  }
  
  .modern-checkbox-label {
    font-weight: 500;
  }
  
  /* Modern Danger Zone */
  .modern-danger-zone {
    border: 2px dashed #fc8181;
    border-radius: 15px;
    padding: 30px;
    background: #fff5f5;
  }
  
  /* Modern Search */
  .modern-search-container {
    margin-bottom: 20px;
  }
  
  .modern-search {
    background: white;
    border-radius: 50px;
    padding: 5px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
  }
  
  .modern-search-input {
    border: none !important;
    background: transparent !important;
    padding-left: 45px !important;
    border-radius: 50px !important;
  }
  
  .modern-search-input:focus {
    box-shadow: none !important;
  }
  
  .modern-search-icon {
    left: 20px;
    color: #667eea;
  }
  
  /* Modern Who to Follow */
  .modern-who-to-follow {
    background: white;
    border-radius: 20px;
    padding: 25px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(0, 0, 0, 0.05);
  }
  
  .modern-section-title {
    font-size: 18px;
    color: #2d3748;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #e2e8f0;
  }
  
  .modern-user-card {
    background: #f8fafc;
    border-radius: 15px;
    padding: 15px;
    margin-bottom: 15px;
    border: 1px solid #e2e8f0;
    transition: all 0.3s ease;
  }
  
  .modern-user-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
  }
  
  .modern-user-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    border: 3px solid #667eea;
  }
  
  .modern-user-info {
    flex: 1;
    margin-left: 15px;
  }
  
  .modern-user-name {
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 2px;
  }
  
  .modern-user-handle {
    color: #718096;
    font-size: 14px;
  }
  
  .modern-follows-you {
    background: #667eea;
    color: white;
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 11px;
  }
  
  .modern-follow-btn {
    border: none;
    border-radius: 20px;
    padding: 8px 20px;
    font-weight: 600;
    transition: all 0.3s ease;
  }
  
  .modern-follow-btn.follow {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
  }
  
  .modern-follow-btn.following {
    background: #e2e8f0;
    color: #4a5568;
  }
  
  .modern-follow-btn:hover {
    transform: translateY(-1px);
  }
  
  .follow-icon {
    margin-right: 5px;
    font-size: 12px;
  }
  
  /* Modern Active State for Sidebar */
  .modern-active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    border-radius: 10px;
    margin: 5px;
  }
  
  .modern-active a {
    color: white !important;
  }
  
  .modern-user-card .grid-user {
    background: white;
    border-radius: 15px;
    padding: 15px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
  }
  
  /* Responsive Design */
  @media (max-width: 768px) {
    .modern-container {
      padding: 20px 15px;
    }
    
    .modern-tab-content {
      padding: 25px;
    }
    
    .modern-tabs {
      flex-direction: row;
      overflow-x: auto;
    }
    
    .modern-tab-item {
      min-width: 120px;
    }
  }
</style>

</html>