window.addEventListener('load', function()
{
	var buttonsContainers = document.getElementsByClassName('tabs-buttons');

	for(var i = 0; i < buttonsContainers.length; i++)
	{
		var buttonsContainer = buttonsContainers[i];

		if(!buttonsContainer.hasAttribute('data-tabs-id'))
		{
			console.warn('Контейнер кнопок вкладок не содержит атрибут data-tabs-id!');
			continue;
		}

		var tabsContainerId	= buttonsContainer.getAttribute('data-tabs-id'),
			tabsContainer	= document.getElementById(tabsContainerId);

		if(!tabsContainer)
		{
			console.error('Контейнер вкладок с идентификатором ' + tabsContainerId + ' не найден!');
			continue;
		}

		var tabsCount, 									//Кол-во вкладок
			buttons		= buttonsContainer.children,	//Кнопки вкладок
			tabs		= tabsContainer.children;		//Вкладки

		if(buttons.length !== tabs.length)
		{
			console.warn('Количество вкладок контейнера ' + tabsContainerId + ' не совпадает с количеством кнопок.');
			tabsCount = Math.min(buttons.length, tabs.length);
		}
		else
		{
			tabsCount = buttons.length;
		}

		if(tabsCount === 0) { continue; }

		/**
		 * Формирует функцию отображения вкладки по номеру
		 * @param  {int}		tabNum Номер вкладки
		 * @return {function}	Функция отображения вкладки
		 */
		function showTab(tabNum)
		{
			return function()
			{
				for(var z = 0; z < tabsCount; z++)
				{
					tabs[z].style.display = 'none';
					removeClass(buttons[z], 'active');
				}

				tabs[tabNum].style.display = '';
				addClass(buttons[tabNum], 'active');

				//Вызываем callback-функцию, если задана
				if(buttons[tabNum].hasAttribute('data-tab-callback'))
				{
					var funcName = buttons[tabNum].getAttribute('data-tab-callback');
					if(typeof window[funcName] === 'function')
					{
						window[funcName]();
					}
				}
			};
		}

		//Добавляем контейнеру функцию отображения вкладки по номеру
		tabsContainer.showTab = function(tabNum)
		{
			showTab(tabNum)();
		};

		//Отображаем первую вкладку
		showTab(0)();

		//Вешаем события на кнопки вкладок
		for(var j = 0; j < tabsCount; j++)
		{
			buttons[j].addEventListener('click', showTab(j));
		}
	}
});