<?php

    define('CR',"\n\r");
    define('TAB',"\t");
    
    if (!file_exists(CONTENT_PATH.CONTENT_TPL)):
        die(CONTENT_PATH.CONTENT_TPL.' - not found');
    endif;
    
    
    $sitemap = new SiteMap(CONTENT_PATH.CONTENT_TPL,DOC_PAGE);
    
//    test($sitemap);
    
    
    /**
 * Задача: преобразовать файл-описания иерархии в таблицу вида
 *   page parent title
 */
class SiteMap {
    
    public $map;
    protected $docpage;
            
    /**
     * 
     * @param type $filename файл content.tpl
     * @param type $docpage
     */
    function __construct($filename,$docpage='./index.php') {
        $this->docpage=$docpage;
        $this->map = array();
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
             $this->map[] = $m;
        }
        fclose($file);
        ;
    }
    
    /**
     * 
     * @param type $page
     * @return string
     */
    function content($parent){
        $result = '';
        foreach ($this->pages($parent) as $page){
            $a = $this->page($page);
            $result .= '* ['.$a['title'].']['.$a['page'].']'."\n";
        }
        if (strlen($result)>0){
            $result = "\n\r\n\rВ этой главе следующие разделы\n\r\n\r".$result;
        }
        return $result;
    }
    
    private function recur($page,$padding=''){
        
        $value  = $this->page($page);
        echo $padding.'<a href="'.$this->docpage.'?page='.$page.'" title="'.$page.'">'.$value['title'].'</a><br>';

        foreach ($this->pages($page) as $pn){
            $value = $this->page($pn);
            $this->recur($value['page'],$padding."\t");
        }
        
        
    }
    
    function sitemap(){
        echo '<h1>Карта справочника</h1><pre>';
        foreach ($this->map as $value){
            if (empty($value['parent'])){
                $this->recur($value['page']);
            }
        }
        echo '</pre>';
    }    
    
    function page($page){
        foreach ($this->map as $m){
            if ($m['page']===$page){
                return $m;
            }
        }
    }
    
    function pages($parent = null){
        $result = array();
        foreach ($this->map as $value){
            if (!isset($parent) || $parent==$value['parent']){
                $result[]=$value['page'];
            }
        }
        return $result;
    }
    
    function nextprior($page){

        $pageIndex = null;
        foreach ($this->map as $k=>$v){
            if ($v['page']===$page){
                $pageIndex=$k;
                break;
            }
        }
        $nextPage = null;
        $priorPage=null;
        if (key_exists($pageIndex-1, $this->map)){
            $priorPage = $this->map[$pageIndex-1]['page'];
        }
        if (key_exists($pageIndex+1, $this->map)){
            $nextPage=  $this->map[$pageIndex+1]['page'];
        }
        return array('next'=>$nextPage,'prior'=>$priorPage);
        
    }
    
}

function test($sitemap){
    foreach ($sitemap->pages() as $page){
    echo ''.$page.'<br>';
    $p = $sitemap->page($page);
    echo 'title     : '.$p['title'].'<br>';
    echo 'nextprior : '.print_r($sitemap->nextprior($page)).'<br>';
    echo 'pages     : '.print_r($sitemap->pages($page)).'<br>';
    echo '<hr>';
    }
}

    
