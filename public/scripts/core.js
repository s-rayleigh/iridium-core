/**
 * Отправляет запрос серверу и получает ответ.
 * @param {string} method Метод доступа (POST/GET).
 * @param {string} path Адрес запроса.
 * @param {object} params Параметры запроса.
 * @param {function} [handler] Обработчик ответа на запрос.
 * @param {boolean} [parse = true] Нужно-ли парсить json.
 */
function sendRequest(method, path, params, handler, parse)
{
	var xmlHttp = new XMLHttpRequest(),
		isGet   = method == 'GET';

	parse = parse === undefined;

	xmlHttp.open(method, path + (isGet ? params : ""), true);
	xmlHttp.onreadystatechange = function()
	{
		if(xmlHttp.readyState === 4 && xmlHttp.status === 200)
		{
			var response = parse ? JSON.parse(xmlHttp.responseText) : xmlHttp.responseXML;
			if(typeof response === 'object' && 'error' in response)
			{
				displayPopupError(response);
			}
			else if(typeof handler == 'function')
			{
				handler(response);
			}
		}
	};

	if(!isGet)
	{
		xmlHttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

		if(typeof params === 'object')
		{
			params = objectToPostData(params);
		}
	}

	xmlHttp.send(isGet ? null : params);
}

function displayPopupError(error)
{
	if(typeof Popup === 'function')
	{
		(new Popup({
			content: error.error,
			title: error.title,
			overlay: true,
			closeCross: true,
			buttons: [{text: 'Ок'}]
		})).show();
	}
	console.error(error);
}

/**
 * Генерирует случайный идентификатор с заданной длиной.
 * @param {int} [length = 5] Длина идентификатора.
 * @return {string} Сгенерированный идентификатор.
 */
function randomId(length)
{
	length = length === undefined ? 5 : length;

	var result        = '',
		min = 33, max = 126; //Диапазон кодов символов

	for(var i = 0; i < length; i++)
	{
		result += String.fromCharCode(Math.floor(Math.random() * (max - min + 1) + min));
	}

	return result;
}

/**
 * Преобразует число типа float в тип int.
 * @param {float} floatNum Число типаа float.
 * @return {int} Число типа int.
 */
function floatToInt(floatNum)
{
	return floatNum | 0;
}

/**
 * Проверяет входит-ли подстрока в строку
 * @param  {string} needle    Подстрока, которая предположительно содержиться в heystack
 * @param  {string} haystack    Строка, в которой предположительно содержиться подстрока
 * @return {boolean}            true - если needle входит в haystack
 */
function stringContains(needle, haystack)
{
	return haystack.indexOf(needle) > -1;
}

/**
 * Добавляет класс объекту.
 * @param {object} object
 * @param {string} className
 */
function addClass(object, className)
{
	if(!object.className)
	{
		object.className = className;
		return;
	}
	if(stringContains(className, object.className))
	{ return; }
	object.className += ' ' + className;
}

/**
 * Удаляет класс объекта.
 * @param {object} object
 * @param {string} className
 */
function removeClass(object, className)
{
	object.className = object.className.replace(new RegExp('(^|\\s)' + className + '(?!\\S)'), '');
}

/**
 * Преобразует объект с параметрами POST в строку для отправки посредством AJAX
 * @param  {object} params Объект с параметрами POST
 * @return {string}        Строка с параметрами POST
 */
function objectToPostData(params)
{
	var str = '';

	for(var i in params)
	{
		if(Array.isArray(params[i]))
		{
			params[i].forEach(function(el, j)
			{
				str += encodeURIComponent(i) + '[' + encodeURIComponent(j.toString()) + ']=' + encodeURIComponent(el) + '&';
			});
		}
		else
		{
			str += encodeURIComponent(i) + '=' + encodeURIComponent(params[i]) + '&';
		}
	}

	return str.slice(0, -1);
}

/**
 * Обновляет свойства объекта на основании свойств второго объекта.
 * Также удаляет свойство объекта, если передан null.
 * @param {object} object Объект, который нужно обновить.
 * @param {object} params Новые свойства объекта.
 * @param {boolean} [addNew] Добавлять новые свойства.
 */
function updateObject(object, params, addNew)
{
	if(typeof params !== 'object')
	{ return; }
	if(addNew === undefined)
	{ addNew = true; }

	for(var key in params)
	{
		if(object.hasOwnProperty(key) || addNew)
		{
			if(params[key] == null)
			{
				delete object[key];
			}
			else
			{
				object[key] = params[key];
			}
		}
	}
}

/**
 * Рекурсивно копирует объект.
 * @see http://stackoverflow.com/questions/728360/most-elegant-way-to-clone-a-javascript-object
 */
