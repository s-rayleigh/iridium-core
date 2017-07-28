/**
 * Модуль предварительной загрузки файлов на сервер через форму
 * @module file_uploader
 */

window.addEventListener('load', function() { registerCustomElement('file-uploader'); });

function createFileUploader(newParams)
{
	var params = {
		opUrl: undefined,				//URL операции для загрузки файла на сервер
		deleteOpUrl: undefined,
		defaultMessage: undefined,		//Сообщение, которое показывается по умолчанию, перед загрузкой файла
		multiple: false,				//Возможность загрузки нескольких файлов
		required: false,				//Требовать загрузить файл при отправки формы
		maxSize: 1024 * 1024,			//Максимальный размер загружаемого файла в байтах (по умолчанию - 1 МиБ)
		allowedExtensions: undefined,	//Допустимые расширения файлов
		postParams: {}					//Параметры, которые передаются через POST вместе с файлом
	};

	updateObject(params, newParams, false);

	if(!params.opUrl)
	{
		console.error('Не передан url операции при создании объекта загрузчика файла!');
		return;
	}

	//Создаем сообщение по умолчанию для загрузки
	if(!params.defaultMessage)
	{
		params.defaultMessage = document.createElement('div');
		params.defaultMessage.appendChild(document.createTextNode('Загрузите файл!'));
	}

	var uplReq,
		abort          = false,									//Пользователь отменил загрузку
		loadedIds      = [],										//Массив идентификаторов загруженных файлов
		fileUploader   = document.createElement('file-uploader'),
		fileInputField = document.createElement('input'),			//Скрытое поле выбора файла
		loadingMessage = document.createElement('div'),			//Сообщение, которое показывается при загрузке файла на сервер
		dropMessage    = document.createElement('div'),			//Сообщение при наведении курсором с перетаскиваение файла
		previewMessage = document.createElement('div'),			//Предпросмотр загруженных файлов
		progressBar    = document.createElement('div'),			//Полоса загрузки
		uploadProgress = document.createElement('span'),			//Отображение прогресса загрузки
		abortLink      = document.createElement('a'),				//Ссылка отмены загрузки
		deleteLink     = document.createElement('a');				//Ссылка удаления загруженных файлов

	//Получение значение поля. Возвращает идентификатор(ы) загруженного(ых) файла
	Object.defineProperty(fileUploader, 'value', {
		get: function()
		{
			if(loadedIds.length === 0)
			{ return null; }
			return params.multiple ? loadedIds : loadedIds[0];
		},
		set: function(val)
		{
			hide(params.defaultMessage);
			show(previewMessage);

			loadedIds.length = 0;

			if(Array.isArray(val))
			{ loadedIds.pushArray(val); }
			else
			{ loadedIds.push(val); }
		}
	});

	//Указывает на обязательность заполнения поля
	Object.defineProperty(fileUploader, 'required', {
		get: function() { return params.required; },
		set: function(val) { params.required = val; }
	});

	params.defaultMessage.className = 'message default';
	dropMessage.className           = 'message drop';
	loadingMessage.className        = 'message loading';
	previewMessage.className        = 'message preview';
	progressBar.className           = 'progress-bar';
	uploadProgress.className        = 'progress';

	fileInputField.type          = 'file';				//Задаем тип полю
	fileInputField.style.display = 'none';		//Скрываем поле файла
	fileInputField.multiple      = params.multiple;	//Возможность загрузки нескольких файлов

	hide(dropMessage);
	hide(loadingMessage);
	hide(previewMessage);

	dropMessage.appendChild(document.createTextNode('Отпустите кнопку мыши чтобы начать загрузку файлов на сервер'));

	//Внутренняя часть полосы загрузки
	progressBar.appendChild(document.createElement('div'));
	progressBar.appendChild(uploadProgress);

	abortLink.appendChild(document.createTextNode('Отмена'));

	loadingMessage.appendChild(document.createTextNode('Загрузка ' + (params.multiple ? 'файлов' : 'файла')));
	loadingMessage.appendChild(progressBar);
	loadingMessage.appendChild(abortLink);

	previewMessage.appendChild(document.createTextNode('Файл' + (params.multiple ? 'ы' : '') + ' успешно загружен' + (params.multiple ? 'ы' : '') + '!'));

	if(params.deleteOpUrl)
	{
		deleteLink.appendChild(document.createTextNode('Удалить'));
		previewMessage.appendChild(deleteLink);
	}

	params.defaultMessage.addEventListener('dragenter', function(e)
	{
		e.stopPropagation();
		e.preventDefault();

		show(dropMessage);
		hide(params.defaultMessage);
	});

	fileUploader.addEventListener('dragover', function(e)
	{
		e.preventDefault();
		e.stopPropagation();
	});

	dropMessage.addEventListener('dragleave', function(e)
	{
		e.stopPropagation();
		e.preventDefault();

		hide(dropMessage);
		show(params.defaultMessage);
	});

	dropMessage.addEventListener('drop', function(e)
	{
		e.stopPropagation();
		e.preventDefault();

		hide(dropMessage);
		show(loadingMessage);

		uploadFiles(e.dataTransfer.files);
	});

	//Выбор файла(ов) по нажатию на сообщение
	params.defaultMessage.addEventListener('click', function(e)
	{
		e.preventDefault();
		fileInputField.click();
	});

	fileInputField.addEventListener('change', function(e)
	{
		e.preventDefault();

		hide(params.defaultMessage);
		show(loadingMessage);

		uploadFiles(this.files);
	});

	//Линк отмены загрузки
	abortLink.addEventListener('click', function(e)
	{
		e.preventDefault();
		abort = true;
		uplReq.abort();
	})

	//Линк удаления файла
	deleteLink.addEventListener('click', function(e)
	{
		e.preventDefault();

		if(loadedIds.length === 0)
		{
			console.error('Файлы не были загружены!');
			return;
		}

		sendRequest('POST', params.deleteOpUrl, params.multiple ? {ids: loadedIds} : {id: loadedIds[0]}, function(result)
		{
			if(result !== 0)
			{ return; }

			hide(previewMessage);
			show(params.defaultMessage);

			(new Notification({
				content: 'Файл успешно удален',
				position: 'top center',
				styleClass: 'info',
				closeCross: true
			})).show();
		});
	});

	//Добавляем сообщения, показываемые в компоненте
	fileUploader.appendChild(params.defaultMessage);
	fileUploader.appendChild(dropMessage);
	fileUploader.appendChild(loadingMessage);
	fileUploader.appendChild(previewMessage);

	/**
	 * Загрузка файлов на сервер
	 * @param {Array} files Файлы, которые необходимо загрузить
	 */
	function uploadFiles(files)
	{
		var data = new FormData();

		uplReq = new XMLHttpRequest();
		uplReq.open('POST', params.opUrl);

		/**
		 * Отображает ошибку превышение максимального размера файла
		 * @param {string} fileName Название файла
		 * @param {int} fileSize Размер файла в байтах
		 */
		function showMaxSizeErr(fileName, fileSize)
		{
			hide(loadingMessage);
			show(params.defaultMessage);

			(new Notification({
				content: 'Превышен максимально допустимый размер в ' + readableSize(params.maxSize) + ' для файла ' + fileName + ' (' + readableSize(fileSize) + ').',
				position: 'top center',
				closeCross: true,
				styleClass: 'warning',
				showTime: 7000
			})).show();
		}

		/**
		 * Проверяет, что имя файла содержит допустимое параметрами расширение
		 * @param {string} fileName Имя файла
		 * @return {boolean} Результат выполнения функции
		 */
		function testFileExtension(fileName)
		{
			if(!params.allowedExtensions)
			{ return true; }

			var nameSplit = fileName.split('.'),
				nameExt   = nameSplit[nameSplit.length - 1];

			//Если задано только одно допустимое расширение
			if(typeof params.allowedExtensions === 'string')
			{
				return nameExt === params.allowedExtensions;
			}

			for(var i = 0; i < params.allowedExtensions.length; i++)
			{
				if(nameExt === params.allowedExtensions[i])
				{
					return true;
				}
			}

			return false;
		}

		function showExtError(fileName)
		{
			hide(loadingMessage);
			show(params.defaultMessage);

			(new Notification({
				content: 'Файл ' + fileName + ' имеет недопустимое расширение!',
				position: 'top center',
				closeCross: true,
				styleClass: 'warning',
				showTime: 5000
			})).show();
		}

		//Загрузка нескольких файлов
		if(params.multiple)
		{
			for(var i = 0; i < files.length; i++)
			{
				//Проверяем, что файл весит меньше максимально допустимого
				if(files[i].size > params.maxSize)
				{
					showMaxSizeErr(files[i].name, files[i].size);
					return;
				}

				//Проверяем расширение файла
				if(!testFileExtension(file.name))
				{
					showExtError(file.name);
					return;
				}

				data.append('files[]', files[i], files[i].name);
			}
		}
		else //Загрузка одного файла
		{
			//Проверяем, что файл весит меньше максимально допустимого
			if(files[0].size > params.maxSize)
			{
				showMaxSizeErr(files[0].name, files[0].size);
				return;
			}

			//Проверяем расширение файла
			if(!testFileExtension(files[0].name))
			{
				showExtError(files[0].name);
				return;
			}

			data.append('file', files[0]);
		}

		//Добавление параметров загрузки
		for(var uplPar in params.postParams)
		{
			data.append(uplPar, params.postParams[uplPar]);
		}

		(uplReq.upload || uplReq).addEventListener('progress', function(e)
		{
			if(e.lengthComputable)
			{
				var prc                            = (Math.ceil((e.loaded / e.total) * 100)) + '%';
				progressBar.firstChild.style.width = prc;
				uploadProgress.innerHTML           = prc;
			}
		});

		//Обработка ответа от сервера
		uplReq.addEventListener('readystatechange', function()
		{
			if(uplReq.readyState === 4)
			{
				//Скрываем полосу загрузки
				hide(loadingMessage);

				if(uplReq.status === 200)
				{
					var result = JSON.parse(uplReq.responseText);

					//Ошибка загрузки файла
					if(typeof result === 'object' && 'error' in result)
					{
						show(params.defaultMessage);

						(new Notification({
							content: '<b>' + result.title + '</b><br>' + result.error,
							position: 'top center',
							closeCross: true,
							showTime: 10000,
							styleClass: 'error'
						})).show();

						return;
					}

					//Отображаем предпросмотр загруженного файла
					show(previewMessage);

					loadedIds.length = 0;

					if(params.multiple)
					{
						loadedIds.appendArray(result.ids);
					}
					else
					{
						loadedIds.push(result.id);
					}
				}
				else
				{
					show(params.defaultMessage);

					if(uplReq.status === 0 && abort)
					{
						abort = false;
						(new Notification({
							content: 'Вы отменили загрузку',
							position: 'top center',
							styleClass: 'warning'
						})).show();
					}
					else
					{
						(new Notification({
							content: 'При отправке файла произошла ошибка! Сервер вернул код ' + uplReq.status + ', ' + uplReq.statusText + '.',
							position: 'top center',
							showTime: 10000,
							closeCross: true,
							styleClass: 'error'
						})).show();
					}
				}
			}
		});

		uplReq.send(data);
	}

	function hide(obj)
	{
		obj.style.display = 'none';
	}

	function show(obj)
	{
		obj.style.display = '';
	}

	return fileUploader;
}