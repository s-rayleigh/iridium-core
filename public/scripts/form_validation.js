//Перемещение блока ошибки при изменении размеров окна
window.addEventListener('resize', function()
{
	var forms = document.getElementsByTagName('form');

	for(var i = 0; i < forms.length; i++)
	{
		if(forms[i].errorObject === undefined
			|| forms[i].errorEventListener === undefined
			|| forms[i].errorField === undefined)
		{ continue; }

		placeErrorObject(forms[i].errorObject, forms[i], forms[i].errorField);
	}
});

//Функция добавления валидации для формы при событии submit
function addFormValidation(form, responseHandler)
{
	if(form == null)
	{
		console.error('Не задана форма для валидации!');
		return;
	}

	//Отключаем стандартную проверку поля
	form.setAttribute('novalidate', '');

	form.addEventListener('submit', function(e)
	{
		e.preventDefault();

		//Очищаем страницу от предыдущей ошибки формы (если есть)
		clearFormError(form);

		//Проверяем форму на ошибки и, если ошибки найдены, отображаем их на странице
		if(!validateForm(form))
		{ return; }

		//Посылаем запрос
		sendFormData(form, responseHandler);
	});
}

//Функция проверки формы на ошибки и их отображение
function validateForm(form)
{
	clearFormError(form);

	var formElements = getFormElements(form);

	for(var i = 0; i < formElements.length; i++)
	{
		var element = formElements[i];

		//Не проверяем поля только для чтения, выключенные поля, а также поле подтвержение (кнопка)
		//Также пропускаем все остальные элементы
		if(element.readOnly || element.disabled || element.type === 'submit')
		{ continue; }

		if(typeof element.testValid === 'function')
		{
			var testResult = element.testValid();

			if(testResult !== true)
			{
				printError(form, element, testResult);
				return false;
			}
			else
			{
				continue;
			}
		}

		//Проверяем поле на заполнение, если требуется
		if(element.required && !element.value)
		{
			printError(form, element, 'Требуется заполнить это поле!');
			return false;
		}

		//Проверяем поле на соответствие шаблону
		if(element.pattern)
		{
			var regExp = new RegExp(element.pattern);
			if(!regExp.test(element.value))
			{
				printError(form, element, 'Ввведите данные в требуемом формате: <br>' + element.title);
				return false;
			}
		}

		if(element.type === 'number')
		{
			var value = parseInt(element.value);

			if(element.min.length && value < element.min)
			{
				printError(form, element, 'Число должно быть не меньше ' + element.min);
				return false;
			}

			if(element.max.length && value > element.max)
			{
				printError(form, element, 'Число должно быть не больше ' + element.max);
				return false;
			}
		}
	}

	return true;
}

/**
 * Отправляет данные формы используя AJAX.
 * @param {object} form Форма.
 * @param {function} responseHandler Функция обработки ответа.
 */
function sendFormData(form, responseHandler)
{
	var sendData = {};

	getFormElements(form).forEach(function(element)
	{
		if(element.disabled || !element.value || element.type === 'checkbox' && !element.checked)
		{
			return;
		}

		if(element.name.endsWith('[]'))
		{
			var arrName = element.name.slice(0, -2);

			if(!Array.isArray(sendData[arrName]))
			{
				sendData[arrName] = [];
			}

			sendData[arrName].push(element.value);

			return;
		}

		sendData[element.name] = element.value;
	});

	sendRequest(form.method, form.action, sendData, responseHandler);
}

//Функция вывода блока ошибки
function printError(form, field, error)
{
	var errorObj = document.createElement('div');

	//Создаем объект ошибки и задаем ему свойства
	errorObj.className = 'field-error';
	errorObj.innerHTML = error;

	form.appendChild(errorObj);

	placeErrorObject(errorObj, form, field);

	var fieldEventListener = function()
	{
		clearFormError(form);
		field.removeEventListener('mouseenter', fieldEventListener);
	};

	//Добавляем обработчик события, для того, чтобы если поле получило фокус, то ошибка пропала
	field.addEventListener('mouseenter', fieldEventListener);

	//Запоминаем необходимые данные для удаления обработчика события
	form.errorEventListener = fieldEventListener;
	form.errorField         = field;
	form.errorObject        = errorObj;
}

//Функция удаления блока ошибки формы
function clearFormError(form)
{
	if(form.errorObject === undefined ||
		form.errorEventListener === undefined ||
		form.errorField === undefined)
	{ return; }

	form.errorField.removeEventListener('mouseenter', form.errorEventListener);
	form.removeChild(form.errorObject);
	delete form.errorObject;
}

//Функция расположения блока ошибки
function placeErrorObject(errorObj, form, field)
{
	var fieldRect = field.getBoundingClientRect(),
		formRect  = form.getBoundingClientRect();

	errorObj.style.top  = (fieldRect.bottom - formRect.top + 10) + 'px';
	errorObj.style.left = (fieldRect.left - formRect.left) + 'px';
}

/**
 * Возвращает массив элементов формы.
 * @param {object} form    Форма, элементы которой нужно получить.
 * @returns {Array} Массив элементов формы.
 */
function getFormElements(form)
{
	var result            = [],
		tags              = ['input', 'textarea', 'select', 'combo-box', 'file-uploader'],
		excludeChildsTags = ['combo-box', 'file-uploader'];

	for(var i = 0; i < form.children.length; i++)
	{
		if(tags.indexOf(form.children[i].tagName.toLowerCase()) >= 0)
		{
			result.push(form.children[i]);
		}
		else if(form.children[i].children.length > 0)
		{
			result.pushArray(getFormElements(form.children[i]));
		}
	}

	return result;
}