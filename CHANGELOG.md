## 0.2.1-indev
- Added `HTTP::GetUserLangCode()` method.
- Register default filter instead of throwing the exception in the `HTTP` class.
- Language module:
	- Added caching.
	- Added inline commands.
	- Added language auto detection and cookies support.
	
## 0.2-indev
- Added initial version of the language module.
## 0.1.1-indev
- HTTP class:
	- Added type hints for the methods.
	- Updated comments.
	- Removed deprecated `SendRequest` method.
- Added `Core/Tools` namespace:
	- Added `ArrayTools` class.
	- Added `StringTools` class.
	- Moved functions `StartsWith` and `EndsWith` to the `StringTools` class.