/**
 * Модуль добавляет возможность организовать выделение элементов списка
 * @module list_selection
 */

/**
 * @constructor
 * @param {object} newParams Параметры выделения списка.
 */
function ListSelection(newParams)
{
	//Стандартные параметры
	var params = {
		parent: null,						//Родительский элемент списка
		elementsClass: '',					//Класс элементов списка
		selectByClick: true,				//Нужно-ли делать выбор элемента по клику
		
		onSelect: function(obj) { },		//Функция, которая будет вызвана при выборе объекта
		onUnselect: function(obj) { },		//Функция, которая будет вызвана при отмене выбора объекта
		onFirstSelect: function(obj) { },	//Функция, которая будет вызвана при выборе первого объекта
		onAllUnselect: function() { }		//Функция, которая будет вызвана при отмене выбора всех объектов
	};

	updateObject(params, newParams);

	if(params.parent === null || params.parent === undefined)
	{
		console.error('Не задан родительский объект списка для создания объекта выделения!');
		return;
	}

	var selected = [],
		listSelection = this;
	
	/**
	 * @prop {Array} selected Возвращает список выбранных элементов списка.
	 */
	Object.defineProperty(this, 'selected', {
		get: function() { return selected; }
	});

	/**
	 * Добавляет всем дочерним элементам возможность выделения
	 */
	this.updateSelection = function()
	{
		var elements = params.parent.getElementsByClassName(params.elementsClass);

		for(var i = 0; i < elements.length; i++)
		{
			var child = elements[i];
			listSelection.addSelectPossibility(child);
		}
	};
	
	/**
	 * Добавляет объекту возможность выделения
	 * @param {object} obj Объект
	 */
	this.addSelectPossibility = function(obj)
	{
		/**
		 * Выделяет данный объект и добавляет его в список выбранных
		 */
		obj.select = function()
		{
			//Вызываем callback функцию выбора
			params.onSelect(obj);
			
			//Вызываем callback функцию выбора первого элемента
			if(selected.length === 0)
			{
				params.onFirstSelect(obj);
			}

			//Добавляем класс выделения
			addClass(obj, 'selected');

			//Добавляем в массив выбранных элементов
			selected.push(obj);
		};

		/**
		 * Снимает выделение с объекта и удаляет его из списка выбранных
		 */
		obj.unselect = function()
		{
			//Вызываем callback функцию отмены выбора
			params.onUnselect(obj);

			//Удаляем класс выделения
			removeClass(obj, 'selected');

			//Удаляем елемент из массива выбранных элементов
			var index = selected.indexOf(obj);
			if(index >= 0)
			{
				selected.splice(index, 1);
			}
			
			//Вызываем callback функцию отмены выбора последнего элемента
			if(selected.length === 0)
			{
				params.onAllUnselect();
			}
		};
		
		//Если нужно делать выбор по клику
		if(params.selectByClick)
		{
			obj.addEventListener('click', function()
			{
				if(listSelection.objectSelected(obj))
				{
					obj.unselect();
				}
				else
				{
					obj.select();
				}
			});
		}
	};
	
	/**
	 * Проверяет выбран-ли объект
	 * @param {object} obj Объект для проверки
	 * @return {boolean} true, если объект выбран
	 */
	this.objectSelected = function(obj)
	{
		return selected.indexOf(obj) >= 0;	
	};

	/**
	 * Очищает выделение без вызова callback функции
	 */
	this.clearSelection = function()
	{
		selected.forEach(function(element)
		{
			removeClass(element, 'selected');
		});

		selected.length = 0;
		
		params.onAllUnselect();
	};
}