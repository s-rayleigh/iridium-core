var usersList     = document.getElementById('users-list'),
	pages         = document.querySelector(".page-control > span"),
	usersDataList = new DataList({
		opUrl: 'index.php?op=cp.us.usersList',
		usePageNavigation: true,
		onLoad: function(result)
		{
			pages.innerHTML = (result.page + 1) + '/' + result.pages;

			while(usersList.children.length)
			{
				usersList.removeChild(usersList.children[0]);
			}

			if(!result.list)
			{ return; }

			result.list.forEach(function(userData)
			{
				usersList.appendChild(createUserPlank(userData));
			});
		}
	});

window.addEventListener('load', function()
{
	usersDataList.load();

	document.getElementById('prev-page').addEventListener('click', function()
	{
		usersDataList.prevPage();
		usersDataList.load();
	});

	document.getElementById('next-page').addEventListener('click', function()
	{
		usersDataList.nextPage();
		usersDataList.load();
	});

	var loginSearchField = document.getElementById('login-search'),
		emailSearchField = document.getElementById('email-search'),
		bannedCheckbox   = document.getElementById('only-banned'),
		unbannedCheckbox = document.getElementById('only-unbanned');

	loginSearchField.addEventListener('input', function()
	{
		usersDataList.updateLoadParameters({login: loginSearchField.value});
		usersDataList.load();
	});

	emailSearchField.addEventListener('input', function()
	{
		usersDataList.updateLoadParameters({email: emailSearchField.value});
		usersDataList.load();
	});

	bannedCheckbox.addEventListener('change', function()
	{
		unbannedCheckbox.checked = false;
		updateBanFilter();
	});

	unbannedCheckbox.addEventListener('change', function()
	{
		bannedCheckbox.checked = false;
		updateBanFilter();
	});

	function updateBanFilter()
	{
		if(bannedCheckbox.checked)
		{
			usersDataList.updateLoadParameters({ban: 1});
		}
		else if(unbannedCheckbox.checked)
		{
			usersDataList.updateLoadParameters({ban: 2});
		}
		else
		{
			usersDataList.updateLoadParameters({ban: 0});
		}

		usersDataList.load();
	}
});

/**
 * Создает элемент DOM таблички с данными пользователя.
 * @param {object} userData Данные пользователя.
 * @returns {Element} Элемент DOM таблички с данными пользователя.
 */
