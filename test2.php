<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$server_path = __DIR__.DIRECTORY_SEPARATOR;
include './libs/config.php';
include './libs/docsmd/docsmd-config.php';
include './libs/docsmd/site-map.php';
include './libs/connect.php';



function update_toics($sitemap){

    foreach ($sitemap->pages() as $page){
        $topic_caption = $page['title'];
        echo $page['page'].''.$page['title'].'<br>';
        $result = mysql_query("select topic_id from topic where topic_name='".$page['page']."'") or die(mysql_error());
        if (mysql_num_rows($result)===1){
            $data=  mysql_fetch_array($result);
            $topic_id=$data['topic_id'];
            echo '->'.$topic_id.'<br>';
            mysql_query("update topic set topic_caption='$topic_caption' where topic_id=$topic_id") or die(mysql_error());
        }
    }
}

function insert_topics($sitemap){
    foreach ($sitemap->pages() as $page){
//        $v=$sitemap->page($page);
        $topic_name=$page['page'];
        $result = mysql_query("select topic_id from topic where topic_name='$topic_name'") or die(mysql_error());
        if (mysql_num_rows($result)===0){
            mysql_query("insert into topic(topic_name) values ('$topic_name')");
            echo 'insert '.$topic_name.' <br>';
        }
    }
}

insert_topics($sitemap);
update_toics($sitemap);

