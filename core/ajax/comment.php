<?php 
	include '../init.php';
	$user_id = $_SESSION['user_id'];
	// Comment place
	if(isset($_POST['qoute']) && !empty($_POST['qoute'])){
		$tweet_id  = $_POST['qoute'];
		$get_id    = $_POST['user_id'];
		// $flag = $_POST['isQoute'];
		// $qoq = $_POST['qoq'];
		$comment   = User::checkInput($_POST['comment']);
        date_default_timezone_set("Africa/Cairo");
		// $retweet = Tweet::getRetweet($tweet_id);
		

        //  if(!$flag_retweeted) {
			

			$data = [
				'user_id' => $_SESSION['user_id'] , 
                'post_id' => $tweet_id , 
                'comment' => $comment , 
				'time' => date("Y-m-d H:i:s") ,
			];
		    if ($comment != '') {
				$for_user = Tweet::getData($tweet_id)->user_id;
		
					if($for_user != $user_id) {
						$data_notify = [
						'notify_for' => $for_user ,
						'notify_from' => $user_id ,
						'target' => $tweet_id , 
						'type' => 'comment' ,
						'time' => date("Y-m-d H:i:s") ,
						'count' => '0' , 
						'status' => '0'
						];
				
						Tweet::create('notifications' , $data_notify);
						
					} 

		     User::create('comments' , $data);
		  
			//  $comments = Tweet::comments($tweet_id);
			//  foreach($comments as $comment) {
			// 	$tweet_user = User::getData($comment->user_id) ;
            //      echo '<div class="box-comment feed py-2"  >
                
          
			// 	 <div class="grid-tweet">
			// 	   <div>
			// 		 <img
			// 		   src="assets/images/users/'. $tweet_user->img.' "
			// 		   alt=""
			// 		   class="img-user-tweet"
			// 		 />
			// 	   </div>
	   
			// 	   <div>
			// 		 <p>
			// 		   <strong> '. $tweet_user->name .' </strong>
			// 		   <span class="username-twitter">@ '.$tweet_user->username.'  </span>
			// 		   <span class="username-twitter"> $timeAgo </span>
			// 		 </p>
			// 		 <p>
					  
			// 		  '.  Tweet::getTweetLinks($comment->comment) .'
			// 		 </p>
			// 	   </div> 
			   
			// 	 </div>  </div> ';
			//  }



			}
	}

	if(isset($_POST['reply']) && !empty($_POST['reply'])){
		$tweet_id  = $_POST['reply'];
		$get_id    = $_POST['user_id'];
	
		$comment   = User::checkInput($_POST['comment']);

			date_default_timezone_set("Africa/Cairo");
          
		
			$data = [
				'user_id' => $_SESSION['user_id'] , 
                'comment_id' => $tweet_id , 
                'reply' => $comment , 
				'time' => date("Y-m-d H:i:s") ,
			];
		    if ($comment != '') { 
				// notification
				$for_user = Tweet::getComment($tweet_id)->user_id;
				$target = Tweet::getComment($tweet_id)->post_id;
		
				if($for_user != $user_id) {
					$data_notify = [
					'notify_for' => $for_user ,
					'notify_from' => $user_id ,
					'target' => $target , 
					'type' => 'reply' ,
					'time' => date("Y-m-d H:i:s") ,
					'count' => '0' , 
					'status' => '0'
					];
			
					Tweet::create('notifications' , $data_notify);
					
				} 
                //  end
				
		     User::create('replies' , $data);
			}
	}
        // Comment on Post popup
	if(isset($_POST['showPopup']) && !empty($_POST['showPopup'])){
		$tweet_id   = $_POST['showPopup'];
		$user       = User::getData($user_id);
		$retweet_comment = false;
		$qoq = false;
		if (Tweet::isRetweet($tweet_id)) {
		$retweet =Tweet::getRetweet($tweet_id);
		if ($retweet->retweet_id == null) {

				// when the retweetd tweet is normal tweet
				
			if ($retweet->retweet_msg != null) {
				
				// when quote 

                $user_tweet = User::getData($retweet->user_id) ;
				 $timeAgo = Tweet::getTimeAgo($retweet->post_on) ; 
				 $qoute = $retweet->retweet_msg;
                 $retweet_comment = true;
           

              $tweet_inner = Tweet::getTweet($retweet->tweet_id);
              $user_inner_tweet = User::getData($tweet_inner->user_id) ;
              $timeAgo_inner = Tweet::getTimeAgo($tweet_inner->post_on); 


			} else {
				// when normal retweet

				$tweet      = Tweet::getTweet($retweet->tweet_id);
		    	$user_tweet = User::getData($tweet->user_id);
		    	$timeAgo = Tweet::getTimeAgo($tweet->post_on) ; 
			}
		} else {
			// if tweet_id = null and retweeted_id not null then it's retweet od quote
			// so we have to get the retweeted tweet first

			// here condtion of retweeted a quoted tweet
		
			if ($retweet->retweet_msg == null) {
				
				$retweeted_tweet = Tweet::getRetweet($retweet->retweet_id);

				if($retweeted_tweet->tweet_id != null) {
						$user_tweet = User::getData($retweeted_tweet->user_id) ;
						$timeAgo = Tweet::getTimeAgo($retweeted_tweet->post_on) ; 

						$retweet_inner = Tweet::getRetweet($retweet->retweet_id);

						$qoute = $retweet_inner->retweet_msg;
						$retweet_comment = true;
				

					
					$tweet_inner = Tweet::getTweet($retweet_inner->tweet_id);
					$user_inner_tweet = User::getData($tweet_inner->user_id) ;
					$timeAgo_inner = Tweet::getTimeAgo($tweet_inner->post_on); 

				} else {
					// hereeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee

					     $user_tweet = User::getData($retweeted_tweet->user_id) ;
						$timeAgo = Tweet::getTimeAgo($retweeted_tweet->post_on) ; 

						$retweet_inner = Tweet::getRetweet($retweet->retweet_id);

						$qoute = $retweet_inner->retweet_msg;
						$retweet_comment = true;
				        $qoq = true;

					
					$tweet_inner = Tweet::getRetweet($retweeted_tweet->retweet_id);
					// $tweet_inner = Tweet::getRetweet($tweet_inner->retweet_id);
					$user_inner_tweet = User::getData($tweet_inner->user_id) ;
					$timeAgo_inner = Tweet::getTimeAgo($tweet_inner->post_on); 
                    $inner_qoute = $tweet_inner->retweet_msg;

				}
			} else {

				// here must handle the quote of quote display

				$user_tweet = User::getData($retweet->user_id) ;
				$timeAgo = Tweet::getTimeAgo($retweet->post_on) ; 
				// $likes_count = Tweet::countLikes($tweet->id) ;
				// $user_like_it = Tweet::userLikeIt($user_id ,$tweet->id);
				// $retweets_count = Tweet::countRetweets($tweet->id) ;
				// $user_retweeted_it = Tweet::userRetweeetedIt($user_id ,$tweet->id);
				$qoute = $retweet->retweet_msg;
				$qoq = true; // stand for quote of quote
				
				$tweet_inner = Tweet::getRetweet($retweet->retweet_id);
				$user_inner_tweet = User::getData($tweet_inner->user_id) ;
				$timeAgo_inner = Tweet::getTimeAgo($tweet_inner->post_on);
				$inner_qoute = $tweet_inner->retweet_msg;
			}
			
		}	

	} else {

		 // when normal tweet

		$tweet      = Tweet::getTweet($tweet_id);
		$user_tweet = User::getData($tweet->user_id);
		$timeAgo = Tweet::getTimeAgo($tweet->post_on) ;
		

	}
	
?>
<div class="retweet-popup">
<div class="wrap5">
	<div class="retweet-popup-body-wrap">
		<div class="retweet-popup-heading">
			<h3>Reply Post</h3>
			<span><button class="close-retweet-popup"><i class="fa fa-times" aria-hidden="true"></i></button></span>
		</div>
		<div class="retweet-popup-input">
			<div class="retweet-popup-input-inner">
				<input id="comment-input" class="retweet-msg" type="text" placeholder="Add Comment.."/>
				<button type="button" id="emoji-picker-button" class="emoji-btn" style="background: none; border: none; cursor: pointer;">
					<i class="far fa-smile" style="font-size: 1.2em;"></i>
				</button> 
			</div>
		</div>
		
		<!-- Enhanced Emoji Picker Container -->
		<div id="emoji-picker-container" class="emoji-picker-hidden">
			<div class="emoji-picker-header">
				<span>Choose an emoji</span>
				<button id="close-emoji-picker" class="close-emoji-btn">&times;</button>
			</div>
			<div class="emoji-category">
				<div class="emoji-grid">
					<span class="emoji" data-emoji="😀">😀</span>
					<span class="emoji" data-emoji="😃">😃</span>
					<span class="emoji" data-emoji="😄">😄</span>
					<span class="emoji" data-emoji="😁">😁</span>
					<span class="emoji" data-emoji="😆">😆</span>
					<span class="emoji" data-emoji="😅">😅</span>
					<span class="emoji" data-emoji="😂">😂</span>
					<span class="emoji" data-emoji="🤣">🤣</span>
					<span class="emoji" data-emoji="😊">😊</span>
					<span class="emoji" data-emoji="😇">😇</span>
					<span class="emoji" data-emoji="🙂">🙂</span>
					<span class="emoji" data-emoji="🙃">🙃</span>
					<span class="emoji" data-emoji="😉">😉</span>
					<span class="emoji" data-emoji="😌">😌</span>
					<span class="emoji" data-emoji="😍">😍</span>
					<span class="emoji" data-emoji="🥰">🥰</span>
					<span class="emoji" data-emoji="😘">😘</span>
					<span class="emoji" data-emoji="😗">😗</span>
					<span class="emoji" data-emoji="😙">😙</span>
					<span class="emoji" data-emoji="😚">😚</span>
					<span class="emoji" data-emoji="😋">😋</span>
					<span class="emoji" data-emoji="😛">😛</span>
					<span class="emoji" data-emoji="😝">😝</span>
					<span class="emoji" data-emoji="😜">😜</span>
					<span class="emoji" data-emoji="🤪">🤪</span>
					<span class="emoji" data-emoji="🤨">🤨</span>
					<span class="emoji" data-emoji="🧐">🧐</span>
					<span class="emoji" data-emoji="🤓">🤓</span>
					<span class="emoji" data-emoji="😎">😎</span>
					<span class="emoji" data-emoji="🤩">🤩</span>
					<span class="emoji" data-emoji="🥳">🥳</span>
					<span class="emoji" data-emoji="😏">😏</span>
					<span class="emoji" data-emoji="😒">😒</span>
					<span class="emoji" data-emoji="😞">😞</span>
					<span class="emoji" data-emoji="😔">😔</span>
					<span class="emoji" data-emoji="😟">😟</span>
					<span class="emoji" data-emoji="😕">😕</span>
					<span class="emoji" data-emoji="🙁">🙁</span>
					<span class="emoji" data-emoji="☹️">☹️</span>
					<span class="emoji" data-emoji="😣">😣</span>
					<span class="emoji" data-emoji="😖">😖</span>
					<span class="emoji" data-emoji="😫">😫</span>
					<span class="emoji" data-emoji="😩">😩</span>
					<span class="emoji" data-emoji="🥺">🥺</span>
					<span class="emoji" data-emoji="😢">😢</span>
					<span class="emoji" data-emoji="😭">😭</span>
					<span class="emoji" data-emoji="😤">😤</span>
					<span class="emoji" data-emoji="😠">😠</span>
					<span class="emoji" data-emoji="😡">😡</span>
					<span class="emoji" data-emoji="🤬">🤬</span>
					<span class="emoji" data-emoji="🤯">🤯</span>
					<span class="emoji" data-emoji="😳">😳</span>
					<span class="emoji" data-emoji="🥵">🥵</span>
					<span class="emoji" data-emoji="🥶">🥶</span>
					<span class="emoji" data-emoji="😱">😱</span>
					<span class="emoji" data-emoji="😨">😨</span>
					<span class="emoji" data-emoji="😰">😰</span>
					<span class="emoji" data-emoji="😥">😥</span>
					<span class="emoji" data-emoji="😓">😓</span>
					<span class="emoji" data-emoji="🤗">🤗</span>
					<span class="emoji" data-emoji="🤔">🤔</span>
					<span class="emoji" data-emoji="🤭">🤭</span>
					<span class="emoji" data-emoji="🤫">🤫</span>
					<span class="emoji" data-emoji="🤥">🤥</span>
					<span class="emoji" data-emoji="😶">😶</span>
					<span class="emoji" data-emoji="😐">😐</span>
					<span class="emoji" data-emoji="😑">😑</span>
					<span class="emoji" data-emoji="😬">😬</span>
					<span class="emoji" data-emoji="🙄">🙄</span>
					<span class="emoji" data-emoji="😯">😯</span>
					<span class="emoji" data-emoji="😦">😦</span>
					<span class="emoji" data-emoji="😧">😧</span>
					<span class="emoji" data-emoji="😮">😮</span>
					<span class="emoji" data-emoji="😲">😲</span>
					<span class="emoji" data-emoji="🥱">🥱</span>
					<span class="emoji" data-emoji="😴">😴</span>
					<span class="emoji" data-emoji="🤤">🤤</span>
					<span class="emoji" data-emoji="😪">😪</span>
					<span class="emoji" data-emoji="😵">😵</span>
					<span class="emoji" data-emoji="🤐">🤐</span>
					<span class="emoji" data-emoji="🥴">🥴</span>
					<span class="emoji" data-emoji="🤢">🤢</span>
					<span class="emoji" data-emoji="🤮">🤮</span>
					<span class="emoji" data-emoji="🤧">🤧</span>
					<span class="emoji" data-emoji="😷">😷</span>
					<span class="emoji" data-emoji="🤒">🤒</span>
					<span class="emoji" data-emoji="🤕">🤕</span>
					<span class="emoji" data-emoji="🤑">🤑</span>
					<span class="emoji" data-emoji="🤠">🤠</span>
					<span class="emoji" data-emoji="😈">😈</span>
					<span class="emoji" data-emoji="👿">👿</span>
					<span class="emoji" data-emoji="👹">👹</span>
					<span class="emoji" data-emoji="👺">👺</span>
					<span class="emoji" data-emoji="🤡">🤡</span>
					<span class="emoji" data-emoji="💩">💩</span>
					<span class="emoji" data-emoji="👻">👻</span>
					<span class="emoji" data-emoji="💀">💀</span>
					<span class="emoji" data-emoji="☠️">☠️</span>
					<span class="emoji" data-emoji="👽">👽</span>
					<span class="emoji" data-emoji="👾">👾</span>
					<span class="emoji" data-emoji="🤖">🤖</span>
					<span class="emoji" data-emoji="🎃">🎃</span>
					<span class="emoji" data-emoji="😺">😺</span>
					<span class="emoji" data-emoji="😸">😸</span>
					<span class="emoji" data-emoji="😹">😹</span>
					<span class="emoji" data-emoji="😻">😻</span>
					<span class="emoji" data-emoji="😼">😼</span>
					<span class="emoji" data-emoji="😽">😽</span>
					<span class="emoji" data-emoji="🙀">🙀</span>
					<span class="emoji" data-emoji="😿">😿</span>
					<span class="emoji" data-emoji="😾">😾</span>
				</div>
			</div>
		</div>
				
		<div class="grid-tweet py-2">
              <div>
                <img
                  src="assets/images/users/<?php echo $user_tweet->img; ?>"
                  alt=""
                  class="img-user-tweet"
                />
              </div>
  
              <div>
                <p>
                  <strong> <?php echo $user_tweet->name ?> </strong>
                  <span class="username-twitter">@<?php echo $user_tweet->username ?> </span>
                  <span class="username-twitter"><?php echo $timeAgo ?></span>
                </p>
                <p>
				<?php
                  // check if it's quote or normal tweet
                  if ($retweet_comment || $qoq)
                  echo  Tweet::getTweetLinks($qoute);
                  else echo  Tweet::getTweetLinks($tweet->status); ?>
				</p>
				
				<?php if ($retweet_comment == false && $qoq == false) { ?>
                <?php if ($tweet->img != null) { ?>
                <p class="mt-post-tweet">
                  <img
                    src="assets/images/tweets/<?php echo $tweet->img; ?>"
                    alt=""
                    class="img-post-retweet"
                  />
                </p>
			   <?php } ?>
			   <?php }  else { ?>

				<div  class="mt-post-tweet comment-post">

				<div class="grid-tweet py-3  ">
				<div>
				<img
				src="assets/images/users/<?php echo $user_inner_tweet->img; ?>"
				alt=""
				class="img-user-tweet"
				/>
				</div>

				<div>
				<p>
				<strong> <?php echo $user_inner_tweet->name ?> </strong>
				<span class="username-twitter">@<?php echo $user_inner_tweet->username ?> </span>
				<span class="username-twitter"><?php echo $timeAgo_inner ?></span>
				</p>
				<p>
				<?php 
				    if ($qoq)
                    echo $inner_qoute;
                    else  echo  Tweet::getTweetLinks($tweet_inner->status); ?>
				</p>
				<?php
				if($qoq == false) {
				if ($tweet_inner->img != null) { ?>
				<p class="mt-post-tweet">
				<img
				src="assets/images/tweets/<?php echo $tweet_inner->img; ?>"
				alt=""
				class="img-post-retweet"
				/>
				</p>
         <?php } } ?>

</div>
</div>
	   

</div>

<?php } ?>
			   

	</div>
</div>


		<div class="retweet-popup-footer"> 
			<div class="retweet-popup-footer-right">
				<button class="comment-it" 
				data-tweet="<?php echo $tweet_id;?>"
				data-user="<?php echo $user_id;?>"
				data-tmp="<?php echo $retweet_comment; ?>" 
				data-qoq="<?php echo $qoq; ?>" 
			 type="submit"><i class="fas fa-pencil-alt" aria-hidden="true"></i>Reply</button>
			</div>
		</div> 
		

</div>

<!-- Post Comment PopUp ends-->

<?php }  

