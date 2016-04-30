<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
        <link rel="stylesheet" href="libs/docsmd/style.css">
        <script src="libs/docsmd/docseditor.js"></script>
    </head>
    <body>
        <?php
            // Определить путь к сайту
            $server_path= __DIR__.DIRECTORY_SEPARATOR;
            include './libs/config.php';
            include './libs/docsmd/pattern.php';
            ?>
        
        
        <div id="adminpanel">
            <?php  include './libs/docsmd/adminpanel.php';?>
        </div>
        
        <?php  
           include './libs/docsmd/searchform.php';
           document_navigator();
        ?>

        <div class="searchresult">
            
            
        <a href="./?page=video">Видео</a>    
        <a href="./?page=images">Формы</a>    
        <a href="./?page=index">Документация</a>    

        <?php
            document_page();
        ?>
            
        </div>    
        
        <script>
            var editor = new Editor(adminpanel,{
                contenttpl  : '<?=CONTENT_TPL?>',
                page        : '<?=$page?>',
                linktpl     : '<?=LINK_TPL?>',
                contentlink : '<?=CONTENT_LINK?>',
                 
            });
            Search(searchform,document.querySelector('.searchresult'));
            
        </script>
    </body>
</html>
