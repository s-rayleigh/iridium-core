<?php


namespace site\daemons;


use core\Daemon;
use core\Database;
use core\EmailLetter;
use core\log\Log;

/**
 * Демон, выполняющий рассылку уведомлений об новых каналах, сезонах и эпизодах подписавшимся пользователям.
 * @package site\daemons
 */
final class NotificationsDaemon extends Daemon
{
	protected function Iteration()
	{
		$notificationData = Database::GetRow("SELECT `id`, `type` FROM `" . MAIL_NOTIFICATIONS . "` ORDER BY `time` ASC LIMIT 1;");
		Database::Query("DELETE FROM `" . MAIL_NOTIFICATIONS . "` WHERE `id` = '{$notificationData['id']}' AND `type` = '{$notificationData['type']}';");

		if(empty($notificationData))
		{
			Log::Debug("Очередь уведомлений пуста.");
			return;
		}

		Log::Debug("Отправка уведомления с id {$notificationData['id']} и типом {$notificationData['type']}.");

		switch((int)$notificationData['type'])
		{
			case NOTIFICATION_TYPE_CHANNEL:
				$channelData = Database::GetRow("SELECT `name`, `description` FROM `" . CHANNELS . "` WHERE `id` = '{$notificationData['id']}' LIMIT 1;");

				$whatShort = 'канал';
				$what      = "$whatShort \"{$channelData['name']}\"";
				$desc      = $channelData['description'];

				$privField = "e_notify_chan";
				break;
			case NOTIFICATION_TYPE_SEASON:
				$seasonData = Database::GetRow("SELECT sea.`name`, sea.`description`, chan.`name` as `channel_name`
												FROM `" . SEASONS . "` as sea, `" . CHANNELS . "` as chan
												WHERE sea.`id` = '{$notificationData['id']}' AND sea.`channel_id` = chan.`id` LIMIT 1;");

				$whatShort = 'сезон';
				$what      = "$whatShort \"{$seasonData['name']}\" канала \"{$seasonData['channel_name']}\"";
				$desc      = $seasonData['description'];

				$privField = "e_notify_sea";
				break;
			case NOTIFICATION_TYPE_EPISODE:
				$episodeData = Database::GetRow("SELECT ep.`name`, ep.`description`, chan.`name` as `channel_name`
												FROM `" . EPISODES . "` as ep, `" . CHANNELS . "` as chan
												WHERE ep.`id` = '{$notificationData['id']}' AND ep.`channel_id` = chan.`id` LIMIT 1;");

				$whatShort = 'эпизод';
				$what      = "$whatShort \"{$episodeData['name']}\" канала \"{$episodeData['channel_name']}\"";
				$desc      = $episodeData['description'];

				$privField = "e_notify_ep";
				break;
			default:
				Log::Error("Неправильно задан тип уведомления! Тип: {$notificationData['type']}.");
				return;
		}

		$usersData = Database::GetRows("SELECT u.`login`, u.`email`, u.`first_name`, u.`second_name`, u.`ban`
									FROM `" . USERS . "` as u, `" . USERS_SETTINGS . "` as s
									WHERE u.`id` = s.`user_id` AND s.`$privField` > 0;");

		$content = "На сайте появился новый $what.<br>Описание:<br>$desc";
		$subject = "Новый $whatShort на сайте " . SITE_NAME;

		foreach($usersData as $user)
		{
			if($user['ban'])
			{
				continue;
			}

			$userName         = empty($user['first_name']) ? $user['login'] : $user['first_name'];
			$notificationMail = new EmailLetter("Здравствуй, $userName!<br>" . $content, $subject);

			if(empty($user['first_name']) || empty($user['second_name']))
			{
				$notificationMail->AddRecipient($user['email']);
			}
			else
			{
				$notificationMail->AddRecipient($user['email'], $user['first_name'] . ' ' . $user['second_name']);
			}

			$notificationMail->Send();
		}
	}
}