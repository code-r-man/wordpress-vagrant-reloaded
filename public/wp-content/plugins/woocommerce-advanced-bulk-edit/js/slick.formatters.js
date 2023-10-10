/***
 * Contains basic SlickGrid formatters.
 * 
 * NOTE:  These are merely examples.  You will most likely need to implement something more
 *        robust/extensible/localizable/etc. for your use!
 * 
 * @module Formatters
 * @namespace Slick
 */

(function ($) {
	// register namespace
	$.extend(true, window, {
		'Slick': {
			'Formatters': {
				'PercentComplete': PercentCompleteFormatter,
				'PercentCompleteBar': PercentCompleteBarFormatter,
				'YesNo': YesNoFormatter,
				'Checkmark': CheckmarkFormatter,
				'Image': ImageFormatter,
				'Gallery': ImageGalleryFormatter,
				'ProductUrl':ProductUrlFormatter,
				'CustomAttr':CustomAttributesFormatter,
				'Title':TitleFormatter,
				'DateTime':DateTimeFormatter,
				'Url':UrlFormatter,
				'SyncWith':SyncWithFormatter,
				'AutocompleteProductSelector': AutocompleteProductSelectorFormatter,
				'MaybeNotEditable':MaybeNotEditableFormatter,
				'SerializedToCSV':SerializedToCSVFormatter
			}
		}
	});

	function TitleFormatter(row, cell, value, columnDef, dataContext) {
		if(columnDef !== undefined) {
			if(columnDef.id === 'post_title') {
				if(dataContext['product_type'] === 'grouped' && window.W3Ex !== undefined) {
					return value+'(<a href="javascript:;" data-item-grouped="'+dataContext['ID']+'" class="grouped-items" onclick="window.W3Ex.abemodule.handleGroupedItems('+dataContext['ID']+');">select group</a>)';
				}
				if(dataContext['product_type'] === 'variable') {
					return '<div class="showvarslink" data-id="'+dataContext['ID']+'">' + value + '</div>';
				}
				if(dataContext['post_type'] === 'product_variation') {
					return '<span style="color:#868686 !important;">&nbsp;&nbsp;&nbsp;' + value + '</span>';
				}
			}
		}

		if(value === undefined || value === null) {
			value = 'No Title';
		}
		value = value.replace('<', '&lt;');
		return value;
	}

	function PercentCompleteFormatter(row, cell, value, columnDef, dataContext) {
		if (value == null || value === '') {
			return '-';
		} else if (value < 50) {
			return '<span style="color:red;font-weight:bold;">' + value + '%</span>';
		} else {
			return "<span style='color:green'>" + value + "%</span>";
		}
	}

	function PercentCompleteBarFormatter(row, cell, value, columnDef, dataContext) {
		if (value == null || value === '') {
			return "";
		}

		var color;

		if (value < 30) {
			color = 'red';
		} else if (value < 70) {
			color = 'silver';
		} else {
			color = 'green';
		}

		return '<span class="percent-complete-bar" style="background:' + color + ';width:' + value + '%"></span>';
	}

	function YesNoFormatter(row, cell, value, columnDef, dataContext) {
		return value ? 'Yes' : 'No';
	}

	function CheckmarkFormatter(row, cell, value, columnDef, dataContext) {
		if(value === 'yes' || value === 'no') {
			return value === 'yes' ? '<img src="' + W3Ex.imagepath + 'images/tick.png">' : '';
		} else {
			return value === 'instock' ? '<img src="' + W3Ex.imagepath + 'images/tick.png">' : '';
		}
	}

	function ImageFormatter(row, cell, value, columnDef, dataContext) {
		if(dataContext['_thumbnail_id_val'] !== undefined && dataContext['_thumbnail_id_val'] !== '') {
			var dims = '32';
			if(W3Ex !== undefined && W3Ex._abe_rowheight !== undefined) {
				if(W3Ex._abe_rowheight === '2') {
					dims = '52';
				} else if(W3Ex._abe_rowheight === '3') {
					dims = '72';
				}
			}
			return '<img class="imageover" style="width:'+dims+'px;height:'+dims+'px;position:relative;top:-6px;" '+
				'src="' + dataContext['_thumbnail_id_val'] +
				'" data-original-src="' + dataContext['_thumbnail_id_original_image_url'] +
				'" data-prod-id="' + dataContext['ID'] +
				'" data-prod-title="' + dataContext['post_title'] +
				'">';
		}

		return '';
	}

	function ProductUrlFormatter(row, cell, value, columnDef, dataContext) {
		if(columnDef.id !== undefined) {
			if(columnDef.id === '_product_permalink') {
				if(dataContext['_product_permalink'] !== undefined && dataContext['_product_permalink'] !== '') {
					return '<a href="' + dataContext['_product_permalink'] + '" target="_blank">' + dataContext['_product_permalink'] + '</a>';
				}
			} else {
				if(dataContext['_product_adminlink'] !== undefined && dataContext['_product_adminlink'] !== '') {
					return "<a href='" + dataContext['_product_adminlink'] + "' target='_blank'>" + dataContext['_product_adminlink'] + "</a>";
				}
				return "";
			}
		}

		if(dataContext['_product_permalink'] !== undefined && dataContext['_product_permalink'] !== '') {
			return '<a href="' + dataContext['_product_permalink'] + '" target="_blank">' + dataContext['_product_permalink'] + "</a>";
		}

		return '';
	}

	function ImageGalleryFormatter(row, cell, value, columnDef, dataContext) {
		if(
			dataContext['_product_image_gallery_val'] !== undefined &&
			dataContext['_product_image_gallery_val'] !== '' &&
			dataContext['_product_image_gallery_val'] !== null)
		{
			var dims = '32';
			if(W3Ex !== undefined && W3Ex._abe_rowheight !== undefined) {
				if(W3Ex._abe_rowheight === '2') {
					dims = '52';
				} else if(W3Ex._abe_rowheight === '3') {
					dims = '72';
				}
			}
			var imgstr = '';
			var images = dataContext['_product_image_gallery_val'];
			if(images.indexOf('|') !== -1) {
				var res = images.split('|');
				if(res instanceof Array) {
					for(var i=0;i< res.length; i++) {
						imgstr+= '<img class="imageover" style="width:'+dims+'px;height:'+dims+'px;position:relative;top:-6px;" src="' + res[i] + '"'+
							' data-prod-id="' + dataContext['ID'] + '"' +
							' data-prod-title="' + dataContext['post_title'] + '"' +
							'>';
					}
					return imgstr;
				}
			}
			return '<img class="imageover" style="width:'+dims+'px;height:'+dims+'px;position:relative;top:-6px;" src="' + images + '"' +
			' data-prod-title="' + dataContext['post_title'] + '">';
		}

		if(dataContext['post_type'] === "product_variation") {
			return '<span class="ui-icon ui-icon-closethick"></span>';
		}

		return '';
	}

	function MaybeNotEditableFormatter(row, cell, value, columnDef, dataContext) {
		if(dataContext['post_type'] === "product_variation") {
			return "<span class='ui-icon ui-icon-closethick'></span>";
		} else {
			//return value;
			if (value) {
				return value.replace(/(<([^>]+)>)/gi, '');
			} else {
				return value;
			}
		}
	}

	function CustomAttributesFormatter(row, cell, value, columnDef, dataContext) {
		if(dataContext['_custom_attributes'] !== undefined && dataContext['_custom_attributes'] !== '') {
			var cust = dataContext['_custom_attributes'];
			var text = "";
			if(cust instanceof Array) {
				for(var i=0;i< cust.length; i++) {
					var customobj = cust[i];
					if(text === "") {
						text+= customobj.name + ": " + customobj.value;
					 } else {
						text+= "; " + customobj.name + ": " + customobj.value;
					 }
				}
			}

			return text;
		}
		return '';
	}

	function DateTimeFormatter(row, cell, value, columnDef, dataContext) {
		if (columnDef.field in dataContext) {
			return dataContext[columnDef.field];
		} else {
			return '';
		}
	}

	function SyncWithFormatter(row, cell, value, columnDef, dataContext) {
		if (columnDef.field in dataContext) {
			return dataContext[columnDef.field];
		} else {
			return '';
		}
	}

	function AutocompleteProductSelectorFormatter(row, cell, value, columnDef, dataContext) {
		if (columnDef.field in dataContext) {
			return dataContext[columnDef.field];
		} else {
			return '';
		}
	}

	function UrlFormatter(row, cell, value, columnDef, dataContext) {
		if (columnDef.field in dataContext) {
			return '<a href="' + dataContext[columnDef.field] + '" target="_blank">' + dataContext[columnDef.field] + '</a>';
		} else {
			return '';
		}
	}

	function SerializedToCSVFormatter(row, cell, value, columnDef, dataContext) {
		if (columnDef.field in dataContext) {
			var unserializedVal = wcabehelper.unserializeCSV(dataContext[columnDef.field]);
			return unserializedVal;
		} else {
			return '';
		}
	}

})(jQuery);
