/**
 * Управление списком демонов.
 * @module daemons_control
 * @author rayleigh <rayleigh@protonmail.ch>
 */

function createDaemonObject(daemon, contentPanel)
{
	var daemonPlank     = document.createElement('section'),
		nameDiv         = document.createElement('div'),
		statusObj       = document.createElement('div'),
		descriptionDiv  = document.createElement('div'),
		buttonsPanelDiv = document.createElement('div'),
		nameSpan        = document.createElement('span'),
		deleteButton    = document.createElement('button'),
		statusButton    = document.createElement('button'),
		editButton      = document.createElement('button');

	var deleteButtonImg = document.createElement('img'),
		editButtonImg   = document.createElement('img'),
		startButtonImg  = document.createElement('img'),
		stopButtonImg   = document.createElement('img');

	deleteButtonImg.src = glImagesPath + '/delete_cleaned.svg';
	editButtonImg.src   = glImagesPath + '/edit_cleaned.svg';
	startButtonImg.src  = glImagesPath + '/start_cleaned.svg';
	stopButtonImg.src   = glImagesPath + '/stop_cleaned.svg';

	daemonPlank.className     = 'plank';
	nameDiv.className         = 'name';
	descriptionDiv.className  = 'description';
	buttonsPanelDiv.className = 'buttons-panel';
	deleteButton.className    = 'icon-button';
	statusButton.className    = 'icon-button';
	editButton.className      = 'icon-button';

	//Функция обновления статуса демона на табличке
	daemonPlank.updateStatus = function()
	{
		statusObj.className = 'status';

		statusButton.removeChild(statusButton.firstChild);

		if(daemon.status == 0)
		{
			statusObj.className += ' status-off-color';
			statusButton.appendChild(startButtonImg);
		}
		else
		{
			if(daemon.need_stop == 1)
			{
				statusObj.className += ' status-wait-off-color';
				statusButton.disabled = true;
			}
			else
			{
				statusButton.disabled = false;
				statusObj.className += ' status-on-color';
			}

			statusButton.appendChild(stopButtonImg);
		}
	};

	function updateDescription()
	{
		while(descriptionDiv.children.length > 0)
		{
			descriptionDiv.removeChild(descriptionDiv.children[0]);
		}

		descriptionDiv.appendChild(document.createTextNode(daemon.description));
		descriptionDiv.appendChild(document.createElement('br'));
		descriptionDiv.appendChild(document.createTextNode('Время ожидания: ' + daemon.sleep_time + ' сек.'));
		descriptionDiv.appendChild(document.createElement('br'));
		descriptionDiv.appendChild(document.createTextNode('Последний запуск: ' + (daemon.last_start == null ? 'неизвестно' : daemon.last_start)));
	}

	statusButton.appendChild(document.createTextNode(''));

	//Обновляем статус
	daemonPlank.updateStatus();

	nameSpan.appendChild(document.createTextNode(daemon.displ_name + ' (' + daemon.name + ')'));

	nameDiv.appendChild(statusObj);
	nameDiv.appendChild(nameSpan);

	updateDescription();

	deleteButton.appendChild(deleteButtonImg);
	editButton.appendChild(editButtonImg);

	buttonsPanelDiv.appendChild(deleteButton);
	buttonsPanelDiv.appendChild(statusButton);
	buttonsPanelDiv.appendChild(editButton);

	daemonPlank.appendChild(nameDiv);
	daemonPlank.appendChild(document.createElement('hr'));
	daemonPlank.appendChild(descriptionDiv);
	daemonPlank.appendChild(document.createElement('hr'));
	daemonPlank.appendChild(buttonsPanelDiv);

	//Кнопка удаления демона
	deleteButton.addEventListener('click', function()
	{
		function deleteDaemon()
		{
			sendRequest('POST', 'index.php?op=cp.daemon.deleteDaemon', 'id=' + daemon.id, function(data)
			{
				(new Popup({
					title: 'Удаление демона',
					content: 'Демон успешно удален!',
					overlay: true,
					buttons: [{text: 'Ок'}]
				})).show();

				//Удаляем табличку удалённого демона с панели
				contentPanel.removeChild(daemonPlank);
			});
		}

		(new Popup({
			title: 'Удаление демона',
			content: 'Вы действительно хотите удалить демон?',
			overlay: true,
			buttons: [{text: 'Нет'}, {text: 'Да', action: deleteDaemon}]
		})).show();
	});

	//Кнопка редактирования демона
	editButton.addEventListener('click', function()
	{
		var editForm = new FormBuilder(
			[
				{
					type: 'hidden',
					name: 'id',
					value: daemon.id
				},
				{
					label: 'Название класса',
					name: 'name',
					pattern: class_name_pattern,
					title: 'Буквы латинского алфавита',
					boolAttr: ['autofocus', 'required'],
					value: daemon.name
				},
				{
					label: 'Отображаемое название',
					name: 'displ_name',
					pattern: displ_name_pattern,
					title: 'Буквы латинского и кириллического алфавита, а также символы - и _',
					boolAttr: ['required'],
					value: daemon.displ_name
				},
				{
					label: 'Описание',
					tag: 'textarea',
					name: 'description',
					boolAttr: ['required'],
					value: daemon.description
				},
				{
					label: 'Время ожидания',
					type: 'number',
					name: 'sleep_time',
					min: 0,
					step: 1,
					boolAttr: ['required'],
					value: daemon.sleep_time
				}
			],
			'POST', 'index.php?op=cp.daemon.editDaemon'
		).build('edit-daemon-form');

		function editResultHandler(data)
		{
			if(data !== 0)
			{ return; }

			var editFormElements = editForm.elements;

			daemon.name       = editFormElements.name.value;
			daemon.displ_name = editFormElements.displ_name.value;
			daemon.sleep_time = editFormElements.sleep_time.value;

			updateDescription();

			(new Popup({
				title: 'Редактирование демона',
				content: 'Демон успешно отредактирован!',
				overlay: true,
				buttons: [{text: 'Ок'}]
			})).show();
		}

		(new Popup({
			title: 'Редактирование демона',
			content: editForm,
			overlay: true,
			buttons: [{text: 'Отмена'}, {
				text: 'Изменить',
				hide: false,
				submitFormId: 'edit-daemon-form',
				responseHandler: editResultHandler
			}]
		})).show();
	});

	//Кнопка измнения статуса демона
	statusButton.addEventListener('click', function()
	{
		if(daemon.status == 0)
		{
			sendRequest('POST', 'index.php?op=cp.daemon.startDaemon', 'id=' + daemon.id, function(data)
			{
				daemon.status    = 1;
				daemon.need_stop = 0;

				daemonPlank.updateStatus();

				(new Popup({
					title: 'Запуск демона',
					content: 'Демон успешно запущен!',
					overlay: true,
					buttons: [{text: 'Ок'}]
				})).show();
			});
		}
		else
		{
			sendRequest('POST', 'index.php?op=cp.daemon.stopDaemon', 'id=' + daemon.id, function(data)
			{
				daemon.need_stop = 1;

				daemonPlank.updateStatus();

				(new Popup({
					title: 'Остановка демона',
					content: 'Демон будет остановлен после окончания текущей задачи!',
					overlay: true,
					buttons: [{text: 'Ок'}]
				})).show();
			});
		}
	});

	createTooltip(deleteButton, 'Удалить демон', 'bottom', 20);
	createTooltip(editButton, 'Редактировать демон', 'bottom', 20);
	createTooltip(startButtonImg, 'Запустить демон', 'bottom', 20);
	createTooltip(stopButtonImg, 'Остановить демон', 'bottom', 20);

	//Запоминаем объект для быстрого доступа
	daemon['plank'] = daemonPlank;

	return daemonPlank;
}

