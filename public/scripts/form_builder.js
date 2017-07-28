/**
 * Построитель форм.
 * @param {Array} content Содержание формы.
 * @param {string} [method = 'POST'] HTTP метод отправки данных формы (GET/POST).
 * @param {string} [action] URL отправки данных формы.
 * @constructor
 * @author rayleigh <rayleigh@protonmail.com>
 */
function FormBuilder(content, method, action)
{
	if(!Array.isArray(content))
	{
		console.error('Ошибка входных параметров построителя форм!');
		return;
	}

	var needValidation = false,
		validationCallback;

	/**
	 * Выполняет построение формы.
	 * @param {string} [id] Идентификатор формы.
	 * @returns {Element} DOM элемент формы.
	 */
	this.build = function(id)
	{
		var form = document.createElement('form');

		if(id)
		{
			form.id = id;
		}

		if(action)
		{
			form.action = action;
		}

		form.method = method || 'POST';

		function setIfNotNull(value, object, variable)
		{
			if(!value)
			{
				return;
			}

			object[variable] = value;
		}

		content.forEach(function(obj)
		{
			//Если объект является элементом HTML, добавляем его в форму
			if(isElement(obj))
			{
				form.appendChild(obj);
				return;
			}

			var elementObject;

			if(obj.tag === 'combo-box')
			{
				elementObject = createCombobox(obj);
			}
			else if(obj.tag === 'file-uploader')
			{
				elementObject = createFileUploader(obj);
			}
			else
			{
				elementObject = document.createElement(obj.tag || 'input');
			}

			elementObject.type = obj.type || 'text';
			elementObject.id   = obj.id || randomId();

			setIfNotNull(obj.value, elementObject, 'value');
			setIfNotNull(obj.class, elementObject, 'className');
			setIfNotNull(obj.name, elementObject, 'name');
			setIfNotNull(obj.pattern, elementObject, 'pattern');
			setIfNotNull(obj.placeholder, elementObject, 'placeholder');
			setIfNotNull(obj.title, elementObject, 'title');
			setIfNotNull(obj.min, elementObject, 'min');
			setIfNotNull(obj.max, elementObject, 'max');
			setIfNotNull(obj.step, elementObject, 'step');
			setIfNotNull(obj.checked, elementObject, 'checked');

			//Булевы атрибуты
			if(Array.isArray(obj.boolAttr))
			{
				obj.boolAttr.forEach(function(attr)
				{
					elementObject[attr] = true;
				});
			}

			if(obj.label)
			{
				var labelObject       = document.createElement('label');
				labelObject.innerHTML = obj.label;
				labelObject.htmlFor   = elementObject.id;

				//В случае, если тип объекта - чекбокс, то сначала добавляется объект, а потом подпись
				if(obj.type === 'checkbox')
				{
					form.appendChild(elementObject);
					form.appendChild(labelObject);
				}
				else
				{
					form.appendChild(labelObject);
					form.appendChild(elementObject);
				}
			}
			else
			{
				form.appendChild(elementObject);
			}
		});

		//Добавляем валидацию формы, если указано
		if(needValidation)
		{
			addFormValidation(form, validationCallback);
		}

		return form;
	};

	this.addValidation = function(callback)
	{
		if(typeof callback !== 'function')
		{
			console.error('Параметр callback должен быть функцией!');
			return;
		}
		if(typeof addFormValidation !== 'function')
		{
			console.error('Не подключен скрипт валидации формы!');
			return;
		}

		needValidation     = true;
		validationCallback = callback;
	};
}