// Repling to comment popup

if(isset($_POST['showReply']) && !empty($_POST['showReply'])){
	$comment_id   = $_POST['showReply'];
	$user       = User::getData($user_id);
	

	$tweet      = Tweet::getComment($comment_id);
	$user_tweet = User::getData($tweet->user_id);
	$timeAgo = Tweet::getTimeAgo($tweet->time) ; 

?>
<div class="retweet-popup">
<div class="wrap5">
<div class="retweet-popup-body-wrap">
	<div class="retweet-popup-heading">
		<h3>Reply Comment</h3>
		<span><button class="close-retweet-popup"><i class="fa fa-times" aria-hidden="true"></i></button></span>
	</div>
	<div class="retweet-popup-input">
		<div class="retweet-popup-input-inner">
			<input id="reply-input" class="retweet-msg" type="text" placeholder="Add Reply.."/>
			<button type="button" id="emoji-picker-button-reply" class="emoji-btn" style="background: none; border: none; cursor: pointer;">
				<i class="far fa-smile" style="font-size: 1.2em;"></i>
			</button> 
		</div>
	</div>
	
	<!-- Enhanced Emoji Picker Container for Reply -->
	<div id="emoji-picker-container-reply" class="emoji-picker-hidden">
		<div class="emoji-picker-header">
			<span>Choose an emoji</span>
			<button id="close-emoji-picker-reply" class="close-emoji-btn">&times;</button>
		</div>
		<div class="emoji-category">
			<div class="emoji-grid">
				<span class="emoji" data-emoji="😀">😀</span>
				<span class="emoji" data-emoji="😃">😃</span>
				<span class="emoji" data-emoji="😄">😄</span>
				<span class="emoji" data-emoji="😁">😁</span>
				<span class="emoji" data-emoji="😆">😆</span>
				<span class="emoji" data-emoji="😅">😅</span>
				<span class="emoji" data-emoji="😂">😂</span>
				<span class="emoji" data-emoji="🤣">🤣</span>
				<span class="emoji" data-emoji="😊">😊</span>
				<span class="emoji" data-emoji="😇">😇</span>
				<span class="emoji" data-emoji="🙂">🙂</span>
				<span class="emoji" data-emoji="🙃">🙃</span>
				<span class="emoji" data-emoji="😉">😉</span>
				<span class="emoji" data-emoji="😌">😌</span>
				<span class="emoji" data-emoji="😍">😍</span>
				<span class="emoji" data-emoji="🥰">🥰</span>
				<span class="emoji" data-emoji="😘">😘</span>
				<span class="emoji" data-emoji="😗">😗</span>
				<span class="emoji" data-emoji="😙">😙</span>
				<span class="emoji" data-emoji="😚">😚</span>
				<span class="emoji" data-emoji="😋">😋</span>
				<span class="emoji" data-emoji="😛">😛</span>
				<span class="emoji" data-emoji="😝">😝</span>
				<span class="emoji" data-emoji="😜">😜</span>
				<span class="emoji" data-emoji="🤪">🤪</span>
				<span class="emoji" data-emoji="🤨">🤨</span>
				<span class="emoji" data-emoji="🧐">🧐</span>
				<span class="emoji" data-emoji="🤓">🤓</span>
				<span class="emoji" data-emoji="😎">😎</span>
				<span class="emoji" data-emoji="🤩">🤩</span>
				<span class="emoji" data-emoji="🥳">🥳</span>
				<span class="emoji" data-emoji="😏">😏</span>
				<span class="emoji" data-emoji="😒">😒</span>
				<span class="emoji" data-emoji="😞">😞</span>
				<span class="emoji" data-emoji="😔">😔</span>
				<span class="emoji" data-emoji="😟">😟</span>
				<span class="emoji" data-emoji="😕">😕</span>
				<span class="emoji" data-emoji="🙁">🙁</span>
				<span class="emoji" data-emoji="☹️">☹️</span>
				<span class="emoji" data-emoji="😣">😣</span>
				<span class="emoji" data-emoji="😖">😖</span>
				<span class="emoji" data-emoji="😫">😫</span>
				<span class="emoji" data-emoji="😩">😩</span>
				<span class="emoji" data-emoji="🥺">🥺</span>
				<span class="emoji" data-emoji="😢">😢</span>
				<span class="emoji" data-emoji="😭">😭</span>
				<span class="emoji" data-emoji="😤">😤</span>
				<span class="emoji" data-emoji="😠">😠</span>
				<span class="emoji" data-emoji="😡">😡</span>
				<span class="emoji" data-emoji="🤬">🤬</span>
				<span class="emoji" data-emoji="🤯">🤯</span>
				<span class="emoji" data-emoji="😳">😳</span>
				<span class="emoji" data-emoji="🥵">🥵</span>
				<span class="emoji" data-emoji="🥶">🥶</span>
				<span class="emoji" data-emoji="😱">😱</span>
				<span class="emoji" data-emoji="😨">😨</span>
				<span class="emoji" data-emoji="😰">😰</span>
				<span class="emoji" data-emoji="😥">😥</span>
				<span class="emoji" data-emoji="😓">😓</span>
				<span class="emoji" data-emoji="🤗">🤗</span>
				<span class="emoji" data-emoji="🤔">🤔</span>
				<span class="emoji" data-emoji="🤭">🤭</span>
				<span class="emoji" data-emoji="🤫">🤫</span>
				<span class="emoji" data-emoji="🤥">🤥</span>
				<span class="emoji" data-emoji="😶">😶</span>
				<span class="emoji" data-emoji="😐">😐</span>
				<span class="emoji" data-emoji="😑">😑</span>
				<span class="emoji" data-emoji="😬">😬</span>
				<span class="emoji" data-emoji="🙄">🙄</span>
				<span class="emoji" data-emoji="😯">😯</span>
				<span class="emoji" data-emoji="😦">😦</span>
				<span class="emoji" data-emoji="😧">😧</span>
				<span class="emoji" data-emoji="😮">😮</span>
				<span class="emoji" data-emoji="😲">😲</span>
				<span class="emoji" data-emoji="🥱">🥱</span>
				<span class="emoji" data-emoji="😴">😴</span>
				<span class="emoji" data-emoji="🤤">🤤</span>
				<span class="emoji" data-emoji="😪">😪</span>
				<span class="emoji" data-emoji="😵">😵</span>
				<span class="emoji" data-emoji="🤐">🤐</span>
				<span class="emoji" data-emoji="🥴">🥴</span>
				<span class="emoji" data-emoji="🤢">🤢</span>
				<span class="emoji" data-emoji="🤮">🤮</span>
				<span class="emoji" data-emoji="🤧">🤧</span>
				<span class="emoji" data-emoji="😷">😷</span>
				<span class="emoji" data-emoji="🤒">🤒</span>
				<span class="emoji" data-emoji="🤕">🤕</span>
				<span class="emoji" data-emoji="🤑">🤑</span>
				<span class="emoji" data-emoji="🤠">🤠</span>
				<span class="emoji" data-emoji="😈">😈</span>
				<span class="emoji" data-emoji="👿">👿</span>
				<span class="emoji" data-emoji="👹">👹</span>
				<span class="emoji" data-emoji="👺">👺</span>
				<span class="emoji" data-emoji="🤡">🤡</span>
				<span class="emoji" data-emoji="💩">💩</span>
				<span class="emoji" data-emoji="👻">👻</span>
				<span class="emoji" data-emoji="💀">💀</span>
				<span class="emoji" data-emoji="☠️">☠️</span>
				<span class="emoji" data-emoji="👽">👽</span>
				<span class="emoji" data-emoji="👾">👾</span>
				<span class="emoji" data-emoji="🤖">🤖</span>
				<span class="emoji" data-emoji="🎃">🎃</span>
				<span class="emoji" data-emoji="😺">😺</span>
				<span class="emoji" data-emoji="😸">😸</span>
				<span class="emoji" data-emoji="😹">😹</span>
				<span class="emoji" data-emoji="😻">😻</span>
				<span class="emoji" data-emoji="😼">😼</span>
				<span class="emoji" data-emoji="😽">😽</span>
				<span class="emoji" data-emoji="🙀">🙀</span>
				<span class="emoji" data-emoji="😿">😿</span>
				<span class="emoji" data-emoji="😾">😾</span>
			</div>
		</div>
	</div>
			
	<div class="grid-tweet py-2">
		  <div>
			<img
			  src="assets/images/users/<?php echo $user_tweet->img; ?>"
			  alt=""
			  class="img-user-tweet"
			/>
		  </div>

		  <div>
			<p>
			  <strong> <?php echo $user_tweet->name ?> </strong>
			  <span class="username-twitter">@<?php echo $user_tweet->username ?> </span>
			  <span class="username-twitter"><?php echo $timeAgo ?></span>
			</p>
			<p>
			<?php
			  // check if it's quote or normal tweet
			   echo  Tweet::getTweetLinks($tweet->comment); ?>
			</p>

</div>
</div>
   




	<div class="retweet-popup-footer"> 
		<div class="retweet-popup-footer-right">
			<button class="reply-it" 
			data-tweet="<?php echo $comment_id;?>"
			data-user="<?php echo $user_id;?>"
		 type="submit"><i class="fas fa-pencil-alt" aria-hidden="true"></i>Reply</button>
		</div>
	</div> 
	

</div>

<!-- Retweet PopUp ends-->
<?php }?>

