/**
 * Модуль управления страницей управления видеороликами
 * @module video_control
 * @author rayleigh <rayleigh@protonmail.ch>
 */

var tabs, //Объект вкладок
	channelsListSelection,
	seasonsListSelection,
	episodesListSelection,
	channelsList   = new DataList({ //Список каналов
		opUrl: 'index.php?op=eslist.channelsList',
		usePageNavigation: true,
		loadParameters: {pg_img: 1}, //Получить также пути к изображениям страниц каналов
		onLoad: function(result)
		{
			var channelPlanksContainer = tabs.children[0].getElementsByClassName('blocks-container')[0],
				channelPlanks          = channelPlanksContainer.getElementsByClassName('plank'),
				pagesSpan              = tabs.children[0].getElementsByClassName('page-control')[0].children[1];

			while(channelPlanks.length > 0)
			{
				channelPlanksContainer.killChild(channelPlanks[0], true);
			}

			//Заполняем поле страниц
			pagesSpan.innerHTML = (result.page + 1) + '/' + result.pages;

			if(result.count)
			{
				result.list.forEach(function(channelData)
				{
					channelPlanksContainer.appendChild(createChannelObject(channelData));
				});

				channelsListSelection.updateSelection();
			}
		}
	}),
	seasonsList    = new DataList({ //Список сезонов
		opUrl: 'index.php?op=eslist.seasonsList',
		usePageNavigation: true,
		onLoad: function(result)
		{
			var seasonPlanksContainer = tabs.children[1].getElementsByClassName('blocks-container')[0],
				seasonPlanks          = seasonPlanksContainer.getElementsByClassName('plank'),
				pagesSpan             = tabs.children[1].getElementsByClassName('page-control')[0].children[1];

			while(seasonPlanks.length > 0)
			{
				seasonPlanksContainer.killChild(seasonPlanks[0]);
			}

			pagesSpan.innerHTML = (result.page + 1) + '/' + result.pages;

			if(result.count)
			{
				result.list.forEach(function(seasonData)
				{
					seasonPlanksContainer.appendChild(createSeasonObject(seasonData));
				});
			}

			seasonsListSelection.updateSelection();
		}
	}),
	videosList     = new DataList({ //Список видеороликов
		opUrl: 'index.php?op=eslist.episodesList',
		usePageNavigation: true,
		loadParameters: {videos: 1, hidden: 1}, //Получить также данные видеороликов для эпизодов и скрытые эпизоды
		onLoad: function(result)
		{
			var episodePlanksContainer = tabs.children[2].getElementsByClassName('blocks-container')[0],
				episodePlanks          = episodePlanksContainer.getElementsByClassName('plank'),
				pagesSpan              = tabs.children[2].getElementsByClassName('page-control')[0].children[1];

			while(episodePlanks.length > 0)
			{
				episodePlanksContainer.killChild(episodePlanks[0]);
			}

			pagesSpan.innerHTML = (result.page + 1) + '/' + result.pages;

			if(result.count)
			{
				result.list.forEach(function(episodeData)
				{
					episodePlanksContainer.appendChild(createEpisodeObject(episodeData));
				});
			}

			episodesListSelection.updateSelection();
		}
	}),
	categoriesList = new DataList({ //Cписок категорий
		opUrl: 'index.php?op=eslist.categoriesList'
	});

