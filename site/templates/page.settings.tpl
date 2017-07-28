<main>
	<section class="block block-shadow">
		<div class="tabs-buttons" data-tabs-id="settings-tabs">
			<button>Личные данные</button>
			<button>Приватность</button>
			<button>Оповещения</button>
		</div>

		<hr>

		<div class="tabs" id="settings-tabs">

			<!-- BEGIN: Вкладка параметров личных данных -->
			<div class="edit-data-container">

				<div id="avatar-upload-container">
					<h3>Изменение аватара</h3>
				</div>

				<form method="POST" action="index.php?op=settings.changeUserData" id="change-user-data-form">
					<table>
						<caption><h3>Изменение данных</h3></caption>
						<tr>
							<td>
								<label for="email">E-mail</label>
							</td>
							<td>
								<input value="{user_data['email']}" type="text" name="email" id="email" pattern="{email_regexpr}" required>
							</td>
						</tr>
						<tr>
							<td>
								<label for="first_name">Имя</label>
							</td>
							<td>
								<input type="text" value="{user_data['first_name']}" name="first_name" id="first_name" pattern="{name_regexpr}">
							</td>
						</tr>
						<tr>
							<td>
								<label for="second_name">Фамилия</label>
							</td>
							<td>
								<input type="text" value="{user_data['second_name']}" name="second_name" id="second_name" pattern="{name_regexpr}">
							</td>
						</tr>
						<tr>
							<td colspan="2">
								<input type="submit" value="Сохранить">
							</td>
						</tr>
					</table>
				</form>

				<form method="POST" action="index.php?op=settings.changePassword" id="change-password-form">
					<table>
						<caption><h3>Изменение пароля</h3></caption>
						<tr>
							<td>
								<label for="old_pass">Старый пароль</label>
							</td>
							<td>
								<input type="password" name="old_pass" id="old_pass" required>
							</td>
						</tr>
						<tr>
							<td>
								<label for="new_pass">Новый пароль</label>
							</td>
							<td>
								<input type="password" name="new_pass" id="new_pass" pattern="{password_regexpr}" title="От 4 до 128 символов." required>
							</td>
						</tr>
						<tr>
							<td>
								<label for="repeat_new_pass">Повторите новый пароль</label>
							</td>
							<td>
								<input type="password" name="repeat_new_pass" id="repeat_new_pass" required>
							</td>
						</tr>
						<tr>
							<td colspan="2">
								<input type="submit" value="Изменить">
							</td>
						</tr>
					</table>
				</form>
			</div>
			<!-- END: Вкладка параметров личных данных -->

			<!-- BEGIN: Вкладка параметров приватности -->
			<div>
				<h2>Параметры приватности</h2>
				<form method="POST" action="index.php?op=settings.changePrivacySettings" id="change-privacy-settings-form">
					<input type="checkbox" value="1" id="display-name-comments" name="display-name-comments"{if user_data['d_comm_name']} checked{/if}>
					<label for="display-name-comments">Отображать мои имя и фамилию в комментариях вместо логина</label>

					<input type="checkbox" value="1" id="display-name-profile" name="display-name-profile"{if user_data['d_prof_name']} checked{/if}>
					<label for="display-name-profile">Показывать мои имя и фамилию в профиле</label>

					<input type="checkbox" value="1" id="display-mail-profile" name="display-mail-profile"{if user_data['d_email']} checked{/if}>
					<label for="display-mail-profile">Показывать мой e-mail в профиле</label>

					<input type="checkbox" value="1" id="display-subscriptions" name="display-subscriptions"{if user_data['d_subs']} checked{/if}>
					<label for="display-subscriptions">Позволить просматривать мои подписки другим пользователям</label>

					<input type="submit" value="Сохранить">
				</form>
			</div>
			<!-- END: Вкладка параметров приватности -->

			<!-- BEGIN: Вкладка параметров оповещений -->
			<div>
				<h2>Параметры оповещений</h2>
				<form method="POST" action="index.php?op=settings.changeNotificationSettings" id="change-notification-settings-form">
					<input type="checkbox" value="1" id="m-notify-episodes" name="m-notify-episodes"{if user_data['e_notify_ep']} checked{/if}>
					<label for="m-notify-episodes">Оповещать меня по e-mail о выходе новых эпизодов на каналах, на которые я подписан(а)</label>

					<input type="checkbox" value="1" id="m-notify-seasons" name="m-notify-seasons"{if user_data['e_notify_sea']} checked{/if}>
					<label for="m-notify-seasons">Оповещать меня о появлении новых сезонов на каналах, на которые я подписан(а)</label>

					<input type="checkbox" value="1" id="m-notify-channels" name="m-notify-channels"{if user_data['e_notify_chan']} checked{/if}>
					<label for="m-notify-channels">Оповещать меня о появлении новых каналов на сайте</label>

					<input type="submit" value="Сохранить">
				</form>
			</div>
			<!-- END: Вкладка параметров оповещений -->

		</div>
	</section>
</main>