<style>
/* Enhanced Emoji Picker Styles */
#emoji-picker-container, #emoji-picker-container-reply {
    position: absolute;
    bottom: 60px;
    left: 20px;
    width: 300px;
    background: white;
    border: 1px solid #e1e8ed;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    z-index: 1000;
    padding: 10px;
    max-height: 250px;
    overflow-y: auto;
    transition: all 0.3s ease;
}

.emoji-picker-hidden {
    display: none;
}

.emoji-picker-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 12px;
    border-bottom: 1px solid #e1e8ed;
    margin-bottom: 8px;
    font-weight: 600;
    color: #1da1f2;
}

.close-emoji-btn {
    background: none;
    border: none;
    font-size: 18px;
    cursor: pointer;
    color: #657786;
    padding: 0;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}

.close-emoji-btn:hover {
    background-color: #e1e8ed;
    color: #1da1f2;
}

.emoji-grid {
    display: grid;
    grid-template-columns: repeat(8, 1fr);
    gap: 4px;
}

.emoji {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 6px;
    cursor: pointer;
    border-radius: 6px;
    font-size: 18px;
    transition: all 0.2s ease;
}

.emoji:hover {
    background-color: #1da1f2;
    transform: scale(1.1);
}

.emoji-btn {
    background: none;
    border: none;
    color: #1da1f2;
    padding: 8px;
    border-radius: 50%;
    transition: all 0.2s ease;
}

