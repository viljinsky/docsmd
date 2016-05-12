<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Управление содержимым сайта v 4.0</title>
        <link rel="stylesheet" href="libs/docsmd/style.css">
        <script src="libs/docsmd/docseditor.js"></script>
    </head>
    <body>
        
        <?php
            // Определить путь к сайту
            $server_path= __DIR__.DIRECTORY_SEPARATOR;
            include './libs/config.php';
            include './libs/docsmd/pattern.php';
            
            document_navigator();
            
        ?>

        <div class="searchresult">
            
            
            <ul class="menu main-menu">    
                <li><a href="./?page=video">Видео</a></li>    
                <li><a href="./?page=images">Формы</a></li>    
                <li><a href="./?page=index">Документация</a></li>    
           </ul>

        <?php  document_page(); ?>
            
        </div>    
        
        <script>
            
            Editor({
                contenttpl  : '<?=CONTENT_TPL?>',
                page        : '<?=$page?>',
                linktpl     : '<?=LINK_TPL?>',
                contentlink : '<?=CONTENT_LINK?>',
                 
            });
    
            Search(document.querySelector('.searchresult'));
            
        </script>
    </body>
</html>
