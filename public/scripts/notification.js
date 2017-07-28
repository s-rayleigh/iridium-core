
/**
 * @constructor
 * @param {object} newParams Параметры уведомления
 */
function Notification(newParams)
{
	var params = {
		content: '',
		position: 'top right',
		showTime: 2000,
		closeCross: true,
		styleClass: 'info'
	};

	updateObject(params, newParams);

	var domObj,
		notification		= this,
		verticalMargin		= '5px',
		horizontalMargin	= '5px';

	function build()
	{
		domObj = document.createElement('div');

		domObj.className = 'notification block-shadow ' + params.styleClass;

		if(params.closeCross)
		{
			var crossObj = createCloseCross();
			domObj.appendChild(crossObj);

			crossObj.addEventListener('click', function()
			{
				notification.hide();
			});
		}

		if(typeof params.content === 'object') //Если объект, то просто добавляем
		{
			domObj.appendChild(params.content);
		}
		else //Если текст, то innerHTML
		{
			var text = document.createElement('span');
			text.innerHTML = params.content;
			domObj.appendChild(text);
		}
	}

	this.show = function()
	{
		if(!domObj)
		{
			build();
		}

		var posArr = params.position.split(' ');

		document.body.appendChild(domObj);

		switch(posArr[0])
		{
			case 'top':
				domObj.style.top = verticalMargin;
				break;
			case 'bottom':
				domObj.style.bottom = verticalMargin;
				break;
			case 'center':
				domObj.style.top = (document.body.offsetHeight / 2 - domObj.offsetHeight / 2) + 'px';
				break;
		}

		switch(posArr[1])
		{
			case 'left':
				domObj.style.left = horizontalMargin;
				break;
			case 'right':
				domObj.style.right = horizontalMargin;
				break;
			case 'center':
				domObj.style.left = (document.body.offsetWidth / 2 - domObj.offsetWidth / 2) + 'px';
				break;
		}

		Animation.animate({
			obj: domObj,
			duration: 150,
			timeFunction: 'quad',
			onEnd: function()
			{
				setTimeout(function()
				{
					Animation.animate({
						obj: domObj,
						duration: 300,
						onEnd: function()
						{
							notification.hide();
						},
						animation: 'fadeOut'
					});
				}, params.showTime);
			}
		});
	};

	this.hide = function()
	{
		if(document.body.contains(domObj))
		{
			document.body.removeChild(domObj);
		}
	};
}