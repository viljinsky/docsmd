<?php
session_start();

$server_path = realpath('../../').DIRECTORY_SEPARATOR;
include '../connect.php';

include './docsmd-config.php';

include_once './Parsedown.php';
include '../auth/Permission.php';



$command = filter_input(INPUT_POST,'command');
if (isset($command)){
    switch ($command){
        case 'count':
            $topic = filter_input(INPUT_POST,'topic');
            $n= get_message_count($topic);
            $count = intval($n['count']);
            if ($count===0){
                echo '<h2>Комментарии (ещё никто не писал)</h2>';
            } else {
                echo '<h2>Комментарии ('.$count.')</h2>';
            }
            
            break;
        case 'add':
        case 'replay':
            add_message();
            break;
        
        case 'edit':
            edit_message();
            break;
        
        case 'delete':
            echo delete_message();
            break;
        
        case 'read':
            read();
            break;
        
        case 'quotes':
            quotes();
            break;
        
        case 'comment-header':
            comment_header();
            break;
        
        case 'read-attachment':
            read_attachment();
            break;
            
        case 'delete-attachment':
            delete_attachment();
            break;
        
        case 'upload-attach':
            upload_attach();
            break;
        
        case 'reload-item':
            reload_item();
            break;
        
        case 'user-messages':
            user_messages();
            break;
        
        case 'mark':
            mark();
            break;
        default :
            echo "Command '".$command."' - not defined";
    }
}

function mark(){
    $user_id = filter_input(INPUT_POST,'user_id');
    $item_id = filter_input(INPUT_POST,'item_id');
    $mark = filter_input(INPUT_POST,'mark');
    
    $result = mysql_query("select mark from item_mark where user_id=$user_id and item_id=$item_id");
    if (mysql_num_rows($result)===0){
        mysql_query("insert into item_mark(user_id,item_id,mark) values($user_id,$item_id,$mark)") or die(mysql_error());
    } else {
        mysql_query("update item_mark set mark=$mark where user_id=$user_id and item_id=$item_id") or die(mysql_error());
    }

    $result = mysql_query("select (select count(*) from item_mark "
            . "where item_id=$item_id and mark=true) as up,"
            . "(select count(*) from item_mark where item_id=$item_id and mark=false) as down;") or die(mysql_error());
    $data= mysql_fetch_array($result,MYSQL_ASSOC);
    
    echo '{"up":'.$data['up'].',"down":'.$data['down'].',"error":0}';//print_r($data);
}

