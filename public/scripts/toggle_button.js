/**
 * Кнопка-переключатель.
 * @module toggle_button
 */

window.addEventListener('load', function() { registerCustomElement('toggle-button'); });

/**
 * Создает кнопку-переключатель.
 * @param parameters Параметры кнопки-переключателя.
 * @returns {Element} Объект кнопки-переключателя.
 * @author rayleigh <rayleigh@protonmail.ch>
 */
function createToggleButton(parameters)
{
	var params = {
		activeClass: 'tb-active',		//Класс активной кнопки.
		unactiveClass: 'tb-unactive',	//Класс неактивной кнопки.
		activated: false,				//Активирована изначально.
		activeContent: null,			//Объект (или массив объектов), который отображается при активной кнопке.
		unactiveContent: null,			//Объект (или массив объектов), который отображается при неактивной кнопке.
		onToggle: function() { },		//Функция, которая вызывается при нажатии на кнопку.
		onTurnOn: function() { },		//Функция, которая вызывается при включении кнопки.
		onTurnOff: function() { }		//Функция, которая вызывается при отключении кнопки.
	};

	updateObject(params, parameters);

	var toggle = document.createElement('toggle-button');

	toggle.tbActive = false;

	if(params.activated)
	{ turnOn(false); }
	else
	{ turnOff(false); }

	toggle.addEventListener('click', function(event)
	{
		event.preventDefault();
		event.stopPropagation();

		if(toggle.tbActive)
		{ turnOff(); }
		else
		{ turnOn(); }
	});

	toggle.tbTurnOn  = turnOn;
	toggle.tbTurnOff = turnOff;

	function turnOn(triggerEvents)
	{
		triggerEvents = triggerEvents === undefined ? true : triggerEvents;

		toggle.tbActive = true;
		addClass(toggle, params.activeClass);
		removeClass(toggle, params.unactiveClass);

		show(params.activeContent);
		hide(params.unactiveContent);

		if(triggerEvents)
		{
			params.onToggle();
			params.onTurnOn();
		}
	}

	function turnOff(triggerEvents)
	{
		triggerEvents = triggerEvents === undefined ? true : triggerEvents;

		toggle.tbActive = false;
		addClass(toggle, params.unactiveClass);
		removeClass(toggle, params.activeClass);

		show(params.unactiveContent);
		hide(params.activeContent);

		if(triggerEvents)
		{
			params.onToggle();
			params.onTurnOff();
		}
	}

	function show(element)
	{
		if(!element) { return; }

		if(Array.isArray(element))
		{
			element.forEach(function(el)
			{
				show(el);
			});

			return;
		}

		element.style.display = '';
	}

	function hide(element)
	{
		if(!element) { return; }

		if(Array.isArray(element))
		{
			element.forEach(function(el)
			{
				hide(el);
			});

			return;
		}

		element.style.display = 'none';
	}

	return toggle;
}