function createUserPlank(userData)
{
	var ban = userData.ban | 0;

	var plank        = document.createElement('section'),
		avatar       = document.createElement('img'),
		dataDiv      = document.createElement('div'),
		infoDiv      = document.createElement('div'),
		loginSpan    = document.createElement('span'),
		emailLink    = document.createElement('a'),
		buttonsPanel = document.createElement('div'),
		deleteImg    = document.createElement('img'),
		editImg      = document.createElement('img'),
		banImg       = document.createElement('img'),
		noBanImg     = document.createElement('img'),
		deleteButton = document.createElement('button'),
		editButton   = document.createElement('button'),
		banButton    = createToggleButton({
			activeContent: banImg,
			unactiveContent: noBanImg,
			activated: ban,
			onTurnOn: function()
			{
				sendRequest('POST', 'index.php?op=cp.us.banUser', {id: userData.id}, function(result)
				{
					if(result !== 0)
					{
						banButton.onTurnOff(false);
						return;
					}

					(new Notification({
						content: 'Пользователь ' + userData.login + ' заблокирован!',
						showTime: 3000,
						styleClass: 'warning'
					})).show();

					addBanSpan();
				});
			},
			onTurnOff: function()
			{
				sendRequest('POST', 'index.php?op=cp.us.banUser', {id: userData.id, unban: 1}, function(result)
				{
					if(result !== 0)
					{
						banButton.onTurnOn(false);
						return;
					}

					(new Notification({
						content: 'Пользователь ' + userData.login + ' разблокирован!',
						showTime: 3000,
						styleClass: 'success'
					})).show();

					removeBanSpan();
				});
			}
		}),
		banSpan;

	plank.className        = 'plank';
	dataDiv.className      = 'description';
	avatar.className       = 'avatar';
	infoDiv.className      = 'info';
	loginSpan.className    = 'login';
	emailLink.className    = 'email';
	buttonsPanel.className = 'buttons-panel';
	deleteButton.className = 'icon-button';
	editButton.className   = 'icon-button';
	banButton.className    = 'icon-button';

	deleteImg.src = glImagesPath + 'delete_cleaned.svg';
	editImg.src   = glImagesPath + 'edit_cleaned.svg';
	banImg.src    = glImagesPath + 'banana.svg';
	noBanImg.src  = glImagesPath + 'no_banana.svg';

	if(userData.avatar_path)
	{
		avatar.src = userData.avatar_path;
		createTooltip(avatar, 'Аватар пользователя');
	}
	else
	{
		avatar.src = glImagesPath + 'user_cleaned.svg';
		avatar.className += ' no-avatar-pad';
		createTooltip(avatar, 'Пользователь не загрузил аватар');
	}

	loginSpan.appendChild(document.createTextNode(userData.login));

	if(userData.full | 0)
	{
		var fullAccessSpan  = document.createElement('span');
		fullAccessSpan.className = 'status full-access small exp';
		fullAccessSpan.innerHTML = 'Полный&nbsp;доступ';
		loginSpan.appendChild(fullAccessSpan);
	}

	if(ban)
	{
		addBanSpan();
	}

	emailLink.appendChild(document.createTextNode(userData.email));
	emailLink.href = 'mailto:' + userData.email;

	infoDiv.appendChild(loginSpan);
	infoDiv.appendChild(document.createElement('br'));
	infoDiv.appendChild(emailLink);

	if(userData.first_name && userData.second_name)
	{
		var nameSpan       = document.createElement('span');
		nameSpan.className = 'name';
		nameSpan.innerHTML = userData.first_name + '&nbsp;' + userData.second_name;

		infoDiv.appendChild(document.createElement('br'));
		infoDiv.appendChild(nameSpan);
	}

	infoDiv.appendChild(document.createElement('br'));
	infoDiv.appendChild(document.createTextNode('Группа: ' + userData.group_name));

	dataDiv.appendChild(avatar);
	dataDiv.appendChild(infoDiv);

	deleteButton.appendChild(deleteImg);
	editButton.appendChild(editImg);
	banButton.appendChild(banImg);
	banButton.appendChild(noBanImg);

	buttonsPanel.appendChild(deleteButton);
	buttonsPanel.appendChild(banButton);
	buttonsPanel.appendChild(editButton);

	plank.appendChild(dataDiv);
	plank.appendChild(document.createElement('hr'));
	plank.appendChild(buttonsPanel);

	//Кнопка удаления пользователя
	deleteButton.addEventListener('click', function(event)
	{
		event.stopPropagation();
		event.preventDefault();

		(new Popup({
			title: 'Удаление пользователя',
			content: 'Вы действительно хотите удалить пользователя ' + userData.login + '? Операцию невозможно будет отменить!',
			overlay: true,
			closeCross: true,
			buttons: [{
				text: 'Да', action: function()
				{
					sendRequest('POST', 'index.php?op=cp.us.deleteUser', {id: userData.id}, function(result)
					{
						if(result !== 0)
						{ return; }

						(new Notification({
							content: 'Пользователь ' + userData.login + ' успешно удален!',
							showTime: 3000,
							styleClass: 'success'
						})).show();

						usersDataList.load();
					});
				}
			}, {text: 'Нет'}]
		})).show();
	});

	//Кнопка редактирования пользователя
	editButton.addEventListener('click', function(event)
	{
		event.stopPropagation();
		event.preventDefault();

		var userEditForm = new FormBuilder([
			{
				name: 'id',
				value: userData.id,
				type: 'hidden'
			},
			{
				name: 'login',
				pattern: loginRegexpr,
				label: 'Логин',
				boolAttr: ['required'],
				value: userData.login
			},
			{
				tag: 'combo-box',
				name: 'group_id',
				value: userData.group_name,
				label: 'Группа пользователей',
				data: new DataList({opUrl: 'index.php?op=cp.us.groups.usersGroupsList'}),
				dataElementProperty: 'name',
				dataReturnProperty: 'id',
				boolAttr: ['required']
			},
			{
				name: 'email',
				pattern: emailRegexpr,
				type: 'email',
				label: 'Email',
				boolAttr: ['required'],
				value: userData.email
			},
			{
				name: 'first_name',
				pattern: nameRegexpr,
				label: 'Имя',
				value: userData.first_name
			},
			{
				name: 'second_name',
				pattern: nameRegexpr,
				label: 'Фамилия',
				value: userData.second_name
			}
		], 'POST', 'index.php?op=cp.us.editUser').build('user-edit-form');

		(new Popup({
			title: 'Редактирование пользователя',
			content: userEditForm,
			overlay: true,
			closeCross: true,
			buttons: [{text: 'Отмена'}, {
				text: 'Редактировать',
				submitFormId: 'user-edit-form',
				responseHandler: userEditRespHandler
			}]
		})).show();

		function userEditRespHandler(result)
		{
			if(result !== 0)
			{
				return;
			}

			(new Notification({
				content: 'Пользователь успешно отредактирован.',
				showTime: 1500,
				styleClass: 'success'
			})).show();

			usersDataList.load();
		}
	});

	createTooltip(deleteButton, 'Удалить пользователя', 'bottom', 15);
	createTooltip(editButton, 'Редактировать пользователя', 'bottom', 15);

	function addBanSpan()
	{
		banSpan           = document.createElement('span');
		banSpan.className = 'status ban small exp';
		banSpan.appendChild(document.createTextNode('Забанен'));
		loginSpan.appendChild(banSpan);
	}

	function removeBanSpan()
	{
		loginSpan.removeChild(banSpan);
	}

	return plank;
}