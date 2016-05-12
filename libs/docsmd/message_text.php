
<div class="comment-item" <?=$attr?>>
    <div class="comment-item-header">    
<!--        <a href="#" data-user-id="<?=$user_id?>"><?=$user_name?></a>
        сообщение от <?=$comment_time?> (<a href="#" data-action="user_messages">всего сообщений <?=$message_count?>)</a><br>-->


        <?php 
         echo '<strong>'.$user_name.'</strong>';
         if (isset($replay_to_user)){
            echo ' в ответ пользователю <strong>'.$replay_to_user.'</strong> ';
         }
         '&nbsp;'.time_ago($ago);
        ?>

    </div>
    
    <div class="comment-avatar">
        <a href="#">
            <img src="./libs/docsmd/avatar.php?user_id=<?=$user_id?>" alt="нет аватара"  data-user-id="<?=$user_id?>">
        </a>
    </div>
    
    <div class="comment-text">
        <?=$comment_text?>
    </div>
    <div style="clear: left"></div>
</div>



