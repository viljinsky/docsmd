<?php

include '../connect.php';

function topic_list(){
    echo '<h3>Темы</h3>';
    $sql = "select a.topic_id,topic_name,topic_caption,(select count(*) 
            from topic_item where topic_id=a.topic_id) as item_count from topic a;";
    $result = mysql_query($sql) or die(mysql_error());
    echo '<table>';
    while ($data = mysql_fetch_array($result)){
        list($topic_id,$topic_name,$topic_caption,$item_count)=$data;
        echo "<tr data-id='$topic_name'><td>$topic_id</td><td><a href='#' data-action='page'>$topic_name</a></td><td>$topic_caption</td><td>$item_count </td><tr>";
    }
    echo '</table>';
    
    echo "Всего сообщений ".total_item();
    echo '<hr>';
}

function user_messages(){
    echo '<h3>Активность</h3>';
    $sql="select a.user_id,a.login,concat(a.first_name,' ',a.last_name) as user_name,
          (select count(*) from topic_item where user_id=a.user_id) as item_count,
          (select max(comment_time) from topic_item where user_id=a.user_id) last_comment_time
          from users a ";
    $result = mysql_query($sql)or die(mysql_error());
    echo '<table>';
    while ($data=  mysql_fetch_array($result)){
        list($user_id,$login,$user_name,$item_count,$last_comment_time)=$data;
        echo "<tr><td>$user_id</td><td>$login</td><td>$user_name</td><td>$item_count</td><td>$last_comment_time</td><tr>";
    }
    echo '</table>';
    
    echo 'Всего сообщений '.  total_item();
    echo '<hr>';
    
}

/** всего сообщений */
function total_item(){
    $sql = "select count(*) from topic_item";
    $result = mysql_query($sql) or die(mysql_error());
    while ($data = mysql_fetch_array($result)){
        return intval($data[0]);
        return;
    }
    
}

$page = filter_input(INPUT_GET,'page');
if (!isset($page)){
    $page=1;    
}


/** список сообщений */
function item_list($page){
    $n_count = 50;
    $page_count = intval(total_item() / $n_count);
    $start = ($page-1)*$n_count;

    $first = ($page>1)?'<a href="?page=1">Страница 1</a>':'';
    $prior = ($page>2)?'<a href="?page='.($page-1).'">Страница '.($page-1).'</a>':'';
    $next  = ($page<$page_count-1)?'<a href="?page='.($page+1).'">Страница '.($page+1).'</a>':'';
    $last  = ($page<$page_count)?'<a href="?page='.($page_count).'">Страница '.$page_count.'</a>':'';
    echo '<ul><li>'.$first.'</li><li>'.$prior.'</li><li>Страница '.$page.'</li><li>'.$next.'</li><li>'.$last.'</li></ul>';
    
    echo '<h3>Список сообщений</h3>';
    
//    $sql= "select b.topic_name,
//           b.topic_caption,
//           concat(u.last_name,' ',u.first_name) as user_name,
//           u.login,
//           a.comment_time,a.comment_text,
//           a.item_id,
//           a.replay_to,
//           a.topic_id,
//           a.replay_to,
//           a.user_id
//           from topic_item a inner join topic b on a.topic_id=b.topic_id
//            inner join users u on u.user_id=a.user_id 
//            order by ifnull(a.replay_to,a.item_id),item_id
//            limit $start,$n_count
//            ;";

    $result = mysql_query("select * from v_comments") or die(mysql_error());
    $message_no = ($page-1)*$n_count;
    while ($data = mysql_fetch_array($result)){
        echo '<div class="comment-item">';
        echo ''.$data['user_name'].'<br>';
        echo ''.$data['replay_to_user'].'<br>';
        echo ''.$data['comment_text'];
        echo '</div>';
    }
    
    
}


?>

<!DOCTYPE=html>
<html lang="ru">
    <head>
        <title>Сообщения</title>
        <style>
            ul {list-style: none;}
            li {display: inline-block;}
            .comment-item{border: 1px solid #ccc;margin-bottom: 10px;}
            [data-replay-to]{margin-left: 80px;}
        </style>
            
    </head>
    <body>

    <h2>Сообщения пользователей</h2>

    <div class="lass_messages">
    <?php  item_list($page); ?>
    </div>
    
    <div class="topic-list">
    <?php topic_list(); ?>
    </div>

    <div class="user_activity">
    <?php user_messages(); ?>
    </div>



    <script>
        var element = document.querySelector('.topic-list');
                if (element!==null){
                    element.onclick= function(event){
                    var target = event.target;
                    if (target.hasAttribute('data-action')){
                        var action  = target.getAttribute('data-action');
                        var tr = target.closest('tr');
                        var id = tr.getAttribute('data-id');
        //                alert(action+' '+id);
                        location.assign('http://localhost/docsmd3/?'+action+'='+id);
                    }
                    
                    return false;
                };
            }

    </script>

    </body>
</html>

