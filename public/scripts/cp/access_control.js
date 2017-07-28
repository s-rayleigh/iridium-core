window.addEventListener('load', function() 
{
	var getAccessForm = document.getElementById('get-access-form'),
		disableAccessLink = document.getElementById('disable-access');

	if(getAccessForm !== null)
	{
		addFormValidation(getAccessForm, responseHandler);
	}
	else if(disableAccessLink !== null)
	{
		disableAccessLink.addEventListener('click', function(event)
		{
			event.preventDefault();
			sendRequest('POST', 'index.php?op=cp.unauthorize', null, responseHandler);
		});
	}
});

function responseHandler(data)
{
	if(data === 0)
	{
		window.location = "index.php?page=cp.index";
	}
}

	
