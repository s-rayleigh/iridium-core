/**
 * Конструктор списка данных. Позволяет наладить связь со списком на стороне сервера удобным способом
 * @param {object} parameters Параметры списка
 */
function DataList(parameters)
{
	var dataList = this,
		data			= [],		//Загружаемые данные
		opUrl,						//Адрес операции
		loadParameters	= {},		//Параметры запроса данных
		onLoadCallback,				//Функция, которая вызывается после загрузки данных
		pageNavigation	= false,	//Использовать постраничную навигацию
		page			= 0,		//Номер текущей страницы
		pages			= 0,		//Всего страниц
		move			= 0,		//Перемещение по страницам
		reloadIntervId	= 0;		//Идентификатор периодичной перезагрузки списка

	if(typeof parameters.opUrl === 'string' && !parameters.opUrl)
	{
		console.error('Объекту DataList не передан адрес операции (opUrl)!');
		return;
	}

	if(typeof parameters.onLoad === 'function')
	{
		onLoadCallback = parameters.onLoad;
	}

	if(parameters.loadParameters)
	{
		loadParameters	= parameters.loadParameters;
	}

	opUrl			= parameters.opUrl;
	pageNavigation	= parameters.usePageNavigation;

	Object.defineProperty(this, 'list', {
		get: function() { return data; }
	});

	/**
	 * Запрашивает данные и вызывает callback функцию при успешной загрузке
	 */
	this.load = function(callback)
	{
		if(pageNavigation)
		{
			loadParameters.page = page;
			loadParameters.move = move;
		}

		sendRequest('POST', opUrl, loadParameters, function(result)
		{
			if(pageNavigation)
			{
				page = result.page;
				pages = result.pages;
				move = 0;
			}

			dataList.clearData();
			data.pushArray(result.list);

			if(onLoadCallback) { onLoadCallback(result); }
			if(callback) { callback(); }
		});
	};

	/**
	 * Устанавливает парметры POST запроса.
	 * @param {object} params Новые параметры.
	 */
	this.setLoadParameters = function(params)
	{
		loadParameters = params;
	};

	/**
	 * Обновляет параметры POST запроса.
	 * @param  {object} params Новые параметры.
	 */
	this.updateLoadParameters = function(params)
	{
		updateObject(loadParameters, params);
	};

	/**
	 * Запускает периодичное обновление списка
	 * @param  {int}	interval Период обновления
	 */
	this.liveReload = function(interval)
	{
		reloadIntervId = setInterval(dataList.load, interval);
	};

	/**
	 * Останавливает периодичное обновление списка
	 */
	this.stopLiveReload = function()
	{
		clearInterval(reloadIntervId);
	};

	/**
	 * Указывает перейти на следующую страницу при загрузке данных
	 */
	this.nextPage = function() { move = 1; };

	/**
	 * Указывает перейти на предыдущую страницу при загрузке данных
	 */
	this.prevPage = function() { move = -1; };

	/**
	 * Переход на первую страницу при последующей загрузке данных.
	 */
	this.firstPage = function()
	{
		page = 0;
		move = 0;
	};

	/**
	 * Переход на последнюю страницу при последующей загрузке данных.
	 */
	this.lastPage = function()
	{
		page = pages;
		move = 0;
	};

	/**
	 * Указывает перейти на конкретную страницу при загрузке данных
	 * @param  {int} newPage Номер страницы
	 */
	this.toPage = function(newPage)
	{
		move = 0;
		page = newPage;
	};

	/**
	 * Очищает массив данных
	 */
	this.clearData = function()
	{
		if(data) { data.length = 0; }
	};
}