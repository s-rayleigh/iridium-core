/**
 * Модуль добавляет возможность создания элемента "Комбобокс"
 * @module combobox
 */

window.addEventListener('load', function() { registerCustomElement('combo-box'); });

/**
 * Создает объект комбобокса.
 * @param {object} newParams Параметры комбобокса.
 * @return {Element} Объект комбобокса.
 */
function createCombobox(newParams)
{
	//Стандартные параметры
	var params = {
		data: null,					//Массив данных
		dataElementProperty: '',	//Свойство элемента массива данных, с которого читать данные
		dataReturnProperty: '',		//Свойство элемента, которое нужно вернуть. Если указано, требуется точное совпадение введенных данных с значением dataElementProperty
		hintsCount: 5				//Количество отображаемых подсказок
	};

	updateObject(params, newParams, false);

	if(!params.data)
	{
		console.error('Невозможно создать объект комбобокса. Не был передан массив данных.');
		return null;
	}

	var combobox       = document.createElement('combo-box'),
		inputField     = document.createElement('input'),
		hintsContainer = document.createElement('div'),
		selHintIndex   = -1,
		useDataList    = false;

	//Используем DataList
	if(params.data instanceof DataList)
	{
		if(!params.dataElementProperty)
		{
			console.error('В случае использования DataList для комбобокса, необходимо передать также имя свойства из которого необходимо получать значение!');
			return null;
		}

		useDataList = true;
		params.data.load();
	}

	/**
	 * Устанавливает видимость контейнера подсказок
	 * @param {boolean} v true, чтобы отобразить
	 */
	function setHintsVisible(v)
	{
		if(v)
		{ hintsContainer.style.display = 'block'; }
		else
		{ hintsContainer.style.display = 'none'; }
	}

	/**
	 * Обновляет значение поля ввода и вызывает событие input
	 * @param {string} val Новое значение поля
	 */
	function updateInputValue(val)
	{
		inputField.value = val;
		inputField.dispatchEvent(new Event('input'));
	}

	/**
	 * Функция возвращает используемый массив данных
	 * @return {[type]} [description]
	 */
	function getDataArray()
	{
		return useDataList ? params.data.list : params.data;
	}

	/**
	 * Возвращает значение поля или элемент массива данных под указанным индексом
	 * @param {int} index Индекс
	 */
	function getDataArrayValue(index)
	{
		var array = getDataArray();
		return params.dataElementProperty ? array[index][params.dataElementProperty] : array[index];
	}

	inputField.type = 'text';
	setHintsVisible(false);

	//Получение/установка значения комбобокса
	Object.defineProperty(combobox, 'value',
		{
			get: function()
			{
				if(!params.dataReturnProperty)
				{
					return inputField.value;
				}
				else
				{
					var array = getDataArray(),
						res;

					//Ищем подходящий объект и, если он найден, запоминаем значение поля dataReturnProperty для возвращения
					//в качестве результата
					for(var i = 0; i < array.length; i++)
					{
						var val = getDataArrayValue(i);

						if(val === inputField.value)
						{
							res = array[i][params.dataReturnProperty];
							break;
						}
					}

					return res;
				}
			},
			set: function(val) { inputField.value = val; }
		});

	//Получение/установка регулярного выражения проверки комбобокса
	Object.defineProperty(combobox, 'pattern',
		{
			get: function() { return inputField.pattern; },
			set: function(val) { inputField.pattern = val; }
		});

	//Получение/установка placeholder комбобокса
	Object.defineProperty(combobox, 'placeholder',
		{
			get: function() { return inputField.placeholder; },
			set: function(val) { inputField.placeholder = val; }
		});

	//Получение/установка id комбобокса
	//Но на самом деле ставит id для поля ввода, чтобы можно было сделать label
	Object.defineProperty(combobox, 'id',
		{
			get: function() { return inputField.id; },
			set: function(val) { inputField.id = val; }
		});

	//Получение/установка title комбобокса
	Object.defineProperty(combobox, 'title',
		{
			get: function() { return inputField.title; },
			set: function(val) { inputField.title = val; }
		});

	//Получение/установка обязательности запомления комбобокса
	Object.defineProperty(combobox, 'required',
		{
			get: function() { return inputField.required; },
			set: function(val) { inputField.required = val; }
		});

	//Добавляем в комбобокс поле ввода данных и контейнер для подсказок
	combobox.appendChild(inputField);
	combobox.appendChild(hintsContainer);

	/**
	 * Функция удаления подсказок из контейнера
	 */
	function removeHints()
	{
		while(hintsContainer.children.length > 0)
		{
			hintsContainer.removeChild(hintsContainer.children[0]);
		}
	}

	/**
	 * Функция добавления подсказок
	 */
	function addHints()
	{
		//Получаем длину массива данных
		var len = getDataArray().length;

		//Кол-во добавленных подсказок
		var count = 0;

		for(var i = 0; i < len && count < params.hintsCount; i++)
		{
			//Значение из массива данных
			var value = getDataArrayValue(i);

			if(inputField.value && stringContains(inputField.value.toLowerCase(), value.toLowerCase()))
			{
				var hint = document.createElement('span');

				hint.addEventListener('mousedown', function(val)
				{
					return function() { updateInputValue(val); };
				}(value));

				hint.appendChild(document.createTextNode(value));
				hintsContainer.appendChild(hint);

				count++;
			}
		}
	}

	/**
	 * Обновление видимости контейнера подсказок и сброс выбранной подсказки
	 */
	function updateHintsContainer()
	{
		//Если добавлена хотябы одна подсказка и есть фокус в поле, отображаем контейнер с подсказками
		setHintsVisible(hintsContainer.children.length > 0 && document.activeElement === inputField);

		//Сбиваем индекс выделенной подсказки
		selHintIndex = -1;
	}

	//Обработка события ввода текста в текстовое поле
	inputField.addEventListener('input', function()
	{
		//Если используем DataList
		if(useDataList)
		{
			//Устанавливаем параметр поиска по введенному значению
			var newParam                         = {};
			newParam[params.dataElementProperty] = inputField.value;
			newParam.m_count                     = params.hintsCount; //Нужно записей не больше, чем мы можем вывести подсказок на экран
			params.data.updateLoadParameters(newParam);

			//Перезагружаем список
			params.data.load(function()
			{
				removeHints();
				addHints();
				updateHintsContainer();
			});
		}
		else
		{
			//Удаляем все подсказки
			removeHints();

			//Добавляем новые подсказки
			addHints();

			//Обновляем видимость контейнера подсказок и сбрасываем индекс выбранной
			updateHintsContainer();
		}
	});

	//Обработка события нажатия клавиши при активном фокусе текст. поля
	inputField.addEventListener('keydown', function(e)
	{
		//Если нет посказок - ничего не делаем
		if(!hintsContainer.children.length)
		{ return; }

		if(e.keyCode === 38 || e.keyCode === 40)
		{
			//Чтобы не перемещать курсор между символами в поле ввода клавишами вверх и вниз
			e.preventDefault();

			if(hintsContainer.children[selHintIndex])
			{
				removeClass(hintsContainer.children[selHintIndex], 'selected');
			}

			if(e.keyCode === 38)		//up
			{ selHintIndex--; }
			else if(e.keyCode === 40)	//down
			{ selHintIndex++; }

			if(selHintIndex < 0)
			{
				//Ограничиваем мин. значением
				selHintIndex = -1;
			}
			else
			{
				//Ограничиваем макс. значением
				if(selHintIndex > hintsContainer.children.length - 1)
				{
					selHintIndex = hintsContainer.children.length - 1;
				}

				addClass(hintsContainer.children[selHintIndex], 'selected');
			}
		}
		else if(e.keyCode === 13) //Enter
		{
			//Чтобы не совершать отправку данных формы по Enter
			e.preventDefault();

			if(selHintIndex >= 0)
			{
				updateInputValue(hintsContainer.children[selHintIndex].innerHTML);
				inputField.blur();
			}
		}
	});

	//Обработка события получения тестовым полем фокуса
	inputField.addEventListener('focus', function()
	{
		inputField.select();
		setHintsVisible(hintsContainer.children.length > 0);
	});

	//Обработка события потери фокуса текстовым полем
	inputField.addEventListener('blur', function()
	{
		setHintsVisible(false);
	});

	//Добавляем функцию отдельной проверки только вслучае если используется массив объектов и нужно вернуть конкретное поле данных
	if(params.dataReturnProperty)
	{
		/**
		 * Функция дополнительного тестирования на точное совпадение введенного значения
		 * @return {boolean|string} Результат тестирования. True - если тестирование прошло успешно. В противном случае возвращает тест ошибки
		 */
		combobox.testValid = function()
		{
			if(inputField.required)
			{
				if(!inputField.value.length)
				{
					return 'Поле должно быть заполнено!';
				}

				var found = false,
					array = getDataArray();

				//Ищем совпадение введенного значение с полем одного из элементов массива данных
				for(var i = 0; i < array.length; i++)
				{
					var value = getDataArrayValue(i);

					if(value === inputField.value)
					{
						found = true;
						break;
					}
				}

				if(!found)
				{ return 'Значение поля должно совпадать с одним из предложенных!'; }
			}

			return true;
		};
	}

	//Далее все обработчики событий, которые будут добавляться объекту combobox, на самом деле будут добавляться inputField
	combobox.addEventListener = function(event, callback, bub)
	{
		inputField.addEventListener(event, callback, bub);
	};

	return combobox;
}