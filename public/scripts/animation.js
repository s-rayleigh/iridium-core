/**
 * Анимирует свойства объектов.
 * @author rayleigh <rayleigh@protonmail.com>
 */
var Animation = 
{
	/**
	 * ВременнЫе функции.
	 *
	 * Для данных функций должны соблюдаться следующие условия:
	 * f(0) = 0, f(1) = 1
	 */
	timeFunctions:
	{
		linear: function(x) { return x; },				//y = x
		quad: function(x) { return Math.pow(x, 2); },	//y = x^2
		sqrt: function(x) { return Math.sqrt(x); }		//y = sqrt(x)
	},

	/**
	 * Набор стандартных анимаций.
	 */
	animations:
	{
		fadeIn: {
			start: {opacity: 0},
			end: {opacity: 1}
		},
		fadeOut: {
			start: {opacity: 1},
			end: {opacity: 0}
		}
	},

	/**
	 * Задает анимацию объекту.
	 * @param {object} newParams Параметры анимации.
	 * @param {string|object} [newParams.animation]
	 */
	animate: function(newParams)
	{
		var params = {
			animation: 'fadeIn',	//Название функции анимации
			timeDirection: 'in',	//Направление действия функции анимации (in/out/inout)
			timeFunction: 'linear',	//Временная функция
			duration: 1000,			//Время анимации в мс
			repeat: false,			//Нужно-ли повторять анимацию
			onEnd: function() {}	//Функция, которая вызывается по окончанию анимации
		};

		updateObject(params, newParams);

		if(params.obj === null || params.obj === undefined)
		{
			console.error('Не задан объект для выполнения анимации!');
			return;
		}

		if(typeof Animation.timeFunctions[params.timeFunction] !== 'function')
		{
			console.error('Задана несуществующая временная функция "' + params.timeFunction + '" для анимации!');
			return;
		}

		var animation;

		//Получаем объект со свойствами для анимации
		if(typeof params.animation === 'object')
		{
			animation = params.animation;
		}
		else if(typeof params.animation === 'string' && 
				typeof Animation.animations[params.animation] === 'object')
		{
			//Копируем объект, чтобы не изменять исходные данные
			animation = clone(Animation.animations[params.animation]);
		}
		else
		{
			console.error('Свойства анимации заданы неправильно!');
			return;
		}
		
		if(animation.start === undefined && animation.end === undefined)
		{
			console.error('Нужно задать start и/или end для анимации.');
			return;
		}
		
		//Подготавливаем свойства анимации
		prepareAnimationValues(params.obj, animation);
		
		//Время начала анимации
		var startTime = performance.now();

		requestAnimationFrame(function animationFrame(time)
		{
			//Частичное время анимации (процент прохождения анимации) [0; 1]
			var fractionTime = (time - startTime) / params.duration;
			if(fractionTime > 1) { fractionTime = 1; }

			//Зависимость прогресса анимации от времени анимации
			var progress;
			
			switch(params.direction)
			{
				default:
				case 'in':
					progress = Animation.timeFunctions[params.timeFunction](fractionTime);
					break;
				case 'out':
					progress = 1 - Animation.timeFunctions[params.timeFunction](1 - fractionTime);
					break;
				case 'inout':
					if(fractionTime < 0.5)
					{
						progress = Animation.timeFunctions[params.timeFunction](fractionTime * 2) / 2;
					}
					else
					{
						progress = (2 - Animation.timeFunctions[params.timeFunction]((1 - fractionTime) * 2)) / 2;
					}
					break;
			}

			for(var prop in animation.start)
			{
				params.obj.style[prop] = (animation.start[prop].value + animation.difference[prop].value * progress) + animation.start[prop].unit;
			}

			//Отрисовка кадра анимации
			//animationFunction(params.obj, progress);

			//Пока частичное время не достигло 1 - продолжаем анимацию
			//Также продолжаем при повторе
			if(fractionTime < 1)
			{
				requestAnimationFrame(animationFrame);
			}
			else if(params.repeat) //Циклический повтор анимации
			{
				startTime = performance.now();
				requestAnimationFrame(animationFrame);		
			}
			else
			{
				//callback функция по завершению анимации
				params.onEnd();
			}
		});
	}
};

/**
 * Подготавливает свойства анимации для их использования
 * @param	{object} obj		Объект анимации
 * @param	{object} animation	Объект со свойствами анимации
 * @return	{object}			Объект с подготовленными свойствами анимации
 */
function prepareAnimationValues(obj, animation)
{
	//Если не задан start или end - создаем их
	if(animation.start === undefined)
	{
		animation.start = {};
	}
	else if(animation.end === undefined)
	{
		animation.end = {};
	}
	
	//Разница start и end
	animation.difference = {};
	
	var prop;
	
	for(prop in animation.end)
	{
		//Свойства, которые отсутсвуют в start копируем из end. Значение берем из стиля объекта
		if(!animation.start.hasOwnProperty(prop))
		{
			animation.start[prop] = getObjectStyleValue(obj, prop);
		}
	}
	
	for(prop in animation.start)
	{
		//Свойства, которые отсутсвуют в end копируем из start. Значение берем из стиля объекта
		if(!animation.end.hasOwnProperty(prop))
		{
			animation.end[prop] = getObjectStyleValue(obj, prop);
		}
		
		//Разделяем числовое значение и единицы измерения у значения свойства
		animation.start[prop]	= splitPropValue(animation.start[prop]);
		animation.end[prop]		= splitPropValue(animation.end[prop]);
		
		//Сопоставляем единицы измерения
		if(animation.start[prop].unit !== animation.end[prop].unit)
		{
			if(!animation.start[prop].unit.length)
			{
				animation.start[prop].unit = animation.end[prop].unit;
			}
			else
			{
				animation.end[prop].unit = animation.start[prop].unit;
			}
		}
		
		//Разница значений анимации
		animation.difference[prop] = {
			value: animation.end[prop].value - animation.start[prop].value,
			unit: animation.start[prop].unit	
		};
	}
}

/**
 * Разеляет значение свойства css на числовое значение и единицы измерения
 * @param	{string} value	Строковое представление значения свойства
 * @return	{object}		Объект с разделенным числовым значением и единицами измерения
 */
function splitPropValue(value)
{
	if(typeof value === 'number')
	{
		return {value: value, unit: ''};
	}
	
	//Ищем справа налево где заканчивается числовое значение
	for(var i = value.length; i > 0; i--)
	{
		if(value[i] <= '9' && value[i] >= '0') { break; }
	}
	
	return {
		value: parseFloat(value.substring(0, ++i)),	//Числовое значение
		unit: value.substring(i)					//Единицы изменения
	};
}

/**
 * Возвращает значение свойства стиля объекта
 * @param	{object}	obj			Объект, свойство которого необходимо получить
 * @param	{string}	propName	Имя свойства
 * @return	{string}				Значение свойства объекта или пустую строку, если нет возможности получить значение свойства
 */
function getObjectStyleValue(obj, propName)
{
	if(obj.style[propName]) //style="" (html)
	{
		return obj.style[propName];
	}
	else if(obj.currentStyle) //IE css
	{
		return obj.currentStyle[propName];
	}
	else if(window.getComputedStyle) //css
	{
		return window.getComputedStyle(obj).getPropertyValue(propName);
	}
	else
	{
		return '';
	}
}