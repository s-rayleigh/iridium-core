window.addEventListener('load', function()
{
	var channelsList = document.getElementById('channels-list');

	var channelsDataList = new DataList({
		opUrl: 'index.php?op=eslist.channelsList',
		usePageNavigation: true,
		onLoad: function(result)
		{
			if(!result.list)
			{ return; }

			var channelsObjs = channelsList.getElementsByClassName('plank');

			while(channelsObjs.length > 0)
			{
				channelsList.killChild(channelsObjs[0], true);
			}

			document.getElementById('pages').innerHTML = (result.page + 1) + '/' + result.pages;

			result.list.forEach(function(channelData)
			{
				channelsList.appendChild(createChannelObject(channelData));
			});
		}
	});

	channelsDataList.load();

	//Кнопки перехода по страницам

	document.getElementById('first-page').addEventListener('click', function()
	{
		channelsDataList.firstPage();
		channelsDataList.load();
	});

	document.getElementById('prev-page').addEventListener('click', function()
	{
		channelsDataList.prevPage();
		channelsDataList.load();
	});

	document.getElementById('next-page').addEventListener('click', function()
	{
		channelsDataList.nextPage();
		channelsDataList.load();
	});

	document.getElementById('last-page').addEventListener('click', function()
	{
		channelsDataList.lastPage();
		channelsDataList.load();
	})
});