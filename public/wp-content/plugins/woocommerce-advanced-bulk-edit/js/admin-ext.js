jQuery('#bulk_regular_price_round, #bulk_sale_price_round').change(function()
{
    var roundType = jQuery(this).val();
    var info = jQuery('#'+this.dataset?.infoBlockId);
    if (!info || info === 'undefined') {
        return;
    }

    switch (roundType) {
        case 'noround': info.html('Example: <strong>123.1234</strong> &#x2192;  <strong>123.12</strong> &#9670; <strong>57.4561</strong> &#x2192;  <strong>57.45</strong>');
            break;
        case 'excelround': info.html('Example: <strong>123.1234</strong> &#x2192;  <strong>123</strong> &#9670; <strong>57.5614</strong> &#x2192;  <strong>58</strong>');
            break;
        case 'excelround1': info.html('Example: <strong>123.1234</strong> &#x2192;  <strong>123.1</strong> &#9670; <strong>57.5614</strong> &#x2192;  <strong>57.6</strong>');
            break;
        case 'excelround01': info.html('Example: <strong>123.1234</strong> &#x2192;  <strong>123.12</strong> &#9670; <strong>57.4561</strong> &#x2192;  <strong>57.46</strong>');
            break;
        case 'roundup100': info.html('Example: <strong>123.1234</strong> &#x2192;  <strong>200</strong>  &#9670; <strong>57.4561</strong> &#x2192;  <strong>100</strong>');
            break;
        case 'roundup10': info.html('Example: <strong>123.1234</strong> &#x2192;  <strong>130</strong>  &#9670; <strong>57.4561</strong> &#x2192;  <strong>60</strong>');
            break;
        case 'roundup': info.html('Example: <strong>123.1234</strong> &#x2192;  <strong>124</strong>  &#9670; <strong>57.4561</strong> &#x2192;  <strong>58</strong>');
            break;
        case 'roundup1': info.html('Example: <strong>123.1234</strong> &#x2192;  <strong>123.2</strong>  &#9670; <strong>57.4561</strong> &#x2192;  <strong>57.5</strong>');
            break;
        case 'roundup01': info.html('Example: <strong>123.1234</strong> &#x2192;  <strong>123.13</strong>  &#9670; <strong>57.4561</strong> &#x2192;  <strong>57.46</strong>');
            break;
        case 'rounddown01': info.html('Example: <strong>123.1234</strong> &#x2192;  <strong>123.12</strong>  &#9670; <strong>57.4561</strong> &#x2192;  <strong>57.45</strong>');
            break;
        case 'rounddown1': info.html('Example: <strong>123.1234</strong> &#x2192;  <strong>123.1</strong>  &#9670; <strong>57.4561</strong> &#x2192;  <strong>57.4</strong>');
            break;
        case 'rounddown': info.html('Example: <strong>123.1234</strong> &#x2192;  <strong>123</strong>  &#9670; <strong>57.4561</strong> &#x2192;  <strong>57</strong>');
            break;
        case 'rounddown10': info.html('Example: <strong>123.1234</strong> &#x2192;  <strong>120</strong>  &#9670; <strong>57.4561</strong> &#x2192;  <strong>50</strong>');
            break;
        case 'rounddown100': info.html('Example: <strong>123.1234</strong> &#x2192;  <strong>100</strong>  &#9670; <strong>57.4561</strong> &#x2192;  <strong>0</strong>');
            break;

        default: info.text('n/a');
    }
});
