window.addEventListener('load', function()
{
	document.querySelector('.exit-link').addEventListener('click', function(event)
	{
		event.preventDefault();

		sendRequest('POST', 'index.php?op=logout', null, function(result)
		{
			if(result !== 0) { return; }
			goto('index.php');
		});
	});
});