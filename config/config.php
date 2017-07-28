<?php

/**
 * Параметры базы данных
 */

$database['host']		= 'localhost';					//Хост
$database['user']		= 'root';						//Пользователь
$database['password']	= '';							//Пароль
$database['db_name']	= 'beleme';						//Имя БД
$database['salt']		= 'ld*U=afv23qM^n*{dEi31LLi0';	//Соль для хеша пароля

/**
 * Параметры видеороликов
 */

//Максимальный размер загружаемого видеоролика в байтах
//Данный параметр должен быть меньше upload_max_filesize, post_max_size, memory_limit, которые устанавливаются в php.ini
//Для 32-х систем недопустимо указывать размер > 2147483648 (i32)
//2147483648 -> 2G
//5368709120 -> 5G
$video['max_video_upload_size'] = 5368709120;

//Удалять исходное видео после обработки?
$video['delete_source_video'] = true;

//Допустимые расширения загружаемых видеороликов. Параметр регистронезависимый
$video['allowed_video_extensions'] = array('mp4', 'avi', 'mkv');

//Максимальный размер картинки видеоролика
//2 * 1024 * 1024 = 2M
$video['max_preview_upl_size'] = 2 * 1024 * 1024;

//Расширение изображения видеоролика
$video['allowed_preview_extension'] = ['png'];

//Форматы и кодеки, в которые будет перекодирован видеоролик
//Структура:
//['format' => ['video_codec', 'audio_codec']]
$video['process_formats'] = array('mp4' => array('libx264', 'libmp3lame'), 'webm' => array('libvpx-vp9', 'libopus'));

//Указывает на необходимость отрисовки водяного знака при обработке видеоролика
$video['draw_watermark'] = true;

//Путь к водяному знаку, который будет добавлен в нижний правый угол видеоролика
$video['watermark_path'] = IMAGES_PATH . 'watermark.png';

/**
 * Параметры загрузки файлов
 */

/**
 * Соль названия файла.
 */
$files['file_name_salt'] = 'fjwiJj9442.Dp]!d%fsdMJa$ddz;';

/**
 * Соль названия группы файлов.
 */
$files['group_name_salt'] = 'k0@nujpJKOpd!@12Ms099fWO}->2U';

/**
 * Максимальное количество файлов в группе файлов.
 */
$files['grop_max_files'] = 1000;

/**
 * Максимальный размер логотипа канала.
 */
$files['channel_logo_max_size'] = 1 * 1024 * 1024;

/**
 * Допустимые расширения логотипа канала.
 */
$files['channel_logo_extensions'] = array('png', 'jpg');

/**
 * Максимальный размер изображения страницы канала.
 */
$files['channel_pg_image_max_size'] = 3 * 1024 * 1024;

/**
 * Допустимые расширения изображения страницы канала.
 */
$files['channel_pg_image_extensions'] = array('png', 'jpg');

/**
 * Параметры аватара.
 */

/**
 * Макимальный размер файла аватара.
 */
$avatar['max_size'] = 256 * 1024;

/**
 * Допустимые расширения файла аватара.
 */
$avatar['extensions'] = array('png', 'jpg', 'gif');

/**
 * Разрешение по x и y файла аватара (т. е. соотношение всегда 1:1).
 */
$avatar['resolution'] = 110;

//Google analytics
define('GA_ENABLE', true);
define('GA_ID', 'UA-27810870-3');