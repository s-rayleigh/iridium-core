/**
 * Кнопка подписки на канал.
 * @module subscribe_button
 */

window.addEventListener('load', function()
{
	var subscribeContainer = document.getElementById('subscribe-container');

	if(subscribeContainer)
	{
		subscribeContainer.appendChild(createSubscribeButton(channelId, subscribed));
	}
});

function createSubscribeButton(channelId, subscribed)
{
	var firstUnsubscibeObj   = document.createElement('div'),
		secondUnsubscribeObj = document.createElement('div'),
		subscribeObj         = document.createElement('div'),
		checkIcon            = document.createElement('img'),
		cancelIcon           = document.createElement('img'),
		subscribeIcon        = document.createElement('img');

	checkIcon.src     = glImagesPath + '/check_icon_cleaned.svg';
	cancelIcon.src    = glImagesPath + '/x_icon_cleaned.svg';
	subscribeIcon.src = glImagesPath + '/message.svg';

	subscribeObj.appendChild(subscribeIcon);
	subscribeObj.appendChild(document.createTextNode('Подписаться'));

	firstUnsubscibeObj.appendChild(checkIcon);
	firstUnsubscibeObj.appendChild(document.createTextNode('Подписан'));
	secondUnsubscribeObj.appendChild(cancelIcon);
	secondUnsubscribeObj.appendChild(document.createTextNode('Отписаться'));

	var subscribeToggleButton = createToggleButton({
		activeClass: 'cancel',
		activated: subscribed,
		activeContent: [firstUnsubscibeObj, secondUnsubscribeObj],
		unactiveContent: subscribeObj,
		onTurnOn: function()
		{
			sendRequest('POST', 'index.php?op=subscribe', { id: channelId }, function(result)
			{
				if(result !== 0)
				{
					//Откат кнопки при неудаче
					subscribeToggleButton.tbTurnOff(false);
					return;
				}

				(new Notification({content: 'Вы подписались на канал. Теперь вы будете получать уведомления о выходе новых эпизодов.'})).show();
			});
		},
		onTurnOff: function()
		{
			sendRequest('POST', 'index.php?op=subscribe', {id: channelId, unsubscribe: 1}, function(result)
			{
				if(result !== 0)
				{
					//Откат кнопки при неудаче
					subscribeToggleButton.tbTurnOn(false);
					return;
				}

				(new Notification({content: 'Вы отписались от обновлений канала.'})).show();
			});
		}
	});

	subscribeToggleButton.appendChild(firstUnsubscibeObj);
	subscribeToggleButton.appendChild(secondUnsubscribeObj);
	subscribeToggleButton.appendChild(subscribeObj);

	return subscribeToggleButton;
}