<?php


$server_path = realpath('../../').DIRECTORY_SEPARATOR ;

include_once '../config.php';
include_once './docsmd-config.php';
include './site-map.php';

$command = filter_input(INPUT_POST,'command');
if (isset($command)){
    switch ($command){
        case 'search':
            search();
            break;
        case 'upload':
            upload();
            break;
        default:
            echo 'command not found';
    }
}

function search(){
    global $map;

    $word = urldecode(filter_input(INPUT_POST,'search'));

    echo 'Вы искали <strong>'.$word.'</strong><br>';
    $count = 0;
    if (isset($word) &&  strlen($word)>0){
        $word= mb_strtolower($word,'UTF-8');
        foreach ($map as $value){
            $page = $value['page'];

            $filename = CONTENT_PATH.$page.'.md';
            if (file_exists($filename)){
                $txt = mb_strtolower(file_get_contents($filename),'UTF-8');

                if (preg_match("/$word/", $txt,$matches)>0){
                    $count++;
                    echo '<div class="search-item"><a href="'.DOC_PAGE.'?page='.$value['page'].'">'.$value['title'].'</a><br>';
                    echo print_r($matches);
                    echo "<br><br>";
                    echo $txt;
                    echo '</div>';
                }

            }
        }
    }    
    echo '<div>Всего найдено '.$count.'</div>';
}


/**
 * Загруска страицы документации
 * @return type
 */
function upload(){

    $filename = filter_input(INPUT_POST,'filename');
    $text = urldecode(filter_input(INPUT_POST, 'text'));
    
    $fp = CONTENT_PATH.$filename;
    
    if (file_exists($fp)){
        $f = fopen($fp,'w');
        fwrite($f, $text);
        fclose($f);
        echo '{"error":0,"message":"OK"}';
    } else {
        echo '{"error":1,"message":"Файл '.$fp.' не найден"}';
    }
    
}