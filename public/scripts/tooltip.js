/**
 * Создание подсказок при наведении мыши
 * @module tooltip
 */

window.addEventListener('load', function()
{
	var ttElements = document.getElementsByClassName('tooltip');

	for(var i = 0; i < ttElements.length; i++)
	{
		createTooltip(ttElements[i]);
	}
});

function createTooltip(element, tooltipContent, position, posMargin)
{
	var tooltipObj;

	if(!element)
	{
		console.error('Не был передан элемент при создании подсказки!');
		return;
	}

	tooltipContent = tooltipContent || element.dataset.tooltip;
	position       = position || element.getAttribute('data-tt-pos') || undefined;

	function updatePosition(e)
	{
		var positionMargin = posMargin || parseInt(element.getAttribute('data-tt-pos-margin')) || 10;

		if(position !== undefined)
		{
			var elementRect = element.getBoundingClientRect(),
				top         = elementRect.top,
				left        = elementRect.left;

			switch(position)
			{
				case 'top':
					top -= tooltipObj.offsetHeight + positionMargin;
					left += (element.offsetWidth - tooltipObj.offsetWidth) / 2;
					break;
				case 'bottom':
					top += element.offsetHeight + positionMargin;
					left += (element.offsetWidth - tooltipObj.offsetWidth) / 2;
					break;
				case 'left':
					left -= tooltipObj.offsetWidth + positionMargin;
					top += (element.offsetHeight - tooltipObj.offsetHeight) / 2;
					break;
				case 'right':
					left += element.offsetWidth + positionMargin;
					top += (element.offsetHeight - tooltipObj.offsetHeight) / 2;
					break;
			}

			tooltipObj.style.top  = floatToInt(top) + 'px';
			tooltipObj.style.left = floatToInt(left) + 'px';

			return;
		}

		tooltipObj.style.top  = (e.pageY + 15) + 'px';
		tooltipObj.style.left = (e.pageX + 15) + 'px';
	}

	function removeTooltipObject()
	{
		if(document.body.contains(tooltipObj))
		{
			document.body.removeChild(tooltipObj);
		}
	}

	element.addEventListener('mouseenter', function(e)
	{
		tooltipObj           = document.createElement('div');
		tooltipObj.className = 'tooltip-object' + (position === undefined ? '' : ' ' + position);
		tooltipObj.innerHTML = tooltipContent;
		document.body.appendChild(tooltipObj);
		updatePosition(e);
	});

	element.addEventListener('mousemove', updatePosition);
	element.addEventListener('mouseleave', removeTooltipObject);
	element.addEventListener('click', removeTooltipObject);
}