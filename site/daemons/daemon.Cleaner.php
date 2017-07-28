<?php


namespace site\daemons;


use core\Daemon;
use core\Database;
use core\file\File;
use core\log\Log;

/**
 * Демон-уборщик.
 * @package site\daemons
 */
final class CleanerDaemon extends Daemon
{
	protected function Iteration()
	{
		$this->DeleteSourceVideos();
		$this->DeleteOldLogs();
	}

	/**
	 * Удаляет исходные видеоролики обработанных эпизодов.
	 */
	private function DeleteSourceVideos()
	{
		$sourceVideos = Database::GetRows("SELECT `video_id` FROM `" . EPISODES . "` WHERE `video_id` IS NOT NULL AND `status` = " . VIDEO_PROCESSED . " LIMIT 150;");

		if(!empty($sourceVideos))
		{
			$sourceVideos = array_column($sourceVideos, 'video_id');
			$count        = count($sourceVideos);

			Log::Info("Удаление $count исходных видеороликов обработанных эпизодов.");

			foreach($sourceVideos as $videoId)
			{
				Log::Debug("Удаление файла с id {$videoId}.");

				try
				{
					$file = File::GetById($videoId);
					$file->Delete();
				}
				catch(\Exception $e)
				{
					Log::Error("Не удалось удалить исходный видеоролик.\nПричина: {$e->getMessage()}.");
				}

				Database::Query("UPDATE `" . EPISODES . "` SET `video_id` = null WHERE `video_id` = '{$videoId}' LIMIT 1;");
			}
		}
		else
		{
			Log::Info("Исходные видеоролики не найдены.");
		}
	}

	/**
	 * Удаляет старые файлы логов.
	 */
	private function DeleteOldLogs()
	{
		if(!file_exists(LOG_FILES_PATH))
		{
			Log::Warning("Файлы логов не удалены, так как директория с файлами логов не существует.");
			return;
		}

		$files = array_diff(scandir(LOG_FILES_PATH), array('..', '.'));

		if(empty($files))
		{
			return;
		}

		foreach($files as $file)
		{
			//Отсеиваем файлы без даты
			if(preg_match("/([0-9]{1,4}\\.{1}){3}log/i", $file) !== 1)
			{
				continue;
			}

			$fileNameDate = str_replace('.log', '', end(explode('_', $file)));
			$fileDate     = date_create_from_format('Y.m.d', $fileNameDate);

			$fileDate->add(new \DateInterval('P7D')); // + неделя

			if($fileDate < new \DateTime())
			{
				Log::Debug("Удаление файла логов $file.\nПолный путь: " . LOG_FILES_PATH . $file);

				if(!unlink(LOG_FILES_PATH . $file))
				{
					Log::Warning("Не удалось удалить файл $file.");
				}
			}
		}
	}
}