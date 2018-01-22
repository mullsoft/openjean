// a class to implement the set abstract data type of strings
// individual elements are maintained as one single string separated by the separator character (default ',')
// this can be used (via getString()) in a query string, or split into an array
// individual elements can be added, removed and checked for inclusion
// note that the internal string begins and ends with the separator
function StringSet()
{
	this.value = ",";
	
	this.add = function(val)
	{
		var strval = val + ",";
		var pos = this.value.indexOf("," + strval);
		if (pos < 0)
		{
			this.value += strval;
		}
	};
	
	this.remove = function(val)
	{
		var strval = val + ",";
		var pos = this.value.indexOf("," + strval);
		if (pos >= 0)
		{
			this.value = this.value.substr(0, pos) + this.value.substr(pos + strval.length);
		}
	};
	
	this.getString = function()
	{
		return this.value.substr(1, this.value.length - 2);
	};
	
	this.getArray = function()
	{
		return this.value.substr(1, this.value.length - 2).split(',');
	};

	this.contains = function(val)
	{
		var strval = "," + val + ",";
		var pos = this.value.indexOf(strval);
		return pos >= 0;
	};
	
	this.length = function()
	{
		return this.value.match(/,/g).length - 1;
	};
	
	this.clear = function()
	{
		this.value = ",";
	};
	
	this.isempty = function()
	{
		return this.value.length == 1;
	};
	
	this.first = function()
	{
		var ret = null;
		if (this.value.length > 1)
		{
			var cma = this.value.indexOf(',', 1);
			if (cma > 0)
			{
				ret = this.value.substr(1, cma - 1);
			}
		}
		return ret;
	};
	
	this.last = function()
	{
		var ret = null;
		if (this.value.length > 1)
		{
			var val = this.value.substr(1, this.value.length - 2);
			var cma = this.value.lastIndexOf(',');
			if (cma > 0)
			{
				ret = this.value.substr(cma + 1);
			}
			else
			{
				ret = val;
			}
		}
		return ret;
	};
}

function stringStartsWith (string, prefix)
{
    return string.slice(0, prefix.length) == prefix;
}

function stringStartsWithIgnoreCase(string, prefix)
{
	return stringStartsWith(string.toLowerCase(), prefix.toLowerCase());
}

function stringEndsWith (string, suffix)
{
    return suffix == '' || string.slice(-suffix.length) == suffix;
}

function stringEndsWithIgnoreCase(string, suffix)
{
	return stringEndsWith(string.toLowerCase(), suffix.toLowerCase());
}

