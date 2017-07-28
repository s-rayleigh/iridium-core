var usersGroupsDataList;

window.addEventListener('load', function()
{
	var listContainer   = document.getElementById('users-groups-list'),
		nameSearchField = document.getElementById('name-search');

	usersGroupsDataList = new DataList({
		opUrl: 'index.php?op=cp.us.groups.usersGroupsList',
		usePageNavigation: true,
		onLoad: function(result)
		{
			document.getElementById('page').innerHTML = (result.page + 1) + '/' + result.pages;

			var curPlanks = listContainer.getElementsByClassName('plank');
			while(curPlanks.length > 0)
			{
				listContainer.removeChild(curPlanks[0]);
			}

			if(result.list)
			{
				result.list.forEach(function(group)
				{
					listContainer.appendChild(createUsersGroupPlank(group));
				});
			}
		}
	});

	usersGroupsDataList.load();

	document.getElementById('prev-page').addEventListener('click', function()
	{
		usersGroupsDataList.prevPage();
		usersGroupsDataList.load();
	});

	document.getElementById('next-page').addEventListener('click', function()
	{
		usersGroupsDataList.nextPage();
		usersGroupsDataList.load();
	});

	nameSearchField.addEventListener('input', function()
	{
		usersGroupsDataList.updateLoadParameters({name: nameSearchField.value});
		usersGroupsDataList.load();
	});

	document.getElementById('add-button').addEventListener('click', function()
	{
		var accessTypesCheckboxes = createAccessTypesCheckboxes(),
			formElements          = [
				{
					name: 'name',
					label: 'Название',
					boolAttr: ['required', 'autofocus']
				},
				{tag: 'hr'},
				{
					type: 'checkbox',
					label: 'Полный доступ',
					name: 'full',
					value: 1
				},
				accessTypesCheckboxes
			];

		var groupAddForm = new FormBuilder(formElements, 'POST', 'index.php?op=cp.us.groups.addUsersGroup').build('group-add-form');

		//Оключаем остальные галочки при включении галочки полного доступа
		groupAddForm.elements['full'].addEventListener('change', function()
		{
			if(!groupAddForm.elements['full'].checked)
			{
				return;
			}

			var checkboxes = accessTypesCheckboxes.getElementsByTagName('input');
			for(var i = 0; i < checkboxes.length; i++)
			{
				checkboxes[i].checked = false;
			}
		});

		(new Popup({
			title: 'Добавление группы пользователей',
			content: groupAddForm,
			overlay: true,
			closeCross: true,
			buttons: [{text: 'Отмена'}, {
				text: 'Добавить',
				submitFormId: 'group-add-form',
				responseHandler: usersGroupAddRespHandler
			}]
		})).show();

		function usersGroupAddRespHandler(result)
		{
			if(result !== 0)
			{
				return;
			}

			(new Notification({
				content: 'Группа пользователей успешно добавлена.',
				styleClass: 'success'
			})).show();

			usersGroupsDataList.load();
		}
	});
});

/**
 * Создает объект таблички группы пользователей.
 * @param {object} groupData Данные группы.
 * @returns {Element} Объект таблички группы пользователей.
 */
function createUsersGroupPlank(groupData)
{
	var plank        = document.createElement('section'),
		nameSpan     = document.createElement('span'),
		infoSpan     = document.createElement('span'),
		buttonsPanel = document.createElement('div'),
		deleteButton = document.createElement('button'),
		editButton   = document.createElement('button'),
		deleteImg    = document.createElement('img'),
		editImg      = document.createElement('img');

	plank.className        = 'plank';
	nameSpan.className     = 'name';
	buttonsPanel.className = 'buttons-panel';
	deleteButton.className = 'icon-button';
	editButton.className   = 'icon-button';

	nameSpan.appendChild(document.createTextNode(groupData.name));

	infoSpan.innerHTML = 'Редактирование разрешено: ' + (groupData.editable | 0 ? 'Да' : 'Нет')
		+ '<br>Полный доступ: ' + (groupData.full | 0 ? 'Да' : 'Нет');

	deleteImg.src = glImagesPath + 'delete_cleaned.svg';
	editImg.src   = glImagesPath + 'edit_cleaned.svg';

	editButton.appendChild(editImg);
	deleteButton.appendChild(deleteImg);
	buttonsPanel.appendChild(deleteButton);
	buttonsPanel.appendChild(editButton);

	plank.appendChild(nameSpan);
	plank.appendChild(document.createElement('hr'));
	plank.appendChild(infoSpan);
	plank.appendChild(document.createElement('hr'));
	plank.appendChild(buttonsPanel);

	//Кнопка удаления
	deleteButton.addEventListener('click', function()
	{
		(new Popup({
			title: 'Удаление группы пользователей',
			content: 'Вы действительно хотите удалить группу пользователей "' + groupData.name + '"?',
			overlay: true,
			closeCross: true,
			buttons: [{text: 'Нет'}, {
				text: 'Да', action: function()
				{
					sendRequest('POST', 'index.php?op=cp.us.groups.deleteUsersGroup', {id: groupData.id}, function(result)
					{
						if(result === 0)
						{
							(new Notification({
								content: 'Группа пользователей "' + groupData.name + '" успешно удалена.',
								styleClass: 'success'
							})).show();

							usersGroupsDataList.load();
						}
					});
				}
			}]
		})).show();
	});

	//Кнопка редактирования
	editButton.addEventListener('click', function()
	{
		var accessTypesCheckboxes = createAccessTypesCheckboxes(groupData.rights),
			formElements          = [
				{
					type: 'hidden',
					name: 'id',
					value: groupData.id
				},
				{
					name: 'name',
					label: 'Название',
					boolAttr: ['required', 'autofocus'],
					value: groupData.name
				},
				{tag: 'hr'},
				{
					type: 'checkbox',
					label: 'Полный доступ',
					name: 'full',
					value: 1,
					checked: Boolean(Number(groupData.full))
				},
				accessTypesCheckboxes
			],
			groupEditForm         = new FormBuilder(formElements, 'POST', 'index.php?op=cp.us.groups.editUsersGroup').build('group-edit-form');

		//Оключаем остальные галочки при включении галочки полного доступа
		groupEditForm.elements['full'].addEventListener('change', function()
		{
			if(!groupEditForm.elements['full'].checked)
			{
				return;
			}

			var checkboxes = accessTypesCheckboxes.getElementsByTagName('input');
			for(var i = 0; i < checkboxes.length; i++)
			{
				checkboxes[i].checked = false;
			}
		});

		(new Popup({
			title: 'Редактирование группы пользователей',
			content: groupEditForm,
			overlay: true,
			closeCross: true,
			buttons: [{text: 'Отмена'}, {
				text: 'Сохранить',
				submitFormId: 'group-edit-form',
				responseHandler: groupEditRespHandler
			}]
		})).show();

		function groupEditRespHandler()
		{
			(new Notification({
				content: 'Группа пользователей успешно отредактирована.',
				styleClass: 'success'
			})).show();

			usersGroupsDataList.load();
		}
	});

	createTooltip(deleteButton, 'Удалить группу', 'bottom', 20);
	createTooltip(editButton, 'Редактировать группу', 'bottom', 20);

	return plank;
}

