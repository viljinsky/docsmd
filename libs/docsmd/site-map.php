<?php

    define('CR',"\n\r");
    define('TAB',"\t");
    
    define('HOME', 'Начало');
    define('NEXT','Вперёд');
    define('PRIOR','Назад');
    define('SITE_MAP', 'sitemap');

    if (!file_exists(CONTENT_PATH.CONTENT_TPL)):
        die(CONTENT_TPL.CONTENT_TPL.' - not found');
    endif;
    
    $map = getMap(CONTENT_PATH.CONTENT_TPL);
    
    function getPage($map,$page){
        
        foreach ($map as $value){
            if ($value['page']===$page){
                return $value;
            }
        }
    }

    function recur($map,$page,$padding=''){
        
        foreach ($map as $key=>$value){
            if ($value['page']===$page){
                echo $padding.'<a href="'.DOC_PAGE.'?page='.$page.'" title="'.$page.'">'.$value['title'].'</a><br>';
                break;
            }
        }
        
        foreach ($map as $key=>$value){
            if ($value['parent']===$page){
                recur($map, $value['page'],$padding."\t");
            }
        }
    }
    
    function getMap($filename){
        $map = array();
        $file = fopen($filename,'r');
        $p = 0 ;
        $last = 0;
        $lastPage = null;
        $L = array();
         while ($str = fgets($file)){

             if (trim($str)==='') { continue; }

             $n = 0;
             for ($i=0;$i<strlen($str);$i++){
                 if($str[$i]!=' '){ break; }
                 $n++;
             }
             $p = $n / 4;    

             if ($p>$last){  array_push($L, $lastPage); }
             for ($i=0;$i<$last-$p;$i++){
                $lastPage = array_pop($L) ;
             }

             list($tmp_page,$title)=  explode('=', trim($str));
             $page = trim($tmp_page);
             if ($title===''){
                 $title=$tmp_page;
             } else {
                 $title=  trim($title);
             }
             if (count($L)===0){
                 $parent=null;
             } else {
                 $parent=$L[count($L)-1];
             }
             $lastPage = $page;

             $last = $p;
             $m = array('page'=>$page,'parent'=>$parent,'title'=>$title);
             $map[] = $m;
        }
        fclose($file);
        return $map;
    }

    function getContent($map,$serch){
        $result = '';
        foreach ($map as $a):
            if ($a['parent']===$serch):
                $result .= '* ['.$a['title'].']['.$a['page'].']'."\n";
            endif;
        endforeach;
        if (strlen($result)>0){
            $result = CR.CR."В этой главе следующие разделы".CR.CR.$result;
        }
        return $result;
    }
    
//------------------------------------------------------------------------------
    function sitemap($map){
        echo '<h1>Карта справочника</h1><pre>';
        foreach ($map as $value){
            if (empty($value['parent'])){
                recur($map, $value['page']);
            }
        }
        echo '</pre>';
    }    
    