window.addEventListener('load', function()
{
	tabs = document.getElementById('hierarchy-tabs');

	channelsListSelection = new ListSelection({
		parent: tabs.children[0],
		elementsClass: 'plank',
		selectByClick: true
	});

	seasonsListSelection = new ListSelection({
		parent: tabs.children[1],
		elementsClass: 'plank',
		selectByClick: true
	});

	episodesListSelection = new ListSelection({
		parent: tabs.children[2],
		elementsClass: 'plank',
		selectByClick: true
	});

	//===============- Каналы -===============

	//Кнопка добавления канала
	document.getElementById('add-channel').addEventListener('click', function()
	{
		var logoMessage      = document.createElement('div'),
			pageImageMessage = document.createElement('div');

		logoMessage.innerHTML = 'Загрузите логотип канала.<br>' + chan_logo_ext.join('/') + ', 1:1, макс. ' + readableSize(chan_logo_max_size);
		pageImageMessage.innerHTML = 'Загрузите изображение страницы канала.<br>' + chan_pg_img_ext.join('/') + ', 4:1, макс. ' + readableSize(chan_pg_img_max_size);

		var channelAddForm = new FormBuilder(
			[
				{
					name: 'name',
					label: 'Название',
					title: 'Буквы латинского или кириллического алфавита, пробел, а также цифры и символ -.',
					pattern: name_regexpr,
					boolAttr: ['autofocus', 'required']
				},
				{
					tag: 'textarea',
					name: 'description',
					label: 'Описание',
					boolAttr: ['required']
				},
				{
					tag: 'combo-box',
					name: 'category_id',
					label: 'Категория',
					data: categoriesList.list,
					dataElementProperty: 'name',
					dataReturnProperty: 'id'
				},
				{
					tag: 'combo-box',
					name: 'editor_id',
					label: 'Редактор',
					data: new DataList({opUrl: 'index.php?op=cp.us.usersList'}),
					dataElementProperty: 'login',
					dataReturnProperty: 'id'
				},
				{
					name: 'logo_id',
					tag: 'file-uploader',
					opUrl: 'index.php?op=cp.uploadChannelLogo',
					deleteOpUrl: 'index.php?op=cp.deleteFile',
					maxSize: chan_logo_max_size,
					allowedExtensions: chan_logo_ext,
					defaultMessage: logoMessage,
					required: true
				},
				{
					name: 'pg_image_id',
					tag: 'file-uploader',
					opUrl: 'index.php?op=cp.uploadChannelPageImage',
					deleteOpUrl: 'index.php?op=cp.deleteFile',
					maxSize: chan_pg_img_max_size,
					allowedExtensions: chan_pg_img_ext,
					defaultMessage: pageImageMessage,
					required: true
				}
			], 'POST', 'index.php?op=cp.channel.addChannel'
		).build('add-channel-form');

		var channelAddPopup = new Popup({
			title: 'Добавление канала',
			content: channelAddForm,
			overlay: true,
			buttons: [{text: 'Отмена'}, {
				text: 'Добавить',
				hide: false,
				submitFormId: 'add-channel-form',
				responseHandler: addChannelRespHandler
			}]
		});

		channelAddPopup.show();

		function addChannelRespHandler()
		{
			channelAddPopup.hide();
			channelsList.load();

			(new Notification({
				content: 'Канал "' + channelAddForm.elements.name.value + '" успешно добавлен!',
				styleClass: 'success',
				showTime: 3000
			})).show();
		}
	});

	//Кнопка удаления выбранных каналов
	document.getElementById('delete-selected-channels').addEventListener('click', function()
	{
		function deleteSelectedChannels()
		{
			//Идентификаторы записей на удаление
			var ids = [];

			channelsListSelection.selected.forEach(function(chan)
			{
				ids.push(chan.dataset.id);
			});

			sendRequest('POST', 'index.php?op=cp.channel.deleteChannel', {ids: ids}, function(result)
			{
				if(result === 0)
				{
					channelsList.load();

					(new Notification({
						content: 'Выбранные каналы успешно удалены.',
						styleClass: 'success',
						showTime: 2000
					})).show();
				}
			});
		}

		(new Popup({
			title: 'Удаление каналов',
			content: 'Вы действительно хотите удалить каналы в кол-ве ' + channelsListSelection.selected.length + ' штук?',
			overlay: true,
			closeCross: true,
			buttons: [{text: 'Нет'}, {text: 'Да', action: deleteSelectedChannels}]
		})).show();
	});

	//Кнопка перехода на предыдущую страницу отображения списка каналов
	document.getElementById('channel-prev-page').addEventListener('click', function()
	{
		channelsList.prevPage();
		channelsList.load();
	});

	//Кнопка перехода на следующую страницу отображения списка каналов
	document.getElementById('channel-next-page').addEventListener('click', function()
	{
		channelsList.nextPage();
		channelsList.load();
	});

	var channelNameSearchField   = document.getElementById('channel-name-search'),
		channelCatSearchCombobox = createCombobox({
			data: categoriesList.list,
			dataElementProperty: 'name',
			dataReturnProperty: 'id'
		});

	channelCatSearchCombobox.placeholder = 'Поиск по категории';

	//Добавляем комбобокс на страницу после поля поиска канала по названию
	channelNameSearchField.insertAfter(channelCatSearchCombobox);

	//Поиск по имени
	channelNameSearchField.addEventListener('input', function()
	{
		channelsList.updateLoadParameters({name: channelNameSearchField.value});
		channelsList.load();
	});

	//Поиск по категории
	channelCatSearchCombobox.addEventListener('input', function()
	{
		var val = channelCatSearchCombobox.value;

		if(val)
		{
			channelsList.updateLoadParameters({catid: val});
		}
		else
		{
			channelsList.updateLoadParameters({catid: -1});
		}

		channelsList.load();
	});

	//=========================================

	//===============- Сезоны -================

	//Кнопка добавления сезона
	document.getElementById('add-season').addEventListener('click', function()
	{
		var addSeasonForm = new FormBuilder([
			{
				name: 'name',
				pattern: name_regexpr,
				label: 'Название',
				title: 'Буквы латинского или кириллического алфавита, пробел, а также цифры и символ -.',
				boolAttr: ['required', 'autofocus']
			},
			{
				name: 'number',
				type: 'number',
				label: 'Номер',
				title: 'Число от 1 до бесконечности.',
				min: 1,
				boolAttr: ['required']
			},
			{
				tag: 'textarea',
				name: 'description',
				label: 'Описание',
				boolAttr: ['required']
			},
			{
				tag: 'combo-box',
				name: 'channel_id',
				label: 'Канал',
				title: 'Название канала',
				data: new DataList({opUrl: 'index.php?op=eslist.channelsList'}),
				dataElementProperty: 'name',
				dataReturnProperty: 'id',
				boolAttr: ['required']
			}
		], 'POST', 'index.php?op=cp.season.addSeason').build('add-season-form');

		var addSeasonPopup = new Popup({
			title: 'Добавление сезона',
			content: addSeasonForm,
			overlay: true,
			buttons: [{text: 'Отмена'}, {
				text: 'Добавить',
				hide: false,
				submitFormId: 'add-season-form',
				responseHandler: addSeasonRespHandler
			}]
		});

		addSeasonPopup.show();

		function addSeasonRespHandler(result)
		{
			if(result === 0)
			{
				seasonsList.load();
				addSeasonPopup.hide();

				(new Notification({
					content: 'Сезон успешно добавлен.',
					styleClass: 'success'
				})).show();
			}
		}
	});

	//Кнопка удаления выбранных сезонов
	document.getElementById('delete-selected-seasons').addEventListener('click', function()
	{
		function deleteSelectedSeasons()
		{
			var ids = [];

			seasonsListSelection.selected.forEach(function(sea)
			{
				ids.push(sea.dataset.id);
			});

			sendRequest('POST', 'index.php?op=cp.season.deleteSeason', {ids: ids}, function(result)
			{
				if(result === 0)
				{
					seasonsList.load();

					(new Notification({
						content: 'Выбранные сезоны успешно удалены.',
						styleClass: 'success'
					})).show();
				}
			});
		}

		(new Popup({
			title: 'Удаление выбранных сезонов',
			content: 'Вы действительно хотите удалить выбранные сезоны в кол-ве ' + seasonsListSelection.selected.length + ' шт?',
			overlay: true,
			closeCross: true,
			buttons: [{text: 'Нет'}, {text: 'Да', action: deleteSelectedSeasons}]
		})).show();
	});

	//Кнопка перехода на предыдущую страницу списка сезонов
	document.getElementById('season-prev-page').addEventListener('click', function()
	{
		seasonsList.prevPage();
		seasonsList.load();
	});

	//Кнопка перехода на следующую страницу списка сезонов
	document.getElementById('season-next-page').addEventListener('click', function()
	{
		seasonsList.nextPage();
		seasonsList.load();
	});

	var seasonNameSearchField       = document.getElementById('season-name-search'),
		seasonNumSearchField        = document.getElementById('season-num-search'),
		seasonChannelSearchCombobox = createCombobox({
			data: new DataList({opUrl: 'index.php?op=eslist.channelsList'}),
			dataElementProperty: 'name',
			dataReturnProperty: 'id'
		});

	seasonChannelSearchCombobox.placeholder = 'Поиск по имени канала';
	seasonNumSearchField.insertAfter(seasonChannelSearchCombobox);

	//Поиск сезона по имени
	seasonNameSearchField.addEventListener('input', function()
	{
		seasonsList.updateLoadParameters({name: seasonNameSearchField.value});
		seasonsList.load();
	});

	//Поиск сезона по номеру
	seasonNumSearchField.addEventListener('input', function()
	{
		if(seasonNumSearchField.value.length)
		{
			seasonsList.updateLoadParameters({number: seasonNumSearchField.value});
		}
		else
		{
			seasonsList.updateLoadParameters({number: -1});
		}

		seasonsList.load();
	});

	seasonChannelSearchCombobox.addEventListener('input', function()
	{
		var channelComboboxValue = seasonChannelSearchCombobox.value;
		if(channelComboboxValue != null)
		{
			seasonsList.updateLoadParameters({chanid: channelComboboxValue});
		}
		else
		{
			seasonsList.updateLoadParameters({chanid: -1});
		}

		seasonsList.load();
	});

	//=========================================

	//===============- Эпизоды -===============

	//Кнопка добавления эпизода
	document.getElementById('add-episode').addEventListener('click', function()
	{
		var videoMessage   = document.createElement('div'),
			previewMessage = document.createElement('div'),
			channelsCBList = new DataList({opUrl: 'index.php?op=eslist.channelsList'}),
			seasonsCBList  = new DataList({opUrl: 'index.php?op=eslist.seasonsList'});

		videoMessage.innerHTML = 'Загрузите видеоролик.<br>' + allowed_video_ext.join('/') + ', макс. ' + readableSize(max_video_size);
		previewMessage.innerHTML = 'Загрузите изображение видеоролика.<br>' + allowed_preview_ext.join('/') + ', ' + preview_ar_x + ':' + preview_ar_y + ', макс. ' + readableSize(max_preview_size);

		var addEpisodeForm = new FormBuilder([
			{
				name: 'name',
				pattern: name_regexpr,
				label: 'Название',
				boolAttr: ['required', 'autofocus']
			},
			{
				name: 'number',
				type: 'number',
				label: 'Номер',
				min: 1,
				boolAttr: ['required']
			},
			{
				tag: 'textarea',
				name: 'description',
				label: 'Описание',
				boolAttr: ['required']
			},
			{
				tag: 'combo-box',
				name: 'channel_id',
				label: 'Канал',
				title: 'Название канала',
				data: channelsCBList,
				dataElementProperty: 'name',
				dataReturnProperty: 'id',
				boolAttr: ['required']
			},
			{
				tag: 'combo-box',
				name: 'season_id',
				label: 'Сезон',
				title: 'Название сезона',
				data: seasonsCBList,
				dataElementProperty: 'name',
				dataReturnProperty: 'id'
			},
			{
				name: 'video_id',
				tag: 'file-uploader',
				opUrl: 'index.php?op=cp.uploadVideo',
				deleteOpUrl: 'index.php?op=cp.deleteFile',
				maxSize: max_video_size,
				allowedExtensions: allowed_video_ext,
				defaultMessage: videoMessage,
				required: true
			},
			{
				name: 'preview_id',
				tag: 'file-uploader',
				opUrl: 'index.php?op=cp.uploadVideoPreview',
				deleteOpUrl: 'index.php?op=cp.deleteFile',
				maxSize: max_preview_size,
				allowedExtensions: allowed_preview_ext,
				defaultMessage: previewMessage,
				required: true
			}
		], 'POST', 'index.php?op=cp.episode.addEpisode').build('add-episode-form');

		var channelCombobox = addEpisodeForm.children[7];

		//Ограничиваем список сезонов выбранным каналом
		channelCombobox.addEventListener('input', function()
		{
			seasonsCBList.updateLoadParameters({chanid: channelCombobox.value});
		});

		var addEpisodePopup = new Popup({
			title: 'Добавление эпизода',
			content: addEpisodeForm,
			overlay: true,
			buttons: [{text: 'Отмена'}, {
				text: 'Добавить',
				hide: false,
				submitFormId: 'add-episode-form',
				responseHandler: addEpisodeRespHandler
			}]
		});

		function addEpisodeRespHandler()
		{
			addEpisodePopup.hide();
			videosList.load();

			(new Notification({
				content: 'Эпизод успешно добавлен!',
				styleClass: 'success'
			})).show();
		}

		addEpisodePopup.show();
	});

	//Кнопка удаления выбранных эпизодов
	document.getElementById('delete-selected-episodes').addEventListener('click', function()
	{
		function deleteSelectedEpisodes()
		{
			var ids = [];

			episodesListSelection.selected.forEach(function(ep)
			{
				ids.push(ep.dataset.id);
			});

			sendRequest('POST', 'index.php?op=cp.episode.deleteEpisode', {ids: ids}, function(result)
			{
				if(result === 0)
				{
					seasonsList.load();

					(new Notification({
						content: 'Выбранные эпизоды успешно удалены.',
						styleClass: 'success'
					})).show();
				}
			});
		}

		(new Popup({
			title: 'Удаление выбранных эпизодов',
			content: 'Вы действительно хотите удалить выбранные эпизоды в кол-ве ' + episodesListSelection.selected.length + ' шт?',
			overlay: true,
			closeCross: true,
			buttons: [{text: 'Нет'}, {text: 'Да', action: deleteSelectedEpisodes}]
		})).show();
	});

	//Кнопка перехода на предыдущую страницу отображения списка эпизодов
	document.getElementById('episode-prev-page').addEventListener('click', function()
	{
		videosList.prevPage();
		videosList.load();
	});

	//Кнопка перехода на следующую страницу отображения списка эпизодов
	document.getElementById('episode-next-page').addEventListener('click', function()
	{
		videosList.nextPage();
		videosList.load();
	});

	var epNameField     = document.getElementById('episode-name-search'),
		epNumField      = document.getElementById('episode-num-search'),
		epCbSeasonsList = new DataList({opUrl: 'index.php?op=eslist.seasonsList'}),
		epCatCombobox   = createCombobox({
			data: new DataList({opUrl: 'index.php?op=eslist.categoriesList'}),
			dataElementProperty: 'name',
			dataReturnProperty: 'id'
		}),
		epChanCombobox  = createCombobox({
			data: new DataList({opUrl: 'index.php?op=eslist.channelsList'}),
			dataElementProperty: 'name',
			dataReturnProperty: 'id'
		}),
		epSeaCombobox   = createCombobox({
			data: epCbSeasonsList,
			dataElementProperty: 'name',
			dataReturnProperty: 'id'
		});

	epCatCombobox.placeholder  = 'Поиск по категории';
	epChanCombobox.placeholder = 'Поиск по каналу';
	epSeaCombobox.placeholder  = 'Поиск по сезону';

	epNumField.insertAfter(epCatCombobox);
	epCatCombobox.insertAfter(epChanCombobox);
	epChanCombobox.insertAfter(epSeaCombobox);

	epNameField.addEventListener('input', function()
	{
		videosList.updateLoadParameters({name: epNameField.value});
		videosList.load();
	});

	epNumField.addEventListener('input', function()
	{
		videosList.updateLoadParameters({number: epNumField.value});
		videosList.load();
	});

	epCatCombobox.addEventListener('input', function()
	{
		videosList.updateLoadParameters({catid: epCatCombobox.value});
		videosList.load();
	});

	epChanCombobox.addEventListener('input', function()
	{
		videosList.updateLoadParameters({chanid: epChanCombobox.value});
		epCbSeasonsList.updateLoadParameters({chanid: epChanCombobox.value});
		videosList.load();
	});

	epSeaCombobox.addEventListener('input', function()
	{
		videosList.updateLoadParameters({'seaid': epSeaCombobox.value});
		videosList.load();
	});

	//=========================================
});