/**
 * Создает элемент с чекбоксами прав доступа, разбитыми на группы.
 * @param {Array} [checkedTypes] Отмеченные типы доступа.
 * @returns {HTMLElement} Элемент с чекбоксами прав доступа.
 */
function createAccessTypesCheckboxes(checkedTypes)
{
	var parent     = document.createElement('div'),
		checkboxes = [], //Соответствие между чекбоксами и их id
		req        = {}; //Зависимости между чекбоксами

	parent.className = 'access-types-groups';

	function getElementId(id)
	{
		return 'access-type-' + id;
	}

	//noinspection JSUnresolvedVariable
	accessTypes.forEach(function(group)
	{
		var groupParent = document.createElement('div'),
			groupName   = document.createElement('h3');

		groupParent.className = 'access-types-group';

		groupName.appendChild(document.createTextNode(group.group_name));
		groupParent.appendChild(groupName);

		group.types.forEach(function(type)
		{
			var typeLabel    = document.createElement('label'),
				typeCheckbox = document.createElement('input'),
				id           = getElementId(type.id);

			typeLabel.appendChild(document.createTextNode(type.name));
			typeLabel.htmlFor = id;

			typeCheckbox.id    = id;
			typeCheckbox.type  = 'checkbox';
			typeCheckbox.name  = 'rights[]';
			typeCheckbox.value = type.id;

			if(checkedTypes && checkedTypes.includes(type.id))
			{
				typeCheckbox.checked = true;
			}

			groupParent.appendChild(typeCheckbox);
			groupParent.appendChild(typeLabel);

			checkboxes[id] = typeCheckbox;

			if(type.reqId)
			{
				if(Array.isArray(type.reqId))
				{
					req[id] = [];

					type.reqId.forEach(function(rid)
					{
						req[id][rid] = getElementId(rid);
					});
				}
				else
				{
					req[id] = getElementId(type.reqId);
				}
			}
		});

		parent.appendChild(groupParent);
	});

	//Устанавливаем зависимости между чекбоксами
	for(var depId in req)
	{
		var reqId = req[depId];

		if(Array.isArray(reqId))
		{
			reqId.forEach(function(rid) { createLink(depId, rid); });
		}
		else
		{
			createLink(depId, reqId);
		}

		/**
		 * Создает зависимость зависимого чекбокса от требуемого.
		 * @param depId id зависимого чекбокса.
		 * @param reqId id требуемого чекбокса.
		 */
		function createLink(depId, reqId)
		{
			var dependentCheckbox = checkboxes[depId],
				requiredCheckbox  = checkboxes[reqId];
			dependentCheckbox.addEventListener('change', createDepLink(dependentCheckbox, requiredCheckbox));
			requiredCheckbox.addEventListener('change', createReqLink(dependentCheckbox, requiredCheckbox));
		}

		/**
		 * Создает функцию-связь между зависимым чекбоксом и требуемым.
		 * @param depChk
		 * @param reqChk
		 * @returns {Function}
		 */
		function createDepLink(depChk, reqChk)
		{
			return function()
			{
				if(depChk.checked)
				{
					reqChk.checked = true;
				}
			};
		}

		/**
		 * Создает функцию-зависимость между требуемым чекбоксом и зависимым.
		 * @param depChk
		 * @param reqChk
		 * @returns {Function}
		 */
		function createReqLink(depChk, reqChk)
		{
			return function()
			{
				if(!reqChk.checked)
				{
					depChk.checked = false;
				}
			};
		}
	}

	return parent;
}