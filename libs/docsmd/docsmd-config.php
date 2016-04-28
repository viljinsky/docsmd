<?php

//------------------------------------------------------------------------------
//
//                           Настройки документации
//                           
//------------------------------------------------------------------------------


/** Имя папки для документов */
define('DOC_FALDER','docs');

/** Ссылка на папку содержимого документации */
define('CONTENT_PATH', $server_path.DOC_FALDER.DIRECTORY_SEPARATOR);

/** путь к процессору документации - pattern.php*/
define('DOC_PAGE','./index.php');

/** Путь к илюстрациям документации */
$image_path = CONTENT_PATH.'images'.DIRECTORY_SEPARATOR;

/** Путь к илюстрациям документации */
define('IMAGE_PATH',CONTENT_PATH.'images'.DIRECTORY_SEPARATOR);

/** Имя файла для хранения линков */
define('LINK_TPL', 'link.tpl');

/** Имя файла для храннеия контекста документации */
define('CONTENT_TPL','content.tpl' );

/** Имя страницы документации по умолчанию */
define('DEFAULT_MD','index');


/** адресс, прописываемый в link.tpl для иллюстраций */
//$image_path_link =  $server_link.'help/images/';
// $image_path_link = './'.DOC_FALDER.'/images/';
define('IMAGE_LINK','./'.DOC_FALDER.'/images/');

/** Путь для размещения скриншортов пользователей */
//$screenshort_path = $server_path
//                    .DOC_FALDER.DIRECTORY_SEPARATOR
//                    .'tmp'.DIRECTORY_SEPARATOR;

define('ATTACH_PATH',$server_path
                    .DOC_FALDER.DIRECTORY_SEPARATOR
                    .'tmp'.DIRECTORY_SEPARATOR);

/** Путь для размещения аттачей(имиджей) пользователей */
define('ATTACH_LINK','./'.DOC_FALDER.'/tmp/');


define('CONTENT_LINK',LOCATION.'/'.DOC_FALDER.'/');
