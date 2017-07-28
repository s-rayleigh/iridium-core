addFormValidation(document.getElementById('reg_form'), responseHandler);
addFormValidation(document.getElementById('login_form'), responseHandler);

function responseHandler(data)
{
	if(data === 0)
	{
		window.location = "index.php";
	}
}