var contentPanel;

window.addEventListener('load', function()
{
	contentPanel = document.getElementsByClassName('blocks-container')[0];

	var listUpdateInterval = 10000,
		searchField        = document.getElementById('name-search'),
		categoriesDataList = new DataList({
			opUrl: 'index.php?op=eslist.categoriesList',
			onLoad: function(data)
			{
				document.getElementById('load-time').innerHTML = '(' + data.time + ')';
				updatePlanks();
			}
		}),
		listSelection      = new ListSelection({
			parent: contentPanel,
			elementsClass: 'plank',
			onFirstSelect: function()
			{
				//Прекращаем обновление списка категорий когда выделен хотя бы один элемент
				categoriesDataList.stopLiveReload();
			},
			onAllUnselect: function()
			{
				//Включаем обратно обновление списка при отмене выделения всех выделенных элементов
				categoriesDataList.liveReload(listUpdateInterval);
			}
		});

	/**
	 * Функция обновления списка табличек демонов
	 */
	function updatePlanks()
	{
		var oldPlanks = contentPanel.getElementsByClassName('plank');

		while(oldPlanks.length > 0)
		{
			contentPanel.killChild(oldPlanks[0], true);
		}

		categoriesDataList.list.forEach(function(category)
		{
			if(stringContains(searchField.value.toLowerCase(), category.name.toLowerCase()))
			{
				var categoryPlank = createCategoryPlank(category);
				contentPanel.appendChild(categoryPlank);
			}
		});

		listSelection.updateSelection();
	}

	categoriesDataList.load();							//Начальная загрузка списка
	//categoriesDataList.liveReload(listUpdateInterval);	//Автообновление раз в 10 секунд

	//Обновление списка табличек демонов при вводе данных в поле поиска по имени
	searchField.addEventListener('input', updatePlanks);

	//Кнопка добавления категории
	document.getElementById('add-category').addEventListener('click', function()
	{
		var addCategoryForm = new FormBuilder(
			[
				{
					label: 'Название',
					name: 'name',
					title: 'Буквы кириллического алфавита',
					boolAttr: ['autofocus', 'required']
				}
			],
			'POST', 'index.php?op=cp.category.addCategory').build('category-add-form');

		var categoryAddWindow = new Popup({
			title: 'Добавление категории',
			content: addCategoryForm,
			overlay: true,
			buttons: [{text: 'Отмена'}, {
				text: 'Добавить',
				hide: false,
				submitFormId: 'category-add-form',
				responseHandler: addCategoryRespHandler
			}]
		});

		categoryAddWindow.show();

		function addCategoryRespHandler(id)
		{
			var categoryName  = addCategoryForm.elements.name.value,
				categoryPlank = createCategoryPlank({
					id: id,
					name: categoryName
				});

			//Добавляем табличку категории на панель
			contentPanel.appendChild(categoryPlank);

			//Скрываем окно добавления категории
			categoryAddWindow.hide();

			//Уведомляем пользователя, что категория успешно добавлена
			(new Notification({
				content: 'Категория ' + categoryName + ' успешно добавлена! id: ' + id + '.',
				styleClass: 'success',
				showTime: 3000
			})).show();
		}
	});

	//Кнопка удаления выбранных категорий
	document.getElementById('delete-selected').addEventListener('click', function()
	{
		//Идентификаторы записей на удаление
		var ids = [];

		listSelection.selected.forEach(function(cat)
		{
			ids.push(cat.dataset.id);
		});

		(new Popup({
			title: 'Удаление нескольких категорий',
			content: 'Вы уверены что хотите удалить выбранные категории? Вы выбрали ' + ids.length + ' шт.',
			closeCross: true,
			overlay: true,
			buttons: [{text: 'Нет'}, {
				text: 'Да', action: function()
				{
					sendRequest('POST', 'index.php?op=cp.category.deleteCategory', {ids: ids}, function(data)
					{
						if(data === 0)
						{
							(new Notification({
								content: 'Выбранные категории удалены.',
								styleClass: 'success',
								showTime: 2000
							})).show();

							listSelection.selected.forEach(function(cat)
							{
								contentPanel.removeChild(cat);
							});

							listSelection.clearSelection();
						}
					});
				}
			}]
		})).show();
	});
});

