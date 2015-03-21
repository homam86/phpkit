function UploadWidget(config) {
    this.__$ = jQuery;
    this.__id = null;
    this.__url = '#';
    this.__callback = null;
    
    for(var i in config)
        this['__'+i] = config[i];
    
    this.init();
}

UploadWidget.prototype = {
    id: function(name) {
        if (name)
            return this.__id+'_'+name;
        return this.__id;
    },
    
    url: function() {return this.__url; },
    
    $$: function(id, callIdMethod) {
        return this.__$('#'+(callIdMethod ? this.id(id) : id));  
    },
    
    fireUploadedEvent: function(data) {
        if(this.__callback)
            this.__callback(data);
    },
    
    init: function() {
        var _this = this;
        var $ = this.__$;
        var url = this.url();
        var $$ = this.$$.bind(this);
        
        $$('form', true).on('submit', function(e){
            e.preventDefault();
            $$('message', true).empty();
            $$('loading', true).show();
            
            $.ajax({
                url         : url,
                type        : 'POST',
                dataType    : 'json',
                data        : new FormData(this),
                contentType : false,
                cache       : false,
                processData : false,
                success     : function(data) {
                    var html = '';
                    $$('loading', true).hide();
                    
                    if (data.status=='OK') {
                        $$('message', true).html(Insider.html({
                            tag: 'span',
                            'class' : 'success',
                            content: [
                                tt('File loaded successfully...'),
                                {
                                    tag: 'a',
                                    'href' : data.url,
                                    content: data.url                                        
                                }
                            ]
                        }));
                        _this.fireUploadedEvent(data);
                    }
                    else {
                        $$('message', true).html(Insider.html({
                            tag: 'span',
                            'class' : 'error',
                            content: data.message
                        }));
                    }
                    
                }
            });
            
        });
    }
}