function clone(obj)
{
	var copy;

	//Обрабатываем 3 простых типа случая
	if(obj === null || obj === undefined || typeof obj !== 'object')
	{
		return obj;
	}

	//Обрабатываем как дату
	if(obj instanceof Date)
	{
		copy = new Date();
		copy.setTime(obj.getTime());
		return copy;
	}

	//Обрабатываем как массив рекурсивно
	if(obj instanceof Array)
	{
		copy = [];

		for(var i = 0, len = obj.length; i < len; i++)
		{
			copy[i] = clone(obj[i]);
		}

		return copy;
	}

	//Обрабатываем как объект
	if(obj instanceof Object)
	{
		copy = {};

		for(var attr in obj)
		{
			if(obj.hasOwnProperty(attr))
			{
				copy[attr] = clone(obj[attr]);
			}
		}

		return copy;
	}

	throw new Error("Невозможно скопировать объект. Неподдерживаемый тип!");
}

/**
 * Проверяет является-ли объект элементом HTML.
 * @param {object} obj Объект.
 * @returns {boolean} True, если объект является элементом HTML.
 */
function isElement(obj)
{
	if(typeof HTMLElement === 'object')
	{
		return obj instanceof HTMLElement;
	}

	return !!obj && typeof obj.nodeName === 'string';
}

/**
 * Создает крестик закрытия в формате svg
 */
function createCloseCross()
{
	var xmlns = 'http://www.w3.org/2000/svg';

	var cross = document.createElementNS(xmlns, 'svg');
	cross.setAttributeNS('', 'viewBox', '0 0 16 16');

	var g = document.createElementNS(xmlns, 'g');
	g.setAttributeNS('', 'stroke-width', '3');
	g.setAttributeNS('', 'stroke-linecap', 'round');

	var path1 = document.createElementNS(xmlns, 'path'),
		path2 = document.createElementNS(xmlns, 'path');

	path1.setAttributeNS('', 'd', 'M 2 2 L 14 14');
	path2.setAttributeNS('', 'd', 'M 2 14 L 14 2');

	g.appendChild(path1);
	g.appendChild(path2);

	cross.appendChild(g);
	cross.setAttribute('class', 'close-cross unselectable');

	return cross;
}

/**
 * Добавляет в массив элементы переданного массива без создания нового массива
 * @param {Array} array Массив элементов, которые необходимо добавить
 */
Array.prototype.pushArray = function(array)
{
	if(typeof array !== 'object')
	{ return; }
	for(var i = 0; i < array.length; i++)
	{
		this.push(array[i]);
	}
};

/**
 * Добавляет переданный элемент после текущего
 * @param {Node} element Элемент.
 */
Element.prototype.insertAfter = function(element)
{
	//Надо помнить, что эта фигня не будет работать, если this - последний элемент родителя...
	this.parentNode.insertBefore(element, this.nextSibling);
};

Node.prototype.killChild = function(child, dispatch)
{
	if(dispatch)
	{
		dispatchAllChildEvent(child, new MouseEvent('mouseout'));
	}

	this.removeChild(child);
};

function dispatchAllChildEvent(obj, event)
{
	for(var i = 0; i < obj.children.length; i++)
	{
		obj.children[i].dispatchEvent(event);
		dispatchAllChildEvent(obj.children[i], event);
	}
}

/**
 * Преобразовывает кол-во байт в сокращенный вариант с двоичными приставками МЭК.
 * @param {int} bytes Кол-во байт.
 * @return {string} Сокращенный вариант с приставками МЭК.
 * @author rayleigh <rayleigh@protonmail.com>
 */
function readableSize(bytes)
{
	var quantities = ['Б', 'КиБ', 'МиБ', 'ГиБ', 'ТиБ', 'ПиБ'], result = bytes, i;

	for(i = 0; result >= 1024;)
	{
		result = bytes / Math.pow(2, ++i * 10);
	}

	//Приводим result к числовому типу, приводим к одному знаку после запятой и опять приводим к числу
	return +(+result).toFixed(1) + ' ' + quantities[i];
}

/**
 * Регистрирует новый элемент.
 * @param elementName Название элемента.
 */
function registerCustomElement(elementName)
{
	if(document.registerElement)
	{
		document.registerElement(elementName);
		console.log('Зарегистрирован элемент ' + elementName + '.');
	}
}

/**
 * Переадресовывает на указанный URL.
 * @param {string} url URL.
 */
function goto(url)
{
	window.location = url;
}

if(!String.prototype.endsWith)
{
	/**
	 * Определяет заканчивается-ли строка подстрокой searchString.
	 * @param {string} searchString
	 * @param {int} [position]
	 * @returns {boolean}
	 */
	String.prototype.endsWith = function(searchString, position)
	{
		var subjectString = this.toString();

		if(position === undefined || position > subjectString.length)
		{
			position = subjectString.length;
		}

		position -= searchString.length;
		var lastIndex = subjectString.indexOf(searchString, position);
		return lastIndex !== -1 && lastIndex === position;
	};
}

if(!Array.prototype.includes)
{
	/**
	 * Определяет, содержит ли массив определённый элемент, возвращая в зависимости от этого true или false.
	 * @param {*} searchElement Искомый элемент
	 * @returns {boolean}
	 */
	Array.prototype.includes = function(searchElement)
	{
		return this.indexOf(searchElement) !== -1;
	};
}