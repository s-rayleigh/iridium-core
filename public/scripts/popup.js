var lastPopup;

/**
 * Создает всплывающее окно.
 *
 * @param {object} properties Параметры всплывающего окна.
 *
 * @param {string|Element} properties.content Содержание.
 * @param {string} [properties.title] Заголовок.
 * @param {boolean} [properties.overlay = true] Затемнение фона всплывающего окна.
 * @param {boolean} [properties.closeCross = false] Крестик закрытия окна.
 *
 * @param {object[]} [properties.buttons] Массив кнопок.
 * @param {string} [properties.buttons[].text] Текст кнопки.
 * @param {function} [properties.buttons[].action] Действие при нажатии кнопки.
 * @param {boolean} [properties.buttons[].hide = true] Нажатие кнопки закроет окно.
 *
 * @param {function} [properties.onHide] Действие при закрытии окна.
 *
 * @constructor
 *
 * @author rayleigh <rayleigh@protonmail.com>
 */
function Popup(properties)
{
	/**
	 * Параметры всплывающего окна по умолчанию.
	 * @type {{content: string|Element, title: string, overlay: boolean, closeCross: boolean, buttons: object[]|null, onHide: function|null}}
	 */
	var parameters = {
		content: '',
		title: '',
		buttons: null,
		overlay: true,
		closeCross: false,
		onHide: null
	};

	updateObject(parameters, properties, false);

	var popup     = this,
		displayed = false,
		popupObject,
		overlayObject;

	/**
	 * Обновляет положение всплывающего окна, располагая его по центру области просмотра.
	 */
	function updatePopupPosition()
	{
		popupObject.style.top  = (document.body.offsetHeight / 2 - popupObject.offsetHeight / 2) + 'px';
		popupObject.style.left = (document.body.offsetWidth / 2 - popupObject.offsetWidth / 2) + 'px';
	}

	/**
	 * Отображает всплывающее окно.
	 */
	this.show = function()
	{
		//Если уже отображается какое-либо окно, закрываем его
		if(lastPopup && lastPopup.isDisplayed())
		{
			lastPopup.hide();
		}

		if(this.isDisplayed())
		{
			return;
		}

		popupObject           = document.createElement('div');
		popupObject.className = 'popup-window';

		if(parameters.title)
		{
			var title       = document.createElement('span');
			title.className = 'title';
			title.innerHTML = parameters.title;

			if(parameters.closeCross)
			{
				var cross = createCloseCross();
				cross.addEventListener('click', popup.hide);
				title.appendChild(cross);
			}

			popupObject.appendChild(title);
		}

		var content       = document.createElement('span');
		content.className = 'content';

		if(typeof parameters.content === 'object')
		{
			content.appendChild(parameters.content);
		}
		else
		{
			content.innerHTML = parameters.content;
		}

		popupObject.appendChild(content);

		//Фон всплывающего окна
		if(parameters.overlay)
		{
			overlayObject           = document.createElement('div');
			overlayObject.className = 'popup-overlay';
			overlayObject.addEventListener('click', popup.hide);
			document.body.appendChild(overlayObject);
		}

		//Кнопки
		if(parameters.buttons)
		{
			var buttonsPanel       = document.createElement('span');
			buttonsPanel.className = 'buttons-panel';

			parameters.buttons.forEach(function(buttonData)
			{
				var button       = document.createElement('button');
				button.innerHTML = buttonData.text;

				//Нажатие кнопки
				button.addEventListener('click', function()
				{
					//Действие при нажании кнопки
					if(typeof buttonData.action === 'function')
					{
						buttonData.action();
					}

					if(buttonData.submitFormId)
					{
						var form = document.getElementById(buttonData.submitFormId);

						if(validateForm(form))
						{
							sendFormData(form, buttonData.responseHandler);
						}
						else
						{
							return;
						}
					}

					//Если buttonData.hide не задано или true, то кнопка будет закрывать окно
					if(buttonData.hide === undefined || buttonData.hide)
					{
						popup.hide();
					}
				});

				buttonsPanel.appendChild(button);
			});

			popupObject.appendChild(buttonsPanel);
		}

		document.body.appendChild(popupObject);
		updatePopupPosition();

		//Перемещаем всплывающее окно при изменении размера области просмотра
		window.addEventListener('resize', updatePopupPosition);

		displayed = true;
		lastPopup = this;
	};

	/**
	 * Скрывает всплывающее окно.
	 */
	this.hide = function()
	{
		if(typeof parameters.onHide === 'function')
		{
			parameters.onHide();
		}

		//Отключаем отслеживание изменения размера области просмотра для этого всплывающего окна
		window.removeEventListener('resize', updatePopupPosition);

		document.body.removeChild(popupObject);
		document.body.removeChild(overlayObject);
		popupObject   = null;
		overlayObject = null;

		displayed = false;
	};

	/**
	 * Возвращает true, если окно в данный момент времени отображается.
	 * @returns {boolean} True, если окно отображается.
	 */
	this.isDisplayed = function()
	{
		return displayed;
	};
}