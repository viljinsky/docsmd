<?php

    require_once  'Parsedown.php';

    include 'docsmd-config.php';

    include 'site-map.php';
    
    define('HOME', 'Начало');
    define('NEXT','Вперёд');
    define('PRIOR','Назад');
    define('SITE_MAP', 'sitemap');
    


    // получает все без парента
    function getIndex($map){
        foreach ($map as $a):
            if (empty($a['parent'])):
                echo '<a href="?page='.$a['page'].'">'.$a['title'].'</a></br>';
            endif;            
        endforeach;
    }

    function pageTitle($map,$serach){
        foreach ($map as $key=>$a){
            if ($a['page']===$serach){
                return $a['title'];
            }
        }
    }

    $page = filter_input(INPUT_GET,'page');

    function document_navigator(){
        global $sitemap,$page;
        // ищем парент

        $sm    = '<a href="'.DOC_PAGE.'?page='.SITE_MAP.'">Карта справочника</a>';

        $prior      = PRIOR;
        $next       = NEXT;
        $path       = '';

        $a = $sitemap->nextprior($page) ;//nextPage2($sitemap->map, $page);
        if ($a['prior']!==null){
                $prior = '<a href="'.DOC_PAGE.'?page='.  $a['prior'].'">'.PRIOR.'</a>';            
        }
        if ($a['next']!==null){
                $next = '<a href="'.DOC_PAGE.'?page='.  $a['next'].'">'.NEXT.'</a>';
        }

        if (isset($page) && ($m1 = $sitemap->page($page))){  // getPage($map, $page))){

            // родственники серча
            $a=array();
            $n = 0;
            foreach ($sitemap->map as $m2){
                if ($m1['parent']===$m2['parent']){
                    $a[$m2['page']]=$n++;
                }
            }

            $path = '';
            while (!empty($m1['parent'])){
                $m1=  $sitemap->page($m1['parent']);// getPage($map, $m1['parent']);
                $path ='<a href="'.DOC_PAGE.'?page='.$m1['page'].'">'.$m1['title'].'</a>'.(strlen($path)===0?'':' / ').$path;
            }
                
        }
        if (empty($path)){
            $path = '<a href="'.DOC_PAGE.'">Главная</a>';
        }

        echo    '<!-- page navigator -->       
                <ul class="page-navigator menu">
                <li>'.$sm.'</li>
                <li>'.$prior.'</li>
                <li>'.$next.'</li>
                <li>'.$path.'</li>
                </ul>
                <!-- page navigator -->';
    }


    /**
    * Вывод страницы документации
    * @global type $map
    * @global type $content_path
    */
    function document_page(){
        global $sitemap,$page;

        // страницы исключения
        $a = array(
            'test1'         =>  './test1.php',
            'myschedule'    =>  './app/myschedule.php'
            );
        
        if (!isset($page)){
            $page = DEFAULT_MD;    
        }

        echo '<!-- docpage body -->'.CR.CR;
        echo '<div class="docpage" data-page="'.$page.'">';
        $filename = CONTENT_PATH.$page.'.md';
        // страница исключение
        if (key_exists($page, $a) ){
            $tmp = $a[$page];
            echo 'Попытка загрузить '.$tmp;
            if (file_exists($tmp)){
                include $tmp;
            }
            
        } else if ($page===SITE_MAP) {
            $sitemap->sitemap();
        } else if (file_exists($filename)){
            $parsedown = new Parsedown();
            $text = file_get_contents($filename);
            $link = file_get_contents(CONTENT_PATH.'link.tpl');
            echo $parsedown->text($text."\n".$link);
        } else {    
            echo '<div style="background:red;padding:50px;">Страница не найдена</div>';
        }

        echo '</div>';
        echo CR.CR.'<!-- docpage body -->'.CR.CR;

    }
