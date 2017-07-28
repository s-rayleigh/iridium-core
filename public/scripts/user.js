window.addEventListener('load', function()
{
	var subscribtionsDataList = new DataList({
		opUrl: 'index.php?op=eslist.channelsList',
		loadParameters: {subscriptions: 1, user_id: userId},
		onLoad: function(result)
		{
			if(result === 0)
			{
				var noSubs = document.createElement('p');
				noSubs.appendChild(document.createTextNode("Пользователь скрыл свои подписки."));
				document.getElementById('subs-count').innerHTML = '';
				document.getElementById('channels-list-container').appendChild(noSubs);
			}

			if(!result.list)
			{ return; }

			document.getElementById('subs-count').innerHTML = '(' + result.count + ')';

			var subscriptionsList       = document.createElement('div');
			subscriptionsList.className = 'channels-list';

			result.list.forEach(function(channelData)
			{
				subscriptionsList.appendChild(createChannelObject(channelData));
			});

			document.getElementById('channels-list-container').appendChild(subscriptionsList);
		}
	});

	subscribtionsDataList.load();

	//Кнопка выхода
	var logoutButton = document.querySelector('.logout-link');

	if(logoutButton)
	{
		document.querySelector('.logout-link').addEventListener('click', function(e)
		{
			e.preventDefault();

			(new Popup({
				title: 'Выход',
				content: 'Вы действительно хотите выйти?',
				overlay: true,
				closeCross: false,
				buttons: [{text: 'Передумал'}, {
					text: 'Конечно', action: function()
					{
						sendRequest('POST', 'index.php?op=logout', null, function(result)
						{
							if(result !== 0)
							{ return; }
							goto('index.php');
						});
					}
				}]
			})).show();
		});
	}
});