function user_messages(){
    $user_id = filter_input(INPUT_POST,'user_id');
    $result = mysql_query("select b.topic_caption,b.topic_name,a.comment_time from topic_item a inner join topic b on a.topic_id=b.topic_id
                           where a.user_id=$user_id;") or (die(mysql_error()));
    
    echo '<div class="comments-inner">';
    
    while ($data = mysql_fetch_array($result)){
        list($topic_caption,$topic_name,$comment_time)=$data;
        
        echo '<div class="comment>';
        echo '<div class="comment-item>';
        echo ''.$topic_caption.' '.$topic_name.' '.$comment_time.'<br>';
        echo '</div>';
        
        echo '</div>';
        
    }
    
    echo '</div>';
}

function reload_item(){
    $item_id= filter_input(INPUT_POST,'item_id');
    read_message_item($item_id);
}

function delete_attachment(){
   $image_id = filter_input(INPUT_POST,'image_id');
   $result = mysql_query("select src from topic_images where image_id=$image_id") or die(mysql_error());
   if (mysql_num_rows($result)===1){
       $data = mysql_fetch_array($result);
       $src = $data['src'];
       $filename = ATTACH_PATH.$src;
       if (file_exists($filename)){
           unlink($filename);
       }
       mysql_query("delete from topic_images where image_id=$image_id") or die(mysql_error());
       echo " Файл ".$src.' '.(file_exists($filename)?'exists':'dont exists');
   } else {
       echo 'Имидж id='.$image_id.' - не найден';
   }
}
    
function comment_header(){
    $item_id=  filter_input(INPUT_POST, 'item_id');
    $sql="select a.comment_time,concat(u.last_name,' ',u.first_name),a.comment_text\n" 
        ." from topic_item a inner join users u on a.user_id=u.user_id where a.item_id=$item_id;";
    $result = mysql_query($sql) or die(mysql_error());
    if ($result){
        $data = mysql_fetch_array($result);
        list($comment_time,$user_name,$text)=$data;
        echo '{"error":0,"message":"OK","header":"    на сообщение '.$user_name.' от '.$comment_time.'"}';
    }
}


function upload_attach(){
    $item_id = filter_input(INPUT_POST,'item_id');
    $tempfile = $_FILES['screenshort']['tmp_name'];
    $filename = urldecode($_FILES['screenshort']['name']);

    // закодированное имя файла
    $src = uniqid();
    
    
    define('MAX_UPLOAD_SIZE',200000);
    
    if (filesize($tempfile)>MAX_UPLOAD_SIZE){
        echo '{"error":3,"message":"Размер файла превышеет допустимый"}';
        return;
    }

    if ( move_uploaded_file($tempfile,ATTACH_PATH.$src)){

        $sql = "insert into topic_images (item_id,src,filename) \n"
              ."values ($item_id,'$src','$filename')";

        $result = mysql_query($sql) or die('upload-attach error : '.mysql_error());
            $image_id = mysql_insert_id();
            echo '{"error"   : 0,"message":"OK",'
                   .'"image_id": '.$image_id.','
                   .'"filename": "'.$filename.'",'
                   .'"src"     : "'.ATTACH_LINK.$src.'" }';
    }  
}

/**
 * Чтение содержиого сообщения
 * @return type
 */
function quotes(){
    $item_id = filter_input(INPUT_POST,'item_id');
    $sql = "select comment_text from topic_item where item_id=$item_id";
    $result = mysql_query($sql) or die('quotes : '.  mysql_error());
    
    if (mysql_num_rows($result)>0){
        $data = mysql_fetch_array($result);    
        echo $data['comment_text'];
        return;
    }
    
    echo '{"error":1,"message":"'.mysql_error().'","sql":"'.$sql.'"}';
}

function read_attachment(){
    $item_id= filter_input(INPUT_POST, 'item_id');
    return get_item_attachment($item_id);
}
    
function get_item_attachment($item_id){
    $html = '<div class="item-attachment">';
    $result = mysql_query("select image_id,filename,src from topic_images where item_id=$item_id") 
            or die("Ошибка get_item_images :"+  mysql_error());
    if (mysql_num_rows($result)===0){
        $html.='<div>Нет прикреплений</div>';
    } else {
        $html.='<div>Прикреплено</div>';
        $html.='<table class="attachments">';
        while ($data=  mysql_fetch_array($result)){
            list($image_id,$filename,$src)=$data;
            $html.='<tr data-attach-id="'.$image_id.'">'
                   .'<td>'
                   .'<a href="'.ATTACH_LINK.$src.'" target="_blank">'.$filename.'</a>'
                   .'</td>'
                   .'</tr>';
        }
        $html .= '</table>';
    }
//    $html.='<div><button data-action="add_attachment">Добавить файл</button></div>';
    $html.='</div>';
    return $html;
}    

/**
 * Чтение обного сообщения - содержиого элемента comment
 * @param type $item_id
 */
function read_message_item($item_id){

    $parse = new Parsedown();

    $result=mysql_query("select * from v_comments where item_id=$item_id") or die(mysql_error());

    $data = mysql_fetch_array($result);
    
    list($t_id,$parent_id,$item_id,$comment_time,$comment_text, $user_id,$user_name,$replay_to,$topic_name,$message_count,$ago,$mark_up,$mark_down,$replay_to_user_id,$replay_to_user,$attach_count)=$data;

    $comment_text = $parse->text($comment_text);

    
    
    $allow_attach = true;
    $allow_edit = true;
    $allow_replay =false;
    
    $attr = 'data-user-id="'.$user_id.'" data-comment-id="'.$item_id.'" ' 
           .'data-permission="'.$allow_attach.','.$allow_edit.','.$allow_replay.','.$allow_edit.'" ';
    if (isset($replay_to)){
        $attr .= ' data-replay-to="'.$replay_to.'"';
    }

    $attr .= ' data-mark="'.$mark_up.','.$mark_down.'"';
    

    include './message_text.php';
    echo get_item_attachment($item_id);

}

/**
 * Добавление сообщения
 */
function add_message(){

    $topic_name = urldecode(filter_input(INPUT_POST,'topic_name'));
    $message    = htmlspecialchars(urldecode(filter_input(INPUT_POST, 'message')),ENT_QUOTES);
    $user_id    = filter_input(INPUT_POST, 'user_id');
    $replay_to  = filter_input(INPUT_POST,'replay_to');

    if (!isset($replay_to) || $replay_to===''):
        $replay_to='null';
    endif;

    $result = mysql_query("select topic_id from topic where topic_name='$topic_name'");
    if (mysql_num_rows($result)===0){
        $sql = "insert into topic (topic_name) values('$topic_name')";
        mysql_query($sql) or die('sql: '.$sql."\n message: ".mysql_error());
        $topic_id = mysql_insert_id();
    } else {
        list($topic_id) = mysql_fetch_array($result);
    }

    $sql = "insert into topic_item (topic_id,replay_to,comment_text,user_id) "
          ."values ($topic_id,$replay_to,'$message',$user_id)";

    if (!mysql_query($sql)):
        echo mysql_error().' '.$sql;
    endif;

    $item_id = mysql_insert_id();
    read_message_item($item_id);

}
/**
 * Редактирование сообщения
 * @return type
 */
function edit_message(){

    $item_id = filter_input(INPUT_POST,'item_id');
    $message = htmlspecialchars(urldecode(filter_input(INPUT_POST,'message')),ENT_QUOTES);

    $sql = "update topic_item set comment_text = '$message' where item_id=$item_id";
    if (!mysql_query($sql)):
        return mysql_error()+' '+$sql;
    endif;

    read_message_item($item_id);
}

function delete_message(){
    $item_id= filter_input(INPUT_POST,'item_id');
    
    // физическое удаление изображений пользователя
    $result = mysql_query("select src from topic_images where item_id=$item_id") or die(mysql_error());
    $deleted = '';
    while ($data =  mysql_fetch_array($result)){
        $deleted=$data['src'];
        $filename = ATTACH_PATH.$data['src'];
        if (file_exists($filename)){
            $deleted.=' - OK ';
            unlink($filename);
        } else {
            $deleted.=' - Fail';
        }
    }

    $sql_delete = "delete from topic_item where replay_to=$item_id";
    mysql_query($sql_delete);

    $sql = "delete from topic_item where item_id=$item_id";
    if (mysql_query($sql)){
        return '{"error":0,"message":"Сообщение успешно удалено","deleted":"'.$deleted.'"}';
    } else {
        return '{"error":1,"sql":"'.$sql.'","message":"'.mysql_error().'"}';
    }

} 

function get_message_count($page){
    $sql_select_count = "select count(*) as count ,b.topic_id \n"
          ."from topic_item a inner join topic b on a.topic_id=b.topic_id \n"
          ."where b.topic_name='$page' group by b.topic_id";
    
    $result = mysql_query($sql_select_count) or die(mysql_error());
    return mysql_fetch_array($result);
}

/**
 * Строка дни часы минуты от секунд ago
 * @param type $ago
 */
function time_ago($ago){
     if (isset($ago)){
        $seconds = intval($ago);
        $days = floor($seconds/86400);
        $seconds = $seconds-($days*86400);
        if ($days>0){
            echo $days.' дн. назад';
        } else {
            $hours = floor($seconds/3600);
            $seconds = $seconds-($hours*3600);
            if ($hours>0){
                echo $hours.' ч. назад';
            } else {
                $minutes = floor($seconds/60);
                if ($minutes>0){
                    echo $minutes.' мин. назад';
                } else {
                    echo ' только что';
                }
            }
        }
     }

}


/**
 * Чтение списка сообщений
 * @return type
 */    
function read(){
//    global $permission;


    //---------------- comments-header----------------------------------------//
    $page = urldecode(filter_input(INPUT_POST, 'page'));
    $n = get_message_count($page); 
    echo '<div class="comments-header">';
    if (intval($n['count']) === 0){
        echo '<h2>Комментарии (ещё никто не писал)</h2>';
    } else {
        echo '<h2>Комментарии ('.$n['count'].')</h2>';    
    }
    echo '</div>';
    //---------------- comments-header----------------------------------------//

    //---------------- comments-inner-----------------------------------------//
    echo '<div class="comments-inner">';
    if (intval($n['count'])>0){
    
        $topic_id=$n['topic_id'];    


        $result = mysql_query("select * from v_comments where topic_id=$topic_id order by parent_id desc,item_id") or die(mysql_error());

        $parse = new Parsedown();

//        echo '<div class="comments-inner">';
        
        if (isset($_SESSION['permission'])){
            $permission = $_SESSION['permission'];
        } else {
            $permission = array(1=>true,2=>true,3=>true,4=>true,5=>true);
        }
        $current = intval($_SESSION['user_id']);
//        echo print_r($permission).'<br>';

        while ($data = mysql_fetch_array($result)){
             list($t_id,$parent_id,$item_id,$comment_time,$comment_text, $user_id,$user_name,$replay_to,$topic_name,$message_count,$ago,$mark_up,$mark_down)=$data;
             $replay_to_user= $data['replay_to_user'];

            $comment_text = $parse->text($comment_text);

            // permission 0 - добавлять 1 - отвечать 2- прикреплять 

            
            $allow_write   = $permission[Permission::ADD_MESSAGE] && ($current === intval($user_id));
            $allow_replay  = $permission[Permission::REPLAY_MESSAGE] && ($current!==0 && $current !== intval($user_id));
            $allow_attach  = $permission[Permission::ADD_ATTACHMENT] && ($current === intval($user_id));
            

            $attr = 'data-user-id="'.$user_id.'" data-comment-id="'.$item_id.'" ' 
                   .'data-permission="'.$allow_attach.','.$allow_write.','.$allow_replay.','.$allow_write.'" ';
            if (isset($replay_to)){
                $attr .= ' data-replay-to="'.$replay_to.'"';
            }
            
            $attr .= ' data-mark="'.$mark_up.','.$mark_down.'"';

    //------------------- topic item ----------------------------        
            echo '<div class="comment">';
            include './message_text.php';
            echo get_item_attachment($item_id);
            
//            echo 'allow write  -> "'.$allow_write.'"<br>';
//            echo 'allow replay -> "'.$allow_replay.'"<br>';
//            echo 'allow attach -> "'.$allow_attach.'"<br>';
            
            
            
            echo '</div>';
    //-----------------------------------------------        
        }

    }
    echo '</div>';
    //--------------comments-inner--------------------------------------------//
    
    echo '<div class="comments-footer"><button>Добавить</button></div>';

}

    