//==========- Функции открытия вкладок -==========

function onVideoTabOpened()
{
	categoriesList.load(function()
	{
		videosList.load();
	});
}

function onSeasonsTabOpened()
{
	categoriesList.load(function()
	{
		seasonsList.load();
	});
}

function onChannelsTabOpened()
{
	categoriesList.load(function()
	{
		channelsList.load();
	});
}

//==========- Функции создания объектов табличек -==========

/**
 * Функция создания объекта таблички канала.
 * @param {object} channelData Объект данных канала.
 * @return {object} Объект таблички канала.
 */
function createChannelObject(channelData)
{
	var plank          = document.createElement('section'),
		nameDiv        = document.createElement('div'),
		descriptionDiv = document.createElement('div'),
		buttonsPanel   = document.createElement('div'),
		imagesDiv      = document.createElement('div'),
		deleteButton   = document.createElement('button'),
		editButton     = document.createElement('button'),
		logoImg        = document.createElement('img'),
		pageImg        = document.createElement('img');

	plank.className          = 'plank';
	nameDiv.className        = 'name';
	descriptionDiv.className = 'description';
	buttonsPanel.className   = 'buttons-panel';
	imagesDiv.className      = 'images';
	logoImg.className        = 'logo-img';
	pageImg.className        = 'page-img';
	deleteButton.className   = 'icon-button';
	editButton.className     = 'icon-button';


	nameDiv.appendChild(document.createTextNode(channelData.name));

	logoImg.src = channelData.logo_path;
	pageImg.src = channelData.pg_image_path;

	logoImg.setAttribute('alt', 'Логотип');
	pageImg.setAttribute('alt', 'Изображение страницы');

	imagesDiv.appendChild(pageImg);
	imagesDiv.appendChild(logoImg);

	descriptionDiv.appendChild(imagesDiv);
	descriptionDiv.appendChild(document.createTextNode(channelData.description));

	var deleteImg = document.createElement('img'),
		editImg   = document.createElement('img');

	deleteImg.src = glImagesPath + 'delete_cleaned.svg';
	editImg.src   = glImagesPath + 'edit_cleaned.svg';

	deleteButton.appendChild(deleteImg);
	editButton.appendChild(editImg);

	buttonsPanel.appendChild(deleteButton);
	buttonsPanel.appendChild(editButton);

	plank.appendChild(nameDiv);
	plank.appendChild(document.createElement('hr'));
	plank.appendChild(descriptionDiv);

	//Если задана категория
	if(channelData.category_id != null)
	{
		for(var i = 0; i < categoriesList.list.length; i++)
		{
			if(channelData.category_id === categoriesList.list[i].id)
			{
				descriptionDiv.appendChild(document.createElement('br'));
				descriptionDiv.appendChild(document.createTextNode('Категория: ' + categoriesList.list[i].name));
				break;
			}
		}
	}

	if(channelData.editor_id)
	{
		descriptionDiv.appendChild(document.createElement('br'));
		descriptionDiv.appendChild(document.createTextNode('Редактор: ' + channelData.editor_login));
	}

	plank.appendChild(document.createElement('hr'));
	plank.appendChild(buttonsPanel);

	//Кнопка удаления канала
	deleteButton.addEventListener('click', function(e)
	{
		e.stopPropagation();

		function deleteChannel()
		{
			sendRequest('POST', 'index.php?op=cp.channel.deleteChannel', {id: channelData.id}, function(result)
			{
				if(result === 0)
				{
					plank.load();

					(new Notification({
						content: 'Канал "' + channelData.name + '" успешно удален!',
						styleClass: 'success',
						showTime: 2000
					})).show();
				}
			});
		}

		(new Popup({
			title: 'Удаление канала',
			content: 'Вы уверены что хотите удалить канал "' + channelData.name + '"?',
			overlay: true,
			closeCross: true,
			buttons: [{text: 'Нет'}, {text: 'Да', action: deleteChannel}]
		})).show();
	});

	//Кнопка редактирования канала
	editButton.addEventListener('click', function(e)
	{
		e.stopPropagation();

		var categoryName = '';

		for(var i = 0; i < categoriesList.list.length; i++)
		{
			var el = categoriesList.list[i];

			if(el.id === channelData.category_id)
			{
				categoryName = el.name;
				break;
			}
		}

		var logoMessage      = document.createElement('div'),
			pageImageMessage = document.createElement('div');

		logoMessage.innerHTML = 'Загрузите новый логотип канала.<br>' + chan_logo_ext.join('/') + ', 1:1, макс. ' + readableSize(chan_logo_max_size);
		pageImageMessage.innerHTML = 'Загрузите новое изображение страницы канала.<br>' + chan_pg_img_ext.join('/') + ', 4:1, макс. ' + readableSize(chan_pg_img_max_size);

		var channelEditForm = new FormBuilder(
			[
				{
					name: 'id',
					type: 'hidden',
					value: channelData.id
				},
				{
					name: 'name',
					value: channelData.name,
					label: 'Название',
					title: 'Буквы латинского или кириллического алфавита, пробел, а также цифры и символ -.',
					pattern: name_regexpr,
					boolAttr: ['autofocus', 'required']
				},
				{
					tag: 'textarea',
					name: 'description',
					value: channelData.description,
					label: 'Описание',
					boolAttr: ['required']
				},
				{
					tag: 'combo-box',
					name: 'category_id',
					value: categoryName,
					label: 'Категория',
					data: categoriesList,
					dataElementProperty: 'name',
					dataReturnProperty: 'id'
				},
				{
					tag: 'combo-box',
					name: 'editor_id',
					value: channelData.editor_login,
					label: 'Редактор',
					data: new DataList({opUrl: 'index.php?op=cp.us.usersList'}),
					dataElementProperty: 'login',
					dataReturnProperty: 'id'
				},
				{
					name: 'logo_id',
					tag: 'file-uploader',
					opUrl: 'index.php?op=cp.uploadChannelLogo',
					deleteOpUrl: 'index.php?op=cp.deleteFile',
					maxSize: chan_logo_max_size,
					allowedExtensions: chan_logo_ext,
					defaultMessage: logoMessage
				},
				{
					name: 'pg_image_id',
					tag: 'file-uploader',
					opUrl: 'index.php?op=cp.uploadChannelPageImage',
					deleteOpUrl: 'index.php?op=cp.deleteFile',
					maxSize: chan_pg_img_max_size,
					allowedExtensions: chan_pg_img_ext,
					defaultMessage: pageImageMessage
				}
			], 'POST', 'index.php?op=cp.channel.editChannel'
		).build('channel-edit-form');

		var channelEditPopup = new Popup({
			title: 'Редактирования канала',
			content: channelEditForm,
			overlay: true,
			buttons: [{text: 'Отмена'}, {
				text: 'Редактировать',
				hide: false,
				submitFormId: 'channel-edit-form',
				responseHandler: addChannelRespHandler
			}]
		});

		channelEditPopup.show();

		function addChannelRespHandler()
		{
			channelEditPopup.hide();
			channelsList.load();

			(new Notification({
				content: 'Канал "' + channelEditForm.elements.name.value + '" успешно отредактирован!',
				styleClass: 'success',
				showTime: 3000
			})).show();
		}
	});

	createTooltip(deleteButton, 'Удалить канал', 'bottom', 20);
	createTooltip(editButton, 'Редактировать канал', 'bottom', 20);
	createTooltip(logoImg, 'Логотип канала');
	createTooltip(pageImg, 'Изображение страницы канала');

	plank.setAttribute('data-id', channelData.id);

	return plank;
}