window.addEventListener('load', function()
{
	var contentPanel     = document.getElementsByClassName('blocks-container')[0],
		searchField      = document.getElementById('name-search'),
		enabledCheckbox  = document.getElementById('show-enabled'),
		disabledCheckbox = document.getElementById('show-disabled'),
		workingCheckbox  = document.getElementById('show-working'),
		waitingCheckbox  = document.getElementById('show-waiting');

	var daemonsList = new DataList({
		opUrl: 'index.php?op=cp.daemon.daemonsList',
		onLoad: function(data)
		{
			document.getElementById('load-time').innerHTML = '(' + data.time + ')';
			updateDaemonPlanks();
		}
	});

	//Обновляет таблички демонов в соответствии с списком демонов
	function updateDaemonPlanks()
	{
		//Удаляем все таблички демонов
		var daemons = contentPanel.getElementsByClassName('plank');
		while(daemons.length > 0)
		{
			contentPanel.killChild(daemons[0], true);
		}

		//Добавляем таблички демонов
		daemonsList.list.forEach(function(daemon)
		{
			if(stringContains(searchField.value.toLowerCase(), daemon.displ_name.toLowerCase())
				&& (!enabledCheckbox.checked || daemon.status > 0)
				&& (!disabledCheckbox.checked || daemon.status == 0)
				&& (!workingCheckbox.checked || daemon.status == 2)
				&& (!waitingCheckbox.checked || daemon.status == 1))
			{
				var daemonPlank = createDaemonObject(daemon, contentPanel);
				contentPanel.appendChild(daemonPlank);
			}
		});
	}

	daemonsList.load();
	daemonsList.liveReload(10000);

	//Кнопка добавления демона
	document.getElementById('add-daemon').addEventListener('click', function()
	{
		function respHandler(data)
		{
			var daemonFormElements = document.forms['add-daemon-form'].elements,
				daemonPlank        = createDaemonObject({
					id: data.id,
					name: daemonFormElements['name'].value,
					status: 0,
					displ_name: daemonFormElements['displ_name'].value,
					description: daemonFormElements['description'].value,
					sleep_time: daemonFormElements['sleep_time'].value
				}, contentPanel);

			//Добавляем табличку демона
			contentPanel.appendChild(daemonPlank);

			//Сообраем о успешном выполнении операции
			(new Popup({
				title: 'Добавление демона',
				content: 'Демон был успешно добавлен! Ему присвоен идентификатор ' + data.id + '.',
				overlay: true,
				buttons: [{text: 'Ок'}]
			})).show();
		}

		//Создаем форму добавления
		var formBuilder = new FormBuilder(
			[
				{
					label: 'Название класса',
					name: 'name',
					pattern: class_name_pattern,
					title: 'Буквы латинского алфавита',
					boolAttr: ['autofocus', 'required']
				},
				{
					label: 'Отображаемое название',
					name: 'displ_name',
					pattern: displ_name_pattern,
					title: 'Буквы латинского и кириллического алфавита, а также символы - и _',
					boolAttr: ['required']
				},
				{
					label: 'Описание',
					tag: 'textarea',
					name: 'description',
					boolAttr: ['required']
				},
				{
					label: 'Время ожидания',
					type: 'number',
					name: 'sleep_time',
					min: 0,
					step: 1,
					value: 1,
					boolAttr: ['required']
				},
				{
					type: 'checkbox',
					label: 'Запущен по умолчанию',
					name: 'launch'
				}
			],
			'POST', 'index.php?op=cp.daemon.addDaemon'
		);

		//Окно с формой добавления
		(new Popup({
			title: 'Добавление нового демона',
			content: formBuilder.build('add-daemon-form'),
			overlay: true,
			buttons: [{text: 'Отмена'}, {
				text: 'Добавить',
				hide: false,
				submitFormId: 'add-daemon-form',
				responseHandler: respHandler
			}]
		})).show();
	});

	//Кнопка "Остановить все"
	document.getElementById('stop-all').addEventListener('click', function()
	{
		sendRequest('POST', 'index.php?op=cp.daemon.stopDaemon', 'all=1', function(data)
		{
			if(data === 0)
			{
				//Красим кружочки в желтый цвет всем демонам, которые будут отключены
				if(daemonsList != null)
				{
					daemonsList.forEach(function(daemon)
					{
						if(daemon.status != 0 && daemon.need_stop != 1)
						{
							daemon.need_stop = 1;
							daemon.plank.updateStatus();
						}
					});
				}

				(new Popup({
					title: 'Остановка демонов',
					content: 'Все демоны будут остановлены через некоторое время.',
					overlay: true,
					buttons: [{'text': 'Понял'}]
				})).show();
			}
		});
	});

	//Кнопка сброса фильтра
	document.getElementById('clean-filter').addEventListener('click', function()
	{
		searchField.value        = '';
		enabledCheckbox.checked  = false;
		disabledCheckbox.checked = false;
		workingCheckbox.checked  = false;
		waitingCheckbox.checked  = false;

		updateDaemonPlanks();
	});

	searchField.addEventListener('input', updateDaemonPlanks);

	enabledCheckbox.addEventListener('change', function()
	{
		disabledCheckbox.checked = false;
		updateDaemonPlanks();
	});

	disabledCheckbox.addEventListener('change', function()
	{
		enabledCheckbox.checked = false;
		updateDaemonPlanks();
	});

	workingCheckbox.addEventListener('change', function()
	{
		waitingCheckbox.checked = false;
		updateDaemonPlanks();
	});

	waitingCheckbox.addEventListener('change', function()
	{
		workingCheckbox.checked = false;
		updateDaemonPlanks();
	});
});