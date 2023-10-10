
function setProcessingVisualState(elem)
{
	elem.css('position','relative').append('<div class="showajax"></div>');
	jQuery('.showajax').css({
		left:'15px'
	});
}
function setProcessingCompletedVisualState()
{
	jQuery('.showajax').remove();
}

var wcabehelper = {
	serializeCSV: function(valStr)
	{
		var arr = valStr.split(',');
		var ser = 'a:' + String(arr.length) + ':{';
		var i = 0;
		arr.forEach(function (item) {
			item = item.trim();
			ser += 'i:' + String(i) + ';s:' + String(item.length) + ':"' + item + '";';
			i++;
		});
		ser += '}';

		return ser;
	},

	unserializeCSV: function(valStr)
	{
		//var myString = 'a:4:{i:0;s:6:"105634";i:1;s:6:"105640";i:2;s:6:"105722";i:3;s:6:"105716";}';
		if (valStr === 'a:1:{i:0;s:0:"";}') {
			return '';
		}
		var myRegexp = /:"(?<id>\d+)";/g;
		var m;
		var result = '';

		do {
			m = myRegexp.exec(valStr);
			if (m) {
				result += result.length > 0 ? ',' + m[1] : m[1];
			}
		} while (m);
		return result.length ? result : valStr;
	},
};