/**
 * Функция создания объекта таблички сезона.
 * @param {object} season Объект данных сезона.
 * @return {object} Объект таблички сезона.
 */
function createSeasonObject(season)
{
	var seasonPlank    = document.createElement('section'),
		nameDiv        = document.createElement('div'),
		descriptionDiv = document.createElement('div'),
		buttonsPanel   = document.createElement('div'),
		deleteButton   = document.createElement('button'),
		editButton     = document.createElement('button');

	seasonPlank.className    = 'plank';
	nameDiv.className        = 'name';
	descriptionDiv.className = 'description';
	buttonsPanel.className   = 'buttons-panel';
	deleteButton.className   = 'icon-button';
	editButton.className     = 'icon-button';

	nameDiv.innerHTML = season.name + ' (' + season.number + ')<br>' + season.channel_name;

	descriptionDiv.appendChild(document.createTextNode(season.description));

	var deleteImg = document.createElement('img'),
		editImg   = document.createElement('img');

	deleteImg.src = glImagesPath + 'delete_cleaned.svg';
	editImg.src   = glImagesPath + 'edit_cleaned.svg';

	deleteButton.appendChild(deleteImg);
	editButton.appendChild(editImg);

	buttonsPanel.appendChild(deleteButton);
	buttonsPanel.appendChild(editButton);

	seasonPlank.appendChild(nameDiv);
	seasonPlank.appendChild(document.createElement('hr'));
	seasonPlank.appendChild(descriptionDiv);
	seasonPlank.appendChild(document.createElement('hr'));
	seasonPlank.appendChild(buttonsPanel);

	//Кнопка удаления
	deleteButton.addEventListener('click', function(e)
	{
		e.stopPropagation();

		function deleteSeason()
		{
			sendRequest('POST', 'index.php?op=cp.season.deleteSeason', {id: season.id}, function(result)
			{
				if(result === 0)
				{
					seasonsList.load();

					(new Notification({
						content: 'Сезон ' + season.name + ' успешно удален!',
						styleClass: 'success'
					})).show();
				}
			});
		}

		(new Popup({
			title: 'Удаление сезона',
			content: 'Вы действительно хотите удалить сезон ' + season.name + '?',
			overlay: true,
			closeCross: true,
			buttons: [{text: 'Нет'}, {text: 'Да', action: deleteSeason}]
		})).show();
	});

	//Кнопка редактирования
	editButton.addEventListener('click', function(e)
	{
		e.stopPropagation();

		var seasonEditForm = new FormBuilder(
			[
				{
					type: 'hidden',
					name: 'id',
					value: season.id
				},
				{
					name: 'name',
					pattern: name_regexpr,
					value: season.name,
					label: 'Название',
					title: 'Буквы латинского или кириллического алфавита, пробел, а также цифры и символ -.',
					boolAttr: ['required', 'autofocus']
				},
				{
					name: 'number',
					type: 'number',
					label: 'Номер',
					value: season.number,
					title: 'Число от 1 до бесконечности.',
					min: 1,
					boolAttr: ['required']
				},
				{
					tag: 'textarea',
					name: 'description',
					value: season.description,
					label: 'Описание',
					boolAttr: ['required']
				},
				{
					tag: 'combo-box',
					name: 'channel_id',
					value: season.channel_name,
					label: 'Канал',
					title: 'Название канала',
					data: new DataList({opUrl: 'index.php?op=eslist.channelsList'}),
					dataElementProperty: 'name',
					dataReturnProperty: 'id',
					boolAttr: ['required']
				}
			], 'POST', 'index.php?op=cp.season.editSeason'
		).build('edit-season-form');

		var seasonEditPopup = new Popup({
			title: 'Редактирование сезона',
			content: seasonEditForm,
			overlay: true,
			buttons: [{text: 'Отмена'}, {
				text: 'Редактировать',
				hide: false,
				submitFormId: 'edit-season-form',
				responseHandler: editSeasonRespHandler
			}]
		});

		seasonEditPopup.show();

		function editSeasonRespHandler()
		{
			seasonEditPopup.hide();
			seasonsList.load();

			(new Notification({
				content: 'Сезон ' + seasonEditForm.elements.name.value + ' успешно отредактирован!',
				styleClass: 'success',
				showTime: 3000
			})).show();
		}
	});

	createTooltip(deleteImg, 'Удалить сезон', 'bottom', 20);
	createTooltip(editImg, 'Редактировать сезон', 'bottom', 20);

	seasonPlank.setAttribute('data-id', season.id);

	return seasonPlank;
}