.emoji-btn:hover {
    background-color: #e8f5fe;
    color: #1da1f2;
}

/* Enhanced Popup Styles */
.retweet-popup {
    background: rgba(0, 0, 0, 0.6);
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(4px);
}

.wrap5 {
    background: white;
    border-radius: 16px;
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
    animation: popup-appear 0.3s ease-out;
}

@keyframes popup-appear {
    from {
        opacity: 0;
        transform: scale(0.9) translateY(-20px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

.retweet-popup-body-wrap {
    padding: 20px;
}

.retweet-popup-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 16px;
    border-bottom: 1px solid #e1e8ed;
    margin-bottom: 16px;
}

.retweet-popup-heading h3 {
    margin: 0;
    color: #14171a;
    font-size: 20px;
    font-weight: 700;
}

.close-retweet-popup {
    background: none;
    border: none;
    color: #657786;
    font-size: 18px;
    cursor: pointer;
    padding: 8px;
    border-radius: 50%;
    transition: all 0.2s ease;
}

.close-retweet-popup:hover {
    background-color: #e1e8ed;
    color: #1da1f2;
}

.retweet-popup-input {
    margin-bottom: 16px;
    position: relative;
}

.retweet-popup-input-inner {
    display: flex;
    align-items: center;
    border: 1px solid #e1e8ed;
    border-radius: 8px;
    padding: 8px 12px;
    transition: all 0.2s ease;
}

.retweet-popup-input-inner:focus-within {
    border-color: #1da1f2;
    box-shadow: 0 0 0 2px rgba(29, 161, 242, 0.2);
}

.retweet-msg {
    flex: 1;
    border: none;
    outline: none;
    padding: 8px 0;
    font-size: 16px;
    color: #14171a;
}

.retweet-msg::placeholder {
    color: #657786;
}

.grid-tweet {
    display: grid;
    grid-template-columns: 48px 1fr;
    gap: 12px;
    padding: 12px 0;
}

.img-user-tweet {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
}

.username-twitter {
    color: #657786;
    font-size: 14px;
}

.mt-post-tweet {
    margin-top: 12px;
}

.img-post-retweet {
    max-width: 100%;
    border-radius: 12px;
    object-fit: cover;
}

.comment-post {
    background: #f7f9fa;
    border-radius: 12px;
    padding: 12px;
    margin-top: 12px;
    border: 1px solid #e1e8ed;
}

.retweet-popup-footer {
    display: flex;
    justify-content: flex-end;
    padding-top: 16px;
    border-top: 1px solid #e1e8ed;
    margin-top: 16px;
}

.comment-it, .reply-it {
    background: #1da1f2;
    color: white;
    border: none;
    border-radius: 24px;
    padding: 10px 20px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

.comment-it:hover, .reply-it:hover {
    background: #1a91da;
    transform: translateY(-1px);
}

.comment-it:disabled, .reply-it:disabled {
    background: #9bd1f9;
    cursor: not-allowed;
    transform: none;
}
</style>

<script>
// Enhanced Emoji Picker Functionality
document.addEventListener('DOMContentLoaded', function() {
    // Function to initialize emoji picker
    function initEmojiPicker(buttonId, containerId, inputId, closeButtonId) {
        const emojiButton = document.getElementById(buttonId);
        const emojiContainer = document.getElementById(containerId);
        const textInput = document.getElementById(inputId);
        const closeButton = document.getElementById(closeButtonId);
        
        if (!emojiButton || !emojiContainer || !textInput) return;
        
        // Toggle emoji picker visibility
        emojiButton.addEventListener('click', function(e) {
            e.stopPropagation();
            emojiContainer.classList.toggle('emoji-picker-hidden');
        });
        
        // Close emoji picker
        if (closeButton) {
            closeButton.addEventListener('click', function(e) {
                e.stopPropagation();
                emojiContainer.classList.add('emoji-picker-hidden');
            });
        }
        
        // Add emoji to input
        const emojis = emojiContainer.querySelectorAll('.emoji');
        emojis.forEach(emoji => {
            emoji.addEventListener('click', function() {
                const emojiChar = this.getAttribute('data-emoji');
                const cursorPos = textInput.selectionStart;
                const textBefore = textInput.value.substring(0, cursorPos);
                const textAfter = textInput.value.substring(cursorPos);
                
                textInput.value = textBefore + emojiChar + textAfter;
                
                // Set cursor position after inserted emoji
                const newCursorPos = cursorPos + emojiChar.length;
                textInput.setSelectionRange(newCursorPos, newCursorPos);
                
                // Keep focus on input
                textInput.focus();
                
                // Close emoji picker after selection
                emojiContainer.classList.add('emoji-picker-hidden');
            });
        });
        
        // Close emoji picker when clicking outside
        document.addEventListener('click', function(e) {
            if (!emojiContainer.contains(e.target) && e.target !== emojiButton) {
                emojiContainer.classList.add('emoji-picker-hidden');
            }
        });
        
        // Close emoji picker on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                emojiContainer.classList.add('emoji-picker-hidden');
            }
        });
    }
    
    // Initialize emoji pickers for both comment and reply popups
    initEmojiPicker('emoji-picker-button', 'emoji-picker-container', 'comment-input', 'close-emoji-picker');
    initEmojiPicker('emoji-picker-button-reply', 'emoji-picker-container-reply', 'reply-input', 'close-emoji-picker-reply');
    
    // Enhanced comment and reply functionality
    const commentButtons = document.querySelectorAll('.comment-it, .reply-it');
    
    commentButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tweetId = this.getAttribute('data-tweet');
            const userId = this.getAttribute('data-user');
            const isComment = this.classList.contains('comment-it');
            const inputField = isComment ? 
                document.getElementById('comment-input') : 
                document.getElementById('reply-input');
            
            if (!inputField) return;
            
            const commentText = inputField.value.trim();
            
            if (commentText === '') {
                // Add visual feedback for empty input
                inputField.style.borderColor = '#e0245e';
                setTimeout(() => {
                    inputField.style.borderColor = '#e1e8ed';
                }, 2000);
                return;
            }
            
            // Disable button during submission
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Posting...';
            
            // Create form data
            const formData = new FormData();
            formData.append(isComment ? 'qoute' : 'reply', tweetId);
            formData.append('user_id', userId);
            formData.append('comment', commentText);
            
            // Send AJAX request
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                // Close popup on success
                const popup = this.closest('.retweet-popup');
                if (popup) {
                    popup.remove();
                }
                
                // You might want to refresh comments section here
                // or add the new comment dynamically
            })
            .catch(error => {
                console.error('Error:', error);
                // Re-enable button on error
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-pencil-alt" aria-hidden="true"></i>' + 
                    (isComment ? 'Reply' : 'Reply');
            });
        });
    });
    
    // Close popup functionality
    const closeButtons = document.querySelectorAll('.close-retweet-popup');
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const popup = this.closest('.retweet-popup');
            if (popup) {
                popup.remove();
            }
        });
    });
    
    // Close popup when clicking on backdrop
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('retweet-popup')) {
            e.target.remove();
        }
    });
    
    // Close popup on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const popups = document.querySelectorAll('.retweet-popup');
            popups.forEach(popup => popup.remove());
        }
    });
});
</script>