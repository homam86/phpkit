var _log = function(title, message) {
    if (!message) {
        message = title;
        title = null;
    }
    
    if (title) console.log(">>>>>>> " + title);
    console.log(message);
    if (title) console.log("<<<<<<<");
}

Insider = {};

Insider.UID = sQuery.cookie('spUID');
Insider.partner = (typeof PARTNER_NAME != 'undefined')
    ? PARTNER_NAME                 /* defined in (web/api/frame.php) */
    : sQuery.cookie('partnerName')  /* try to get partnerName from cookie*/
;  
Insider.apiUrl = 'http://'+Insider.partner+'.api.sociaplus.com/';
Insider.panelUrl = 'http://'+Insider.partner+'.panel.sociaplus.com/';

/**
 * Return TRUE if the given object is an array.
 */
Insider.isArray = function(object) {
    return Object.prototype.toString.call( object ) === '[object Array]';
};

Insider.dictionary = (function(){
    var _category;
    var _dictionary = {};
    
    return {
        init: function(dictionary) {
            _dictionary = dictionary;
        },
        
        append: function(category, data) {
            _log('Insider.Dictionary: category <'+category+'>');
            _category = category;
            for(var i in data)
                _dictionary[i] = data[i];
        },
        
        log: function(){
            _log('Insider Dictionary <category: '+_category+'>:', _dictionary);
        },
        
        sendRequest: function(string)
        {
            //console.log('Dictionary new message:', string);
            $.ajax({
                url:Insider.panelUrl+'ajax.php?op=translate',
                type: 'POST',
                dataType: 'json',
                data: {category: _category, message:string},
                success: function(data){
                    //console.log(data);
                }
            });
        },
        
        t: function(string, params) {
            params = params || {};
            var t = _dictionary[string] || null;
            if (!t) {
                
                this.sendRequest(string);
                t = string;
            }
            string = t;
            for(var p in params)
                string = string.replace(p, params[p]);
                
            return string;
        }
    };
})();

Insider.appendToDic = Insider.dictionary.append;
var tt = Insider.dictionary.t.bind(Insider.dictionary);
/**
 * Return 'true' or 'false' debending on the given value
 */
Insider.boolean = function(value) {
    switch (value) {
    case 'false':
    case 'False':
    case 'FALSE':
    case '0':
    case 0:
    case false:
    case null:
        return false;
        
    }
    return true;
};
/**
 * Conver an object to string, This is an example how can 'style' field
 * be an object: {
 *     padding:'10px',
 *     'margin-top': '7px',
 *     'border': '1px solid blue'
 * }
 *
 * kv: the seperator between the name of the field and its value.
 * ff: the seperator between the two fields.
 */
Insider.serialize = function (object, kv, ff, quote) {
    var css = '';
    if (!kv) kv = ' : ';
    if (!ff) kv = '\n';
    if (typeof object == 'object') {
        for(var i in object) {
            css += i + kv + (quote?'"':'') + object[i] + (quote?'"':'') + ff;
        }
    }
    else
        css = object;//.toString();
    return css;
};

/**
 * Return html string that represents the giving arguments. We can supply the data
 * in three fromes:
 *  - object only: in this case, the givin argument can be in the form:
 *        data = {
 *            tag: 'div',
 *            style: 'color:blue',
 *            content: 'Welcome to my HTML generator',
 *            'class': 'label',
 *            'id' : 'header'
 *        };
 *  - string, object, the first string is the tag, the second object like the
 *    above example.
 *  - string, string OR object OR array, object, here, all the givin object will be considered as
 *    HTML attributes for the giving HTML tag.
 * The 'data' can be primitive
 * type (int, float, string) or it can be an object that represents and HTML element,
 * so, it contains tag, contents and attributes, ex:

 * The 'content' attribute can be more completected. It can be an object with the
 * same structure or an array of objects, so the functio will be called recursivly
 * to generated HTML string for the inner objects.
 * 
 */
Insider.html = function(tag, content, attr) {
    /**
     * Handle limit-case (html(5), or html('hiiii') )
     */
    if (typeof tag != 'object' && !(content || attr))
        return tag;
    
    else if (Insider.isArray(tag)) {
        var html = '';
        for(var i in tag)
            html += Insider.html(tag[i]);
        return html;
    }
    
    var tmp;
    if (typeof tag == 'object') {
        attr = tag;
        tag = attr.tag || 'span';
        content = attr.content || null;
        //_log('HTML, object: '+tag+', '+content+', '+attr.toString());
        delete attr.tag;
        delete attr.content;
    }
    else if (content && typeof content == 'object') {
        attr = attr || [];          // make sure the 'attr' is not null.
        content = content || [];    // make sure the 'content' is not null.
        if (!Insider.isArray(content)){
            attr = content;
            content = attr.content || null;
            delete attr.content;
        }
    }
    else
        attr = attr || [];          // make sure the 'attr' is not null.
    

        
    var html = '';
  
    // make sure that 'style' is a string
    attr.style = Insider.serialize(attr.style || '', ':', ';', false);
    if (!attr.style.length) delete attr.style;
    attr = Insider.serialize(attr, '=', ' ', true);
    
    html = '<'+tag;
    if (attr.length)
        html += ' ' + attr;
    if (content) {
        if (Insider.isArray(content)) {
            tmp = content;
            content = '';
            for(var i in tmp)
                content += Insider.html(tmp[i]);
        }
        else
            content = Insider.html(content);
        html += '>'+content+'</'+tag+'>';
    }
    else {
        if (['input', 'hr', 'br', 'img'].indexOf(tag)>=0)
            html += '/>'
        else
            html += '></'+tag+'>';
    }
    
    return html;
    
};