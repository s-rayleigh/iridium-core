<?php

namespace site\daemons;

use core\Daemon;
use core\Database;
use core\exceptions\NoticeableException;
use core\file\File;
use core\file\FileType;
use core\log\Log;

/**
 * Демон обработки загруженных видеороликов.
 *
 * Требует ffmpeg.
 *
 * @author rayleigh <rayleigh@protonmail.ch>
 */
final class VideoDaemon extends Daemon
{
	protected function Prepare()
	{
		$timeout = 7 * 24 * 60 * 60; //неделя

		Log::Debug("Ставим максимальное время неактивности перед разрывом соединения с сервером mysql в {$timeout} секунд.");
		Database::Query("SET SESSION wait_timeout = {$timeout}, interactive_timeout = {$timeout};");
	}

	protected function Iteration()
	{
		global $video;

		$episodeData = Database::GetRow("SELECT `id`, `video_id` FROM `" . EPISODES . "` WHERE `status` = '" . VIDEO_UPLOADED . "' ORDER BY `time` ASC LIMIT 1;");

		//Если очередь пуста, тогда переходим к следующей итерации
		if(empty($episodeData['video_id']))
		{
			Log::Debug("Очередь пуста.");
			return;
		}

		try
		{
			$videoFile = File::GetById($episodeData['video_id']);
		}
		catch(NoticeableException $e)
		{
			$e->LogException();
			return;
		}
		catch(\Exception $e)
		{
			Log::Error("При получении файла с id {$episodeData['video_id']} было выброшено исключение:\n{$e->getMessage()}");
			Log::Save();
			return;
		}

		$videoPath = $videoFile->GetPath();

		$ffmpegVersion = [];
		exec("ffmpeg -version", $ffmpegVersion);

		Log::Info("Начало обработки видео с id файла {$episodeData['video_id']}.\nПуть к файлу: $videoPath\nid эпизода: {$episodeData['id']}\nВерсия ffmpeg: {$ffmpegVersion[0]}");
		Log::Save();

		//Проверяем наличие файла видеоролика
		if(!file_exists($videoPath))
		{
			Log::Error("Не найден файл видеоролика!\nПуть к файлу: $videoPath\nИдентификатор эпизода: " . $episodeData['id']);
			return;
		}

		//Меняем текущий статус эпизода на "Обрабатывается"
		Database::Query("UPDATE `" . EPISODES . "` SET `status` = '" . VIDEO_PROCESSING . "' WHERE `id` = '{$episodeData['id']}' LIMIT 1;");

		//Перебираем список форматов, в которые нужно перекодировать видеофайл
		foreach($video['process_formats'] as $format => list($videoCodec, $audioCodec))
		{
			$resultTempPath = TEMP_PATH . "tmp_processed_video.$format";

			if($video['draw_watermark'] && file_exists($video['watermark_path']))
			{
				$command = "ffmpeg -y -i $videoPath -i {$video['watermark_path']} -filter_complex '[1:v]scale=120x120[pic_sc];[0:v][pic_sc]overlay=W-w-10:H-h-10[out]' -map '[out]' -map 0:a:0 -c:v $videoCodec -c:a $audioCodec $resultTempPath";
			}
			else
			{
				$command = "ffmpeg -y -i $videoPath -c:v $videoCodec -c:a $audioCodec $resultTempPath";
			}

			Log::Debug("Начинаем обработку в формат $format.\nВидеокодек: $videoCodec\nАудиокодек: $audioCodec\nКоманда: $command");
			Log::Save();

			exec($command);

			Log::Debug("Файл обработан. Создаем объект файла.");

			try
			{
				$processedVideo = File::Local($resultTempPath);

				Log::Debug("Объект файла создан. Перемещаем файл в хранилище.");

				$processedVideo->MoveToStorage(FileType::VIDEO);
			}
			catch(NoticeableException $e)
			{
				$e->LogException();
				continue;
			}
			catch(\Exception $e)
			{
				Log::Error("При создании или перемещении файла обработанного видеоролика было выброшено исключение:\n{$e->getMessage()}");
				continue;
			}

			Log::Debug("Обработанный файл перемещен в хранилище файлов.");

			Database::Query("INSERT INTO `" . PROCESSED_VIDEOS . "`(`episode_id`, `file_id`) VALUES('{$episodeData['id']}', '{$processedVideo->GetId()}');");
		}

		//Меняем статус на "видео обработано"
		Database::Query("UPDATE `" . EPISODES . "` SET `status` = '" . VIDEO_PROCESSED . "' WHERE `id` = {$episodeData['id']} LIMIT 1;");
	}
}