/**
 * Функция создания элемента DOM эпизода.
 * @param {object} episode Объект данных эпизода.
 * @return {object} Объект эпизода.
 */
function createEpisodeObject(episode)
{
	var plank                  = document.createElement('section'),
		nameDiv                = document.createElement('div'),
		channelSeasonDiv       = document.createElement('div'),
		descriptionDiv         = document.createElement('div'),
		buttonsPanel           = document.createElement('div'),
		infDiv                 = document.createElement('div'),
		statsDiv               = document.createElement('div'),
		previewImg             = document.createElement('img'),
		deleteImg              = document.createElement('img'),
		editImg                = document.createElement('img'),
		eyeImg                 = document.createElement('img'),
		strikethroughEyeImg    = document.createElement('img'),
		deleteButton           = document.createElement('button'),
		editButton             = document.createElement('button'),
		commentsLink           = document.createElement('a'),
		visibilityToggleButton = createToggleButton({
			activeContent: eyeImg,
			unactiveContent: strikethroughEyeImg,
			activated: !Number(episode.hidden),
			onTurnOn: function()
			{
				if(episode.status != statusVideoProcessed)
				{
					(new Notification({
						content: 'Невозможно сделать видимым эпизод, так как видеоролик эпизода не был обработан!',
						showTime: 5000,
						styleClass: 'error'
					})).show();

					visibilityToggleButton.tbTurnOff(false);
					return;
				}

				sendRequest('POST', 'index.php?op=cp.episode.episodeVisibility', {
					id: episode.id,
					visibility: 1
				}, function()
				{
					(new Notification({
						content: 'Эпизод "' + episode.name + '" теперь доступен для просмотра пользователями.',
						showTime: 3000
					})).show();

					if(!visibilityToggleButton.tbActive)
					{
						visibilityToggleButton.tbTurnOn(false);
					}
				});
			},
			onTurnOff: function()
			{
				sendRequest('POST', 'index.php?op=cp.episode.episodeVisibility', {
					id: episode.id,
					visibility: 0
				}, function()
				{
					(new Notification({
						content: 'Эпизод "' + episode.name + '" скрыт.',
						showTime: 1500
					})).show();

					if(visibilityToggleButton.tbActive)
					{
						visibilityToggleButton.tbTurnOff(false);
					}
				});
			}
		});

	plank.className            = 'plank';
	nameDiv.className          = 'name';
	channelSeasonDiv.className = 'info';
	infDiv.className           = 'info';
	statsDiv.className         = 'info';
	descriptionDiv.className   = 'description';
	buttonsPanel.className     = 'buttons-panel';
	deleteButton.className = 'icon-button';
	editButton.className = 'icon-button';
	visibilityToggleButton.className = 'icon-button';

	previewImg.src = episode.preview_path;

	nameDiv.appendChild(document.createTextNode(episode.name + ' (' + episode.number + ')'));

	channelSeasonDiv.innerHTML = 'Канал: ' + episode.channel_name;
	if(episode.season_name)
	{
		channelSeasonDiv.innerHTML += '<br>Сезон: ' + episode.season_name;
	}

	commentsLink.appendChild(document.createTextNode('Список комментариев'));
	commentsLink.href = 'index.php?page=cp.commentsList&type=' + commentTypeEpisode + '&id=' + episode.id;
	commentsLink.addEventListener('click', function(e) { e.stopPropagation(); });

	descriptionDiv.appendChild(previewImg);
	descriptionDiv.appendChild(document.createTextNode(episode.description));
	descriptionDiv.appendChild(document.createElement('br'));
	descriptionDiv.appendChild(commentsLink);

	function buildFileSpan(extension, size)
	{
		var span       = document.createElement('span');
		span.className = 'video';
		span.appendChild(document.createTextNode(extension + ' (' + readableSize(size) + ')'));
		return span;
	}

	infDiv.appendChild(document.createTextNode('Видеофайлы: '));

	if(episode.videos_data)
	{
		episode.videos_data.forEach(function(video)
		{
			infDiv.appendChild(buildFileSpan(video.extension, video.size));
			infDiv.appendChild(document.createTextNode(' '));
		});
	}

	//Исходный видеоролик
	if(episode.source_video_data)
	{
		infDiv.appendChild(document.createElement('br'));
		infDiv.appendChild(document.createTextNode('Исходный видеоролик: '));
		infDiv.appendChild(buildFileSpan(episode.source_video_data.extension, episode.source_video_data.size));
	}

	var viewsCount    = document.createElement('span'),
		commentsCount = document.createElement('span'),
		likesCount    = document.createElement('span');

	viewsCount.className    = 'stat';
	commentsCount.className = 'stat';
	likesCount.className    = 'stat';

	viewsCount.appendChild(document.createTextNode(episode.views));
	commentsCount.appendChild(document.createTextNode(episode.comments));
	likesCount.appendChild(document.createTextNode(episode.likes));

	statsDiv.appendChild(viewsCount);
	statsDiv.appendChild(commentsCount);
	statsDiv.appendChild(likesCount);

	SVG.loadFromURL(glImagesPath + 'eye.svg', function(svg)
	{
		viewsCount.insertAfter(svg);
	});

	SVG.loadFromURL(glImagesPath + 'comment.svg', function(svg)
	{
		commentsCount.insertAfter(svg);
	});

	SVG.loadFromURL(glImagesPath + 'heart.svg', function(svg)
	{
		likesCount.insertAfter(svg);
	});

	deleteImg.src           = glImagesPath + 'delete_cleaned.svg';
	editImg.src             = glImagesPath + 'edit_cleaned.svg';
	eyeImg.src              = glImagesPath + 'eye.svg';
	strikethroughEyeImg.src = glImagesPath + 'strikethrough_eye.svg';

	eyeImg.id              = 'visible';
	strikethroughEyeImg.id = 'invisible';

	deleteButton.appendChild(deleteImg);
	editButton.appendChild(editImg);
	visibilityToggleButton.appendChild(eyeImg);
	visibilityToggleButton.appendChild(strikethroughEyeImg);

	buttonsPanel.appendChild(visibilityToggleButton);
	buttonsPanel.appendChild(deleteButton);
	buttonsPanel.appendChild(editButton);

	plank.appendChild(nameDiv);
	plank.appendChild(document.createElement('hr'));
	plank.appendChild(channelSeasonDiv);
	plank.appendChild(document.createElement('hr'));
	plank.appendChild(descriptionDiv);
	plank.appendChild(document.createElement('hr'));
	plank.appendChild(infDiv);
	plank.appendChild(document.createElement('hr'));
	plank.appendChild(statsDiv);
	plank.appendChild(document.createElement('hr'));
	plank.appendChild(buttonsPanel);

	createTooltip(deleteButton, 'Удалить эпизод', 'bottom', 20);
	createTooltip(editButton, 'Редактировать эпизод', 'bottom', 20);

	//Кнопка удаления эпизода
	deleteButton.addEventListener('click', function(e)
	{
		e.preventDefault();
		e.stopPropagation();

		function deleteEpisode()
		{
			sendRequest('POST', 'index.php?op=cp.episode.deleteEpisode', {id: episode.id}, function(result)
			{
				if(result === 0)
				{
					(new Notification({
						content: 'Эпизод ' + episode.name + ' успешно удален!',
						styleClass: 'success'
					})).show();

					videosList.load();
				}
			});
		}

		(new Popup({
			title: 'Удаление эпизода',
			content: 'Вы действительно хотите удалить эпизод ' + episode.name + '?',
			overlay: true,
			closeCross: true,
			buttons: [{text: 'Нет'}, {text: 'Да', action: deleteEpisode}]
		})).show();
	});

	//Кнопка редактирования эпизода
	editButton.addEventListener('click', function(e)
	{
		e.preventDefault();
		e.stopPropagation();

		var videoMessage   = document.createElement('div'),
			previewMessage = document.createElement('div'),
			channelsCBList = new DataList({opUrl: 'index.php?op=eslist.channelsList'}),
			seasonsCBList  = new DataList({opUrl: 'index.php?op=eslist.seasonsList'});

		videoMessage.innerHTML = 'Загрузите новый видеоролик.<br>' + allowed_video_ext.join('/') + ', макс. ' + readableSize(max_video_size);
		previewMessage.innerHTML = 'Загрузите новое изображение видеоролика.<br>' + allowed_preview_ext.join('/') + ', ' + preview_ar_x + ':' + preview_ar_y + ', макс. ' + readableSize(max_preview_size);

		var episodeEditForm = new FormBuilder([
			{
				type: 'hidden',
				name: 'id',
				value: episode.id
			},
			{
				name: 'name',
				value: episode.name,
				pattern: name_regexpr,
				label: 'Название',
				boolAttr: ['required', 'autofocus']
			},
			{
				name: 'number',
				type: 'number',
				value: episode.number,
				label: 'Номер',
				min: 1,
				boolAttr: ['required']
			},
			{
				tag: 'textarea',
				name: 'description',
				value: episode.description,
				label: 'Описание',
				boolAttr: ['required']
			},
			{
				tag: 'combo-box',
				name: 'channel_id',
				value: episode.channel_name,
				label: 'Канал',
				title: 'Название канала',
				data: channelsCBList,
				dataElementProperty: 'name',
				dataReturnProperty: 'id',
				boolAttr: ['required']
			},
			{
				tag: 'combo-box',
				name: 'season_id',
				value: episode.season_name,
				label: 'Сезон',
				title: 'Название сезона',
				data: seasonsCBList,
				dataElementProperty: 'name',
				dataReturnProperty: 'id'
			},
			{
				name: 'video_id',
				tag: 'file-uploader',
				opUrl: 'index.php?op=cp.uploadVideo',
				deleteOpUrl: 'index.php?op=cp.deleteVideo',
				maxSize: max_video_size,
				allowedExtensions: allowed_video_ext,
				defaultMessage: videoMessage
			},
			{
				name: 'preview_id',
				tag: 'file-uploader',
				opUrl: 'index.php?op=cp.uploadVideoPreview',
				deleteOpUrl: 'index.php?op=cp.deleteVideoPreview',
				maxSize: max_preview_size,
				allowedExtensions: allowed_preview_ext,
				defaultMessage: previewMessage
			}
		], 'POST', 'index.php?op=cp.episode.editEpisode').build('episode-edit-form');

		var channelCombobox = episodeEditForm.children[8];

		//Ограничивает список сезонов в комбобоксе выбранным каналом
		function limitSeasonsList()
		{
			seasonsCBList.updateLoadParameters({chanid: channelCombobox.value});
		}

		channelsCBList.load(limitSeasonsList);

		//Ограничиваем список сезонов выбранным каналом
		channelCombobox.addEventListener('input', limitSeasonsList);

		var episodeEditPopup = new Popup({
			title: 'Редактирование эпизода',
			content: episodeEditForm,
			overlay: true,
			buttons: [{text: 'Отмена'}, {
				text: 'Редактировать',
				submitFormId: 'episode-edit-form',
				hide: false,
				responseHandler: episodeEditRespHandler
			}]
		});

		function episodeEditRespHandler(result)
		{
			if(result === 0)
			{
				videosList.load();

				episodeEditPopup.hide();

				(new Notification({
					content: 'Эпизод успешно отредактирован!',
					styleClass: 'success',
					showTime: 3000
				})).show();
			}
		}

		episodeEditPopup.show();
	});

	plank.setAttribute('data-id', episode.id);

	return plank;
}