window.addEventListener('load', function()
{
	var avatarPrompt = document.createElement('div');
	avatarPrompt.innerHTML = 'Загрузите аватар.<br>' + avatarExtensions.join('/') + ', ' + avatarResolution + 'x' + avatarResolution + ', макс. ' + readableSize(avatarMaxSize);

	//Создаем поле загрузки аватара
	document.getElementById('avatar-upload-container').appendChild(createFileUploader({
		opUrl: 'index.php?op=uploadAvatar',
		maxSize: avatarMaxSize,
		allowedExtensions: avatarExtensions,
		defaultMessage: avatarPrompt
	}));

	addFormValidation(document.getElementById('change-password-form'), function(result)
	{
		if(result !== 0)
		{ return; }

		(new Popup({
			title: 'Смена пароля',
			content: 'Пароль успешно изменен. Войдите на сайт используя свой новый пароль.',
			overlay: true,
			buttons: [{text: 'Хорошо'}],
			onHide: function()
			{
				goto('index.php?page=loginRegister');
			}
		})).show();
	});

	addFormValidation(document.getElementById('change-user-data-form'), function(result)
	{
		if(result !== 0)
		{ return; }

		(new Notification({
			content: 'Данные пользователя успешно изменены.',
			styleClass: 'success'
		})).show();
	});

	addFormValidation(document.getElementById('change-privacy-settings-form'), function(result)
	{
		if(result !== 0)
		{ return; }

		(new Notification({
			content: 'Параметры приватности успешно сохранены.',
			styleClass: 'success'
		})).show();
	});

	addFormValidation(document.getElementById('change-notification-settings-form'), function(result)
	{
		if(result !== 0)
		{ return; }

		(new Notification({
			content: 'Параметры оповещений успешно сохранены.',
			styleClass: 'success'
		})).show();
	});
});