/**
 * Создает табличку категории
 * @param  {object} category Объект с данными категории
 */
function createCategoryPlank(category)
{
	var plank        = document.createElement('section'),
		nameSpan     = document.createElement('span'),
		controlsDiv  = document.createElement('div'),
		buttonsDiv   = document.createElement('div'),
		deleteButton = document.createElement('button'),
		editButton   = document.createElement('button'),
		deleteImg    = document.createElement('img'),
		editImg      = document.createElement('img');

	plank.className        = 'plank';
	controlsDiv.className  = 'controls';
	buttonsDiv.className   = 'buttons';
	deleteButton.className = 'icon-button';
	editButton.className   = 'icon-button';

	deleteImg.src = glImagesPath + '/delete_cleaned.svg';
	editImg.src   = glImagesPath + '/edit_cleaned.svg';

	deleteButton.appendChild(deleteImg);
	editButton.appendChild(editImg);

	controlsDiv.appendChild(deleteButton);
	controlsDiv.appendChild(editButton);

	nameSpan.appendChild(document.createTextNode(category.name));

	plank.appendChild(nameSpan);
	plank.appendChild(controlsDiv);

	//Кнопка удаления категории
	deleteButton.addEventListener('click', function(event)
	{
		//Чтобы не выбрать категорию перед удалением
		event.stopPropagation();

		(new Popup({
			title: 'Удаление категории',
			content: 'Вы действительно хотите удалить категорию ' + category.name + '?',
			closeCross: 'true',
			overlay: true,
			buttons: [{text: 'Нет'}, {
				text: 'Да', action: function()
				{
					sendRequest('POST', 'index.php?op=cp.category.deleteCategory', {id: category.id}, function(result)
					{
						if(result === 0)
						{
							contentPanel.removeChild(plank);

							(new Notification({
								content: 'Категория ' + category.name + ' успешно удалена!',
								showTime: 2500,
								styleClass: 'success'
							})).show();
						}
					});
				}
			}]
		})).show();
	});

	//Кнопка редактирования категории
	editButton.addEventListener('click', function(event)
	{
		//Чтобы не выбрать категорию перед редактированием
		event.stopPropagation();

		//Форма редактирования категории
		var editCategoryForm = new FormBuilder(
			[
				{
					label: 'Название',
					name: 'name',
					value: category.name,
					title: 'Буквы кириллического алфавита',
					boolAttr: ['autofocus', 'required']
				},
				{
					name: 'id',
					type: 'hidden',
					value: category.id
				}
			],
			'POST', 'index.php?op=cp.category.editCategory').build('category-edit-form');

		var categoryEditWindow = new Popup({
			title: 'Редактирование категории',
			content: editCategoryForm,
			overlay: true,
			buttons: [{text: 'Отмена'}, {
				text: 'Изменить',
				hide: false,
				submitFormId: 'category-edit-form',
				responseHandler: editCategoryRespHandler
			}]
		});

		//Отображаем окно редактирования
		categoryEditWindow.show();

		//Обработчик ответа редактирования категории
		function editCategoryRespHandler(data)
		{
			if(data === 0)
			{
				//Меняем имя категории на табличке
				nameSpan.firstChild.nodeValue = editCategoryForm.elements.name.value;

				//Скрываем окно редактирования категории
				categoryEditWindow.hide();

				//Уведомляем о успешном редактировании категории
				(new Notification({
					content: 'Категория успешно отредактирована!',
					styleClass: 'success'
				})).show();
			}
		}
	});

	createTooltip(deleteButton, 'Удалить категорию', 'bottom', 20);
	createTooltip(editButton, 'Редактировать категорию', 'bottom', 20);

	plank.setAttribute('data-id', category.id);

	return plank;
}