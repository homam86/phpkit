var _log = function(message) {
    console.log(message);
}

Insider = {};

/**
 * Return TRUE if the given object is an array.
 */
Insider.isArray = function(object) {
    return Object.prototype.toString.call( object ) === '[object Array]';
};

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
            //alert('*** content is array');
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

Insider.Overlay = function(content) {
    this.fixed = false;
    this.width = '600px';
    this.height = '500px';
    this.borderRadius = '15px';
    this.content = content;
    
    this._init();
};

Insider.Overlay.prototype = {
    constructor: Insider.Overlay,
    
    _init : function () {
        var attr_lay = {
            display: 'none',
            position: 'fixed', left: '0px',  top: '0px',
            width:'100%',  height: this.clientHeight()+'px',
            'text-align':'center', 'z-index': 1000,
            'background-color' : '#777', opacity: 0.3
        };
        
        var attr_holder = {
            display: 'none',
            left: '0px', top: '0px', width:'100%',
            'z-index': 1111,
            height: this.clientHeight()+'px',
            'position' : this.fixed ? 'fixed' : 'absolute'
        };
        
        var attr_close = {
            display: 'block',
            right: '5px', top: '5px',
            width:'32px',
            height:'32px',
            padding: '2px',
            'font-weight': 'bold',
            'font-size' : '26px',
            'background' : 'url(images/widgets/close/close32.png) no-repeat',
            'z-index': 1111,
            'position' : 'absolute'
        };
        
        var attr_div = {
            width:this.width,
            height:this.height,
            padding:'15px',
            margin: '100px auto',
            background: '#fff',
             'position' : 'relative',
            'border-radius': this.borderRadius,
            'box-shadow' : '#113 0 0 20px',
        };
        
        
        var html = Insider.html([
            {
                tag: 'div',
                id: 'insider-overlay',
                style: attr_lay
            },
            {
                tag: 'div',
                id: 'insider-overlay-holder',
                style: attr_holder,
                content: {
                    tag:'div',
                    style: attr_div,
                    content: [
                        {
                            tag:'style',
                            content: '#insider_overlay_close a:hover {color:red;text-decoration:none;} #insider_overlay_close:hover{color:red;background:url("images/widgets/close/close32_h.png")  no-repeat;}'
                        },
                        {
                            id: 'insider_overlay_close',
                            tag:'div',
                            style: attr_close,
                            onclick: "$('#insider-overlay').hide();$('#insider-overlay-holder').hide('slow');",
                            content: '<a href="#">&#x26CC</a>'
                        },
                        this.content
                    ]
                }
            }
        ]);
        
        $(html).appendTo('body');
    },

    show: function() {
        $('#insider-overlay').show();
        //('#insider-overlay-holder').show();
        $('#insider-overlay-holder').fadeIn(700);
    },

    hide: function() {
        $('#insider-overlay').hide();
        $('#insider-overlay-holder').hide('slow');
    },
    
    clientHeight: function() {
        return $(document).height();
        return false
            || window.innerHeight
            || document.documentElement.clientHeight
            || document.body.clientHeight;  
    },
    
    close : function() {
        //$('#insider-overlay').remove();
        //$('#insider-overlay-holder').remove();
    },
    
    position : function() {
        alert('position');
        
    }
};





$(document).ready(function(){
    
    //var overlay = new Insider.Overlay();
    //overlay.display();
    //overlay.close();
    
//   
//   var html, attr = {
//        id:'myid',
//        style: {
//            color: '#335599',
//            'border' : '1px solid red'
//        }
//    };
//    
//    html = Insider.html('h2', /*null*/['This is a string'], attr);
//
//    //attr.tag = 'h1';
//    //attr.content = 'This is a super string';
//    //html = Insider.html(attr);
//
//    alert(html);
//
//    $('#info_builder .quizizer').html(html);
//    
});
